<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "saved" event.
     */
    public function saved(Payment $payment): void
    {
        $payment->invoice->updateTotals();
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        $payment->invoice->updateTotals();
    }
}
