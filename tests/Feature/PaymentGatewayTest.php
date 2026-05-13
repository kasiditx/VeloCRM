<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\Settings;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
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
    }

    public function test_admin_can_save_payment_gateway_settings(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Settings::class)
            ->call('setTab', 'payments')
            ->assertSet('activeTab', 'payments')
            ->set('payment_driver', 'manual')
            ->set('payment_mode', 'test')
            ->set('payment_currency', 'thb')
            ->set('payment_bank_transfer_instructions', "Bank: Demo Bank\nAccount: 123")
            ->set('payment_stripe_public_key', 'pk_test_demo')
            ->set('payment_stripe_secret_key', 'sk_test_demo')
            ->set('payment_stripe_webhook_secret', 'whsec_demo')
            ->call('savePaymentGateways')
            ->assertHasNoErrors();

        $this->assertSame('manual', Setting::get('payment_driver'));
        $this->assertSame('THB', Setting::get('payment_currency'));
        $this->assertSame("Bank: Demo Bank\nAccount: 123", Setting::get('payment_manual_instructions'));
        $this->assertSame('sk_test_demo', Setting::get('payment_stripe_secret_key', '', true));
        $this->assertSame('whsec_demo', Setting::get('payment_stripe_webhook_secret', '', true));
    }

    public function test_public_invoice_pay_button_redirects_to_manual_instructions(): void
    {
        Setting::set('payment_driver', 'manual');
        Setting::set('payment_manual_instructions', "Bank: Demo Bank\nAccount: 123");

        $invoice = $this->invoiceWithToken();

        $this->get(route('public.invoice.show', $invoice->public_token))
            ->assertOk()
            ->assertSee('Pay with Bank Transfer');

        $this->get(route('public.invoice.pay', $invoice->public_token))
            ->assertRedirect(route('public.invoice.show', $invoice->public_token).'?payment=manual');
    }

    public function test_signed_gateway_webhook_records_payment_and_updates_invoice_totals(): void
    {
        Setting::set('payment_driver', 'paypal');
        Setting::set('payment_paypal_webhook_secret', 'webhook-secret', true);

        $invoice = $this->invoiceWithToken();
        $payload = [
            'status' => 'paid',
            'public_token' => $invoice->public_token,
            'amount' => 250,
            'transaction_id' => 'txn_123',
            'external_reference' => 'order_123',
        ];
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $json, 'webhook-secret');

        $this->postJson(route('payments.webhook', 'paypal'), $payload, [
            'X-VeloCRM-Signature' => $signature,
        ])->assertOk()
            ->assertJson(['message' => 'Payment recorded.']);

        $invoice->refresh();

        $this->assertSame('250.00', number_format((float) $invoice->amount_paid, 2, '.', ''));
        $this->assertSame('250.00', number_format((float) $invoice->balance_due, 2, '.', ''));
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'gateway' => 'paypal',
            'transaction_id' => 'txn_123',
            'status' => 'paid',
        ]);
    }

    public function test_gateway_webhook_rejects_invalid_signature(): void
    {
        Setting::set('payment_paypal_webhook_secret', 'webhook-secret', true);

        $this->postJson(route('payments.webhook', 'paypal'), [
            'status' => 'paid',
            'invoice_id' => 1,
            'amount' => 10,
            'transaction_id' => 'bad_txn',
        ], [
            'X-VeloCRM-Signature' => 'invalid',
        ])->assertUnauthorized();
    }

    private function invoiceWithToken(): Invoice
    {
        $customer = Customer::create([
            'name' => 'Gateway Customer',
            'email' => 'gateway@example.com',
            'user_id' => $this->admin->id,
        ]);

        $invoice = Invoice::forceCreate([
            'number' => 'INV-PAY-1',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 500,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 500,
            'amount_paid' => 0,
            'balance_due' => 500,
            'status' => 'Sent',
            'user_id' => $this->admin->id,
        ]);
        $invoice->ensurePublicToken();

        return $invoice->refresh();
    }
}
