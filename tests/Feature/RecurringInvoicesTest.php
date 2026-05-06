<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\GenerateRecurringInvoices;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RecurringInvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_recurring_invoice_command_generates_child_invoice_and_advances_template(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Admin');

        $customer = Customer::create([
            'name' => 'Recurring Customer',
            'email' => 'customer@example.com',
            'user_id' => $owner->id,
        ]);

        $template = Invoice::create([
            'number' => 'INV-TEMPLATE-1',
            'customer_id' => $customer->id,
            'invoice_date' => now()->subMonth()->toDateString(),
            'due_date' => now()->subMonth()->addDays(14)->toDateString(),
            'subtotal' => 3000,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 3000,
            'amount_paid' => 0,
            'balance_due' => 3000,
            'status' => 'Draft',
            'is_recurring' => true,
            'recurring_cycle' => 'monthly',
            'next_recurring_date' => now()->toDateString(),
            'user_id' => $owner->id,
        ]);

        $template->items()->create([
            'description' => 'Monthly subscription',
            'quantity' => 1,
            'unit_price' => 3000,
            'amount' => 3000,
        ]);

        Artisan::call(GenerateRecurringInvoices::class);

        $template->refresh();

        $generatedInvoice = Invoice::query()
            ->where('recurring_parent_id', $template->id)
            ->first();

        $this->assertNotNull($generatedInvoice);
        $this->assertSame(now()->toDateString(), $generatedInvoice->invoice_date);
        $this->assertSame('Draft', $generatedInvoice->status);
        $this->assertSame('3000.00', number_format((float) $generatedInvoice->balance_due, 2, '.', ''));
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $generatedInvoice->id,
            'description' => 'Monthly subscription',
        ]);
        $this->assertSame(now()->addMonth()->toDateString(), $template->next_recurring_date);
    }

    public function test_recurring_invoice_command_does_not_duplicate_same_run_date(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Admin');

        $customer = Customer::create([
            'name' => 'Recurring Customer',
            'email' => 'customer@example.com',
            'user_id' => $owner->id,
        ]);

        $template = Invoice::create([
            'number' => 'INV-TEMPLATE-2',
            'customer_id' => $customer->id,
            'invoice_date' => now()->subWeek()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'subtotal' => 1200,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 1200,
            'amount_paid' => 0,
            'balance_due' => 1200,
            'status' => 'Draft',
            'is_recurring' => true,
            'recurring_cycle' => 'weekly',
            'next_recurring_date' => now()->toDateString(),
            'user_id' => $owner->id,
        ]);

        $template->items()->create([
            'description' => 'Weekly retainer',
            'quantity' => 1,
            'unit_price' => 1200,
            'amount' => 1200,
        ]);

        Artisan::call(GenerateRecurringInvoices::class);
        Artisan::call(GenerateRecurringInvoices::class);

        $this->assertSame(1, Invoice::query()->where('recurring_parent_id', $template->id)->count());
    }
}
