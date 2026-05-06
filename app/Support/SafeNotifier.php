<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class SafeNotifier
{
    public static function send(object $notifiable, Notification $notification, array $context = []): bool
    {
        try {
            if (! method_exists($notifiable, 'notify')) {
                return false;
            }

            $notifiable->notify($notification);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Notification delivery failed.', array_merge($context, [
                'notification' => $notification::class,
                'notifiable' => $notifiable::class,
                'notifiable_id' => $notifiable->id ?? null,
                'error' => $exception->getMessage(),
            ]));

            return false;
        }
    }
}
