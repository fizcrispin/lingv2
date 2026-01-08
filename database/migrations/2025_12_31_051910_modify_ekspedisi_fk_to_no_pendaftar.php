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
        // Schema::table('ekspedisi', function (Blueprint $table) {
        //     // 1. Drop old foreign key and column
        //     // Note: constraint name is usually table_column_foreign
        //     // $table->dropForeign(['pendaftar_lingkungan_id']);
        //     // $table->dropColumn('pendaftar_lingkungan_id');

        //     // 2. Add new column referencing no_pendaftar
        //     // We use string because no_pendaftar in parent is string
        //     // $table->string('no_pendaftar')->nullable()->after('id');
        //     // Adding index for performance
        //     // $table->index('no_pendaftar');
        // // });
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
