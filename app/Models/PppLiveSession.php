<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PppSecret;

class PppLiveSession extends Model
{
    public function pppSecret(): BelongsTo
    {
        return $this->belongsTo(PppSecret::class);
    }
}
