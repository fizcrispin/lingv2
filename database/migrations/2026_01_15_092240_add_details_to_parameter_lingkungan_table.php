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
        Schema::table('parameter_lingkungan', function (Blueprint $table) {
            if (!Schema::hasColumn('parameter_lingkungan', 'batas_max')) {
                $table->string('batas_max')->nullable();
            }
            if (!Schema::hasColumn('parameter_lingkungan', 'satuan')) {
                $table->string('satuan')->nullable();
            }
            if (!Schema::hasColumn('parameter_lingkungan', 'metode_pemeriksaan')) {
                $table->string('metode_pemeriksaan')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parameter_lingkungan', function (Blueprint $table) {
            //
        });
    }
};
