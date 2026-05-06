<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use BelongsToTenant, LogsActivity, SoftDeletes;

    protected $fillable = [
        'number',
        'customer_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_total',
        'discount',
        'total',
        'amount_paid',
        'balance_due',
        'status',
        'is_recurring',
        'recurring_cycle',
        'next_recurring_date',
        'recurring_parent_id',
        'notes',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function updateTotals(): void
    {
        $this->amount_paid = $this->payments()->sum('amount');
        $this->balance_due = $this->total - $this->amount_paid;

        if ($this->balance_due <= 0) {
            $this->status = 'Paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'Partially Paid';
        }

        $this->save();
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
