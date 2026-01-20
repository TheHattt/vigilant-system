<?php

namespace App\Models\Concerns;

use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            if (empty($model->tenant_id) && Tenant::current()->id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        static::addGlobalScope("tenant", function (Builder $builder) {
            if (Tenant::checkCurrent()) {
                $builder->where("tenant_id", Tenant::current()->id);
            }
        });
    }
}
