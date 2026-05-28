<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek & Buat Relasi
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            }

            // Cek & Buat Identitas Karyawan
            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'photo_path')) {
                $table->string('photo_path')->nullable();
            }

            // Cek & Buat Sertifikasi Pilot
            if (!Schema::hasColumn('users', 'license_number')) {
                $table->string('license_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'license_issued_by')) {
                $table->string('license_issued_by')->nullable();
            }
            if (!Schema::hasColumn('users', 'license_expiration_date')) {
                $table->date('license_expiration_date')->nullable();
            }
            if (!Schema::hasColumn('users', 'digital_signature')) {
                $table->string('digital_signature')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Bagian down dikosongkan saja untuk menghindari rollback tidak sengaja pada data yang sudah ada
    }
};