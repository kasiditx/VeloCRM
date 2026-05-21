<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCustomFields;
use App\Support\InvoiceDocuments;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use BelongsToTenant, HasCustomFields, LogsActivity, SoftDeletes;

    protected $fillable = [
        'number',
        'document_type',
        'customer_id',
        'tax_id',
        'branch',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_total',
        'wht_total',
        'discount',
        'total',
        'amount_paid',
        'balance_due',
        'status',
        'currency',
        'exchange_rate',
        'is_recurring',
        'recurring_cycle',
        'next_recurring_date',
        'recurring_parent_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'wht_total' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'public_viewed_at' => 'datetime',
        ];
    }

    public function documentTypeLabel(): string
    {
        return InvoiceDocuments::label($this->document_type);
    }

    public function documentTypeEnglishLabel(): string
    {
        return InvoiceDocuments::englishLabel($this->document_type);
    }

    public function documentTypeFooter(): string
    {
        return InvoiceDocuments::footer($this->document_type);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function updateTotals(): void
    {
        $this->loadMissing('items');

        if ($this->items->isNotEmpty()) {
            $this->subtotal = round((float) $this->items->sum('amount'), 2);
            $this->wht_total = round((float) $this->items->sum('wht_amount'), 2);
        }

        $this->total = max(round(
            (float) $this->subtotal - (float) $this->discount + (float) $this->tax_total - (float) $this->wht_total,
            2
        ), 0);
        $this->amount_paid = $this->payments()
            ->where('status', 'paid')
            ->sum('amount');
        $this->balance_due = max(round((float) $this->total - (float) $this->amount_paid, 2), 0);

        if ($this->balance_due <= 0) {
            $this->status = 'Paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'Partially Paid';
        }

        $this->save();
    }

    public function withholdingTaxLabel(): string
    {
        $rateLabel = $this->withholdingTaxRateLabel();

        if ($rateLabel !== null) {
            return __('Withholding Tax (:rate)', ['rate' => $rateLabel]);
        }

        return __('Withholding Tax');
    }

    public function withholdingTaxRateLabel(): ?string
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();
        $rates = $items
            ->filter(fn (InvoiceItem $item): bool => (float) $item->wht_amount > 0)
            ->map(fn (InvoiceItem $item): float => (float) $item->wht_rate)
            ->filter(fn (float $rate): bool => $rate > 0)
            ->unique()
            ->values();

        if ($rates->count() === 1) {
            return $this->formatRate($rates->first()).'%';
        }

        return null;
    }

    private function formatRate(float $rate): string
    {
        return rtrim(rtrim(number_format($rate, 2), '0'), '.');
    }

    public function ensurePublicToken(): string
    {
        if (! $this->public_token) {
            $this->forceFill(['public_token' => (string) Str::uuid()])->saveQuietly();
        }

        return (string) $this->public_token;
    }

    public function publicShareUrl(): string
    {
        return route('public.invoice.show', $this->ensurePublicToken());
    }

    public function markPublicView(string $ip): void
    {
        if ($this->public_viewed_at) {
            return;
        }

        $this->forceFill([
            'public_viewed_at' => now(),
            'public_viewed_ip' => $ip,
        ])->saveQuietly();

        activity()
            ->performedOn($this)
            ->event('public_viewed')
            ->withProperties(['ip' => $ip])
            ->log('invoice public link viewed');
    }

    public function money(float|int|string|null $amount, int $decimals = 2): string
    {
        return velocrm_money($amount, $this->currency ?: velocrm_currency_code(), $decimals);
    }

    public function baseAmount(float|int|string|null $amount): float
    {
        return round((float) $amount * max((float) ($this->exchange_rate ?: 1), 0), 2);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recurringParent()
    {
        return $this->belongsTo(self::class, 'recurring_parent_id')->withTrashed();
    }

    public function generatedInvoices()
    {
        return $this->hasMany(self::class, 'recurring_parent_id');
    }
}
