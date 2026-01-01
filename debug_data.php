<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PendaftarLingkungan;

$pendaftar = PendaftarLingkungan::with(['jenisSampel'])->latest()->first();

if ($pendaftar) {
    echo "ID: {$pendaftar->id}\n";
    echo "Waktu Sampling (Raw): " . ($pendaftar->waktu_sampling ?? 'NULL') . "\n";
    echo "Jenis Sampling ID: " . ($pendaftar->jenis_sampling ?? 'NULL') . "\n";
    
    if ($pendaftar->jenisSampel) {
        echo "Jenis Sampel Nama: " . ($pendaftar->jenisSampel->nama_sampel ?? 'NULL') . "\n";
    } else {
        echo "Relation jenisSampel is NULL.\n";
    }
} else {
    echo "No matching record.\n";
}
