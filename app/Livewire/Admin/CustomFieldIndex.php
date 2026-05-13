<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\Lead;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CustomFieldIndex extends Component
{
    public ?int $editingId = null;

    public string $model_type = Lead::class;

    public string $key = '';

    public string $label_en = '';

    public string $label_th = '';

    public string $type = CustomField::TYPE_TEXT;

    public string $options_text = '';

    public array $modelTypes = [
        Lead::class => 'Lead',
        Customer::class => 'Customer',
        Invoice::class => 'Invoice',
    ];

    public array $fieldTypes = [
        CustomField::TYPE_TEXT => 'Text',
        CustomField::TYPE_TEXTAREA => 'Textarea',
        CustomField::TYPE_NUMBER => 'Number',
        CustomField::TYPE_DATE => 'Date',
        CustomField::TYPE_SELECT => 'Select',
        CustomField::TYPE_BOOLEAN => 'Boolean',
    ];

    public function save(): void
    {
        $data = $this->validate($this->rules());
        $options = $this->parseOptions($data['options_text'] ?? '');

        if ($data['type'] === CustomField::TYPE_SELECT && $options === []) {
            $this->addError('options_text', __('Add at least one option for select fields.'));

            return;
        }

        CustomField::updateOrCreate(
            ['id' => $this->editingId],
            [
                'model_type' => $data['model_type'],
                'key' => $data['key'],
                'label_en' => $data['label_en'],
                'label_th' => $data['label_th'] ?: null,
                'type' => $data['type'],
                'options' => $data['type'] === CustomField::TYPE_SELECT ? $options : null,
            ]
        );

        $this->resetForm();
        session()->flash('success', 'Custom field saved successfully.');
    }

    public function edit(int $fieldId): void
    {
        $field = CustomField::findOrFail($fieldId);

        $this->editingId = $field->id;
        $this->model_type = $field->model_type;
        $this->key = $field->key;
        $this->label_en = $field->label_en;
        $this->label_th = $field->label_th ?? '';
        $this->type = $field->type;
        $this->options_text = implode(PHP_EOL, $field->normalizedOptions());
    }

    public function delete(int $fieldId): void
    {
        CustomField::findOrFail($fieldId)->delete();

        if ($this->editingId === $fieldId) {
            $this->resetForm();
        }

        session()->flash('success', 'Custom field deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'key', 'label_en', 'label_th', 'options_text']);
        $this->model_type = Lead::class;
        $this->type = CustomField::TYPE_TEXT;
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'model_type' => ['required', Rule::in(array_keys($this->modelTypes))],
            'key' => [
                'required',
                'alpha_dash:ascii',
                'max:100',
                Rule::unique('custom_fields', 'key')
                    ->where('model_type', $this->model_type)
                    ->ignore($this->editingId),
            ],
            'label_en' => ['required', 'string', 'max:255'],
            'label_th' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(CustomField::TYPES)],
            'options_text' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function parseOptions(string $options): array
    {
        return collect(preg_split('/\r\n|\r|\n|,/', $options) ?: [])
            ->map(fn (string $option): string => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.custom-field-index', [
            'customFields' => CustomField::query()
                ->orderBy('model_type')
                ->orderBy('label_en')
                ->get()
                ->groupBy('model_type'),
        ])->layout('layouts.app');
    }
}
