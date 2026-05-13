<?php

declare(strict_types=1);

namespace App\Livewire\Portal;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class InvoiceShow extends Component
{
    use AuthorizesRequests;

    public Invoice $invoice;

    public string $companyName;

    public string $companyAddress;

    public string $paymentUrl = '';

    public string $paymentGatewayLabel = '';

    public string $bankTransferInstructions = '';

    public string $paymentStatus = '';

    public function mount(int $invoiceId, PaymentGatewayManager $gateways): void
    {
        $this->invoice = Invoice::withoutGlobalScopes()
            ->with(['customer', 'items', 'payments'])
            ->findOrFail($invoiceId);
        $this->authorize('view', $this->invoice);

        $this->companyName = Setting::get('company_name', velocrm_company_name());
        $this->companyAddress = Setting::get('company_address', '');
        $this->paymentUrl = route('public.invoice.pay', $this->invoice->ensurePublicToken());
        $this->paymentGatewayLabel = $gateways->driver()->label();
        $this->bankTransferInstructions = (string) Setting::get('payment_manual_instructions', config('payments.drivers.manual.instructions', ''));
        $this->paymentStatus = (string) request()->query('payment', '');
    }

    public function render()
    {
        return view('livewire.portal.invoice-show')
            ->layout('layouts.portal')
            ->title(__('Invoice :number', ['number' => $this->invoice->number]));
    }
}
