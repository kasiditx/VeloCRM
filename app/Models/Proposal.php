<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Proposal extends Model
{
    use BelongsToTenant, LogsActivity, SoftDeletes;

    protected $fillable = [
        'number',
        'customer_id',
        'lead_id',
        'subject',
        'content',
        'total',
        'status',
    ];

    protected function casts(): array
    {
        return [
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

    public function ensurePublicToken(): string
    {
        if (! $this->public_token) {
            $this->forceFill(['public_token' => (string) Str::uuid()])->saveQuietly();
        }

        return (string) $this->public_token;
    }

    public function publicShareUrl(): string
    {
        return route('public.proposal.show', $this->ensurePublicToken());
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
            ->log('proposal public link viewed');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
