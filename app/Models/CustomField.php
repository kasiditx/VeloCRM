<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_TEXTAREA = 'textarea';

    public const TYPE_NUMBER = 'number';

    public const TYPE_DATE = 'date';

    public const TYPE_SELECT = 'select';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_TEXTAREA,
        self::TYPE_NUMBER,
        self::TYPE_DATE,
        self::TYPE_SELECT,
        self::TYPE_BOOLEAN,
    ];

    protected $fillable = [
        'model_type',
        'key',
        'label_th',
        'label_en',
        'type',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function label(): string
    {
        if (app()->getLocale() === 'th' && filled($this->label_th)) {
            return (string) $this->label_th;
        }

        return (string) $this->label_en;
    }

    public function normalizedOptions(): array
    {
        if ($this->type !== self::TYPE_SELECT) {
            return [];
        }

        return collect($this->options ?? [])
            ->filter(fn (mixed $option): bool => is_scalar($option) && trim((string) $option) !== '')
            ->map(fn (mixed $option): string => trim((string) $option))
            ->values()
            ->all();
    }
}
