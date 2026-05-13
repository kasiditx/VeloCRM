<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $start = Carbon::parse($filters['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $end = Carbon::parse($filters['end_date'] ?? now()->endOfMonth()->toDateString())->endOfDay();

        $paidInvoices = Invoice::query()
            ->where('status', 'Paid')
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->with('customer')
            ->get();

        $invoices = Invoice::query()
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $leads = Lead::query()
            ->whereBetween('created_at', [$start, $end])
            ->with('customer')
            ->get();

        $totalLeads = $leads->count();
        $convertedLeads = $leads->filter(
            fn (Lead $lead): bool => $lead->status === 'Won' || $lead->customer !== null
        )->count();
        $totalRevenue = (float) $paidInvoices->sum(
            fn (Invoice $invoice): float => $invoice->baseAmount($invoice->total)
        );

        return response()->json([
            'period' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            'stats' => [
                'revenue' => $totalRevenue,
                'invoices' => $invoices->count(),
                'paid_invoices' => $paidInvoices->count(),
                'unpaid_balance' => (float) $invoices
                    ->where('status', '!=', 'Paid')
                    ->sum(fn (Invoice $invoice): float => $invoice->baseAmount($invoice->balance_due)),
                'leads' => $totalLeads,
                'converted_leads' => $convertedLeads,
                'conversion_rate' => $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0.0,
                'customers' => Customer::query()->whereBetween('created_at', [$start, $end])->count(),
            ],
            'revenue_by_customer' => $paidInvoices
                ->groupBy(fn (Invoice $invoice): string => $invoice->customer?->name ?? 'Deleted customer')
                ->map(fn (Collection $group, string $customer): array => [
                    'customer' => $customer,
                    'amount' => (float) $group->sum(fn (Invoice $invoice): float => $invoice->baseAmount($invoice->total)),
                ])
                ->sortByDesc('amount')
                ->values()
                ->all(),
            'lead_statuses' => $leads
                ->groupBy('status')
                ->map(fn (Collection $group, string $status): array => [
                    'status' => $status,
                    'total' => $group->count(),
                ])
                ->values()
                ->all(),
        ]);
    }
}
