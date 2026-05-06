<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generatePdf(Invoice $invoice)
    {
        // Authorization: ensure user can view this invoice
        if (!auth()->user()->hasRole('Admin') && $invoice->user_id !== auth()->id()) {
            abort(403);
        }

        // Use logo from admin settings (uploads disk), fallback to default
        $logoSetting = Setting::get('logo');
        $logoBase64 = null;

        if ($logoSetting) {
            $logoPath = public_path('uploads/' . $logoSetting);
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $companyName = Setting::get('company_name', velocrm_company_name());
        $companyAddress = Setting::get('company_address');
        $companyUrl = config('app.url');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice->load('customer', 'items'),
            'logo_base64' => $logoBase64,
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'company_url' => $companyUrl,
        ]);

        return $pdf->stream('Invoice-' . $invoice->number . '.pdf');
    }
}
