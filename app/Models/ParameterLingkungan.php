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
        'nama_parameter',
        'harga_parameter',

        // ... kolom lainnya
    ];


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
}


