<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\InvoiceOverdueNotification;
use App\Support\SafeNotifier;
use Illuminate\Console\Command;

class SendOverdueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-overdue-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send overdue invoice reminders to the assigned staff user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = 0;

        Invoice::query()
            ->with(['user', 'customer'])
            ->where('status', '!=', 'Paid')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get()
            ->each(function (Invoice $invoice) use (&$count): void {
                if (! $invoice->user || ! $invoice->user->email || ! $invoice->user->is_active) {
                    return;
                }

                if (SafeNotifier::send($invoice->user, new InvoiceOverdueNotification($invoice), [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                ])) {
                    $count++;
                }
            });

        $this->info("Sent {$count} overdue reminder(s).");

        return self::SUCCESS;
    }
}
