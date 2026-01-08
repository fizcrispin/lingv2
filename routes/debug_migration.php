<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Route::get('/debug-migration', function () {
    try {
        $exists = Schema::hasTable('pendaftar_lingkungan');
        $statusTable = Schema::hasTable('statuses');
        
        // Cek migrasi pending
        $pending = collect(DB::select("SHOW TABLES"))->map(function($t){
            return array_values((array)$t)[0];
        });

        return response()->json([
            'pendaftar_lingkungan_exists' => $exists,
            'statuses_exists' => $statusTable,
            'tables' => $pending
        ]);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
