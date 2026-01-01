<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ekspedisi', function (Blueprint $table) {
            // Drop old columns first (safest way to change type without doctrine/dbal if not installed)
            $table->dropColumn(['verifikasi_hasil', 'validasi1', 'validasi2']);
            
            // Re-add as boolean
            $table->boolean('verifikasi_hasil')->default(false);
            $table->boolean('validasi1')->default(false);
            $table->boolean('validasi2')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ekspedisi', function (Blueprint $table) {
            //
        });
    }
};
