<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Imports\LeadImport as LeadCsvImport;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class LeadImport extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public mixed $file = null;

    /**
     * @var list<string>
     */
    public array $csvHeaders = [];

    /**
     * @var list<array<int, string>>
     */
    public array $previewRows = [];

    /**
     * @var array<int, string>
     */
    public array $columnMap = [];

    /**
     * @var array{imported:int, skipped:int, failed:int, failures:list<array{row:int, attribute:string, errors:list<string>}>}|null
     */
    public ?array $importSummary = null;

    /**
     * @var array<string, string>
     */
    public array $fieldOptions = [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'company' => 'Company',
        'status' => 'Status',
        'source' => 'Source',
        'value' => 'Value',
        'notes' => 'Notes',
        'assigned_to' => 'Assigned To',
    ];

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    public function updatedFile(): void
    {
        $this->authorize('create', Lead::class);
        $this->validateOnly('file');

        $rows = $this->readCsv($this->file->getRealPath());

        if (count($rows) < 2) {
            $this->resetImportState();
            $this->addError('file', 'The CSV must include a header row and at least one data row.');

            return;
        }

        $this->csvHeaders = $rows[0];
        $this->previewRows = array_slice($rows, 1, 5);
        $this->columnMap = $this->buildDefaultColumnMap($this->csvHeaders);
        $this->importSummary = null;
        $this->resetErrorBag('file');
    }

    public function import(): void
    {
        $this->authorize('create', Lead::class);
        $this->validate();

        if ($this->csvHeaders === [] || $this->previewRows === []) {
            $this->addError('file', 'Upload a CSV file before starting the import.');

            return;
        }

        $selectedFields = array_values(array_filter($this->columnMap));

        if (! in_array('name', $selectedFields, true)) {
            $this->addError('columnMap', 'Map at least one column to Name before importing.');

            return;
        }

        if (count($selectedFields) !== count(array_unique($selectedFields))) {
            $this->addError('columnMap', 'Each CRM field can only be mapped once.');

            return;
        }

        $mappedCsvPath = $this->buildMappedCsv();
        $import = new LeadCsvImport(auth()->id());

        try {
            Excel::import($import, $mappedCsvPath, null, \Maatwebsite\Excel\Excel::CSV);
        } finally {
            File::delete($mappedCsvPath);
        }

        $this->importSummary = [
            'imported' => $import->importedCount,
            'skipped' => $import->skippedCount,
            'failed' => count($import->failuresData),
            'failures' => array_slice($import->failuresData, 0, 5),
        ];

        session()->flash('success', 'Lead import completed.');
    }

    protected function buildMappedCsv(): string
    {
        $fieldOrder = array_values(array_intersect(array_keys($this->fieldOptions), array_values($this->columnMap)));
        $sourceRows = $this->readCsv($this->file->getRealPath());
        $path = tempnam(storage_path('app'), 'lead-import-');
        $handle = fopen($path, 'wb');

        fputcsv($handle, $fieldOrder);

        foreach (array_slice($sourceRows, 1) as $row) {
            $mappedRow = [];

            foreach ($fieldOrder as $field) {
                $sourceIndex = array_search($field, $this->columnMap, true);
                $mappedRow[] = ($sourceIndex !== false && array_key_exists($sourceIndex, $row))
                    ? trim((string) $row[$sourceIndex])
                    : '';
            }

            fputcsv($handle, $mappedRow);
        }

        fclose($handle);

        return $path;
    }

    /**
     * @return list<array<int, string>>
     */
    protected function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $rows[] = array_map(
                fn (?string $value): string => $value === null ? '' : trim($value),
                $row,
            );
        }

        fclose($handle);

        if ($rows !== [] && isset($rows[0][0])) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]) ?? $rows[0][0];
        }

        return $rows;
    }

    /**
     * @param  list<string>  $headers
     * @return array<int, string>
     */
    protected function buildDefaultColumnMap(array $headers): array
    {
        $aliases = [
            'name' => ['name', 'full name', 'lead name'],
            'email' => ['email', 'email address'],
            'phone' => ['phone', 'phone number', 'mobile'],
            'company' => ['company', 'company name', 'organization'],
            'status' => ['status', 'lead status'],
            'source' => ['source', 'lead source'],
            'value' => ['value', 'deal value', 'amount'],
            'notes' => ['notes', 'note', 'description'],
            'assigned_to' => ['assigned to', 'assigned_to', 'owner', 'user', 'sales rep'],
        ];

        $map = [];

        foreach ($headers as $index => $header) {
            $normalizedHeader = Str::lower(trim($header));
            $mappedField = '';

            foreach ($aliases as $field => $possibleHeaders) {
                if (in_array($normalizedHeader, $possibleHeaders, true)) {
                    $mappedField = $field;
                    break;
                }
            }

            $map[$index] = $mappedField;
        }

        return $map;
    }

    protected function resetImportState(): void
    {
        $this->csvHeaders = [];
        $this->previewRows = [];
        $this->columnMap = [];
        $this->importSummary = null;
    }

    public function render()
    {
        return view('livewire.leads.lead-import', [
            'assignableUsers' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
