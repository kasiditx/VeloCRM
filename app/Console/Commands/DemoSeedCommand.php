<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSeedCommand extends Command
{
    protected $signature = 'velocrm:demo-seed {--force : Run even when VELOCRM_DEMO_MODE is not enabled}';

    protected $description = 'Seed deterministic demo users and CRM records for the public demo site';

    public function handle(): int
    {
        if (! $this->option('force') && ! (bool) config('app.demo_mode', false)) {
            $this->error('Refusing to seed demo data unless VELOCRM_DEMO_MODE=true or --force is provided.');

            return self::FAILURE;
        }

        Model::unguarded(function (): void {
            Role::firstOrCreate(['name' => 'Admin']);
            Role::firstOrCreate(['name' => 'Staff']);
            Role::firstOrCreate(['name' => 'Customer']);

            $admin = $this->demoUser('Demo Admin', 'admin@demo.velocrm.app', 'Admin');
            $staff = $this->demoUser('Demo Staff', 'staff@demo.velocrm.app', 'Staff');
            $customerUser = $this->demoUser('Demo Customer', 'customer@demo.velocrm.app', 'Customer');

            $owners = [$admin, $staff];
            $statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Won'];
            $sources = ['Website', 'Referral', 'Email', 'Social Media', 'Event'];
            $leads = collect();

            for ($i = 1; $i <= 20; $i++) {
                $owner = $owners[$i % count($owners)];
                $leads->push(Lead::withoutGlobalScopes()->updateOrCreate(
                    ['email' => sprintf('lead%02d@demo.velocrm.app', $i)],
                    [
                        'name' => sprintf('Demo Lead %02d', $i),
                        'phone' => sprintf('080-000-%04d', $i),
                        'company' => sprintf('Demo Prospect Co., Ltd. %02d', $i),
                        'status' => $statuses[($i - 1) % count($statuses)],
                        'source' => $sources[($i - 1) % count($sources)],
                        'value' => 15000 + ($i * 1250),
                        'notes' => 'Demo lead for reviewer walkthrough.',
                        'user_id' => $owner->id,
                    ]
                ));
            }

            $customers = collect();

            for ($i = 1; $i <= 10; $i++) {
                $owner = $owners[$i % count($owners)];
                $lead = $leads->get($i - 1);
                $customers->push(Customer::withoutGlobalScopes()->updateOrCreate(
                    ['email' => sprintf('customer%02d@demo.velocrm.app', $i)],
                    [
                        'lead_id' => $lead?->id,
                        'name' => sprintf('Demo Customer %02d', $i),
                        'phone' => sprintf('081-000-%04d', $i),
                        'company' => sprintf('Demo Customer Co., Ltd. %02d', $i),
                        'address' => sprintf('%d Demo Road, Bangkok 10%03d', 100 + $i, $i),
                        'user_id' => $i === 1 ? $customerUser->id : $owner->id,
                    ]
                ));
            }

            for ($i = 1; $i <= 5; $i++) {
                $customer = $customers->get($i - 1);
                $owner = $owners[$i % count($owners)];
                $subtotal = 12000 + ($i * 3500);
                $taxTotal = round($subtotal * 0.07, 2);
                $total = $subtotal + $taxTotal;
                $amountPaid = $i % 2 === 0 ? round($total / 2, 2) : 0;

                $invoice = Invoice::withoutGlobalScopes()->updateOrCreate(
                    ['number' => sprintf('DEMO-INV-%04d', $i)],
                    [
                        'customer_id' => $customer->id,
                        'invoice_date' => now()->subDays(12 - $i)->toDateString(),
                        'due_date' => now()->addDays(18 + $i)->toDateString(),
                        'subtotal' => $subtotal,
                        'tax_total' => $taxTotal,
                        'discount' => 0,
                        'total' => $total,
                        'amount_paid' => $amountPaid,
                        'balance_due' => $total - $amountPaid,
                        'status' => $amountPaid > 0 ? 'Partially Paid' : 'Sent',
                        'currency' => 'THB',
                        'exchange_rate' => 1,
                        'is_recurring' => $i === 5,
                        'recurring_cycle' => $i === 5 ? 'monthly' : null,
                        'next_recurring_date' => $i === 5 ? now()->addMonth()->toDateString() : null,
                        'notes' => 'Demo invoice for reviewer walkthrough.',
                        'user_id' => $owner->id,
                    ]
                );

                $invoice->items()->delete();
                $invoice->items()->createMany([
                    [
                        'description' => 'CRM implementation package',
                        'quantity' => 1,
                        'unit_price' => $subtotal * 0.7,
                        'amount' => $subtotal * 0.7,
                    ],
                    [
                        'description' => 'Training and onboarding',
                        'quantity' => 1,
                        'unit_price' => $subtotal * 0.3,
                        'amount' => $subtotal * 0.3,
                    ],
                ]);
            }

            for ($i = 1; $i <= 3; $i++) {
                Proposal::withoutGlobalScopes()->updateOrCreate(
                    ['number' => sprintf('DEMO-PROP-%04d', $i)],
                    [
                        'customer_id' => $customers->get($i - 1)?->id,
                        'lead_id' => $leads->get($i + 9)?->id,
                        'subject' => sprintf('Demo Proposal %02d', $i),
                        'content' => 'This demo proposal shows the PDF and sales workflow for reviewers.',
                        'total' => 25000 + ($i * 5000),
                        'status' => ['Draft', 'Sent', 'Accepted'][$i - 1],
                        'user_id' => $owners[$i % count($owners)]->id,
                    ]
                );
            }

            for ($i = 1; $i <= 8; $i++) {
                Task::withoutGlobalScopes()->updateOrCreate(
                    ['title' => sprintf('Demo Task %02d', $i)],
                    [
                        'description' => 'Demo task for board and calendar walkthrough.',
                        'due_date' => now()->addDays($i)->toDateString(),
                        'priority' => ['Low', 'Medium', 'High', 'Urgent'][($i - 1) % 4],
                        'status' => ['Todo', 'In Progress', 'Done', 'Cancelled'][($i - 1) % 4],
                        'relatable_type' => $i % 2 === 0 ? Customer::class : Lead::class,
                        'relatable_id' => $i % 2 === 0 ? $customers->get($i % 10)?->id : $leads->get($i % 20)?->id,
                        'assigned_to' => $owners[$i % count($owners)]->id,
                        'user_id' => $owners[($i + 1) % count($owners)]->id,
                    ]
                );
            }
        });

        $this->info('Demo data seeded successfully.');

        return self::SUCCESS;
    }

    private function demoUser(string $name, string $email, string $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('demo1234'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $user->syncRoles([$role]);

        return $user;
    }
}
