<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class ProposalController extends Controller
{
    public function generatePdf(Proposal $proposal)
    {
        Gate::authorize('view', $proposal);

        return $this->streamPdf($proposal);
    }

    public function generatePublicPdf(string $token)
    {
        $proposal = Proposal::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        return $this->streamPdf($proposal);
    }

    protected function streamPdf(Proposal $proposal)
    {
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

        $pdf = Pdf::setOptions([
            'defaultFont' => 'thsarabunnew',
            'isFontSubsettingEnabled' => true,
        ])->loadView('pdf.proposal', [
            'proposal' => $proposal->load('customer', 'lead'),
            'logo_base64' => $logoBase64,
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'company_url' => $companyUrl,
        ]);

        return $pdf->stream('Proposal-'.$proposal->number.'.pdf');
    }
}
