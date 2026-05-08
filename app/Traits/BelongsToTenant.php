<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Scopes\TenancyScope;

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
                if (in_array('user_id', $model->getFillable()) || $model->isFillable('user_id')) {
                    $model->user_id = auth()->id();
                }
            }
        });
    }
}
