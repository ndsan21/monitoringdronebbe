<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Mengecek agar tidak error duplicate jika ternyata sudah ada
            if (!Schema::hasColumn('users', 'subscription_group_id')) {
                // Pastikan nama tabel referensinya benar 'subscription_groups'
                $table->foreignId('subscription_group_id')->nullable()->constrained('subscription_groups')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Kosongkan saja
    }
};