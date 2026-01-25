<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PppSecret;

class Router extends Model
{
    protected $fillable = [
        "tenant_id",
        "site_id",
        "name",
        "model",
        "serial_number",
        "hardware_name",
        "host",
        "api_port",
        "api_username",
        "api_password",
        "radius_secret",
        "radius_coa_port",
        "os_version",
        "is_online",
    ];

    protected $casts = [
        "api_password" => "encrypted",
        "radius_secret" => "encrypted",
        "api_port" => "integer",
        "radius_coa_port" => "integer",
        "is_online" => "boolean",
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pppSecrets(): HasMany
    {
        return $this->hasMany(PppSecret::class);
    }
}
