<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // JANGAN ADA Schema::create('subscription_groups') DI SINI!
        
        // Cukup tambahkan kolom ke tabel yang sudah ada
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('subscription_group_id')->nullable()->constrained('subscription_groups')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('subscription_group_id')->nullable()->constrained('subscription_groups')->onDelete('set null');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('subscription_group_id')->nullable()->constrained('subscription_groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) { $table->dropForeign(['subscription_group_id']); $table->dropColumn('subscription_group_id'); });
        Schema::table('users', function (Blueprint $table) { $table->dropForeign(['subscription_group_id']); $table->dropColumn('subscription_group_id'); });
        Schema::table('assets', function (Blueprint $table) { $table->dropForeign(['subscription_group_id']); $table->dropColumn('subscription_group_id'); });
    }
};