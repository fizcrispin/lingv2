<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PendaftarLingkungan extends Model
{
    protected $table = 'pendaftar_lingkungan';
    protected $guarded = [];



    protected static function booted()
    {
        static::created(function ($model) {
             $model->ekspedisi()->create([
                 'no_pendaftar' => $model->no_pendaftar,
             ]);
        });

        static::deleting(function ($model) {
            $model->ekspedisi()->delete();
            $model->invoice()->delete(); // Hapus Invoice (Transaksi)
            $model->hasilLingkungans()->delete(); // Hapus Hasil Lab
        });

        static::saving(function ($model) {
            // Pastikan parameter dalam bentuk array (tidak integer atau string)
            if (is_string($model->parameter)) {
                $decoded = json_decode($model->parameter, true);
                $model->parameter = is_array($decoded) ? array_map('intval', $decoded) : [];
            }

            // Jika paket dipilih, hapus/musnahkan parameter manual
            if (!empty($model->paket_id)) {
                $model->parameter = null; // atau null, sesuai kebutuhan/cast
            } else {
                // kalau paket kosong, pastikan parameter tetap array (bukan integer)
                $model->parameter = is_array($model->parameter) ? array_map('intval', $model->parameter) : [];
            }
        });
    }

    public function ekspedisi(): HasOne
    {
        return $this->hasOne(Ekspedisi::class, 'no_pendaftar', 'no_pendaftar');
    }


    /**
     * Cast attributes to native types
     */
    protected $casts = [
        'parameter' => 'array',  // otomatis decode JSON ke array
        'tanggal_pendaftar' => 'date',
        'tanggal_sampling' => 'date',
        'is_free' => 'boolean',
    ];

    /**
     * Get the jenis sampel that owns the pendaftar.
     */
    public function jenisSampel(): BelongsTo
    {
        return $this->belongsTo(JenisSampel::class, 'jenis_sampling');
    }

    /**
     * Get the regulasi that owns the pendaftar.
     */
    public function regulasi(): BelongsTo
    {
        return $this->belongsTo(Regulasi::class, 'regulasi_id');
    }

    /**
     * Get the paket parameter that owns the pendaftar.
     */
    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketParameter::class, 'paket_id');
    }

    /**
     * Get the invoice associated with the pendaftar.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(InvoiceLingkungan::class, 'id_pendaftar', 'no_pendaftar');
    }

    /**
     * Accessor: Check if using manual parameters
     */
    public function getIsManualParameterAttribute(): bool
    {
        return !empty($this->parameter) && 
               $this->parameter !== '[]' && 
               $this->parameter !== [] && 
               is_null($this->paket_id);
    }

    /**
     * Accessor: Check if using paket
     */
    public function getIsPaketAttribute(): bool
    {
        return !is_null($this->paket_id);
    }

    /**
     * Get selected parameters (untuk mode manual)
     */
    public function selectedParameters()
    {
        if (empty($this->parameter)) {
            return collect([]);
        }

        $parameterIds = is_array($this->parameter) ? $this->parameter : json_decode($this->parameter, true);
        
        if (empty($parameterIds)) {
            return collect([]);
        }

        return ParameterLingkungan::whereIn('id', $parameterIds)->get();
    }

    /**
     * Calculate total harga
     */
    public function getTotalHargaAttribute(): int
    {
        // Jika menggunakan paket
        if ($this->paket_id && $this->paket) {
            return $this->paket->total_harga ?? 0;
        }

        // Jika menggunakan parameter manual
        if (!empty($this->parameter)) {
            $parameterIds = is_array($this->parameter) ? $this->parameter : json_decode($this->parameter, true);
            
            if (!empty($parameterIds) && $parameterIds !== '[]') {
                return ParameterLingkungan::whereIn('id', $parameterIds)
                    ->sum('harga_parameter');
            }
        }

        return 0;
    }
    /**
     * Get the hasil lingkungan records (results) associated with the pendaftar.
     */
    public function hasilLingkungans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HasilLingkungan::class, 'id_pendaftar');
    }

    /**
     * Get ALL selected parameters (Manual + Paket)
     */
    public function getAllParametersAttribute()
    {
        $allIds = [];

        // 1. Ambil dari kolom parameter (manual)
        $manualIds = $this->parameter; 
        if (is_array($manualIds)) {
             $allIds = array_merge($allIds, $manualIds);
        } elseif (is_string($manualIds)) {
             $decoded = json_decode($manualIds, true);
             if (is_array($decoded)) {
                 $allIds = array_merge($allIds, $decoded);
             }
        }

        // 2. Ambil dari paket_id
        // Load paket relation if not loaded to avoid N+1 issues in loops if accessed carelessly
        // But for single record view it is fine.
        if (!empty($this->paket_id)) {
            $paket = $this->paket; 
            if ($paket && is_array($paket->parameter)) {
                $allIds = array_merge($allIds, $paket->parameter);
            }
        }

        $allIds = array_unique(array_filter(array_map('intval', $allIds)));
        
        if (empty($allIds)) {
            return collect([]);
        }

        return ParameterLingkungan::whereIn('id', $allIds)->get();
    }

    /**
     * Sync/Refresh Invoice & Hasil Lingkungan based on current selection
     */
    public function refreshRelatedRecords()
    {
        // 1. Dapatkan semua parameter terpilih
        $parameters = $this->all_parameters; // menggunakan accessor getAllParametersAttribute
        $parameterIds = $parameters->pluck('id')->toArray();

        // 2. Sync Hasil Lingkungan
        // Ambil hasil yang sudah ada
        $existingHasil = $this->hasilLingkungans()->pluck('id_parameter', 'id')->toArray();
        $existingParameterIds = array_values($existingHasil);

        // Cari yang perlu ditambah (ada di selected tapi belum ada di tabel hasil)
        $toAdd = array_diff($parameterIds, $existingParameterIds);
        
        // Cari yang perlu dihapus (ada di tabel hasil tapi tidak ada di selected)
        // Note: Hati-hati menghapus jika sudah ada isian hasil? 
        // Request user: "reset juga ... disesuaikan dengan paket_id atau parameter yang baru"
        // Asumsi: Delete yang tidak relevan.
        $toDeleteParamIds = array_diff($existingParameterIds, $parameterIds);

        // Delete yang extraneous
        if (!empty($toDeleteParamIds)) {
            $this->hasilLingkungans()->whereIn('id_parameter', $toDeleteParamIds)->delete();
        }

        // Add yang missing
        $newRecords = [];
        $paramsToAdd = ParameterLingkungan::whereIn('id', $toAdd)->get()->keyBy('id');

        foreach ($toAdd as $paramId) {
            $paramModel = $paramsToAdd[$paramId] ?? null;
            $newRecords[] = [
                'id_pendaftar' => $this->id,
                'id_parameter' => $paramId,
                'nama_parameter' => $paramModel ? $paramModel->nama_parameter : null,
                'hasil_parameter' => null, // default
            ];
        }

        if (!empty($newRecords)) {
            $this->hasilLingkungans()->insert($newRecords);
        }

        // 3. Sync Invoice Lingkungan
        $totalHarga = $this->total_harga; // menggunakan accessor getTotalHargaAttribute

        // Cek apakah invoice sudah ada
        $invoice = $this->invoice;
        if ($invoice) {
            $invoice->update([
                'total_harga' => $totalHarga,
                // Jika belum lunas, mungkin update total_bayar? Atau biarkan user handle pembayaran
                // Biasanya total_bayar update saat bayar. Di sini hanya update tagihan.
            ]);
        } else {
            // Create invoice baru jika belum ada
            if ($totalHarga > 0) {
                $this->invoice()->create([
                    'total_harga' => $totalHarga,
                    'total_bayar' => 0,
                    'status_bayar' => 0,
                    'tanggal_tagihan' => now(),
                ]);
            }
        }
    }
}