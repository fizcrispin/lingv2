<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;

class ParameterLingkungan extends Model
{
    protected $table = 'parameter_lingkungan';
        protected $fillable = [
        'id',
        'id_regulasi',
        'kategori',
        'nama_parameter',
        'harga_parameter',
        'batas_max',
        'satuan',
        'metode_pemeriksaan',

        // ... kolom lainnya
    ];

    public $timestamps = false;


    // public function getParameterAttribute($value)
    // {
    //     if (empty($value)) {
    //         return [];
    //     }

    //     return explode(',', $value);
    // }

    // public function setParameterAttribute($value)
    // {
    //     $this->attributes['parameter'] = is_array($value)
    //         ? implode(',', $value)
    //         : $value;
    // }

    public function kategoriData(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori');
    }

    public function regulasi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Regulasi::class, 'id_regulasi');
    }
}


