<?php

use App\Models\PendaftarLingkungan;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting full backfill...\n";
// Load all missing records at once (safe for ~2000 records)
$missing = PendaftarLingkungan::doesntHave('ekspedisi')->get();
$count = 0;
foreach ($missing as $p) {
    $p->ekspedisi()->create([]);
    $count++;
}

echo "Backfilled $count records.\n";
