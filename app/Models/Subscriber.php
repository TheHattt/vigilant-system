<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscriber extends Model
{
    protected $fillable = [
        "tenant_id",
        "site_id",
        "router_id",
        "username",
        "password",
        "bandwidth_profile_id",
        "static_ip",
        "is_active",
    ];

    protected $casts = [
        "is_active" => "boolean",
        "password" => "encrypted", // Radius can decrypt this if using PAP
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function bandwidthProfile(): BelongsTo
    {
        return $this->belongsTo(BandwidthProfile::class);
    }
}
