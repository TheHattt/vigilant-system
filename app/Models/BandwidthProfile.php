<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandwidthProfile extends Model
{
    protected $fillable = [
        "tenant_id",
        "name",
        "download_kbps",
        "upload_kbps",
        "mikrotik_rate_limit",
    ];

    /**
     * The "booted" method allows us to hook into the saving event.
     */
    protected static function booted()
    {
        static::saving(function ($profile) {
            // Convert KBPS to M/K string for Mikrotik (e.g., 10240 -> 10M)
            $up =
                $profile->upload_kbps >= 1024
                    ? $profile->upload_kbps / 1024 . "M"
                    : $profile->upload_kbps . "K";
            $dl =
                $profile->download_kbps >= 1024
                    ? $profile->download_kbps / 1024 . "M"
                    : $profile->download_kbps . "K";

            $profile->mikrotik_rate_limit = "{$up}/{$dl}";
        });
    }
}
