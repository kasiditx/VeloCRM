<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class InvoiceShow extends Component
{
    use AuthorizesRequests;

    public Invoice $invoice;

    public string $companyName;

    public string $companyAddress;

    public string $publicShareUrl = '';

    public string $activeTab = 'overview';

    // Payment Form state
    public bool $showPaymentModal = false;

    public $paymentAmount;

    public $paymentDate;

    public $paymentMethod = 'Bank Transfer';

    public $paymentNotes;

    public function mount(int $invoiceId)
    {
        $this->loadInvoice($invoiceId);
        $this->authorize('view', $this->invoice);

        $this->companyName = Setting::get('company_name', velocrm_company_name());
        $this->companyAddress = Setting::get('company_address', '');
        $this->publicShareUrl = $this->invoice->publicShareUrl();

        $this->paymentDate = date('Y-m-d');
        $this->paymentAmount = $this->invoice->balance_due;
    }

    private function loadInvoice(int $invoiceId)
    {
        $this->invoice = Invoice::with(['customer', 'items', 'payments'])->findOrFail($invoiceId);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['overview', 'activity'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function recordPayment()
    {
        $this->authorize('update', $this->invoice);
        $this->authorize('create', Payment::class);

        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|string',
            'paymentNotes' => 'nullable|string',
        ]);

        DB::transaction(function () {
            $this->invoice->payments()->create([
                'amount' => $this->paymentAmount,
                'payment_date' => $this->paymentDate,
                'payment_method' => $this->paymentMethod,
                'notes' => $this->paymentNotes,
            ]);

            $this->invoice->updateTotals();
        });

        $this->showPaymentModal = false;
        $this->loadInvoice($this->invoice->id);
        $this->paymentAmount = $this->invoice->balance_due;
        $this->paymentNotes = null;

        session()->flash('success', __('Payment recorded successfully.'));
    }

    public function render()
    {
        $activities = Activity::where('subject_type', Invoice::class)
            ->where('subject_id', $this->invoice->id)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        return view('livewire.invoices.invoice-show', [
            'activities' => $activities,
        ])
            ->title(__('Invoice :number', ['number' => $this->invoice->number]));
    }
}
