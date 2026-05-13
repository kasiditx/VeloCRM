<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Scopes\TenancyScope;
use Illuminate\Support\Facades\Schema;

trait BelongsToTenant
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenancyScope);

        static::creating(function ($model) {
            if (auth()->check() && ! auth()->user()->hasRole('Admin')) {
                if (
                    array_key_exists('user_id', $model->getAttributes()) ||
                    Schema::hasColumn($model->getTable(), 'user_id')
                ) {
                    $model->user_id = auth()->id();
                }
            }
        });
    }
}
