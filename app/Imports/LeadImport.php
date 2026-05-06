<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class LeadImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public int $importedCount = 0;
    public int $skippedCount = 0;

    /**
     * @var list<array{row:int, attribute:string, errors:list<string>}>
     */
    public array $failuresData = [];

    public function __construct(
        protected readonly int $defaultUserId,
    ) {
    }

    public function model(array $row): ?Lead
    {
        $email = $this->normalizeString($row['email'] ?? null);
        $phone = $this->normalizeString($row['phone'] ?? null);

        if ($email && Lead::query()->where('email', $email)->exists()) {
            $this->skippedCount++;

            return null;
        }

        if (! $email && $phone && Lead::query()->where('phone', $phone)->exists()) {
            $this->skippedCount++;

            return null;
        }

        $this->importedCount++;

        return new Lead([
            'name' => (string) ($row['name'] ?? ''),
            'email' => $email,
            'phone' => $phone,
            'company' => $this->normalizeString($row['company'] ?? null),
            'status' => $this->normalizeStatus($row['status'] ?? null),
            'source' => $this->normalizeString($row['source'] ?? null),
            'value' => (float) ($row['value'] ?? 0),
            'notes' => $this->normalizeString($row['notes'] ?? null),
            'user_id' => $this->resolveAssignedUserId($row['assigned_to'] ?? null),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:New,Contacted,Qualified,Lost,Won'],
            'source' => ['nullable', 'string', 'max:100'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failuresData[] = [
                'row' => $failure->row(),
                'attribute' => (string) $failure->attribute(),
                'errors' => $failure->errors(),
            ];
        }
    }

    protected function resolveAssignedUserId(mixed $value): int
    {
        $assignedTo = $this->normalizeString($value);

        if (! $assignedTo) {
            return $this->defaultUserId;
        }

        if (is_numeric($assignedTo)) {
            $user = User::query()->find((int) $assignedTo);

            return $user?->id ?? $this->defaultUserId;
        }

        $user = User::query()
            ->where('email', $assignedTo)
            ->orWhere('name', $assignedTo)
            ->first();

        return $user?->id ?? $this->defaultUserId;
    }

    protected function normalizeStatus(mixed $value): string
    {
        $status = Str::title(Str::lower((string) $value));

        return in_array($status, ['New', 'Contacted', 'Qualified', 'Lost', 'Won'], true)
            ? $status
            : 'New';
    }

    protected function normalizeString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
