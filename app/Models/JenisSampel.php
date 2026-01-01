<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSampel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    // Ganti 'jenis_sampel' dengan nama tabel yang sesuai di database Anda
    protected $table = 'jenis_sampel'; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // Tambahkan kolom-kolom yang ada di tabel 'jenis_sampel'
    protected $fillable = [
        'nama_sampel',
        'kode',
        // ... kolom lainnya
    ];
}