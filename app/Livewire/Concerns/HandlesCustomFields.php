<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\CustomField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

trait HandlesCustomFields
{
    public array $customFields = [];

    public array $customFieldValues = [];

    protected function loadCustomFields(string $modelType, ?Model $record = null): void
    {
        $fields = CustomField::query()
            ->where('model_type', $modelType)
            ->orderBy('label_en')
            ->get();

        $savedValues = $record?->customFieldValues()
            ->pluck('value', 'custom_field_id')
            ->all() ?? [];

        $this->customFields = $fields
            ->map(fn (CustomField $field): array => [
                'id' => $field->id,
                'key' => $field->key,
                'label' => $field->label(),
                'type' => $field->type,
                'options' => $field->normalizedOptions(),
            ])
            ->all();

        $this->customFieldValues = [];
        foreach ($fields as $field) {
            $this->customFieldValues[$field->id] = $this->defaultCustomFieldValue($field, $savedValues[$field->id] ?? null);
        }
    }

    protected function customFieldRules(): array
    {
        $rules = [];

        foreach ($this->customFields as $field) {
            $rules['customFieldValues.'.$field['id']] = $this->rulesForCustomField($field);
        }

        return $rules;
    }

    private function defaultCustomFieldValue(CustomField $field, mixed $value): mixed
    {
        if ($field->type === CustomField::TYPE_BOOLEAN) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value ?? '';
    }

    private function rulesForCustomField(array $field): array
    {
        return match ($field['type']) {
            CustomField::TYPE_NUMBER => ['nullable', 'numeric'],
            CustomField::TYPE_DATE => ['nullable', 'date'],
            CustomField::TYPE_SELECT => ['nullable', Rule::in($field['options'])],
            CustomField::TYPE_BOOLEAN => ['boolean'],
            default => ['nullable', 'string', 'max:2000'],
        };
    }
}
