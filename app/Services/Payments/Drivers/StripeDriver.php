<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\Payments\Drivers\Concerns\VerifiesWebhookSignatures;
use App\Services\Payments\DTO\PaymentCheckout;
use App\Services\Payments\DTO\PaymentWebhookResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StripeDriver extends AbstractPaymentGateway
{
    use VerifiesWebhookSignatures;

    public function key(): string
    {
        return 'stripe';
    }

    public function isConfigured(): bool
    {
        return filled($this->setting('secret_key', '', true));
    }

    public function createCheckout(Invoice $invoice, string $returnUrl, string $cancelUrl): PaymentCheckout
    {
        $secretKey = (string) $this->setting('secret_key', '', true);

        if ($secretKey === '') {
            return new PaymentCheckout(false, message: __('Stripe is not configured yet.'));
        }

        $currency = strtolower((string) ($invoice->currency ?: Setting::get('payment_currency', config('payments.currency', 'USD'))));
        $amount = max(0, (int) round(((float) $invoice->balance_due) * 100));

        if ($amount <= 0) {
            return new PaymentCheckout(false, message: __('This invoice is already paid.'));
        }

        $response = Http::asForm()
            ->withToken($secretKey)
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $this->appendQuery($returnUrl, ['payment' => 'stripe_success']),
                'cancel_url' => $this->appendQuery($cancelUrl, ['payment' => 'cancelled']),
                'client_reference_id' => $invoice->public_token ?: $invoice->ensurePublicToken(),
                'metadata[invoice_id]' => $invoice->id,
                'metadata[public_token]' => $invoice->public_token ?: $invoice->ensurePublicToken(),
                'line_items[0][price_data][currency]' => $currency,
                'line_items[0][price_data][product_data][name]' => __('Invoice :number', ['number' => $invoice->number]),
                'line_items[0][price_data][unit_amount]' => $amount,
                'line_items[0][quantity]' => 1,
            ]);

        if (! $response->successful()) {
            return new PaymentCheckout(false, message: __('Stripe checkout could not be created. Check your API keys and mode.'));
        }

        return new PaymentCheckout(
            available: true,
            redirectUrl: $response->json('url'),
        );
    }

    public function parseWebhook(Request $request): PaymentWebhookResult
    {
        if (! $this->verifyHmacSignature($request, (string) $this->setting('webhook_secret', '', true))) {
            return new PaymentWebhookResult(false, 'invalid', error: 'Invalid signature.');
        }

        $payload = $request->json()->all();
        $data = $payload['data']['object'] ?? $payload;
        $status = (string) ($payload['type'] ?? $data['status'] ?? '');

        if (Str::contains($status, 'checkout.session.completed')) {
            $status = 'paid';
        }

        return new PaymentWebhookResult(
            valid: true,
            status: strtolower($status),
            invoiceToken: $data['metadata']['public_token'] ?? $data['client_reference_id'] ?? $payload['public_token'] ?? null,
            invoiceId: isset($data['metadata']['invoice_id']) ? (int) $data['metadata']['invoice_id'] : null,
            amount: isset($data['amount_total']) ? ((float) $data['amount_total'] / 100) : ($payload['amount'] ?? null),
            transactionId: $data['payment_intent'] ?? $data['id'] ?? $payload['transaction_id'] ?? null,
            externalReference: $data['id'] ?? $payload['external_reference'] ?? null,
            payload: $payload,
        );
    }

    private function appendQuery(string $url, array $query): string
    {
        return $url.(Str::contains($url, '?') ? '&' : '?').http_build_query($query);
    }
}
