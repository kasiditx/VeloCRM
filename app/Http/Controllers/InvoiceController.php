<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Setting;
use App\Support\PromptPay;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function generatePdf(Invoice $invoice, Request $request)
    {
        Gate::authorize('view', $invoice);

        return $this->streamPdf($invoice, $request);
    }

    public function generatePublicPdf(string $token, Request $request)
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        return $this->streamPdf($invoice, $request);
    }

    protected function streamPdf(Invoice $invoice, Request $request)
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);

        // Use logo from admin settings (uploads disk), fallback to default
        $logoSetting = Setting::get('logo');
        $logoBase64 = null;

        if ($logoSetting) {
            $logoPath = public_path('uploads/'.$logoSetting);
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
            }
        }

        $companyName = Setting::get('company_name', velocrm_company_name());
        $companyAddress = Setting::get('company_address');
        $companyUrl = config('app.url');
        $promptPayId = Setting::get('promptpay_id');
        $promptPayQr = PromptPay::invoiceQrDataUri($invoice, is_string($promptPayId) ? $promptPayId : null, 170);

        $pdf = Pdf::setOptions([
            'defaultFont' => 'SarabunPdf',
            'isFontSubsettingEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => base_path(),
        ])
            ->setPaper('a4')
            ->loadView('pdf.invoice', [
                'invoice' => $invoice->load('customer', 'items'),
                'logo_base64' => $logoBase64,
                'company_name' => $companyName,
                'company_address' => $companyAddress,
                'company_url' => $companyUrl,
                'promptpay_qr_data_uri' => $promptPayQr,
                'promptpay_amount' => $invoice->money($invoice->balance_due),
                'promptpay_receiver' => $companyName,
                'locale' => $locale,
            ]);

        $filenamePrefix = preg_replace('/[^A-Z0-9]+/', '-', $invoice->documentTypeEnglishLabel()) ?: 'DOCUMENT';

        return $pdf->stream(trim($filenamePrefix, '-').'-'.$invoice->number.'.pdf');
    }

    private function resolveLocale(Request $request): string
    {
        $locale = $request->query('locale');

        if (! is_string($locale) || $locale === '') {
            $locale = $request->hasSession()
                ? $request->session()->get('locale', config('app.locale'))
                : config('app.locale');
        }

        return in_array($locale, ['en', 'th'], true)
            ? $locale
            : config('app.fallback_locale', 'en');
    }
}
