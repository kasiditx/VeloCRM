<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Livewire\Concerns\HandlesCustomFields;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use App\Models\TaxTemplate;
use App\Notifications\InvoiceSentNotification;
use App\Support\SafeNotifier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;

class InvoiceForm extends Component
{
    use AuthorizesRequests, HandlesCustomFields;

    public $invoiceId;

    public $number;

    public $customer_id;

    public $invoice_date;

    public $due_date;

    public $status = 'Draft';

    public string $currency = 'USD';

    public $exchange_rate = 1;

    public $items = [];

    public $tax_id;

    public $discount = 0;

    public $notes;

    public $is_recurring = false;

    public $recurring_cycle = 'monthly';

    public $next_recurring_date;

    public $subtotal = 0;

    public $tax_total = 0;

    public $total = 0;

    protected $rules = [
        'number' => 'required|unique:invoices,number',
        'customer_id' => 'required|exists:customers,id',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:invoice_date',
        'status' => 'required|in:Draft,Sent,Partially Paid,Paid,Overdue,Cancelled',
        'currency' => 'required|string|size:3',
        'exchange_rate' => 'required|numeric|min:0.000001|max:999999.999999',
        'items.*.description' => 'required',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'tax_id' => 'nullable|exists:tax_templates,id',
        'discount' => 'nullable|numeric|min:0',
        'is_recurring' => 'boolean',
        'recurring_cycle' => 'nullable|in:weekly,monthly,yearly',
        'next_recurring_date' => 'nullable|date|after_or_equal:invoice_date',
    ];

    public function mount($invoiceId = null)
    {
        if ($invoiceId) {
            $invoice = Invoice::with('items')->findOrFail($invoiceId);
            $this->authorize('update', $invoice);

            $this->invoiceId = $invoice->id;
            $this->number = $invoice->number;
            $this->customer_id = $invoice->customer_id;
            $this->invoice_date = $invoice->invoice_date;
            $this->due_date = $invoice->due_date;
            $this->status = $invoice->status;
            $this->currency = (string) ($invoice->currency ?: velocrm_currency_code());
            $this->exchange_rate = $invoice->exchange_rate ?: 1;
            $this->discount = $invoice->discount;
            $this->notes = $invoice->notes;
            $this->is_recurring = $invoice->is_recurring;
            $this->recurring_cycle = $invoice->recurring_cycle;
            $this->next_recurring_date = $invoice->next_recurring_date;
            $this->items = $invoice->items
                ->map(fn (InvoiceItem $item) => $this->normalizeItem($item->toArray()))
                ->toArray();
            $this->loadCustomFields(Invoice::class, $invoice);
        } else {
            $this->authorize('create', Invoice::class);

            $this->number = 'INV-'.strtoupper(Str::random(6));
            $this->invoice_date = now()->format('Y-m-d');
            $this->due_date = now()->addDays(30)->format('Y-m-d');
            $this->status = 'Draft';
            $this->currency = strtoupper((string) Setting::get('default_currency', Setting::get('currency_code', 'USD')));
            $this->exchange_rate = 1;
            $this->items = [$this->newItem()];
            $this->loadCustomFields(Invoice::class);
        }
        $this->calculateTotals();
    }

    public function addItem()
    {
        $this->items[] = $this->newItem();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updated($propertyName)
    {
        if (Str::startsWith($propertyName, 'items.') || $propertyName === 'tax_id' || $propertyName === 'discount') {
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->items as $index => $item) {
            $quantity = $this->toNumber($item['quantity'] ?? 0);
            $unitPrice = $this->toNumber($item['unit_price'] ?? 0);
            $amount = $quantity * $unitPrice;
            $this->items[$index]['amount'] = $amount;
            $this->subtotal += $amount;
        }

        $taxRate = 0;
        if ($this->tax_id) {
            $taxRate = (float) (TaxTemplate::find($this->tax_id)?->rate ?? 0);
        }

        $discount = $this->toNumber($this->discount);
        $this->tax_total = ($this->subtotal - $discount) * ($taxRate / 100);
        $this->total = $this->subtotal - $discount + $this->tax_total;
    }

    private function toNumber(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace(',', '', trim($value));
        }

        if ($value === null || $value === '') {
            return 0.0;
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function newItem(): array
    {
        return [
            '_key' => (string) Str::uuid(),
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'amount' => 0,
        ];
    }

    private function normalizeItem(array $item): array
    {
        return [
            '_key' => $item['_key'] ?? (string) Str::uuid(),
            'description' => $item['description'] ?? '',
            'quantity' => $item['quantity'] ?? 1,
            'unit_price' => $item['unit_price'] ?? 0,
            'amount' => $item['amount'] ?? 0,
        ];
    }

    private function itemAttributes(array $item): array
    {
        return [
            'description' => $item['description'] ?? '',
            'quantity' => $this->toNumber($item['quantity'] ?? 0),
            'unit_price' => $this->toNumber($item['unit_price'] ?? 0),
            'amount' => $this->toNumber($item['amount'] ?? 0),
        ];
    }

    public function save()
    {
        $rules = $this->rules;

        if ($this->invoiceId) {
            $rules['number'] = 'required|unique:invoices,number,'.$this->invoiceId;
        }

        if ($this->is_recurring) {
            $rules['recurring_cycle'] = 'required|in:weekly,monthly,yearly';
            $rules['next_recurring_date'] = 'required|date|after_or_equal:invoice_date';
        }

        $validated = $this->validate($rules + $this->customFieldRules());
        $customFieldValues = $validated['customFieldValues'] ?? [];
        $this->currency = strtoupper($this->currency);

        $previousStatus = null;
        $invoice = $this->invoiceId ? Invoice::find($this->invoiceId) : new Invoice;
        if ($invoice->exists) {
            $this->authorize('update', $invoice);
            $previousStatus = $invoice->status;
        } else {
            $this->authorize('create', Invoice::class);
        }

        $invoice->fill([
            'number' => $this->number,
            'customer_id' => $this->customer_id,
            'tax_id' => $this->invoiceTaxId($invoice),
            'branch' => $this->invoiceBranch($invoice),
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'currency' => $this->currency,
            'exchange_rate' => $this->toNumber($this->exchange_rate) ?: 1,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'discount' => $this->discount,
            'total' => $this->total,
            'balance_due' => $this->total - ($invoice->amount_paid ?? 0),
            'notes' => $this->notes,
            'is_recurring' => $this->is_recurring,
            'recurring_cycle' => $this->is_recurring ? $this->recurring_cycle : null,
            'next_recurring_date' => $this->is_recurring ? $this->next_recurring_date : null,
            'recurring_parent_id' => $invoice->recurring_parent_id,
        ]);

        if (! $invoice->exists) {
            $invoice->user_id = auth()->id();
        }

        $invoice->save();
        $invoice->syncCustomFieldValues($customFieldValues);

        $invoice->items()->delete();
        foreach ($this->items as $item) {
            $invoice->items()->create($this->itemAttributes($item));
        }

        $invoice->load('customer');

        if (
            $invoice->status === 'Sent' &&
            $previousStatus !== 'Sent' &&
            $invoice->customer &&
            $invoice->customer->email
        ) {
            SafeNotifier::send($invoice->customer, new InvoiceSentNotification($invoice), [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
            ]);
        }

        session()->flash('success', __('Invoice saved successfully.'));

        return redirect()->route('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoices.invoice-form', [
            'customers' => Customer::all(),
            'taxTemplates' => TaxTemplate::all(),
            'currencyOptions' => ['THB', 'USD', 'EUR', 'GBP', 'JPY', 'SGD', 'AUD', 'CAD', 'HKD'],
        ]);
    }

    private function invoiceTaxId(Invoice $invoice): ?string
    {
        return $this->shouldRefreshTaxSnapshot($invoice)
            ? Customer::find($this->customer_id)?->tax_id
            : $invoice->tax_id;
    }

    private function invoiceBranch(Invoice $invoice): ?string
    {
        return $this->shouldRefreshTaxSnapshot($invoice)
            ? Customer::find($this->customer_id)?->branch
            : $invoice->branch;
    }

    private function shouldRefreshTaxSnapshot(Invoice $invoice): bool
    {
        return ! $invoice->exists
            || (int) $invoice->customer_id !== (int) $this->customer_id
            || ($invoice->tax_id === null && $invoice->branch === null);
    }
}
