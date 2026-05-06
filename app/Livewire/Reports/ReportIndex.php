<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Task;
use Illuminate\Support\Collection;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportIndex extends Component
{
    public string $startDate = '';
    public string $endDate = '';

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->startDate = $this->startDate !== '' ? $this->startDate : now()->startOfMonth()->subMonths(5)->toDateString();
        $this->endDate = $this->endDate !== '' ? $this->endDate : now()->endOfMonth()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
        ];
    }

    public function applyFilters(): void
    {
        $this->validate();
    }

    public function exportCsv(): StreamedResponse
    {
        $this->validate();

        $data = $this->buildReportData();
        $filename = 'reports-' . $this->startDate . '-to-' . $this->endDate . '.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['Section', 'Label', 'Value']);

            foreach ($data['stats'] as $label => $value) {
                fputcsv($handle, ['Summary', $label, $value]);
            }

            foreach ($data['revenueByMonth'] as $row) {
                fputcsv($handle, ['Revenue By Month', $row['label'], $row['amount']]);
            }

            foreach ($data['revenueByCustomer'] as $row) {
                fputcsv($handle, ['Revenue By Customer', $row['label'], $row['amount']]);
            }

            foreach ($data['leadSources'] as $row) {
                fputcsv($handle, ['Lead Sources', $row['label'], $row['total']]);
            }

            foreach ($data['leadStatuses'] as $row) {
                fputcsv($handle, ['Lead Statuses', $row['label'], $row['total']]);
            }

            fputcsv($handle, ['Conversion', 'Leads Created', $data['conversion']['total_leads']]);
            fputcsv($handle, ['Conversion', 'Converted Leads', $data['conversion']['converted_leads']]);
            fputcsv($handle, ['Conversion', 'Conversion Rate', $data['conversion']['conversion_rate']]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        $this->validate();

        $data = $this->buildReportData();

        return view('livewire.reports.report-index', [
            'stats' => $data['stats'],
            'decisionNotes' => $data['decisionNotes'],
            'revenueByMonth' => $data['revenueByMonth'],
            'revenueByCustomer' => $data['revenueByCustomer'],
            'leadSources' => $data['leadSources'],
            'leadStatuses' => $data['leadStatuses'],
            'conversion' => $data['conversion'],
        ])->layout('layouts.app');
    }

    /**
     * @return array{
     *     stats: array<string, int|float|string>,
     *     decisionNotes: list<array{title:string,message:string,action:string,href:string,tone:string}>,
     *     revenueByMonth: list<array{label:string, amount:float}>,
     *     revenueByCustomer: list<array{label:string, amount:float}>,
     *     leadSources: list<array{label:string, total:int}>,
     *     leadStatuses: list<array{label:string, total:int}>,
     *     conversion: array{total_leads:int, converted_leads:int, conversion_rate:string}
     * }
     */
    protected function buildReportData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $paidInvoices = Invoice::query()
            ->where('status', 'Paid')
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->with('customer')
            ->get();

        $invoicesInRange = Invoice::query()
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $leadsInRange = Lead::query()
            ->whereBetween('created_at', [$start, $end])
            ->with('customer')
            ->get();

        $customersInRange = Customer::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $proposalsInRange = Proposal::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $openTasksInRange = Task::query()
            ->whereIn('status', ['Todo', 'In Progress'])
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $revenueByMonth = $paidInvoices
            ->groupBy(fn (Invoice $invoice): string => Carbon::parse($invoice->invoice_date)->format('Y-m'))
            ->map(fn (Collection $group, string $month): array => [
                'label' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'amount' => (float) $group->sum('total'),
            ])
            ->values()
            ->all();

        $revenueByCustomer = $paidInvoices
            ->groupBy(fn (Invoice $invoice): string => $invoice->customer?->name ?? 'Deleted customer')
            ->map(fn (Collection $group, string $customer): array => [
                'label' => $customer,
                'amount' => (float) $group->sum('total'),
            ])
            ->sortByDesc('amount')
            ->take(8)
            ->values()
            ->all();

        $leadSources = $leadsInRange
            ->groupBy(fn (Lead $lead): string => $lead->source ?: 'Unspecified')
            ->map(fn (Collection $group, string $source): array => [
                'label' => $source,
                'total' => $group->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();

        $leadStatuses = collect(['New', 'Contacted', 'Qualified', 'Won', 'Lost'])
            ->map(fn (string $status): array => [
                'label' => $status,
                'total' => $leadsInRange->where('status', $status)->count(),
            ])
            ->values()
            ->all();

        $convertedLeads = $leadsInRange->filter(
            fn (Lead $lead): bool => $lead->status === 'Won' || $lead->customer !== null
        )->count();

        $totalLeads = $leadsInRange->count();
        $totalPaidRevenue = (float) $paidInvoices->sum('total');
        $conversionRate = $totalLeads > 0
            ? number_format(($convertedLeads / $totalLeads) * 100, 1) . '%'
            : '0.0%';
        $conversionRateValue = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
        $unpaidBalance = (float) $invoicesInRange
            ->where('status', '!=', 'Paid')
            ->sum('balance_due');
        $topLeadSource = collect($leadSources)->first();
        $decisionNotes = [
            [
                'title' => __('Cash collection'),
                'message' => $unpaidBalance > 0
                    ? __('Unpaid invoices in this period total :amount. Review billing before chasing new work.', ['amount' => format_currency($unpaidBalance)])
                    : __('No unpaid balance in this period. Revenue follow-up is not the bottleneck.'),
                'action' => __('Open Invoices'),
                'href' => route('invoices.index'),
                'tone' => $unpaidBalance > 0 ? 'danger' : 'success',
            ],
            [
                'title' => __('Sales conversion'),
                'message' => $totalLeads === 0
                    ? __('No leads were created in this range. Check acquisition before judging conversion.')
                    : __(':converted of :total leads converted (:rate).', [
                        'converted' => $convertedLeads,
                        'total' => $totalLeads,
                        'rate' => $conversionRate,
                    ]),
                'action' => __('Review Leads'),
                'href' => route('leads.index'),
                'tone' => $conversionRateValue >= 25 ? 'success' : 'warning',
            ],
            [
                'title' => __('Best source to double down on'),
                'message' => $topLeadSource
                    ? __(':source produced the most leads in this range (:count).', [
                        'source' => __($topLeadSource['label']),
                        'count' => $topLeadSource['total'],
                    ])
                    : __('No source data yet. Require source when sales adds new leads.'),
                'action' => __('Create Lead'),
                'href' => route('leads.create'),
                'tone' => $topLeadSource ? 'info' : 'neutral',
            ],
        ];

        return [
            'stats' => [
                'Revenue' => $totalPaidRevenue,
                'Invoices' => $invoicesInRange->count(),
                'Leads' => $totalLeads,
                'Customers' => $customersInRange,
                'Proposals' => $proposalsInRange,
                'Open Tasks' => $openTasksInRange,
                'Average Invoice' => $paidInvoices->isNotEmpty() ? (float) $paidInvoices->avg('total') : 0.0,
                'Conversion Rate' => $conversionRate,
            ],
            'decisionNotes' => $decisionNotes,
            'revenueByMonth' => $revenueByMonth,
            'revenueByCustomer' => $revenueByCustomer,
            'leadSources' => $leadSources,
            'leadStatuses' => $leadStatuses,
            'conversion' => [
                'total_leads' => $totalLeads,
                'converted_leads' => $convertedLeads,
                'conversion_rate' => $conversionRate,
            ],
        ];
    }
}
