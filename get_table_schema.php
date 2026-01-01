<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    $tables = ['pendaftar_lingkungan'];
    
    foreach ($tables as $table) {
        if (!Schema::hasTable($table)) {
            echo "Table '$table' not found.\n";
            continue;
        }

        echo "Columns in $table:\n";
        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);
            echo "- $column ($type)\n";
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
