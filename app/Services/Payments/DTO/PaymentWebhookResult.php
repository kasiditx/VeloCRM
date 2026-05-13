<?php

declare(strict_types=1);

namespace App\Services\Payments\DTO;

class PaymentWebhookResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly string $status,
        public readonly ?string $invoiceToken = null,
        public readonly ?int $invoiceId = null,
        public readonly ?float $amount = null,
        public readonly ?string $transactionId = null,
        public readonly ?string $externalReference = null,
        public readonly array $payload = [],
        public readonly ?string $error = null,
    ) {}

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'succeeded', 'success', 'completed'], true);
    }
}
