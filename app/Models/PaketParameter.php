<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketParameter extends Model
{
    protected $table = 'paket_parameter';
    protected $guarded = [];

    protected $casts = [
        'parameter' => 'array',
    ];

    public $timestamps = false;

    public function regulasi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Regulasi::class, 'id_regulasi');
    }

    public function getParameters()
    {
        if (empty($this->parameter)) return collect();
        return ParameterLingkungan::whereIn('id', $this->parameter)->get();
    }
    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->nama_parameter)) {
                $model->nama_parameter = '-';
            }
            if (empty($model->option)) {
                $model->option = '2'; // Default Aktif
            }
        });
    }
}
