<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCustomFields;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use BelongsToTenant, HasCustomFields, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'tax_id',
        'branch',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class)->withTrashed();
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class)->withTrashed();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
