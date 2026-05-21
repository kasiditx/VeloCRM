<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Leads\LeadIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormattingHelpersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_currency_and_date_helpers_follow_settings(): void
    {
        Setting::set('currency_symbol', '€');
        Setting::set('date_format', 'Y-m-d');

        $this->assertSame('€150.50', format_currency(150.5));
        $this->assertSame('2026-04-15', format_date('2026-04-15 10:30:00'));
    }

    public function test_baht_text_helper_formats_thai_currency_words(): void
    {
        $this->assertSame('ศูนย์บาทถ้วน', velocrm_baht_text(0));
        $this->assertSame('สิบเอ็ดบาทถ้วน', velocrm_baht_text(11));
        $this->assertSame('ยี่สิบเอ็ดบาทถ้วน', velocrm_baht_text(21));
        $this->assertSame('หนึ่งร้อยเอ็ดบาทถ้วน', velocrm_baht_text(101));
        $this->assertSame('หนึ่งล้านหนึ่งบาทถ้วน', velocrm_baht_text(1000001));
        $this->assertSame('หนึ่งแสนสองหมื่นสามพันสี่ร้อยห้าสิบหกบาทเจ็ดสิบแปดสตางค์', velocrm_baht_text(123456.78));
        $this->assertSame('หนึ่งบาทถ้วน', velocrm_baht_text(0.999));
    }

    public function test_invoice_pdf_displays_baht_text_for_thb_invoice(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $customer = Customer::create([
            'name' => 'Baht Text Customer',
            'user_id' => $user->id,
        ]);
        $invoice = Invoice::forceCreate([
            'number' => 'INV-BAHT-1',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 123456.78,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 123456.78,
            'amount_paid' => 0,
            'balance_due' => 123456.78,
            'status' => 'Sent',
            'currency' => 'THB',
            'exchange_rate' => 1,
            'user_id' => $user->id,
        ]);

        $html = view('pdf.invoice', [
            'invoice' => $invoice->load('customer', 'items'),
            'logo_base64' => null,
            'company_name' => 'VeloCRM',
            'company_address' => 'Bangkok',
            'company_url' => 'https://example.test',
            'promptpay_qr_data_uri' => null,
            'promptpay_amount' => $invoice->money($invoice->balance_due),
            'promptpay_receiver' => 'VeloCRM',
        ])->render();

        $this->assertStringContainsString('หนึ่งแสนสองหมื่นสามพันสี่ร้อยห้าสิบหกบาทเจ็ดสิบแปดสตางค์', $html);
    }

    public function test_lead_index_uses_configured_currency_symbol(): void
    {
        Setting::set('currency_symbol', '€');
        Setting::set('date_format', 'Y-m-d');

        $user = User::factory()->create();
        $user->assignRole('Staff');

        Lead::create([
            'name' => 'Euro Lead',
            'status' => 'Qualified',
            'value' => 999.99,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(LeadIndex::class)
            ->assertSee('€999.99');
    }
}
