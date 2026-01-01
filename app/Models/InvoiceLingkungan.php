<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLingkungan extends Model
{
    protected $table = 'invoice_lingkungan';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'tanggal_bayar' => 'date',
        'tanggal_tagihan' => 'date',
        'total_harga' => 'integer',
        'total_bayar' => 'integer',
    ];

    /**
     * Get the pendaftar that owns the invoice.
     */
    public function pendaftar(): BelongsTo
    {
        return $this->belongsTo(PendaftarLingkungan::class, 'id_pendaftar');
    }
}
