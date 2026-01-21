<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait: Apply automatic scoping and ID assignment.
     */
    public static function bootBelongsToTenant(): void
    {
        // Auto-assign tenant_id during model creation
        static::creating(
            fn($model) => auth()->check() && empty($model->tenant_id)
                ? ($model->tenant_id = auth()->user()->tenant_id)
                : null,
        );

        // Apply global filter to restrict data by tenant_id
        static::addGlobalScope(
            "tenant",
            fn(Builder $builder) => auth()->check() &&
            !auth()->user()->is_super_admin
                ? $builder->where("tenant_id", auth()->user()->tenant_id)
                : null,
        );
    }

    /**
     * Relationship: The model belongs to a Tenant (ISP Branch).
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
