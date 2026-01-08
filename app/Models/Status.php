<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Status extends Model
{
    protected $guarded = [];
    protected $table = 'statuses';

    public function pendaftarLingkungan(): BelongsTo
    {
        return $this->belongsTo(PendaftarLingkungan::class, 'id_pendaftar');
    }
}
