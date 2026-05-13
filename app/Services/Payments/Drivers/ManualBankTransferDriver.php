<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Models\Invoice;
use App\Services\Payments\DTO\PaymentCheckout;
use App\Services\Payments\DTO\PaymentWebhookResult;
use Illuminate\Http\Request;

class ManualBankTransferDriver extends AbstractPaymentGateway
{
    public function key(): string
    {
        return 'manual';
    }

    public function isConfigured(): bool
    {
        return filled($this->setting('instructions'));
    }

    public function createCheckout(Invoice $invoice, string $returnUrl, string $cancelUrl): PaymentCheckout
    {
        if (! $this->isConfigured()) {
            return new PaymentCheckout(
                available: false,
                message: __('Bank transfer instructions are not configured yet.'),
            );
        }

        return new PaymentCheckout(
            available: true,
            redirectUrl: $returnUrl.(str_contains($returnUrl, '?') ? '&' : '?').'payment=manual',
        );
    }

    public function parseWebhook(Request $request): PaymentWebhookResult
    {
        return new PaymentWebhookResult(
            valid: false,
            status: 'unsupported',
            error: 'Manual bank transfer does not support webhooks.',
        );
    }
}
