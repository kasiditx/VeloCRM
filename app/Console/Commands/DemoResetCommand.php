<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoResetCommand extends Command
{
    protected $signature = 'velocrm:demo-reset {--force : Run even when VELOCRM_DEMO_MODE is not enabled} {--no-seed : Reset demo data without reseeding}';

    protected $description = 'Reset deterministic public demo site data and optionally reseed it';

    public function handle(): int
    {
        if (! $this->option('force') && ! (bool) config('app.demo_mode', false)) {
            $this->error('Refusing to reset demo data unless VELOCRM_DEMO_MODE=true or --force is provided.');

            return self::FAILURE;
        }

        DB::transaction(function (): void {
            $invoiceIds = Invoice::withoutGlobalScopes()
                ->where('number', 'like', 'DEMO-INV-%')
                ->pluck('id');

            Payment::query()->whereIn('invoice_id', $invoiceIds)->delete();

            Invoice::withoutGlobalScopes()
                ->whereIn('id', $invoiceIds)
                ->each(function (Invoice $invoice): void {
                    $invoice->items()->delete();
                    $invoice->forceDelete();
                });

            Proposal::withoutGlobalScopes()
                ->where('number', 'like', 'DEMO-PROP-%')
                ->each(fn (Proposal $proposal) => $proposal->forceDelete());

            Task::withoutGlobalScopes()
                ->where('title', 'like', 'Demo Task %')
                ->delete();

            Note::query()
                ->whereHasMorph('notable', [Lead::class, Customer::class], function ($query): void {
                    $query->withoutGlobalScopes()
                        ->where('email', 'like', '%@demo.velocrm.app');
                })
                ->delete();

            Attachment::query()
                ->whereHasMorph('attachable', [Lead::class, Customer::class], function ($query): void {
                    $query->withoutGlobalScopes()
                        ->where('email', 'like', '%@demo.velocrm.app');
                })
                ->get()
                ->each(function (Attachment $attachment): void {
                    Storage::disk('uploads')->delete($attachment->path);
                    $attachment->delete();
                });

            Customer::withoutGlobalScopes()
                ->where(function ($query): void {
                    $query->where('email', 'like', '%@demo.velocrm.app')
                        ->orWhere('name', 'like', 'Demo Customer %');
                })
                ->each(fn (Customer $customer) => $customer->forceDelete());

            Lead::withoutGlobalScopes()
                ->where(function ($query): void {
                    $query->where('email', 'like', '%@demo.velocrm.app')
                        ->orWhere('name', 'like', 'Demo Lead %');
                })
                ->each(fn (Lead $lead) => $lead->forceDelete());

            User::query()
                ->whereIn('email', [
                    'admin@demo.velocrm.app',
                    'staff@demo.velocrm.app',
                    'customer@demo.velocrm.app',
                ])
                ->get()
                ->each(function (User $user): void {
                    DB::table('model_has_roles')
                        ->where('model_type', $user->getMorphClass())
                        ->where('model_id', $user->id)
                        ->delete();

                    $user->delete();
                });
        });

        if (! $this->option('no-seed')) {
            Artisan::call('velocrm:demo-seed', ['--force' => true]);
            $this->output->write(Artisan::output());
        }

        $this->info('Demo data reset successfully.');

        return self::SUCCESS;
    }
}
