<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Customers\CustomerForm;
use App\Livewire\Invoices\InvoiceForm;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class ThaiTaxFieldsTest extends TestCase
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

    public function test_customer_form_validates_thai_tax_id_checksum(): void
    {
        Livewire::test(CustomerForm::class)
            ->set('name', 'Invalid Tax Customer')
            ->set('tax_id', '1101700207031')
            ->call('save')
            ->assertHasErrors(['tax_id']);
    }

    public function test_customer_form_saves_tax_id_and_branch(): void
    {
        Livewire::test(CustomerForm::class)
            ->set('name', 'Thai Tax Customer')
            ->set('tax_id', '1101700207030')
            ->set('branch', 'Head Office')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'name' => 'Thai Tax Customer',
            'tax_id' => '1101700207030',
            'branch' => 'Head Office',
        ]);
    }

    public function test_invoice_form_snapshots_customer_tax_fields_and_preserves_existing_snapshot(): void
    {
        $customer = Customer::create([
            'name' => 'Snapshot Customer',
            'tax_id' => '1101700207030',
            'branch' => 'Head Office',
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(InvoiceForm::class)
            ->set('number', 'INV-TAX-1')
            ->set('customer_id', $customer->id)
            ->set('invoice_date', now()->toDateString())
            ->set('due_date', now()->addDays(7)->toDateString())
            ->set('items.0.description', 'Tax invoice service')
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 1000)
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('number', 'INV-TAX-1')->firstOrFail();
        $this->assertSame('1101700207030', $invoice->tax_id);
        $this->assertSame('Head Office', $invoice->branch);

        $customer->update([
            'tax_id' => '1234567890121',
            'branch' => 'Branch 00001',
        ]);

        Livewire::test(InvoiceForm::class, ['invoiceId' => $invoice->id])
            ->set('items.0.description', 'Updated service')
            ->call('save')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertSame('1101700207030', $invoice->tax_id);
        $this->assertSame('Head Office', $invoice->branch);
    }

    public function test_invoice_pdf_view_displays_tax_id_and_branch_snapshot(): void
    {
        $customer = Customer::create([
            'name' => 'PDF Tax Customer',
            'user_id' => $this->admin->id,
        ]);
        $invoice = Invoice::forceCreate([
            'number' => 'INV-PDF-TAX-1',
            'customer_id' => $customer->id,
            'tax_id' => '1101700207030',
            'branch' => 'Head Office',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 1000,
            'tax_total' => 70,
            'discount' => 0,
            'total' => 1070,
            'amount_paid' => 0,
            'balance_due' => 1070,
            'status' => 'Sent',
            'currency' => 'THB',
            'exchange_rate' => 1,
            'user_id' => $this->admin->id,
        ]);

        $html = view('pdf.invoice', [
            'invoice' => $invoice->load('customer', 'items'),
            'logo_base64' => null,
            'company_name' => 'VeloCRM',
            'company_address' => 'Bangkok',
            'company_url' => 'https://example.test',
            'locale' => 'th',
        ])->render();

        $this->assertStringContainsString('เลขประจำตัวผู้เสียภาษี: 1101700207030', $html);
        $this->assertStringContainsString('สาขา: Head Office', $html);
    }

    public function test_customer_api_accepts_valid_tax_id(): void
    {
        Sanctum::actingAs($this->admin, ['crm:read', 'crm:write']);

        $this->postJson('/api/customers', [
            'name' => 'API Tax Customer',
            'tax_id' => '1101700207030',
            'branch' => 'Head Office',
        ])->assertCreated()
            ->assertJsonPath('data.tax_id', '1101700207030')
            ->assertJsonPath('data.branch', 'Head Office');
    }
}
