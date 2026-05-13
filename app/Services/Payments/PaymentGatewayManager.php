<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Setting;
use App\Services\Payments\Drivers\ManualBankTransferDriver;
use App\Services\Payments\Drivers\OmiseDriver;
use App\Services\Payments\Drivers\PaymentGatewayDriver;
use App\Services\Payments\Drivers\PayPalDriver;
use App\Services\Payments\Drivers\StripeDriver;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /** @return array<string, class-string<PaymentGatewayDriver>> */
    public function drivers(): array
    {
        return [
            'manual' => ManualBankTransferDriver::class,
            'stripe' => StripeDriver::class,
            'paypal' => PayPalDriver::class,
            'omise' => OmiseDriver::class,
        ];
    }

    public function defaultDriverKey(): string
    {
        $key = (string) Setting::get('payment_driver', config('payments.default', 'manual'));

        return array_key_exists($key, $this->drivers()) ? $key : 'manual';
    }

    public function driver(?string $key = null): PaymentGatewayDriver
    {
        $key = $key ?: $this->defaultDriverKey();
        $drivers = $this->drivers();

        if (! array_key_exists($key, $drivers)) {
            throw new InvalidArgumentException("Unsupported payment gateway [{$key}].");
        }

        return app($drivers[$key]);
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        return collect(array_keys($this->drivers()))
            ->mapWithKeys(fn (string $key) => [$key => $this->driver($key)->label()])
            ->all();
    }
}
