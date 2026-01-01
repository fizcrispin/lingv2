<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilLingkungan extends Model
{
    protected $table = 'hasil_lingkungan';
    protected $guarded = [];

    protected $casts = [
        'tanggal_input' => 'date',
    ];

    // Jika tidak ada updated_at di tabel, nonaktifkan timestamps atau atur kustom
    public $timestamps = false;

    /**
     * Get the pendaftar that owns the hasil.
     */
    public function pendaftar(): BelongsTo
    {
        return $this->belongsTo(PendaftarLingkungan::class, 'id_pendaftar');
    }

    /**
     * Get the parameter that owns the hasil.
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(ParameterLingkungan::class, 'id_parameter');
    }
}
