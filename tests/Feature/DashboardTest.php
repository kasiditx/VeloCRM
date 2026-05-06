<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_dashboard_renders_management_widgets(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $lead = Lead::create([
            'name' => 'Pipeline Lead',
            'email' => 'lead@example.com',
            'company' => 'Acme',
            'status' => 'Qualified',
            'source' => 'Website',
            'value' => 5000,
            'user_id' => $user->id,
        ]);

        $customer = Customer::create([
            'lead_id' => $lead->id,
            'name' => 'Acme Customer',
            'email' => 'customer@example.com',
            'company' => 'Acme',
            'user_id' => $user->id,
        ]);

        Invoice::create([
            'number' => 'INV-1001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->subDays(3)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'subtotal' => 2500,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 2500,
            'amount_paid' => 0,
            'balance_due' => 2500,
            'status' => 'Sent',
            'user_id' => $user->id,
        ]);

        Task::create([
            'title' => 'Follow up proposal',
            'status' => 'Todo',
            'priority' => 'High',
            'due_date' => now()->addDays(2)->toDateString(),
            'assigned_to' => $user->id,
            'user_id' => $user->id,
            'relatable_type' => Lead::class,
            'relatable_id' => $lead->id,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($lead)
            ->log('lead updated');

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee('Revenue by Month')
            ->assertSee('Lead Pipeline')
            ->assertSee('Recent Activity')
            ->assertSee('Upcoming Tasks')
            ->assertSee('Overdue Invoices')
            ->assertSee('INV-1001')
            ->assertSee('Follow up proposal');
    }
}
