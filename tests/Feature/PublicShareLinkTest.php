<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceShow as InternalInvoiceShow;
use App\Livewire\Proposals\ProposalShow as InternalProposalShow;
use App\Livewire\PublicShare\InvoiceShow as PublicInvoiceShow;
use App\Livewire\PublicShare\ProposalShow as PublicProposalShow;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PublicShareLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('Admin');

        $this->customer = Customer::forceCreate([
            'name' => 'Public Buyer',
            'email' => 'buyer@example.com',
            'user_id' => $this->owner->id,
        ]);
    }

    public function test_public_invoice_link_renders_without_login_and_records_first_view(): void
    {
        $invoice = $this->invoice();
        $token = $invoice->ensurePublicToken();

        Livewire::test(PublicInvoiceShow::class, ['token' => $token])
            ->assertSee('INV-PUBLIC-1')
            ->assertSee('Public Buyer');

        $invoice->refresh();

        $this->assertNotNull($invoice->public_viewed_at);
        $this->assertNotNull($invoice->public_viewed_ip);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Invoice::class,
            'subject_id' => $invoice->id,
            'event' => 'public_viewed',
        ]);

        Livewire::test(PublicInvoiceShow::class, ['token' => $token])
            ->assertSee('INV-PUBLIC-1');

        $this->assertSame(1, Activity::where('subject_type', Invoice::class)->where('subject_id', $invoice->id)->where('event', 'public_viewed')->count());
    }

    public function test_public_proposal_link_renders_without_login_and_can_respond(): void
    {
        $proposal = $this->proposal();
        $token = $proposal->ensurePublicToken();

        Livewire::test(PublicProposalShow::class, ['token' => $token])
            ->assertSee('Website rollout')
            ->call('accept')
            ->assertHasNoErrors();

        $this->assertSame('Accepted', $proposal->refresh()->status);
        $this->assertNotNull($proposal->public_viewed_at);

        Livewire::test(PublicProposalShow::class, ['token' => $token])
            ->call('reject')
            ->assertHasNoErrors();

        $this->assertSame('Declined', $proposal->refresh()->status);
    }

    public function test_internal_show_pages_generate_copyable_public_links(): void
    {
        $this->actingAs($this->owner);

        $invoice = $this->invoice();
        $proposal = $this->proposal();

        Livewire::test(InternalInvoiceShow::class, ['invoiceId' => $invoice->id])
            ->assertSet('publicShareUrl', route('public.invoice.show', $invoice->refresh()->public_token))
            ->assertSee('Copy Share Link');

        Livewire::test(InternalProposalShow::class, ['proposalId' => $proposal->id])
            ->assertSet('publicShareUrl', route('public.proposal.show', $proposal->refresh()->public_token))
            ->assertSee('Copy Share Link');
    }

    public function test_invalid_public_tokens_return_404(): void
    {
        $this->get(route('public.invoice.show', 'not-a-token'))->assertNotFound();
        $this->get(route('public.proposal.show', 'not-a-token'))->assertNotFound();
    }

    protected function invoice(): Invoice
    {
        return Invoice::forceCreate([
            'number' => 'INV-PUBLIC-1',
            'customer_id' => $this->customer->id,
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

    protected function proposal(): Proposal
    {
        return Proposal::forceCreate([
            'number' => 'PROP-PUBLIC-1',
            'customer_id' => $this->customer->id,
            'subject' => 'Website rollout',
            'content' => 'Public proposal terms.',
            'total' => 5000,
            'status' => 'Sent',
            'user_id' => $this->owner->id,
        ]);
    }
}
