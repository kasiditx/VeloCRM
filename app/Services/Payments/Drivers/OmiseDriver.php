<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

class OmiseDriver extends ExternalCheckoutDriver
{
    public function key(): string
    {
        return 'omise';
    }
}
