<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint; // ⚡ KUNCI: Harus mengimpor Blueprint yang sah
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ⚡ Ganti type-hint menjadi Blueprint $table
        Schema::table('maintenance_hardware_items', function (Blueprint $table) {
            // Suntikkan kolom note secara aman (nullable)
            $table->text('note')->nullable()->after('condition'); 
        });
    }

    public function down(): void
    {
        // ⚡ Ganti type-hint menjadi Blueprint $table
        Schema::table('maintenance_hardware_items', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};