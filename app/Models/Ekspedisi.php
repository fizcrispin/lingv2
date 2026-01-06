<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ekspedisi extends Model
{
    protected $table = 'ekspedisi';
    protected $guarded = [];

    protected $casts = [
        'sampel_diterima' => 'boolean',
        'sampel_dimusnahkan' => 'boolean',
        'tanggal_diterima' => 'datetime',
        'verifikasi_hasil' => 'boolean',
        'validasi1' => 'boolean',
        'validasi2' => 'boolean',
        // keterangan is string default, no cast needed
    ];

    public function pendaftarLingkungan(): BelongsTo
    {
        return $this->belongsTo(PendaftarLingkungan::class, 'id_pendaftar');
    }
}
