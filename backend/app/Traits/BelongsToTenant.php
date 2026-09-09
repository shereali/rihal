<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;

trait BelongsToTenant
{
    /**
     * The "booted" method of the trait.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (auth()->hasUser() && auth()->user()->tenant_id && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    /**
     * Define a relationship to the Tenant model.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
