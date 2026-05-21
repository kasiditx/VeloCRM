<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class Dashboard extends Component
{
    protected $listeners = [
        'leadUpdated' => '$refresh',
        'taskUpdated' => '$refresh',
    ];

    #[Computed]
    public function revenueSeries(): Collection
    {
        $start = now()->startOfMonth()->subMonths(11);
        $months = collect(range(0, 11))->map(function (int $offset) use ($start) {
            $month = $start->copy()->addMonths($offset);

            return [
                'key' => $month->format('Y-m'),
                'label' => $month->copy()->locale(app()->getLocale())->translatedFormat('M Y'),
                'total' => 0.0,
            ];
        })->keyBy('key');

        Invoice::query()
            ->where('status', 'Paid')
            ->whereDate('invoice_date', '>=', $start->toDateString())
            ->get()
            ->groupBy(fn (Invoice $invoice) => Carbon::parse($invoice->invoice_date)->format('Y-m'))
            ->each(function (Collection $group, string $monthKey) use ($months): void {
                if ($months->has($monthKey)) {
                    $month = $months->get($monthKey);
                    $month['total'] = (float) $group->sum(fn (Invoice $invoice): float => $invoice->baseAmount($invoice->total));
                    $months->put($monthKey, $month);
                }
            });

        return $months->values();
    }

    #[Computed]
    public function leadPipeline(): Collection
    {
        $statuses = collect(['New', 'Contacted', 'Qualified', 'Won', 'Lost']);
        $counts = Lead::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return $statuses->map(fn (string $status) => [
            'label' => $status,
            'total' => (int) ($counts[$status] ?? 0),
        ]);
    }

    public function render()
    {
        $totalLeads = Lead::count();
        $totalCustomers = Customer::count();
        $totalRevenue = Invoice::where('status', 'Paid')
            ->get()
            ->sum(fn (Invoice $invoice): float => $invoice->baseAmount($invoice->total));
        $pendingInvoices = Invoice::whereIn('status', ['Draft', 'Sent', 'Overdue'])->count();
        $pendingInvoiceBalance = Invoice::whereIn('status', ['Draft', 'Sent', 'Overdue'])
            ->get()
            ->sum(fn (Invoice $invoice): float => $invoice->baseAmount($invoice->balance_due));
        $totalProposals = Proposal::count();
        $convertedLeads = Customer::whereNotNull('lead_id')->count();
        $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
        $recentActivity = Activity::query()
            ->with('causer')
            ->latest()
            ->take(10)
            ->get();

        $upcomingTasks = Task::query()
            ->with(['assignee', 'relatable'])
            ->whereIn('status', ['Todo', 'In Progress'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->toDateString(), now()->copy()->addDays(7)->toDateString()])
            ->orderBy('due_date')
            ->take(8)
            ->get();

        $overdueInvoices = Invoice::query()
            ->with('customer')
            ->where('status', '!=', 'Paid')
            ->whereDate('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->take(8)
            ->get()
            ->map(function (Invoice $invoice) {
                if ($invoice->status !== 'Overdue') {
                    $invoice->status = 'Overdue';
                }

                return $invoice;
            });
        $overdueInvoiceTotal = $overdueInvoices->sum(fn (Invoice $invoice): float => $invoice->baseAmount($invoice->balance_due));
        $todayLabel = now()->locale(app()->getLocale())->translatedFormat('l, j F Y');
        $staleLeads = Lead::query()
            ->whereIn('status', ['New', 'Contacted'])
            ->where('updated_at', '<', now()->subDays(7))
            ->count();

        $decisionCards = collect([
            [
                'title' => __('Collect overdue money'),
                'message' => $overdueInvoices->isNotEmpty()
                    ? __(':count invoices are overdue, worth :amount.', [
                        'count' => $overdueInvoices->count(),
                        'amount' => format_currency($overdueInvoiceTotal),
                    ])
                    : __('No overdue invoices right now. Keep billing follow-up weekly.'),
                'action' => __('Review Invoices'),
                'href' => route('invoices.index'),
                'tone' => $overdueInvoices->isNotEmpty() ? 'danger' : 'success',
                'priority' => $overdueInvoices->isNotEmpty() ? 1 : 4,
            ],
            [
                'title' => __('Follow up before deals go cold'),
                'message' => $staleLeads > 0
                    ? __(':count active leads have not changed in 7 days.', ['count' => $staleLeads])
                    : __('Lead follow-up is current for this week.'),
                'action' => __('Review Leads'),
                'href' => route('leads.index'),
                'tone' => $staleLeads > 0 ? 'warning' : 'success',
                'priority' => $staleLeads > 0 ? 2 : 5,
            ],
            [
                'title' => __('Plan today’s sales work'),
                'message' => $upcomingTasks->isNotEmpty()
                    ? __(':count tasks are due in the next 7 days.', ['count' => $upcomingTasks->count()])
                    : __('No dated tasks are due this week. Add follow-ups for active deals.'),
                'action' => __('Open Tasks'),
                'href' => route('tasks.index'),
                'tone' => $upcomingTasks->isNotEmpty() ? 'info' : 'neutral',
                'priority' => $upcomingTasks->isNotEmpty() ? 3 : 6,
            ],
        ])->sortBy('priority')->values();

        $onboardingItems = collect([
            [
                'label' => __('Set branding'),
                'done' => filled(Setting::get('logo')) || filled(Setting::get('company_name')),
                'href' => route('admin.settings').'#branding',
            ],
            [
                'label' => __('Configure SMTP and send a test email'),
                'done' => filled(Setting::get('mail_host')) && filled(Setting::get('mail_from_address')),
                'href' => route('admin.settings').'#smtp',
            ],
            [
                'label' => __('Create the first lead'),
                'done' => $totalLeads > 0,
                'href' => route('leads.create'),
            ],
            [
                'label' => __('Create the first invoice'),
                'done' => Invoice::query()->exists(),
                'href' => route('invoices.create'),
            ],
            [
                'label' => __('Invite a second user'),
                'done' => User::query()->count() > 1,
                'href' => route('admin.users.create'),
            ],
        ]);

        return view('livewire.dashboard', [
            'totalLeads' => $totalLeads,
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'pendingInvoices' => $pendingInvoices,
            'pendingInvoiceBalance' => $pendingInvoiceBalance,
            'totalProposals' => $totalProposals,
            'conversionRate' => round($conversionRate, 2),
            'overdueInvoiceTotal' => $overdueInvoiceTotal,
            'revenueSeries' => $this->revenueSeries,
            'leadPipeline' => $this->leadPipeline,
            'recentActivity' => $recentActivity,
            'upcomingTasks' => $upcomingTasks,
            'overdueInvoices' => $overdueInvoices,
            'todayLabel' => $todayLabel,
            'decisionCards' => $decisionCards,
            'onboardingItems' => $onboardingItems,
            'onboardingComplete' => $onboardingItems->every(fn (array $item): bool => $item['done']),
        ])->layout('layouts.app');
    }
}
