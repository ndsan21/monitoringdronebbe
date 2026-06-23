<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('damage_reports', function (Blueprint $table) {
        $table->foreignId('flight_log_id')
            ->nullable()
            ->after('asset_id')
            ->constrained('flight_logs')
            ->nullOnDelete();

        $table->enum('fuzzy_severity_label', ['ringan', 'sedang', 'berat'])
            ->nullable()
            ->after('damage_severity');

        $table->decimal('fuzzy_severity_score', 5, 2)
            ->nullable()
            ->after('fuzzy_severity_label');

        $table->json('fuzzy_input_snapshot')
            ->nullable()
            ->after('fuzzy_severity_score');
    });
}

public function down(): void
{
    Schema::table('damage_reports', function (Blueprint $table) {
        $table->dropForeign(['flight_log_id']);
        $table->dropColumn([
            'flight_log_id',
            'fuzzy_severity_label',
            'fuzzy_severity_score',
            'fuzzy_input_snapshot',
        ]);
    });
}
};
