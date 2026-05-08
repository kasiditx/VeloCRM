<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function leads()
    {
        return Excel::download(new LeadsExport, 'leads-'.now()->format('Y-m-d').'.xlsx');
    }

    public function customers()
    {
        return Excel::download(new CustomersExport, 'customers-'.now()->format('Y-m-d').'.xlsx');
    }

    public function leadImportTemplate(): StreamedResponse
    {
        $filename = 'velocrm-leads-import-template.csv';
        $headers = ['Content-Type' => 'text/csv'];

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['Name', 'Email', 'Phone', 'Company', 'Status', 'Source', 'Value', 'Notes', 'Assigned To']);
            fputcsv($handle, ['Acme Prospect', 'lead@example.com', '0812345678', 'Acme Co', 'Qualified', 'Website', '25000', 'Warm inbound lead', 'admin@example.com']);
            fputcsv($handle, ['Northwind', 'northwind@example.com', '0899999999', 'Northwind Ltd', 'New', 'Referral', '18000', 'Requested demo', '']);

            fclose($handle);
        }, $filename, $headers);
    }
}
