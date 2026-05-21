<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfLocaleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    public function test_invoice_pdf_view_renders_thai_labels_from_locale(): void
    {
        app()->setLocale('th');

        $html = $this->renderInvoicePdfHtml('th');

        $this->assertStringContainsString('ใบแจ้งหนี้', $html);
        $this->assertStringContainsString('เลขที่ใบแจ้งหนี้', $html);
        $this->assertStringContainsString('วันที่ออกเอกสาร', $html);
        $this->assertStringContainsString('ลูกค้า', $html);
        $this->assertStringContainsString('ราคาต่อหน่วย', $html);
        $this->assertStringContainsString('รวมสุทธิ', $html);
        $this->assertStringNotContainsString('????', $html);
    }

    public function test_invoice_pdf_view_renders_english_labels_from_locale(): void
    {
        app()->setLocale('en');

        $html = $this->renderInvoicePdfHtml('en');

        $this->assertStringContainsString('Invoice No.', $html);
        $this->assertStringContainsString('Issue Date', $html);
        $this->assertStringContainsString('Bill To', $html);
        $this->assertStringContainsString('Unit Price', $html);
        $this->assertStringContainsString('Net Total', $html);
        $this->assertStringNotContainsString('เลขที่ใบแจ้งหนี้', $html);
        $this->assertStringNotContainsString('????', $html);
    }

    public function test_invoice_pdf_route_accepts_locale_query_string(): void
    {
        $invoice = $this->invoice();

        $this->actingAs($this->admin)
            ->get(route('invoices.pdf', ['invoice' => $invoice->id, 'locale' => 'th']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->admin)
            ->get(route('invoices.pdf', ['invoice' => $invoice->id, 'locale' => 'en']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_pdf_route_falls_back_when_locale_is_missing(): void
    {
        $invoice = $this->invoice();

        $this->actingAs($this->admin)
            ->get(route('invoices.pdf', ['invoice' => $invoice->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_show_pdf_link_carries_current_locale(): void
    {
        $invoice = $this->invoice();

        $this->actingAs($this->admin)
            ->withSession(['locale' => 'th'])
            ->get(route('invoices.show', $invoice->id))
            ->assertOk()
            ->assertSee('locale=th', false);
    }

    private function renderInvoicePdfHtml(string $locale): string
    {
        $invoice = $this->invoice();

        return view('pdf.invoice', [
            'invoice' => $invoice->load('customer', 'items'),
            'logo_base64' => null,
            'company_name' => 'VeloCRM',
            'company_address' => 'Bangkok',
            'company_url' => 'https://example.test',
            'promptpay_qr_data_uri' => null,
            'promptpay_amount' => $invoice->money($invoice->balance_due),
            'promptpay_receiver' => 'VeloCRM',
            'locale' => $locale,
        ])->render();
    }

    private function invoice(): Invoice
    {
        $customer = Customer::query()->firstOrCreate(
            ['email' => 'pdf-locale@example.test'],
            [
                'name' => 'PDF Locale Customer',
                'company' => 'PDF Locale Co., Ltd.',
                'address' => 'Bangkok',
                'tax_id' => '0105550000000',
                'branch' => '00000',
                'user_id' => $this->admin->id,
            ]
        );

        $invoice = Invoice::query()->firstOrCreate(
            ['number' => 'INV-PDF-LOCALE'],
            [
                'customer_id' => $customer->id,
                'invoice_date' => '2026-05-21',
                'due_date' => '2026-05-28',
                'subtotal' => 1200,
                'tax_total' => 84,
                'discount' => 0,
                'total' => 1284,
                'amount_paid' => 0,
                'balance_due' => 1284,
                'status' => 'Sent',
                'currency' => 'THB',
                'exchange_rate' => 1,
                'notes' => 'Locale PDF note',
                'user_id' => $this->admin->id,
            ]
        );

        $invoice->items()->firstOrCreate(
            ['description' => 'Implementation service'],
            [
                'quantity' => 1,
                'unit_price' => 1200,
                'amount' => 1200,
                'wht_rate' => 0,
                'wht_amount' => 0,
            ]
        );

        return $invoice;
    }
}
