<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\SendOverdueReminders;
use App\Livewire\Invoices\InvoiceForm;
use App\Livewire\Tasks\TaskForm;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\InvoiceSentNotification;
use App\Notifications\TaskAssignedNotification;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
        Notification::fake();
    }

    public function test_invoice_sent_notification_is_dispatched_to_customer(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $customer = Customer::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(InvoiceForm::class)
            ->set('customer_id', $customer->id)
            ->set('invoice_date', now()->toDateString())
            ->set('due_date', now()->addDays(7)->toDateString())
            ->set('status', 'Sent')
            ->set('items', [
                ['description' => 'Service fee', 'quantity' => 1, 'unit_price' => 1500, 'amount' => 1500],
            ])
            ->call('save')
            ->assertHasNoErrors();

        Notification::assertSentTo($customer, InvoiceSentNotification::class);
    }

    public function test_task_assigned_notification_is_dispatched_to_assignee(): void
    {
        $creator = User::factory()->create();
        $creator->assignRole('Staff');

        $assignee = User::factory()->create();
        $assignee->assignRole('Staff');

        $lead = Lead::create([
            'name' => 'Task Lead',
            'status' => 'New',
            'value' => 1000,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($creator);

        Livewire::test(TaskForm::class)
            ->set('title', 'Call lead back')
            ->set('description', 'Follow up by phone')
            ->set('status', 'Todo')
            ->set('priority', 'High')
            ->set('due_date', now()->addDay()->toDateString())
            ->set('assigned_to', $assignee->id)
            ->set('relatable_type', Lead::class)
            ->set('relatable_id', $lead->id)
            ->call('save')
            ->assertHasNoErrors();

        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    public function test_overdue_reminder_command_notifies_invoice_owner(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Staff');

        $customer = Customer::create([
            'name' => 'Overdue Customer',
            'email' => 'customer@example.com',
            'user_id' => $owner->id,
        ]);

        Invoice::create([
            'number' => 'INV-OD-1',
            'customer_id' => $customer->id,
            'invoice_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(2)->toDateString(),
            'subtotal' => 2000,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 2000,
            'amount_paid' => 0,
            'balance_due' => 2000,
            'status' => 'Sent',
            'user_id' => $owner->id,
        ]);

        Artisan::call(SendOverdueReminders::class);

        Notification::assertSentTo($owner, InvoiceOverdueNotification::class);
    }
}
