<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\ActivityLogIndex;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Invoices\InvoiceShow;
use App\Livewire\Leads\LeadShow;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditTrailTest extends TestCase
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

    public function test_resource_pages_show_activity_tab_from_activitylog(): void
    {
        $lead = Lead::create([
            'name' => 'Audit Lead',
            'status' => 'New',
            'user_id' => $this->admin->id,
        ]);
        $customer = Customer::create([
            'name' => 'Audit Customer',
            'user_id' => $this->admin->id,
        ]);
        $invoice = Invoice::forceCreate([
            'number' => 'INV-AUDIT-1',
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

        activity()->causedBy($this->admin)->performedOn($lead)->event('updated')->log('lead audit changed');
        activity()->causedBy($this->admin)->performedOn($customer)->event('updated')->log('customer audit changed');
        activity()->causedBy($this->admin)->performedOn($invoice)->event('updated')->log('invoice audit changed');

        Livewire::test(LeadShow::class, ['leadId' => $lead->id])
            ->assertSee('Activity')
            ->call('setTab', 'activity')
            ->assertSee('Lead audit changed');

        Livewire::test(CustomerShow::class, ['customerId' => $customer->id])
            ->assertSee('Activity')
            ->call('setTab', 'activity')
            ->assertSee('Customer audit changed');

        Livewire::test(InvoiceShow::class, ['invoiceId' => $invoice->id])
            ->assertSee('Activity')
            ->call('setTab', 'activity')
            ->assertSee('Invoice audit changed');
    }

    public function test_admin_activity_log_filters_by_user_model_and_date(): void
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $otherUser->assignRole('Staff');

        $lead = Lead::create([
            'name' => 'Filtered Lead',
            'status' => 'New',
            'user_id' => $this->admin->id,
        ]);
        $customer = Customer::create([
            'name' => 'Filtered Customer',
            'user_id' => $this->admin->id,
        ]);

        activity()->causedBy($this->admin)->performedOn($lead)->event('updated')->log('visible lead event');
        activity()->causedBy($otherUser)->performedOn($customer)->event('updated')->log('hidden customer event');

        $this->get(route('admin.activity-log.index'))
            ->assertOk()
            ->assertSee('Activity Log')
            ->assertSee('Visible lead event')
            ->assertSee('Hidden customer event');

        Livewire::test(ActivityLogIndex::class)
            ->set('userId', (string) $this->admin->id)
            ->set('modelType', Lead::class)
            ->set('startDate', now()->subDay()->toDateString())
            ->set('endDate', now()->addDay()->toDateString())
            ->assertSee('Visible lead event')
            ->assertDontSee('Hidden customer event');
    }
}
