<?php

declare(strict_types=1);

namespace App\Livewire\PublicShare;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public string $companyName = '';

    public string $companyAddress = '';

    public string $paymentUrl = '';

    public string $paymentGatewayLabel = '';

    public string $bankTransferInstructions = '';

    public string $paymentStatus = '';

    public function mount(string $token, PaymentGatewayManager $gateways): void
    {
        $this->invoice = Invoice::withoutGlobalScopes()
            ->with(['customer', 'items', 'payments'])
            ->where('public_token', $token)
            ->firstOrFail();

        $this->invoice->markPublicView((string) request()->ip());

        $this->companyName = (string) Setting::get('company_name', velocrm_company_name());
        $this->companyAddress = (string) Setting::get('company_address', '');
        $this->paymentUrl = route('public.invoice.pay', $this->invoice->public_token);
        $this->paymentGatewayLabel = $gateways->driver()->label();
        $this->bankTransferInstructions = (string) Setting::get('payment_manual_instructions', config('payments.drivers.manual.instructions', ''));
        $this->paymentStatus = (string) request()->query('payment', '');
    }

    public function render()
    {
        return view('livewire.public-share.invoice-show')
            ->layout('layouts.public-share')
            ->title(__('Invoice :number', ['number' => $this->invoice->number]));
    }
}
