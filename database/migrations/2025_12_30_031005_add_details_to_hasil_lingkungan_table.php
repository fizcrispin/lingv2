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
        Schema::table('hasil_lingkungan', function (Blueprint $table) {
            $table->date('tanggal_input')->nullable()->after('hasil_parameter');
            $table->text('keterangan')->nullable()->after('tanggal_input');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_lingkungan', function (Blueprint $table) {
            $table->dropColumn(['tanggal_input', 'keterangan']);
        });
    }
};
