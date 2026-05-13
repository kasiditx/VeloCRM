<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCustomFields
{
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'model_id')
            ->where('model_type', static::class);
    }

    public function syncCustomFieldValues(array $values): void
    {
        $fields = CustomField::query()
            ->where('model_type', static::class)
            ->get()
            ->keyBy('id');

        foreach ($fields as $fieldId => $field) {
            $value = $this->normalizeCustomFieldValue($field, $values[$fieldId] ?? null);

            CustomFieldValue::updateOrCreate(
                [
                    'model_type' => static::class,
                    'model_id' => $this->getKey(),
                    'custom_field_id' => $fieldId,
                ],
                ['value' => $value]
            );
        }
    }

    private function normalizeCustomFieldValue(CustomField $field, mixed $value): ?string
    {
        if ($field->type === CustomField::TYPE_BOOLEAN) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}
