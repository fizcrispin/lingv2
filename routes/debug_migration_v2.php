<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Route::get('/debug-migration-v2', function () {
    try {
        if (!Schema::hasTable('pendaftar_lingkungan')) {
            return 'Table pendaftar_lingkungan NOT FOUND';
        }
        
        $type = Schema::getColumnType('pendaftar_lingkungan', 'id');
        return 'ID Type: ' . $type;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
