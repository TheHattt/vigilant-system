<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = ["name", "slug", "account_prefix"];
    protected static function booted()
    {
        static::creating(function ($tenant) {
            if (!$tenant->slug) {
                $tenant->slug = \Illuminate\Support\Str::slug($tenant->name);
            }
        });
    }
}
