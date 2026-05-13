<?php

declare(strict_types=1);

namespace App\Services\Payments\DTO;

class PaymentCheckout
{
    public function __construct(
        public readonly bool $available,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $message = null,
    ) {}
}
