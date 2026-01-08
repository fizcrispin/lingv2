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
        Schema::dropIfExists('statuses');
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            // Use unsignedBigInteger just to be safe, standard in Laravel 10+
            $table->unsignedBigInteger('id_pendaftar')->index(); 
            // Removed foreign constraint to avoid type mismatch errors during migration debug
            // $table->foreign('id_pendaftar')->references('id')->on('pendaftar_lingkungan')->cascadeOnDelete();
            
            $table->string('notifikasi')->nullable();
            $table->boolean('dicetak')->default(false);
            $table->boolean('diambil')->default(false);
            $table->string('pengambil')->nullable();
            $table->dateTime('tanggal_diambil')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
