<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'gateway',
        'status',
        'transaction_id',
        'external_reference',
        'notes',
        'raw_payload',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'raw_payload' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
