<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Models\Invoice;
use App\Services\Payments\Drivers\Concerns\VerifiesWebhookSignatures;
use App\Services\Payments\DTO\PaymentCheckout;
use App\Services\Payments\DTO\PaymentWebhookResult;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class ExternalCheckoutDriver extends AbstractPaymentGateway
{
    use VerifiesWebhookSignatures;

    public function isConfigured(): bool
    {
        return filled($this->setting('checkout_url')) && filled($this->setting('webhook_secret', '', true));
    }

    public function createCheckout(Invoice $invoice, string $returnUrl, string $cancelUrl): PaymentCheckout
    {
        $checkoutUrl = (string) $this->setting('checkout_url');

        if ($checkoutUrl === '') {
            return new PaymentCheckout(false, message: __(':gateway checkout URL is not configured yet.', ['gateway' => $this->label()]));
        }

        $separator = Str::contains($checkoutUrl, '?') ? '&' : '?';
        $query = http_build_query([
            'invoice' => $invoice->number,
            'public_token' => $invoice->public_token ?: $invoice->ensurePublicToken(),
            'amount' => number_format((float) $invoice->balance_due, 2, '.', ''),
            'currency' => $invoice->currency ?: velocrm_currency_code(),
            'return_url' => $this->appendQuery($returnUrl, ['payment' => $this->key().'_return']),
            'cancel_url' => $this->appendQuery($cancelUrl, ['payment' => 'cancelled']),
        ]);

        return new PaymentCheckout(true, $checkoutUrl.$separator.$query);
    }

    public function parseWebhook(Request $request): PaymentWebhookResult
    {
        if (! $this->verifyHmacSignature($request, (string) $this->setting('webhook_secret', '', true))) {
            return new PaymentWebhookResult(false, 'invalid', error: 'Invalid signature.');
        }

        $payload = $request->json()->all() ?: $request->all();

        return new PaymentWebhookResult(
            valid: true,
            status: strtolower((string) Arr::get($payload, 'status', Arr::get($payload, 'event', ''))),
            invoiceToken: Arr::get($payload, 'public_token') ?: Arr::get($payload, 'invoice_token'),
            invoiceId: filled(Arr::get($payload, 'invoice_id')) ? (int) Arr::get($payload, 'invoice_id') : null,
            amount: filled(Arr::get($payload, 'amount')) ? (float) Arr::get($payload, 'amount') : null,
            transactionId: Arr::get($payload, 'transaction_id') ?: Arr::get($payload, 'id'),
            externalReference: Arr::get($payload, 'external_reference') ?: Arr::get($payload, 'reference'),
            payload: $payload,
        );
    }

    protected function appendQuery(string $url, array $query): string
    {
        return $url.(Str::contains($url, '?') ? '&' : '?').http_build_query($query);
    }
}
