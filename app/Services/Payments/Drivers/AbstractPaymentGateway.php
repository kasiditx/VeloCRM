<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Models\Setting;
use App\Services\Payments\DTO\PaymentWebhookResult;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

abstract class AbstractPaymentGateway implements PaymentGatewayDriver
{
    public function label(): string
    {
        return (string) config("payments.drivers.{$this->key()}.label", ucfirst($this->key()));
    }

    protected function setting(string $name, mixed $default = '', bool $decrypt = false): mixed
    {
        $key = "payment_{$this->key()}_{$name}";

        return Setting::get($key, config("payments.drivers.{$this->key()}.{$name}", $default), $decrypt);
    }

    protected function genericWebhookResult(Request $request): PaymentWebhookResult
    {
        $payload = $request->json()->all() ?: $request->all();

        return new PaymentWebhookResult(
            valid: true,
            status: strtolower((string) Arr::get($payload, 'status', '')),
            invoiceToken: Arr::get($payload, 'public_token') ?: Arr::get($payload, 'invoice_token'),
            invoiceId: filled(Arr::get($payload, 'invoice_id')) ? (int) Arr::get($payload, 'invoice_id') : null,
            amount: filled(Arr::get($payload, 'amount')) ? (float) Arr::get($payload, 'amount') : null,
            transactionId: Arr::get($payload, 'transaction_id') ?: Arr::get($payload, 'id'),
            externalReference: Arr::get($payload, 'external_reference') ?: Arr::get($payload, 'reference'),
            payload: $payload,
        );
    }
}
