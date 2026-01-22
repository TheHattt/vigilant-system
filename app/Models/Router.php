<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
class Router extends Model
{
    protected $fillable = [
        "tenant_id",
        "site_id",
        "name",
        "horst",
        "api_port",
        "api_username",
        "api_password",
        "radius_secret",
        "os_version",
    ];
    /**
     * Use Laravel's built-in encryption casting for passwords and secrets.
     */
    protected $casts = [
        "api_password" => "encrypted",
        "radius_secret" => "encrypted",
        "is_online" => "boolean",
    ];
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
