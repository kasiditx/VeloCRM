<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return Lead::query();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Company',
            'Status',
            'Source',
            'Value',
            'Created At',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->name,
            $lead->email,
            $lead->phone,
            $lead->company,
            ucfirst($lead->status),
            $lead->source,
            $lead->value,
            $lead->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
