<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $otherStaff;

    private Customer $customer;

    private User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);

        $this->admin = $this->userWithRole('Admin');
        $this->staff = $this->userWithRole('Staff');
        $this->otherStaff = $this->userWithRole('Staff');

        $this->customer = $this->customerFor($this->staff, 'policy-customer@example.com');
        $this->customerUser = User::factory()
            ->forCustomer($this->customer->id)
            ->create(['email' => 'policy-portal@example.com']);
        $this->customerUser->assignRole('Customer');
    }

    public function test_lead_policy_allows_admin_and_owner_but_blocks_other_staff_mutation(): void
    {
        $lead = $this->leadFor($this->staff);

        $this->assertTrue($this->admin->can('view', $lead));
        $this->assertTrue($this->staff->can('update', $lead));
        $this->assertTrue($this->staff->can('delete', $lead));
        $this->assertFalse($this->otherStaff->can('update', $lead));
        $this->assertFalse($this->otherStaff->can('delete', $lead));
        $this->assertFalse($this->staff->can('forceDelete', $lead));
        $this->assertTrue($this->admin->can('forceDelete', $lead));
    }

    public function test_customer_policy_allows_admin_and_owner_but_blocks_other_staff_mutation(): void
    {
        $customer = $this->customerFor($this->staff, 'owned-customer@example.com');

        $this->assertTrue($this->admin->can('view', $customer));
        $this->assertTrue($this->staff->can('update', $customer));
        $this->assertTrue($this->staff->can('delete', $customer));
        $this->assertFalse($this->otherStaff->can('update', $customer));
        $this->assertFalse($this->otherStaff->can('delete', $customer));
        $this->assertFalse($this->staff->can('forceDelete', $customer));
        $this->assertTrue($this->admin->can('forceDelete', $customer));
    }

    public function test_invoice_policy_scopes_staff_and_customer_access(): void
    {
        $invoice = $this->invoiceFor($this->customer, $this->staff, 'INV-POL-1');
        $otherInvoice = $this->invoiceFor(
            $this->customerFor($this->otherStaff, 'other-invoice-customer@example.com'),
            $this->otherStaff,
            'INV-POL-2'
        );

        $this->assertTrue($this->admin->can('view', $invoice));
        $this->assertTrue($this->staff->can('update', $invoice));
        $this->assertFalse($this->otherStaff->can('update', $invoice));
        $this->assertTrue($this->customerUser->can('view', $invoice));
        $this->assertFalse($this->customerUser->can('view', $otherInvoice));
        $this->assertFalse($this->customerUser->can('create', Invoice::class));
        $this->assertFalse($this->customerUser->can('delete', $invoice));
        $this->assertTrue($this->admin->can('forceDelete', $invoice));
    }

    public function test_proposal_policy_scopes_customer_response_to_owned_proposals(): void
    {
        $proposal = $this->proposalFor($this->customer, $this->staff, 'PROP-POL-1');
        $otherProposal = $this->proposalFor(
            $this->customerFor($this->otherStaff, 'other-proposal-customer@example.com'),
            $this->otherStaff,
            'PROP-POL-2'
        );

        $this->assertTrue($this->admin->can('view', $proposal));
        $this->assertTrue($this->staff->can('update', $proposal));
        $this->assertFalse($this->otherStaff->can('update', $proposal));
        $this->assertTrue($this->customerUser->can('view', $proposal));
        $this->assertTrue($this->customerUser->can('respond', $proposal));
        $this->assertFalse($this->customerUser->can('respond', $otherProposal));
        $this->assertFalse($this->customerUser->can('create', Proposal::class));
        $this->assertFalse($this->customerUser->can('delete', $proposal));
        $this->assertTrue($this->admin->can('forceDelete', $proposal));
    }

    public function test_task_policy_allows_admin_and_owner_without_restore_or_force_delete(): void
    {
        $task = Task::forceCreate([
            'title' => 'Policy task',
            'description' => 'Policy coverage',
            'due_date' => now()->addDay()->toDateString(),
            'priority' => 'Medium',
            'status' => 'Open',
            'user_id' => $this->staff->id,
            'assigned_to' => $this->staff->id,
        ]);

        $this->assertTrue($this->admin->can('view', $task));
        $this->assertTrue($this->staff->can('update', $task));
        $this->assertFalse($this->otherStaff->can('update', $task));
        $this->assertFalse($this->admin->can('restore', $task));
        $this->assertFalse($this->admin->can('forceDelete', $task));
    }

    public function test_payment_policy_allows_invoice_owner_and_admin_to_view(): void
    {
        $payment = Payment::forceCreate([
            'invoice_id' => $this->invoiceFor($this->customer, $this->staff, 'INV-POL-PAY')->id,
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cash',
            'gateway' => 'manual',
            'status' => 'paid',
        ]);

        $this->assertTrue($this->admin->can('view', $payment));
        $this->assertTrue($this->staff->can('view', $payment));
        $this->assertFalse($this->otherStaff->can('view', $payment));
        $this->assertTrue($this->customerUser->can('create', Payment::class));
    }

    public function test_note_and_attachment_policies_allow_admin_or_owner_delete_only(): void
    {
        $lead = $this->leadFor($this->staff);
        $note = Note::forceCreate([
            'notable_type' => Lead::class,
            'notable_id' => $lead->id,
            'content' => 'Policy note',
            'user_id' => $this->staff->id,
        ]);
        $attachment = Attachment::forceCreate([
            'attachable_type' => Lead::class,
            'attachable_id' => $lead->id,
            'filename' => 'policy.pdf',
            'path' => 'attachments/policy.pdf',
            'size' => 1024,
            'user_id' => $this->staff->id,
        ]);

        $this->assertTrue($this->admin->can('delete', $note));
        $this->assertTrue($this->staff->can('delete', $note));
        $this->assertFalse($this->otherStaff->can('delete', $note));
        $this->assertTrue($this->admin->can('delete', $attachment));
        $this->assertTrue($this->staff->can('delete', $attachment));
        $this->assertFalse($this->otherStaff->can('delete', $attachment));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function leadFor(User $owner): Lead
    {
        return Lead::forceCreate([
            'name' => 'Policy Lead',
            'email' => 'policy-lead@example.com',
            'status' => 'New',
            'value' => 1000,
            'user_id' => $owner->id,
        ]);
    }

    private function customerFor(User $owner, string $email): Customer
    {
        return Customer::forceCreate([
            'name' => 'Policy Customer',
            'email' => $email,
            'user_id' => $owner->id,
        ]);
    }

    private function invoiceFor(Customer $customer, User $owner, string $number): Invoice
    {
        return Invoice::forceCreate([
            'number' => $number,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 500,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 500,
            'amount_paid' => 0,
            'balance_due' => 500,
            'status' => 'Sent',
            'user_id' => $owner->id,
        ]);
    }

    private function proposalFor(Customer $customer, User $owner, string $number): Proposal
    {
        return Proposal::forceCreate([
            'number' => $number,
            'customer_id' => $customer->id,
            'subject' => 'Policy proposal',
            'content' => 'Proposal body',
            'total' => 500,
            'status' => 'Sent',
            'user_id' => $owner->id,
        ]);
    }
}
