<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceForm;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\TaxTemplate;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class WithholdingTaxCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Customer $customer;

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

        $this->customer = Customer::create([
            'name' => 'WHT Customer',
            'email' => 'wht@example.test',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_invoice_form_calculates_withholding_tax_from_line_items(): void
    {
        $vat = TaxTemplate::forceCreate([
            'name' => 'VAT 7%',
            'rate' => 7,
            'is_default' => true,
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(InvoiceForm::class)
            ->set('number', 'INV-WHT-1')
            ->set('customer_id', $this->customer->id)
            ->set('invoice_date', now()->toDateString())
            ->set('due_date', now()->addDays(7)->toDateString())
            ->set('tax_id', $vat->id)
            ->set('items.0.description', 'Consulting service')
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 1000)
            ->set('items.0.wht_rate', 3)
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::with('items')->where('number', 'INV-WHT-1')->firstOrFail();

        $this->assertSame('1000.00', number_format((float) $invoice->subtotal, 2, '.', ''));
        $this->assertSame('70.00', number_format((float) $invoice->tax_total, 2, '.', ''));
        $this->assertSame('30.00', number_format((float) $invoice->wht_total, 2, '.', ''));
        $this->assertSame('1040.00', number_format((float) $invoice->total, 2, '.', ''));
        $this->assertSame('1040.00', number_format((float) $invoice->balance_due, 2, '.', ''));
        $this->assertSame('3.00', number_format((float) $invoice->items->first()->wht_rate, 2, '.', ''));
        $this->assertSame('30.00', number_format((float) $invoice->items->first()->wht_amount, 2, '.', ''));
    }

    public function test_invoice_update_totals_recalculates_wht_total_and_balance(): void
    {
        $invoice = Invoice::forceCreate([
            'number' => 'INV-WHT-UPDATE',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 0,
            'tax_total' => 0,
            'wht_total' => 0,
            'discount' => 0,
            'total' => 0,
            'amount_paid' => 100,
            'balance_due' => 0,
            'status' => 'Sent',
            'currency' => 'THB',
            'exchange_rate' => 1,
            'user_id' => $this->admin->id,
        ]);

        $invoice->items()->create([
            'description' => 'Professional service',
            'quantity' => 2,
            'unit_price' => 500,
            'amount' => 1000,
            'wht_rate' => 3,
            'wht_amount' => 30,
        ]);
        $invoice->payments()->create([
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
            'status' => 'paid',
        ]);

        $invoice->updateTotals();

        $invoice->refresh();
        $this->assertSame('30.00', number_format((float) $invoice->wht_total, 2, '.', ''));
        $this->assertSame('970.00', number_format((float) $invoice->total, 2, '.', ''));
        $this->assertSame('870.00', number_format((float) $invoice->balance_due, 2, '.', ''));
        $this->assertSame('Partially Paid', $invoice->status);
    }

    public function test_invoice_pdf_displays_withholding_tax_and_net_total_label(): void
    {
        $invoice = Invoice::forceCreate([
            'number' => 'INV-WHT-PDF',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 1000,
            'tax_total' => 70,
            'wht_total' => 30,
            'discount' => 0,
            'total' => 1040,
            'amount_paid' => 0,
            'balance_due' => 1040,
            'status' => 'Sent',
            'currency' => 'THB',
            'exchange_rate' => 1,
            'user_id' => $this->admin->id,
        ]);
        $invoice->items()->create([
            'description' => 'Consulting service',
            'quantity' => 1,
            'unit_price' => 1000,
            'amount' => 1000,
            'wht_rate' => 3,
            'wht_amount' => 30,
        ]);

        $html = view('pdf.invoice', [
            'invoice' => $invoice->load('customer', 'items'),
            'logo_base64' => null,
            'company_name' => 'VeloCRM',
            'company_address' => 'Bangkok',
            'company_url' => 'https://example.test',
            'locale' => 'th',
        ])->render();

        $this->assertStringContainsString('หัก ณ ที่จ่าย (3%)', $html);
        $this->assertStringContainsString('รวมสุทธิ', $html);
        $this->assertStringContainsString('-฿30.00 THB', $html);
    }

    public function test_invoice_api_accepts_withholding_tax_rate_on_items(): void
    {
        Sanctum::actingAs($this->admin, ['crm:read', 'crm:write']);

        $this->postJson('/api/invoices', [
            'number' => 'INV-WHT-API',
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'Draft',
            'currency' => 'THB',
            'exchange_rate' => 1,
            'tax_total' => 70,
            'items' => [
                ['description' => 'API service', 'quantity' => 1, 'unit_price' => 1000, 'wht_rate' => 3],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', 1000)
            ->assertJsonPath('data.tax_total', 70)
            ->assertJsonPath('data.wht_total', 30)
            ->assertJsonPath('data.total', 1040)
            ->assertJsonPath('data.items.0.wht_rate', 3)
            ->assertJsonPath('data.items.0.wht_amount', 30);
    }
}
