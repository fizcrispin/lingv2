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
        Schema::create('ekspedisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftar_lingkungan_id')
                ->constrained('pendaftar_lingkungan')
                ->cascadeOnDelete();
            $table->boolean('sampel_diterima')->default(false);
            $table->dateTime('tanggal_diterima')->nullable();
            $table->string('verifikasi_hasil')->nullable();
            $table->string('validasi1')->nullable();
            $table->string('validasi2')->nullable();
            $table->boolean('sampel_dimusnahkan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekspedisi');
    }
};
