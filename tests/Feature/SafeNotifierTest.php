<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\SafeNotifier;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class SafeNotifierTest extends TestCase
{
    public function test_notification_failure_is_logged_without_throwing(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Notification delivery failed.'
                && $context['error'] === 'SMTP unavailable'
                && $context['notification'] === Notification::class);

        $notifiable = new class {
            public int $id = 123;

            public function notify(Notification $notification): void
            {
                throw new RuntimeException('SMTP unavailable');
            }
        };

        $this->assertFalse(SafeNotifier::send($notifiable, new Notification()));
    }
}
