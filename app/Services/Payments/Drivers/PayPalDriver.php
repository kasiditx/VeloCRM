<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

class PayPalDriver extends ExternalCheckoutDriver
{
    public function key(): string
    {
        return 'paypal';
    }
}
