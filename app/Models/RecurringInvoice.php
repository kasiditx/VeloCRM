<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Recurring invoice schedule — each row drives periodic Invoice generation
 * via the app:generate-recurring-invoices Artisan command.
 *
 * Note: active recurring schedules are stored on the Invoice model itself
 * (is_recurring, recurring_cycle, next_recurring_date columns).  This model
 * exists as a first-class entity for future UI listing of all schedules.
 */
class RecurringInvoice extends Model
{
    protected $fillable = [
        'invoice_id',
        'cycle',
        'next_run_date',
        'last_run_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'next_run_date' => 'date',
            'last_run_date' => 'date',
            'is_active'     => 'boolean',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }
}
