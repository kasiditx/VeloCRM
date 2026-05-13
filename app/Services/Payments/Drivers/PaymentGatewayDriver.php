<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Models\Invoice;
use App\Services\Payments\DTO\PaymentCheckout;
use App\Services\Payments\DTO\PaymentWebhookResult;
use Illuminate\Http\Request;

interface PaymentGatewayDriver
{
    public function key(): string;

    public function label(): string;

    public function isConfigured(): bool;

    public function createCheckout(Invoice $invoice, string $returnUrl, string $cancelUrl): PaymentCheckout;

    public function parseWebhook(Request $request): PaymentWebhookResult;
}
