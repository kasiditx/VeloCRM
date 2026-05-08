<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'app:generate-recurring-invoices';

    protected $description = 'Generate new invoices from recurring templates';

    public function handle(): int
    {
        $generatedCount = 0;

        Invoice::query()
            ->with(['items', 'customer'])
            ->where('is_recurring', true)
            ->whereNotNull('next_recurring_date')
            ->whereNull('recurring_parent_id')
            ->whereDate('next_recurring_date', '<=', now()->toDateString())
            ->get()
            ->each(function (Invoice $template) use (&$generatedCount): void {
                if (! $template->customer) {
                    $this->warn("Skipped template {$template->number}: customer not found.");

                    return;
                }

                $runDate = Carbon::parse($template->next_recurring_date)->startOfDay();

                $alreadyGenerated = Invoice::query()
                    ->where('recurring_parent_id', $template->id)
                    ->whereDate('invoice_date', $runDate->toDateString())
                    ->exists();

                if ($alreadyGenerated) {
                    $template->update([
                        'next_recurring_date' => $this->nextRecurringDate($runDate, (string) $template->recurring_cycle)?->toDateString(),
                    ]);

                    return;
                }

                DB::transaction(function () use ($template, $runDate, &$generatedCount): void {
                    $nextRunDate = $this->nextRecurringDate($runDate, (string) $template->recurring_cycle);

                    if (! $nextRunDate) {
                        $template->update([
                            'is_recurring' => false,
                            'recurring_cycle' => null,
                            'next_recurring_date' => null,
                        ]);

                        return;
                    }

                    $generatedInvoice = $template->replicate([
                        'amount_paid',
                        'balance_due',
                        'status',
                        'recurring_parent_id',
                    ]);

                    $generatedInvoice->fill([
                        'number' => $this->generateInvoiceNumber(),
                        'invoice_date' => $runDate->toDateString(),
                        'due_date' => $this->calculateDueDate($template, $runDate)->toDateString(),
                        'status' => 'Draft',
                        'amount_paid' => 0,
                        'balance_due' => $template->total,
                        'recurring_parent_id' => $template->id,
                    ]);
                    $generatedInvoice->save();

                    foreach ($template->items as $item) {
                        $generatedInvoice->items()->create([
                            'description' => $item->description,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'amount' => $item->amount,
                        ]);
                    }

                    $template->update([
                        'next_recurring_date' => $nextRunDate->toDateString(),
                    ]);

                    $generatedCount++;
                });
            });

        $this->info("Generated {$generatedCount} recurring invoice(s).");

        return self::SUCCESS;
    }

    protected function nextRecurringDate(Carbon $currentRunDate, string $cycle): ?Carbon
    {
        return match ($cycle) {
            'weekly' => $currentRunDate->copy()->addWeek(),
            'monthly' => $currentRunDate->copy()->addMonth(),
            'yearly' => $currentRunDate->copy()->addYear(),
            default => null,
        };
    }

    protected function calculateDueDate(Invoice $template, Carbon $invoiceDate): Carbon
    {
        $templateInvoiceDate = Carbon::parse($template->invoice_date);
        $templateDueDate = Carbon::parse($template->due_date);
        $termsInDays = max($templateInvoiceDate->diffInDays($templateDueDate, false), 0);

        return $invoiceDate->copy()->addDays($termsInDays);
    }

    protected function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-'.Str::upper(Str::random(6));
        } while (Invoice::query()->where('number', $number)->exists());

        return $number;
    }
}
