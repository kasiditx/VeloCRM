<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityLogFormatter
{
    public static function subjectLabel(?string $subjectType): string
    {
        if ($subjectType === null || $subjectType === '') {
            return __('System');
        }

        return Str::headline(class_basename($subjectType));
    }

    public static function eventTone(?string $event, string $description = ''): string
    {
        $text = strtolower($event ?: $description);

        if (str_contains($text, 'created')) {
            return 'emerald';
        }

        if (str_contains($text, 'deleted')) {
            return 'rose';
        }

        if (str_contains($text, 'viewed')) {
            return 'sky';
        }

        return 'primary';
    }

    public static function changedFields(Activity $activity): array
    {
        $attributes = $activity->properties->get('attributes', []);
        $old = $activity->properties->get('old', []);

        if (! is_array($attributes)) {
            return [];
        }

        return collect($attributes)
            ->keys()
            ->filter(fn (mixed $key): bool => is_string($key) && ! str_contains($key, 'password'))
            ->map(fn (string $key): array => [
                'field' => Str::headline($key),
                'old' => self::stringValue($old[$key] ?? null),
                'new' => self::stringValue($attributes[$key] ?? null),
            ])
            ->values()
            ->all();
    }

    private static function stringValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_scalar($value)) {
            return Str::limit((string) $value, 80);
        }

        return __('Changed');
    }
}
