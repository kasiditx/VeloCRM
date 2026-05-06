<?php

declare(strict_types=1);

namespace App\Livewire\Calendar;

use App\Models\Invoice;
use App\Models\Task;
use Carbon\Carbon;
use Livewire\Component;

class CalendarIndex extends Component
{
    public int $currentMonth;

    public int $currentYear;

    public string $currentMonthName;

    public function mount(): void
    {
        $this->currentMonth = (int) date('m');
        $this->currentYear = (int) date('Y');
        $this->updateMonthName();
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->updateMonthName();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->updateMonthName();
    }

    public function goToToday(): void
    {
        $this->currentMonth = (int) date('m');
        $this->currentYear = (int) date('Y');
        $this->updateMonthName();
    }

    private function updateMonthName(): void
    {
        $this->currentMonthName = Carbon::create($this->currentYear, $this->currentMonth, 1)
            ->locale(app()->getLocale())
            ->translatedFormat('F');
    }

    public function render()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        // Pre-fetch all invoices and tasks for the visible range (fix N+1: 2 queries instead of 84)
        $invoicesByDate = Invoice::with('customer')
            ->whereBetween('due_date', [
                $startOfCalendar->format('Y-m-d'),
                $endOfCalendar->format('Y-m-d'),
            ])->get()->groupBy(fn ($invoice) => Carbon::parse($invoice->due_date)->format('Y-m-d'));

        $tasksByDate = Task::whereBetween('due_date', [
            $startOfCalendar->format('Y-m-d'),
            $endOfCalendar->format('Y-m-d'),
        ])->get()->groupBy(fn ($task) => Carbon::parse($task->due_date)->format('Y-m-d'));

        $calendar = [];
        $date = $startOfCalendar->copy();

        while ($date->lte($endOfCalendar)) {
            $dayDate = $date->format('Y-m-d');
            $calendar[] = [
                'date' => $dayDate,
                'day' => $date->day,
                'weekday' => $date->copy()->locale(app()->getLocale())->translatedFormat('D'),
                'fullDate' => $date->copy()->locale(app()->getLocale())->translatedFormat('D, j M Y'),
                'currentMonth' => $date->month == $this->currentMonth,
                'isToday' => $date->isToday(),
                'invoices' => $invoicesByDate->get($dayDate, collect()),
                'tasks' => $tasksByDate->get($dayDate, collect()),
            ];
            $date->addDay();
        }

        $currentMonthDays = collect($calendar)->filter(fn (array $day): bool => $day['currentMonth']);
        $agendaDays = $currentMonthDays
            ->filter(fn (array $day): bool => $day['tasks']->isNotEmpty() || $day['invoices']->isNotEmpty())
            ->values();

        $summary = [
            'tasks' => $currentMonthDays->sum(fn (array $day): int => $day['tasks']->count()),
            'invoices' => $currentMonthDays->sum(fn (array $day): int => $day['invoices']->count()),
            'overdueInvoices' => $currentMonthDays->sum(fn (array $day): int => $day['invoices']
                ->filter(fn (Invoice $invoice): bool => Carbon::parse($invoice->due_date)->isPast() && $invoice->status !== 'Paid')
                ->count()),
            'activeDays' => $agendaDays->count(),
        ];

        return view('livewire.calendar.calendar-index', [
            'agendaDays' => $agendaDays,
            'calendar' => $calendar,
            'summary' => $summary,
        ])->layout('layouts.app');
    }
}
