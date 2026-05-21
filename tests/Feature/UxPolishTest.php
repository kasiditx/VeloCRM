<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceIndex;
use App\Livewire\Leads\LeadIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UxPolishTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
        touch(storage_path('installed'));

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole('Admin');
        $this->actingAs($this->admin);
    }

    public function test_lead_list_saves_filter_view_and_updates_status_inline(): void
    {
        $lead = Lead::create([
            'name' => 'Polish Lead',
            'status' => 'New',
            'source' => 'Website',
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(LeadIndex::class)
            ->set('statusFilter', 'New')
            ->set('filterViewName', 'New website leads')
            ->call('saveFilterView')
            ->call('updateStatus', $lead->id, 'Qualified')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saved_filter_views', [
            'user_id' => $this->admin->id,
            'resource' => 'leads',
            'name' => 'New website leads',
        ]);
        $this->assertSame('Qualified', $lead->refresh()->status);
    }

    public function test_invoice_bulk_status_updates_selected_records(): void
    {
        $customer = Customer::create([
            'name' => 'Bulk Customer',
            'user_id' => $this->admin->id,
        ]);
        $invoice = Invoice::forceCreate([
            'number' => 'INV-UX-1',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 100,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 100,
            'amount_paid' => 0,
            'balance_due' => 100,
            'status' => 'Draft',
            'currency' => 'THB',
            'exchange_rate' => 1,
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(InvoiceIndex::class)
            ->set('selectedIds', [$invoice->id])
            ->set('bulkAction', 'status')
            ->set('bulkStatus', 'Sent')
            ->call('runBulkAction')
            ->assertHasNoErrors();

        $this->assertSame('Sent', $invoice->refresh()->status);
    }

    public function test_dashboard_renders_onboarding_checklist(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Finish the workspace setup');
    }
}
