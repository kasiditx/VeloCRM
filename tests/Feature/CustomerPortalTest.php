<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Portal\InvoiceIndex;
use App\Livewire\Portal\InvoiceShow;
use App\Livewire\Portal\Profile;
use App\Livewire\Portal\ProposalShow;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Customer $customer;

    protected User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('Admin');

        $this->customer = Customer::forceCreate([
            'name' => 'Acme Buyer',
            'email' => 'buyer@example.com',
            'phone' => '0811111111',
            'company' => 'Acme',
            'address' => 'Bangkok',
            'user_id' => $this->owner->id,
        ]);

        $this->customerUser = User::factory()
            ->forCustomer($this->customer->id)
            ->create([
                'email' => 'portal@example.com',
                'password' => Hash::make('password'),
            ]);
        $this->customerUser->assignRole('Customer');
    }

    public function test_customer_can_only_view_their_own_invoices(): void
    {
        $invoice = $this->invoiceFor($this->customer, 'INV-PORTAL-1');
        $otherInvoice = $this->invoiceFor($this->otherCustomer(), 'INV-OTHER-1');

        $this->actingAs($this->customerUser);

        Livewire::test(InvoiceIndex::class)
            ->assertSee('INV-PORTAL-1')
            ->assertDontSee('INV-OTHER-1');

        Livewire::test(InvoiceShow::class, ['invoiceId' => $invoice->id])
            ->assertSee('INV-PORTAL-1');

        Livewire::test(InvoiceShow::class, ['invoiceId' => $otherInvoice->id])
            ->assertForbidden();
    }

    public function test_customer_can_accept_and_reject_own_proposal_only(): void
    {
        $proposal = $this->proposalFor($this->customer, 'PROP-PORTAL-1');
        $otherProposal = $this->proposalFor($this->otherCustomer(), 'PROP-OTHER-1');

        $this->actingAs($this->customerUser);

        Livewire::test(ProposalShow::class, ['proposalId' => $proposal->id])
            ->call('accept')
            ->assertHasNoErrors();

        $this->assertSame('Accepted', $proposal->refresh()->status);

        Livewire::test(ProposalShow::class, ['proposalId' => $proposal->id])
            ->call('reject')
            ->assertHasNoErrors();

        $this->assertSame('Declined', $proposal->refresh()->status);

        Livewire::test(ProposalShow::class, ['proposalId' => $otherProposal->id])
            ->assertForbidden();
    }

    public function test_customer_portal_profile_updates_customer_and_password(): void
    {
        $this->actingAs($this->customerUser);

        Livewire::test(Profile::class)
            ->set('name', 'Portal Contact')
            ->set('customerName', 'Acme Buyer Updated')
            ->set('phone', '0899999999')
            ->set('company', 'Acme Updated')
            ->set('address', 'Chiang Mai')
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Portal Contact', $this->customerUser->refresh()->name);
        $this->assertTrue(Hash::check('new-password', $this->customerUser->password));
        $this->assertSame('Acme Buyer Updated', $this->customer->refresh()->name);
        $this->assertSame('Chiang Mai', $this->customer->address);
    }

    public function test_customer_role_is_blocked_from_internal_crm_routes(): void
    {
        $this->actingAs($this->customerUser);

        $this->get('/portal/invoices')->assertOk();
        $this->get('/leads')->assertForbidden();
        $this->get('/dashboard')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    protected function invoiceFor(Customer $customer, string $number): Invoice
    {
        return Invoice::forceCreate([
            'number' => $number,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 1200,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 1200,
            'amount_paid' => 0,
            'balance_due' => 1200,
            'status' => 'Sent',
            'user_id' => $this->owner->id,
        ]);
    }

    protected function proposalFor(Customer $customer, string $number): Proposal
    {
        return Proposal::forceCreate([
            'number' => $number,
            'customer_id' => $customer->id,
            'subject' => 'Website rollout',
            'content' => 'Project scope and commercial terms.',
            'total' => 5000,
            'status' => 'Sent',
            'user_id' => $this->owner->id,
        ]);
    }

    protected function otherCustomer(): Customer
    {
        return Customer::forceCreate([
            'name' => 'Other Buyer',
            'email' => 'other@example.com',
            'user_id' => $this->owner->id,
        ]);
    }
}
