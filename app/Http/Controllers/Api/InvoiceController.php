<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Support\InvoiceDocuments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in($this->statuses())],
            'customer_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Invoice::query()
            ->with(['customer', 'items'])
            ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return InvoiceResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $data = $this->validatedData($request);
        $customer = Customer::findOrFail($data['customer_id']);
        $this->authorize('view', $customer);

        $invoice = DB::transaction(function () use ($data, $request): Invoice {
            $invoiceData = $this->invoiceAttributes($data);
            $invoiceData += $this->taxSnapshotAttributes($data['customer_id']);
            $invoiceData['user_id'] = $request->user()->id;

            $invoice = Invoice::create($invoiceData);
            $this->syncItems($invoice, $data['items']);

            return $invoice;
        });

        return (new InvoiceResource($invoice->load(['customer', 'items'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice->load(['customer', 'items']));
    }

    public function update(Request $request, Invoice $invoice): InvoiceResource
    {
        $this->authorize('update', $invoice);

        $data = $this->validatedData($request, $invoice);
        $customer = Customer::findOrFail($data['customer_id']);
        $this->authorize('view', $customer);

        DB::transaction(function () use ($invoice, $data): void {
            $attributes = $this->invoiceAttributes($data, $invoice);
            if ((int) $invoice->customer_id !== (int) $data['customer_id'] || ($invoice->tax_id === null && $invoice->branch === null)) {
                $attributes += $this->taxSnapshotAttributes($data['customer_id']);
            }

            $invoice->update($attributes);
            $this->syncItems($invoice, $data['items']);
        });

        return new InvoiceResource($invoice->refresh()->load(['customer', 'items']));
    }

    public function destroy(Invoice $invoice): Response
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->noContent();
    }

    private function validatedData(Request $request, ?Invoice $invoice = null): array
    {
        $invoiceId = $invoice?->id;

        return $request->validate([
            'number' => ['nullable', 'string', 'max:255', Rule::unique('invoices', 'number')->ignore($invoiceId)],
            'document_type' => ['nullable', Rule::in(InvoiceDocuments::types())],
            'customer_id' => ['required', 'integer', Rule::exists(Customer::class, 'id')],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'status' => ['required', Rule::in($this->statuses())],
            'currency' => ['required', 'string', 'size:3'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001', 'max:999999.999999'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurring_cycle' => [Rule::requiredIf($request->boolean('is_recurring')), 'nullable', Rule::in(['weekly', 'monthly', 'yearly'])],
            'next_recurring_date' => [Rule::requiredIf($request->boolean('is_recurring')), 'nullable', 'date', 'after_or_equal:invoice_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.wht_rate' => ['nullable', 'numeric', Rule::in(InvoiceItem::WHT_RATES)],
        ]);
    }

    private function invoiceAttributes(array $data, ?Invoice $invoice = null): array
    {
        $subtotal = collect($data['items'])->sum(
            fn (array $item): float => round((float) $item['quantity'] * (float) $item['unit_price'], 2)
        );
        $whtTotal = collect($data['items'])->sum(function (array $item): float {
            $amount = round((float) $item['quantity'] * (float) $item['unit_price'], 2);

            return $this->calculateWithholdingTax($amount, (float) ($item['wht_rate'] ?? 0));
        });
        $discount = (float) ($data['discount'] ?? 0);
        $taxTotal = (float) ($data['tax_total'] ?? 0);
        $total = max(round($subtotal - $discount + $taxTotal - $whtTotal, 2), 0);
        $amountPaid = min((float) ($data['amount_paid'] ?? $invoice?->amount_paid ?? 0), $total);

        $documentType = InvoiceDocuments::normalize($data['document_type'] ?? $invoice?->document_type);

        return [
            'number' => $data['number'] ?? InvoiceDocuments::nextNumber($documentType, $data['invoice_date']),
            'document_type' => $documentType,
            'customer_id' => $data['customer_id'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'wht_total' => $whtTotal,
            'discount' => $discount,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'balance_due' => max(round($total - $amountPaid, 2), 0),
            'status' => $data['status'],
            'currency' => strtoupper($data['currency']),
            'exchange_rate' => (float) $data['exchange_rate'],
            'notes' => $data['notes'] ?? null,
            'is_recurring' => (bool) ($data['is_recurring'] ?? false),
            'recurring_cycle' => ! empty($data['is_recurring']) ? ($data['recurring_cycle'] ?? null) : null,
            'next_recurring_date' => ! empty($data['is_recurring']) ? ($data['next_recurring_date'] ?? null) : null,
        ];
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $amount = round($quantity * $unitPrice, 2);
            $whtRate = (float) ($item['wht_rate'] ?? 0);

            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => $amount,
                'wht_rate' => $whtRate,
                'wht_amount' => $this->calculateWithholdingTax($amount, $whtRate),
            ]);
        }
    }

    private function calculateWithholdingTax(float $amount, float $rate): float
    {
        if ($amount <= 0 || $rate <= 0) {
            return 0.0;
        }

        return round($amount * ($rate / 100), 2);
    }

    private function taxSnapshotAttributes(int|string $customerId): array
    {
        $customer = Customer::find($customerId);

        return [
            'tax_id' => $customer?->tax_id,
            'branch' => $customer?->branch,
        ];
    }

    private function statuses(): array
    {
        return ['Draft', 'Sent', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'];
    }
}
