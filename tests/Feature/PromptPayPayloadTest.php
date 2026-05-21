<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use App\Support\PromptPay;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptPayPayloadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
    }

    public function test_promptpay_payload_normalizes_mobile_number_and_includes_amount(): void
    {
        $payload = PromptPay::payload('081-234-5678', 1040);

        $this->assertStringStartsWith('000201010212', $payload);
        $this->assertStringContainsString('0016A00000067701011101130066812345678', $payload);
        $this->assertStringContainsString('5303764', $payload);
        $this->assertStringContainsString('54071040.00', $payload);
        $this->assertMatchesRegularExpression('/6304[A-F0-9]{4}$/', $payload);
    }

    public function test_promptpay_payload_accepts_thai_tax_id(): void
    {
        $payload = PromptPay::payload('0105550000000', 250.5);

        $this->assertStringContainsString('0016A00000067701011102130105550000000', $payload);
        $this->assertStringContainsString('5406250.50', $payload);
    }

    public function test_promptpay_qr_data_uri_is_generated_for_thb_invoice(): void
    {
        $invoice = $this->invoice('THB');

        $dataUri = PromptPay::invoiceQrDataUri($invoice, '0812345678');

        $this->assertIsString($dataUri);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
    }

    public function test_promptpay_qr_is_hidden_for_non_thb_invoice(): void
    {
        $invoice = $this->invoice('USD');

        $this->assertNull(PromptPay::invoiceQrDataUri($invoice, '0812345678'));
    }

    public function test_public_invoice_shows_promptpay_qr_and_can_confirm_transfer(): void
    {
        Setting::set('company_name', 'VeloCRM Thailand');
        Setting::set('payment_driver', 'manual');
        Setting::set('payment_manual_instructions', 'Transfer and upload the slip.');
        Setting::set('promptpay_id', '0812345678');

        $invoice = $this->invoice('THB');
        $invoice->ensurePublicToken();

        $this->get(route('public.invoice.show', $invoice->public_token))
            ->assertOk()
            ->assertSee('PromptPay QR')
            ->assertSee('VeloCRM Thailand')
            ->assertSee('Confirm Transfer');

        $this->post(route('public.invoice.confirm-transfer', $invoice->public_token))
            ->assertRedirect(route('public.invoice.show', $invoice->public_token));

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'gateway' => 'manual',
            'status' => 'pending',
            'payment_method' => 'PromptPay / Bank Transfer',
        ]);
    }

    private function invoice(string $currency): Invoice
    {
        $customer = Customer::create([
            'name' => 'PromptPay Customer',
            'email' => 'promptpay@example.test',
            'user_id' => $this->admin->id,
        ]);

        return Invoice::forceCreate([
            'number' => 'INV-PP-'.$currency,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 1000,
            'tax_total' => 70,
            'discount' => 0,
            'total' => 1070,
            'amount_paid' => 30,
            'balance_due' => 1040,
            'status' => 'Partially Paid',
            'currency' => $currency,
            'exchange_rate' => 1,
            'user_id' => $this->admin->id,
        ]);
    }
}
