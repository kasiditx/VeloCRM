<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCustomFields;
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
        'customer_id',
        'tax_id',
        'branch',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_total',
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
            'public_viewed_at' => 'datetime',
        ];
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
        $this->amount_paid = $this->payments()
            ->where('status', 'paid')
            ->sum('amount');
        $this->balance_due = $this->total - $this->amount_paid;

        if ($this->balance_due <= 0) {
            $this->status = 'Paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'Partially Paid';
        }

        $this->save();
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
