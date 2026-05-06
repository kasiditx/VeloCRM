<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Invoices\InvoiceIndex;
use App\Livewire\Leads\LeadIndex;
use App\Livewire\Proposals\ProposalIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SoftDeletesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_lead_can_be_soft_deleted_restored_and_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $lead = Lead::create([
            'name' => 'Trash Lead',
            'email' => 'lead@example.com',
            'status' => 'New',
            'source' => 'Website',
            'value' => 1200,
            'user_id' => $user->id,
        ]);

        Livewire::test(LeadIndex::class)
            ->call('delete', $lead->id);

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);

        Livewire::test(LeadIndex::class)
            ->set('showTrashed', true)
            ->call('restore', $lead->id);

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'deleted_at' => null]);

        $lead->delete();

        Livewire::test(LeadIndex::class)
            ->set('showTrashed', true)
            ->call('forceDelete', $lead->id);

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_customer_can_be_soft_deleted_restored_and_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $customer = Customer::create([
            'name' => 'Trash Customer',
            'email' => 'customer@example.com',
            'company' => 'Acme',
            'user_id' => $user->id,
        ]);

        Livewire::test(CustomerIndex::class)
            ->call('delete', $customer->id);

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);

        Livewire::test(CustomerIndex::class)
            ->set('showTrashed', true)
            ->call('restore', $customer->id);

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);

        $customer->delete();

        Livewire::test(CustomerIndex::class)
            ->set('showTrashed', true)
            ->call('forceDelete', $customer->id);

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_invoice_can_be_soft_deleted_restored_and_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $customer = Customer::create([
            'name' => 'Billing Customer',
            'email' => 'billing@example.com',
            'user_id' => $user->id,
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-1001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'subtotal' => 500,
            'total' => 500,
            'balance_due' => 500,
            'status' => 'Draft',
            'user_id' => $user->id,
        ]);

        Livewire::test(InvoiceIndex::class)
            ->call('delete', $invoice->id);

        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);

        Livewire::test(InvoiceIndex::class)
            ->set('showTrashed', true)
            ->call('restore', $invoice->id);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'deleted_at' => null]);

        $invoice->delete();

        Livewire::test(InvoiceIndex::class)
            ->set('showTrashed', true)
            ->call('forceDelete', $invoice->id);

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_proposal_can_be_soft_deleted_restored_and_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $lead = Lead::create([
            'name' => 'Proposal Lead',
            'email' => 'proposal@example.com',
            'status' => 'Qualified',
            'source' => 'Referral',
            'value' => 2000,
            'user_id' => $user->id,
        ]);

        $proposal = Proposal::create([
            'number' => 'PR-1001',
            'lead_id' => $lead->id,
            'subject' => 'Website Redesign',
            'content' => 'Proposal content',
            'total' => 2000,
            'status' => 'Draft',
            'user_id' => $user->id,
        ]);

        Livewire::test(ProposalIndex::class)
            ->call('delete', $proposal->id);

        $this->assertSoftDeleted('proposals', ['id' => $proposal->id]);

        Livewire::test(ProposalIndex::class)
            ->set('showTrashed', true)
            ->call('restore', $proposal->id);

        $this->assertDatabaseHas('proposals', ['id' => $proposal->id, 'deleted_at' => null]);

        $proposal->delete();

        Livewire::test(ProposalIndex::class)
            ->set('showTrashed', true)
            ->call('forceDelete', $proposal->id);

        $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
    }
}
