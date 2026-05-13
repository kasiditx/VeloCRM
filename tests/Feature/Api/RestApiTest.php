<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RestApiTest extends TestCase
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
            'password' => 'password',
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_login_returns_sanctum_token_for_active_staff_user(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->admin->email,
            'password' => 'password',
            'device_name' => 'Feature test',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token']);
    }

    public function test_lead_crud_uses_authenticated_api_token(): void
    {
        Sanctum::actingAs($this->admin, ['crm:read', 'crm:write']);

        $leadId = $this->postJson('/api/leads', [
            'name' => 'API Lead',
            'email' => 'api-lead@example.com',
            'phone' => '0812345678',
            'company' => 'API Company',
            'status' => 'New',
            'source' => 'Website',
            'value' => 1500,
            'notes' => 'Created from API test',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'API Lead')
            ->json('data.id');

        $this->getJson('/api/leads')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'API Lead');

        $this->putJson("/api/leads/{$leadId}", [
            'name' => 'Updated API Lead',
            'email' => 'api-lead@example.com',
            'phone' => '0812345678',
            'company' => 'API Company',
            'status' => 'Qualified',
            'source' => 'Website',
            'value' => 2500,
            'notes' => 'Updated from API test',
        ])->assertOk()
            ->assertJsonPath('data.status', 'Qualified');

        $this->deleteJson("/api/leads/{$leadId}")
            ->assertNoContent();

        $this->assertSoftDeleted('leads', ['id' => $leadId]);
    }

    public function test_invoice_api_recalculates_totals_from_items(): void
    {
        Sanctum::actingAs($this->admin, ['crm:read', 'crm:write']);

        $customer = Customer::create([
            'name' => 'API Customer',
            'email' => 'api-customer@example.com',
            'user_id' => $this->admin->id,
        ]);

        $invoiceId = $this->postJson('/api/invoices', [
            'number' => 'INV-API-1',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'Draft',
            'currency' => 'thb',
            'exchange_rate' => 1,
            'discount' => 100,
            'tax_total' => 70,
            'items' => [
                ['description' => 'Service', 'quantity' => 2, 'unit_price' => 500],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', 1000)
            ->assertJsonPath('data.total', 970)
            ->assertJsonPath('data.currency', 'THB')
            ->json('data.id');

        $invoice = Invoice::with('items')->findOrFail($invoiceId);

        $this->assertEquals(970, $invoice->total);
        $this->assertCount(1, $invoice->items);
    }

    public function test_staff_cannot_access_another_users_lead_through_api(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('Staff');
        $staff = User::factory()->create(['is_active' => true]);
        $staff->assignRole('Staff');

        $lead = Lead::create([
            'name' => 'Private Lead',
            'status' => 'New',
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($staff, ['crm:read', 'crm:write']);

        $this->getJson("/api/leads/{$lead->id}")
            ->assertNotFound();
    }

    public function test_report_summary_endpoint_returns_core_metrics(): void
    {
        Sanctum::actingAs($this->admin, ['crm:read', 'crm:write']);

        $customer = Customer::create([
            'name' => 'Report Customer',
            'user_id' => $this->admin->id,
        ]);

        Invoice::forceCreate([
            'number' => 'INV-REPORT-1',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 1000,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 1000,
            'amount_paid' => 1000,
            'balance_due' => 0,
            'status' => 'Paid',
            'currency' => 'THB',
            'exchange_rate' => 1,
            'user_id' => $this->admin->id,
        ]);

        $this->getJson('/api/reports/summary')
            ->assertOk()
            ->assertJsonPath('stats.revenue', 1000)
            ->assertJsonPath('stats.paid_invoices', 1);
    }
}
