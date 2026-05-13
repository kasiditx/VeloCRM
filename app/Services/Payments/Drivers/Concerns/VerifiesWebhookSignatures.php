<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait VerifiesWebhookSignatures
{
    protected function verifyHmacSignature(Request $request, string $secret): bool
    {
        if ($secret === '') {
            Log::warning('Payment webhook rejected because no webhook secret is configured.', [
                'gateway' => $this->key(),
            ]);

            return false;
        }

        $provided = (string) $request->header('X-VeloCRM-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $provided);
    }
}
