<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Livewire\Concerns\HandlesCustomFields;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\TaxTemplate;
use App\Notifications\InvoiceSentNotification;
use App\Support\InvoiceDocuments;
use App\Support\InvoiceItemCatalog;
use App\Support\SafeNotifier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;

class InvoiceForm extends Component
{
    use AuthorizesRequests, HandlesCustomFields;

    public $invoiceId;

    public $number;

    public string $document_type = InvoiceDocuments::DEFAULT_TYPE;

    public $customer_id;

    public $invoice_date;

    public $due_date;

    public $status = 'Draft';

    public string $currency = 'USD';

    public $exchange_rate = 1;

    public $items = [];

    public bool $catalogModalOpen = false;

    public string $catalogSearch = '';

    public array $selectedCatalogItems = [];

    public $tax_id;

    public $discount = 0;

    public $notes;

    public $is_recurring = false;

    public $recurring_cycle = 'monthly';

    public $next_recurring_date;

    public $subtotal = 0;

    public $tax_total = 0;

    public $wht_total = 0;

    public $total = 0;

    protected $rules = [
        'number' => 'required|unique:invoices,number',
        'document_type' => 'required|string',
        'customer_id' => 'required|exists:customers,id',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:invoice_date',
        'status' => 'required|in:Draft,Sent,Partially Paid,Paid,Overdue,Cancelled',
        'currency' => 'required|string|size:3',
        'exchange_rate' => 'required|numeric|min:0.000001|max:999999.999999',
        'items' => 'required|array|min:1',
        'items.*.description' => 'required|string|max:255',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.wht_rate' => 'nullable|numeric|in:0,1,2,3,5,10,15',
        'tax_id' => 'nullable|exists:tax_templates,id',
        'discount' => 'nullable|numeric|min:0',
        'is_recurring' => 'boolean',
        'recurring_cycle' => 'nullable|in:weekly,monthly,yearly',
        'next_recurring_date' => 'nullable|date|after_or_equal:invoice_date',
    ];

    public function mount($invoiceId = null)
    {
        if ($invoiceId) {
            $invoice = Invoice::with('items')->findOrFail($invoiceId);
            $this->authorize('update', $invoice);

            $this->invoiceId = $invoice->id;
            $this->number = $invoice->number;
            $this->document_type = InvoiceDocuments::normalize($invoice->document_type);
            $this->customer_id = $invoice->customer_id;
            $this->invoice_date = $invoice->invoice_date;
            $this->due_date = $invoice->due_date;
            $this->status = $invoice->status;
            $this->currency = (string) ($invoice->currency ?: velocrm_currency_code());
            $this->exchange_rate = $invoice->exchange_rate ?: 1;
            $this->discount = $invoice->discount;
            $this->notes = $invoice->notes;
            $this->is_recurring = $invoice->is_recurring;
            $this->recurring_cycle = $invoice->recurring_cycle;
            $this->next_recurring_date = $invoice->next_recurring_date;
            $this->items = $invoice->items
                ->map(fn (InvoiceItem $item) => $this->normalizeItem($item->toArray()))
                ->toArray();
            $this->loadCustomFields(Invoice::class, $invoice);
        } else {
            $this->authorize('create', Invoice::class);

            $this->document_type = InvoiceDocuments::DEFAULT_TYPE;
            $this->number = InvoiceDocuments::previewNumber($this->document_type);
            $this->invoice_date = now()->format('Y-m-d');
            $this->due_date = now()->addDays(30)->format('Y-m-d');
            $this->status = 'Draft';
            $this->currency = strtoupper((string) Setting::get('default_currency', Setting::get('currency_code', 'USD')));
            $this->exchange_rate = 1;
            $this->items = [$this->newItem()];
            $this->loadCustomFields(Invoice::class);
        }
        $this->calculateTotals();
    }

    public function addItem(?array $item = null): void
    {
        $this->items[] = $item ? $this->normalizeItem($item) : $this->newItem();
        $this->calculateTotals();
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function duplicateItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $item = $this->normalizeItem($this->items[$index]);
        $item['_key'] = (string) Str::uuid();
        $this->items = [
            ...array_slice($this->items, 0, $index + 1),
            $item,
            ...array_slice($this->items, $index + 1),
        ];
        $this->calculateTotals();
    }

    public function clearItems(): void
    {
        $this->items = [];
        $this->calculateTotals();
        $this->resetValidation('items');
    }

    public function incrementQuantity(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['quantity'] = $this->toNumber($this->items[$index]['quantity'] ?? 0) + 1;
        $this->calculateTotals();
    }

    public function decrementQuantity(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['quantity'] = max($this->toNumber($this->items[$index]['quantity'] ?? 0) - 1, 0.01);
        $this->calculateTotals();
    }

    public function openCatalogModal(): void
    {
        $this->catalogModalOpen = true;
        $this->selectedCatalogItems = [];
    }

    public function closeCatalogModal(): void
    {
        $this->catalogModalOpen = false;
        $this->catalogSearch = '';
        $this->selectedCatalogItems = [];
    }

    public function toggleCatalogSelection(string $key): void
    {
        $this->selectedCatalogItems[$key] = ! (bool) ($this->selectedCatalogItems[$key] ?? false);
    }

    public function addSelectedCatalogItems(bool $mergeDuplicates = false): void
    {
        $keys = array_keys(array_filter($this->selectedCatalogItems));

        foreach ($keys as $key) {
            $catalogItem = InvoiceItemCatalog::find((string) $key);

            if ($catalogItem === null) {
                continue;
            }

            $this->addCatalogItem($catalogItem, $mergeDuplicates);
        }

        $this->closeCatalogModal();
        $this->calculateTotals();
    }

    public function selectCatalogItem(int $index, string $key): void
    {
        $catalogItem = InvoiceItemCatalog::find($key);

        if ($catalogItem === null || ! isset($this->items[$index])) {
            return;
        }

        $this->items[$index] = $this->itemFromCatalog($catalogItem, $this->items[$index]);
        $this->applyItemTaxOption($index);
        $this->calculateTotals();
    }

    public function commitItemQuery(int $index, ?string $query = null): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $query = trim((string) ($query ?? $this->items[$index]['catalog_query'] ?? ''));
        $this->items[$index]['catalog_query'] = $query;

        if ($query === '') {
            $this->items[$index]['description'] = '';
            $this->items[$index]['catalog_key'] = null;
            $this->items[$index]['catalog_code'] = null;
            $this->items[$index]['unit_label'] = null;

            return;
        }

        $match = InvoiceItemCatalog::search($query, 1)[0] ?? null;

        if ($match !== null) {
            $this->selectCatalogItem($index, (string) $match['key']);

            return;
        }

        $this->useCustomItemQuery($index);
    }

    public function useCustomItemQuery(int $index, ?string $query = null): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $query = trim((string) ($query ?? $this->items[$index]['catalog_query'] ?? ''));

        if ($query === '') {
            return;
        }

        $this->items[$index]['catalog_query'] = $query;
        $this->items[$index]['catalog_key'] = null;
        $this->items[$index]['catalog_code'] = null;
        $this->items[$index]['unit_label'] = null;
        $this->items[$index]['description'] = $query;
        $this->calculateTotals();
    }

    public function addStandardItem(): void
    {
        $catalogItem = InvoiceItemCatalog::all(1)[0] ?? null;

        if ($catalogItem === null) {
            $this->addItem();

            return;
        }

        $this->addCatalogItem($catalogItem);
    }

    public function copyLatestItems(): void
    {
        $query = Invoice::query()
            ->with('items')
            ->whereHas('items')
            ->latest('id');

        if ($this->customer_id) {
            $query->where('customer_id', $this->customer_id);
        }

        if ($this->invoiceId) {
            $query->whereKeyNot($this->invoiceId);
        }

        $latestInvoice = $query->first();

        if (! $latestInvoice) {
            return;
        }

        foreach ($latestInvoice->items as $item) {
            $this->addItem($item->toArray());
        }

        $this->calculateTotals();
    }

    public function addFromLatestProposal(): void
    {
        $proposal = Proposal::query()
            ->when($this->customer_id, fn ($query) => $query->where('customer_id', $this->customer_id))
            ->whereIn('status', ['Accepted', 'Sent', 'Draft'])
            ->latest('id')
            ->first();

        if (! $proposal) {
            return;
        }

        $this->addItem([
            'description' => trim($proposal->subject ?: __('Proposal item')),
            'quantity' => 1,
            'unit_price' => (float) $proposal->total,
            'amount' => (float) $proposal->total,
            'wht_rate' => 0,
            'wht_amount' => 0,
            'catalog_query' => $proposal->number,
            'catalog_code' => $proposal->number,
            'unit_label' => __('proposal'),
            'tax_option' => 'none',
        ]);
    }

    public function updated($propertyName)
    {
        if (preg_match('/^items\.(\d+)\.catalog_query$/', $propertyName, $matches) === 1) {
            $this->syncCustomItemQuery((int) $matches[1]);
        }

        if (preg_match('/^items\.(\d+)\.tax_option$/', $propertyName, $matches) === 1) {
            $this->applyItemTaxOption((int) $matches[1]);
        }

        if (Str::startsWith($propertyName, 'items.') || $propertyName === 'tax_id' || $propertyName === 'discount') {
            $this->calculateTotals();
        }

        if (
            ! $this->invoiceId
            && in_array($propertyName, ['document_type', 'invoice_date'], true)
            && InvoiceDocuments::isGeneratedNumber($this->number)
        ) {
            $this->number = InvoiceDocuments::previewNumber($this->document_type, $this->invoice_date);
        }
    }

    public function itemCatalogMatches(int $index): array
    {
        $query = $this->items[$index]['catalog_query'] ?? $this->items[$index]['description'] ?? '';

        return InvoiceItemCatalog::search(is_string($query) ? $query : '', 5);
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        $this->wht_total = 0;

        foreach ($this->items as $index => $item) {
            $quantity = $this->toNumber($item['quantity'] ?? 0);
            $unitPrice = $this->toNumber($item['unit_price'] ?? 0);
            $amount = $quantity * $unitPrice;
            $whtRate = $this->toNumber($item['wht_rate'] ?? 0);
            $whtAmount = $this->calculateWithholdingTax($amount, $whtRate);
            $this->items[$index]['amount'] = $amount;
            $this->items[$index]['wht_amount'] = $whtAmount;
            $this->subtotal += $amount;
            $this->wht_total += $whtAmount;
        }

        $taxRate = 0;
        if ($this->tax_id) {
            $taxRate = (float) (TaxTemplate::find($this->tax_id)?->rate ?? 0);
        }

        $discount = $this->toNumber($this->discount);
        $taxableAmount = max($this->subtotal - $discount, 0);
        $this->tax_total = round($taxableAmount * ($taxRate / 100), 2);
        $this->total = max(round($taxableAmount + $this->tax_total - $this->wht_total, 2), 0);
    }

    private function toNumber(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace(',', '', trim($value));
        }

        if ($value === null || $value === '') {
            return 0.0;
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function newItem(): array
    {
        return [
            '_key' => (string) Str::uuid(),
            'catalog_key' => null,
            'catalog_query' => '',
            'catalog_code' => null,
            'unit_label' => null,
            'tax_option' => 'none',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'amount' => 0,
            'wht_rate' => 0,
            'wht_amount' => 0,
        ];
    }

    private function normalizeItem(array $item): array
    {
        return [
            '_key' => $item['_key'] ?? (string) Str::uuid(),
            'catalog_key' => $item['catalog_key'] ?? null,
            'catalog_query' => $item['catalog_query'] ?? $item['description'] ?? '',
            'catalog_code' => $item['catalog_code'] ?? null,
            'unit_label' => $item['unit_label'] ?? null,
            'tax_option' => $item['tax_option'] ?? $this->taxOptionFromRate($this->toNumber($item['wht_rate'] ?? 0)),
            'description' => $item['description'] ?? '',
            'quantity' => $item['quantity'] ?? 1,
            'unit_price' => $item['unit_price'] ?? 0,
            'amount' => $item['amount'] ?? 0,
            'wht_rate' => $item['wht_rate'] ?? 0,
            'wht_amount' => $item['wht_amount'] ?? 0,
        ];
    }

    private function itemAttributes(array $item): array
    {
        $amount = $this->toNumber($item['amount'] ?? 0);
        $whtRate = $this->toNumber($item['wht_rate'] ?? 0);

        return [
            'description' => $item['description'] ?? '',
            'quantity' => $this->toNumber($item['quantity'] ?? 0),
            'unit_price' => $this->toNumber($item['unit_price'] ?? 0),
            'amount' => $amount,
            'wht_rate' => $whtRate,
            'wht_amount' => $this->calculateWithholdingTax($amount, $whtRate),
        ];
    }

    private function calculateWithholdingTax(float $amount, float $rate): float
    {
        if ($amount <= 0 || $rate <= 0) {
            return 0.0;
        }

        return round($amount * ($rate / 100), 2);
    }

    private function addCatalogItem(array $catalogItem, bool $mergeDuplicates = false): void
    {
        if ($mergeDuplicates) {
            $existingIndex = $this->findExistingCatalogItemIndex($catalogItem);

            if ($existingIndex !== null) {
                $this->items[$existingIndex]['quantity'] = $this->toNumber($this->items[$existingIndex]['quantity'] ?? 0) + 1;
                $this->calculateTotals();

                return;
            }
        }

        $blankIndex = $this->findBlankItemIndex();

        if ($blankIndex !== null) {
            $this->items[$blankIndex] = $this->itemFromCatalog($catalogItem, $this->items[$blankIndex]);
            $this->applyItemTaxOption($blankIndex);
            $this->calculateTotals();

            return;
        }

        $this->items[] = $this->itemFromCatalog($catalogItem);
        $lastIndex = count($this->items) - 1;
        $this->applyItemTaxOption($lastIndex);
        $this->calculateTotals();
    }

    private function findExistingCatalogItemIndex(array $catalogItem): ?int
    {
        foreach ($this->items as $index => $item) {
            if (($item['catalog_key'] ?? null) === ($catalogItem['key'] ?? null)) {
                return $index;
            }
        }

        return null;
    }

    private function findBlankItemIndex(): ?int
    {
        foreach ($this->items as $index => $item) {
            $hasDescription = filled($item['description'] ?? null);
            $hasCatalog = filled($item['catalog_key'] ?? null);
            $hasPrice = $this->toNumber($item['unit_price'] ?? 0) > 0;

            if (! $hasDescription && ! $hasCatalog && ! $hasPrice) {
                return $index;
            }
        }

        return null;
    }

    private function itemFromCatalog(array $catalogItem, ?array $existingItem = null): array
    {
        $item = $this->normalizeItem($existingItem ?? []);
        $description = (string) ($catalogItem['description'] ?? $catalogItem['name'] ?? '');
        $currency = $catalogItem['currency'] ?? null;

        $item['catalog_key'] = $catalogItem['key'] ?? null;
        $item['catalog_query'] = (string) ($catalogItem['name'] ?? $description);
        $item['catalog_code'] = $catalogItem['code'] ?? $catalogItem['sku'] ?? null;
        $item['unit_label'] = $catalogItem['unit'] ?? null;
        $item['tax_option'] = (string) ($catalogItem['default_tax'] ?? 'none');
        $item['description'] = $description;
        $item['quantity'] = $this->toNumber($item['quantity'] ?? 0) > 0 ? $item['quantity'] : 1;
        $item['unit_price'] = $catalogItem['unit_price'] ?? 0;

        if (is_string($currency) && $currency !== '') {
            $this->currency = strtoupper($currency);
        }

        return $item;
    }

    private function syncCustomItemQuery(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $query = trim((string) ($this->items[$index]['catalog_query'] ?? ''));

        if ($query === '') {
            $this->items[$index]['description'] = '';
            $this->items[$index]['catalog_key'] = null;
            $this->items[$index]['catalog_code'] = null;
            $this->items[$index]['unit_label'] = null;

            return;
        }

        if ($query === ($this->items[$index]['description'] ?? '')) {
            return;
        }

        $this->items[$index]['description'] = $query;
        $this->items[$index]['catalog_key'] = null;
        $this->items[$index]['catalog_code'] = null;
        $this->items[$index]['unit_label'] = null;
    }

    private function applyItemTaxOption(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $taxOption = (string) ($this->items[$index]['tax_option'] ?? 'none');

        if ($taxOption === 'none') {
            $this->items[$index]['wht_rate'] = 0;

            return;
        }

        if ($taxOption === 'vat_7') {
            $this->applyDefaultVatRate(7);
            $this->items[$index]['wht_rate'] = 0;

            return;
        }

        if (Str::startsWith($taxOption, 'wht_')) {
            $rate = (float) Str::after($taxOption, 'wht_');

            if (in_array((int) $rate, InvoiceItem::WHT_RATES, true)) {
                $this->items[$index]['wht_rate'] = $rate;
            }
        }
    }

    private function applyDefaultVatRate(float $rate): void
    {
        $taxTemplateId = TaxTemplate::query()
            ->where('rate', $rate)
            ->value('id');

        if ($taxTemplateId) {
            $this->tax_id = $taxTemplateId;
        }
    }

    private function taxOptionFromRate(float $rate): string
    {
        return $rate > 0 ? 'wht_'.rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.') : 'none';
    }

    public function save()
    {
        $rules = $this->rules;

        if ($this->invoiceId) {
            $rules['number'] = 'required|unique:invoices,number,'.$this->invoiceId;
        } elseif (InvoiceDocuments::isGeneratedNumber($this->number)) {
            $rules['number'] = 'nullable|string|max:255';
        }

        if ($this->is_recurring) {
            $rules['recurring_cycle'] = 'required|in:weekly,monthly,yearly';
            $rules['next_recurring_date'] = 'required|date|after_or_equal:invoice_date';
        }

        $rules['document_type'] = 'required|in:'.implode(',', InvoiceDocuments::types());

        $shouldGenerateNumber = ! $this->invoiceId && InvoiceDocuments::isGeneratedNumber($this->number);

        $validated = $this->validate($rules + $this->customFieldRules());
        $customFieldValues = $validated['customFieldValues'] ?? [];
        $this->currency = strtoupper($this->currency);

        if ($shouldGenerateNumber) {
            $this->number = InvoiceDocuments::nextNumber($this->document_type, $this->invoice_date);
        }

        $previousStatus = null;
        $invoice = $this->invoiceId ? Invoice::find($this->invoiceId) : new Invoice;
        if ($invoice->exists) {
            $this->authorize('update', $invoice);
            $previousStatus = $invoice->status;
        } else {
            $this->authorize('create', Invoice::class);
        }

        $invoice->fill([
            'number' => $this->number,
            'document_type' => InvoiceDocuments::normalize($this->document_type),
            'customer_id' => $this->customer_id,
            'tax_id' => $this->invoiceTaxId($invoice),
            'branch' => $this->invoiceBranch($invoice),
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'currency' => $this->currency,
            'exchange_rate' => $this->toNumber($this->exchange_rate) ?: 1,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'wht_total' => $this->wht_total,
            'discount' => $this->discount,
            'total' => $this->total,
            'balance_due' => max(round($this->total - (float) ($invoice->amount_paid ?? 0), 2), 0),
            'notes' => $this->notes,
            'is_recurring' => $this->is_recurring,
            'recurring_cycle' => $this->is_recurring ? $this->recurring_cycle : null,
            'next_recurring_date' => $this->is_recurring ? $this->next_recurring_date : null,
            'recurring_parent_id' => $invoice->recurring_parent_id,
        ]);

        if (! $invoice->exists) {
            $invoice->user_id = auth()->id();
        }

        $invoice->save();
        $invoice->syncCustomFieldValues($customFieldValues);

        $invoice->items()->delete();
        foreach ($this->items as $item) {
            $invoice->items()->create($this->itemAttributes($item));
        }

        $invoice->load('customer');

        if (
            $invoice->status === 'Sent' &&
            $previousStatus !== 'Sent' &&
            $invoice->customer &&
            $invoice->customer->email
        ) {
            SafeNotifier::send($invoice->customer, new InvoiceSentNotification($invoice), [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
            ]);
        }

        session()->flash('success', __('Invoice saved successfully.'));

        return redirect()->route('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoices.invoice-form', [
            'customers' => Customer::all(),
            'taxTemplates' => TaxTemplate::all(),
            'whtRates' => InvoiceItem::WHT_RATES,
            'taxOptions' => $this->taxOptions(),
            'catalogItems' => InvoiceItemCatalog::search($this->catalogSearch, 12),
            'currencyOptions' => ['THB', 'USD', 'EUR', 'GBP', 'JPY', 'SGD', 'AUD', 'CAD', 'HKD'],
            'documentTypes' => InvoiceDocuments::labels(),
        ]);
    }

    private function taxOptions(): array
    {
        return [
            'none' => __('No Tax'),
            'vat_7' => __('VAT 7%'),
            'wht_1' => __('WHT :rate%', ['rate' => 1]),
            'wht_2' => __('WHT :rate%', ['rate' => 2]),
            'wht_3' => __('WHT :rate%', ['rate' => 3]),
            'wht_5' => __('WHT :rate%', ['rate' => 5]),
            'wht_10' => __('WHT :rate%', ['rate' => 10]),
            'wht_15' => __('WHT :rate%', ['rate' => 15]),
            'custom' => __('Custom'),
        ];
    }

    private function invoiceTaxId(Invoice $invoice): ?string
    {
        return $this->shouldRefreshTaxSnapshot($invoice)
            ? Customer::find($this->customer_id)?->tax_id
            : $invoice->tax_id;
    }

    private function invoiceBranch(Invoice $invoice): ?string
    {
        return $this->shouldRefreshTaxSnapshot($invoice)
            ? Customer::find($this->customer_id)?->branch
            : $invoice->branch;
    }

    private function shouldRefreshTaxSnapshot(Invoice $invoice): bool
    {
        return ! $invoice->exists
            || (int) $invoice->customer_id !== (int) $this->customer_id
            || ($invoice->tax_id === null && $invoice->branch === null);
    }
}
