<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('signedWebhookPayloads')]
    public function test_signed_gateway_webhooks_record_paid_invoice_payment(string $gateway, string $transactionId): void
    {
        $this->seed(DefaultRolesSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Admin');

        Setting::set('payment_'.$gateway.'_webhook_secret', 'webhook-secret', true);

        $invoice = $this->invoiceWithToken($owner, 'INV-WEBHOOK-'.strtoupper($gateway));
        $payload = $this->webhookPayload($gateway, $invoice);
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->postJson(route('payments.webhook', $gateway), $payload, [
            'X-VeloCRM-Signature' => hash_hmac('sha256', $json, 'webhook-secret'),
        ])->assertOk()
            ->assertJson(['message' => 'Payment recorded.']);

        $invoice->refresh();

        $this->assertSame('250.00', number_format((float) $invoice->amount_paid, 2, '.', ''));
        $this->assertSame('250.00', number_format((float) $invoice->balance_due, 2, '.', ''));
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'status' => 'paid',
        ]);
    }

    public function test_gateway_webhook_rejects_invalid_signature_without_recording_payment(): void
    {
        Setting::set('payment_stripe_webhook_secret', 'webhook-secret', true);

        $this->postJson(route('payments.webhook', 'stripe'), [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'client_reference_id' => 'missing-token',
                    'amount_total' => 25000,
                    'payment_intent' => 'pi_invalid',
                ],
            ],
        ], [
            'X-VeloCRM-Signature' => 'invalid-signature',
        ])->assertUnauthorized();

        $this->assertDatabaseMissing('payments', [
            'transaction_id' => 'pi_invalid',
        ]);
    }

    public static function signedWebhookPayloads(): array
    {
        return [
            'stripe' => ['stripe', 'pi_test_123'],
            'paypal' => ['paypal', 'paypal_txn_123'],
        ];
    }

    private function webhookPayload(string $gateway, Invoice $invoice): array
    {
        if ($gateway === 'stripe') {
            return [
                'type' => 'checkout.session.completed',
                'data' => [
                    'object' => [
                        'id' => 'cs_test_123',
                        'client_reference_id' => $invoice->public_token,
                        'metadata' => [
                            'invoice_id' => $invoice->id,
                            'public_token' => $invoice->public_token,
                        ],
                        'amount_total' => 25000,
                        'payment_intent' => 'pi_test_123',
                    ],
                ],
            ];
        }

        return [
            'status' => 'paid',
            'public_token' => $invoice->public_token,
            'amount' => 250,
            'transaction_id' => 'paypal_txn_123',
            'external_reference' => 'paypal_order_123',
        ];
    }

    private function invoiceWithToken(User $owner, string $number): Invoice
    {
        $customer = Customer::forceCreate([
            'name' => 'Webhook Customer',
            'email' => strtolower($number).'@example.com',
            'user_id' => $owner->id,
        ]);

        $invoice = Invoice::forceCreate([
            'number' => $number,
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
            'user_id' => $owner->id,
        ]);
        $invoice->ensurePublicToken();

        return $invoice->refresh();
    }
}
