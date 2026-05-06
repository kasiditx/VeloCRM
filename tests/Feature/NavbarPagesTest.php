<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Calendar\CalendarIndex;
use App\Livewire\Dashboard;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Invoices\InvoiceIndex;
use App\Livewire\Leads\LeadIndex;
use App\Livewire\Proposals\ProposalIndex;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Tasks\TaskBoard;
use App\Livewire\Tasks\TaskIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NavbarPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_admin_can_render_every_primary_navbar_page_component(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $this->actingAs($user);

        Livewire::test(Dashboard::class)->assertStatus(200);
        Livewire::test(LeadIndex::class)->assertStatus(200);
        Livewire::test(CustomerIndex::class)->assertStatus(200);
        Livewire::test(InvoiceIndex::class)->assertStatus(200);
        Livewire::test(ProposalIndex::class)->assertStatus(200);
        Livewire::test(TaskIndex::class)->assertStatus(200);
        Livewire::test(TaskBoard::class)->assertStatus(200);
        Livewire::test(CalendarIndex::class)->assertStatus(200);
        Livewire::test(ReportIndex::class)->assertStatus(200);
    }

    public function test_invoice_index_search_filters_by_invoice_number_and_customer(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $acme = Customer::create([
            'name' => 'Acme Trading',
            'email' => 'billing@acme.test',
            'user_id' => $user->id,
        ]);

        $other = Customer::create([
            'name' => 'Other Customer',
            'email' => 'other@example.test',
            'user_id' => $user->id,
        ]);

        Invoice::create([
            'number' => 'INV-ACME-001',
            'customer_id' => $acme->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'subtotal' => 1000,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 1000,
            'amount_paid' => 0,
            'balance_due' => 1000,
            'status' => 'Sent',
            'user_id' => $user->id,
        ]);

        Invoice::create([
            'number' => 'INV-OTHER-001',
            'customer_id' => $other->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'subtotal' => 500,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 500,
            'amount_paid' => 0,
            'balance_due' => 500,
            'status' => 'Draft',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(InvoiceIndex::class)
            ->set('search', 'Acme')
            ->assertSee('INV-ACME-001')
            ->assertDontSee('INV-OTHER-001');
    }
}
