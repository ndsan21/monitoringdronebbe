<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. COMPANIES
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        // 2. DEPARTMENTS
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // RE-ARCHITECTING USERS TABLE
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('name');
            $table->string('employee_id')->nullable()->unique()->after('full_name');
            $table->string('photo_path')->nullable()->after('employee_id');
            $table->foreignId('company_id')->nullable()->after('photo_path')->constrained('companies');
            $table->foreignId('department_id')->nullable()->after('company_id')->constrained('departments');
            // License fields
            $table->string('license_number')->nullable();
            $table->string('license_issued_by')->nullable();
            $table->date('license_expiration_date')->nullable();
            $table->text('digital_signature')->nullable();
            // Access configuration
            $table->enum('role', ['super_admin', 'admin', 'pilot'])->default('pilot')->after('password');
            $table->boolean('is_approved')->default(true);
        });

        // 3. DRONES
        Schema::create('drones', function (Blueprint $table) {
            $table->id();
            $table->string('model'); // Cukup 1 kolom ini saja untuk menyimpan asset_name
            $table->timestamps();
        });

        // 4. ASSETS
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('serial_number')->unique();
            $table->string('asset_name');
            $table->enum('category', ['DRONE', 'SPAREPART']);
            $table->string('sparepart_type')->nullable();
            $table->foreignId('drone_id')->nullable()->constrained('drones')->nullOnDelete();
            $table->date('entry_date');
            $table->enum('status', ['ready', 'in_use', 'on_repaired', 'out_of_service'])->default('ready');
            $table->foreignId('owner_company_id')->constrained('companies');
            $table->foreignId('department_id')->constrained('departments');
            $table->date('received_date');
            $table->string('received_by');
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        // 5. FLIGHT LOCATIONS
        Schema::create('flight_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name');
            $table->string('iup_number')->nullable();
            $table->foreignId('company_id')->constrained('companies');
            $table->timestamps();
        });

        // 6. FLIGHT LOGS
        Schema::create('flight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drone_id')->constrained('drones');
            $table->foreignId('pilot_id')->constrained('users');
            $table->foreignId('co_pilot_id')->nullable()->constrained('users');
            $table->foreignId('requester_id')->nullable()->constrained('users');
            $table->foreignId('authorized_by_id')->nullable()->constrained('users');
            $table->string('purpose');
            $table->enum('flight_mode', ['auto', 'tc', 'pn', 'sa']);
            $table->string('flight_area_name');
            $table->foreignId('flight_location_id')->nullable()->constrained('flight_locations');
            $table->date('date');
            $table->dateTime('takeoff_time')->nullable();
            $table->dateTime('landing_time')->nullable();
            $table->integer('duration')->nullable()->default(0);
            $table->decimal('takeoff_lat', 10, 7)->nullable();
            $table->decimal('takeoff_lng', 10, 7)->nullable();
            $table->enum('result', ['safe_to_fly', 'postpone', 'cancel'])->nullable();
            $table->text('note')->nullable();
            // Weather Metrics
            $table->string('sky_condition')->nullable();
            $table->decimal('wind_speed_kmh', 6, 2)->nullable();
            $table->string('wind_direction')->nullable();
            $table->decimal('humidity_percent', 5, 2)->nullable();
            $table->decimal('temperature_c', 5, 2)->nullable();
            $table->string('rain_prob')->nullable();
            $table->decimal('visibility_km', 5, 2)->nullable();
            // Checklists
            $table->json('hardware_checklist')->nullable();
            $table->json('system_function_checklist')->nullable();
            $table->json('environment_checklist')->nullable();
            $table->json('safety_permit_checklist')->nullable();
            $table->json('flight_evidences')->nullable();
            $table->timestamps();
        });

        // 7. DAMAGE REPORTS
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets');
            $table->foreignId('reported_by_id')->constrained('users');
            $table->date('report_date');
            $table->enum('damage_severity', ['minor', 'moderate', 'major']);
            $table->date('incident_date');
            $table->time('incident_time');
            $table->string('incident_location_name');
            $table->foreignId('incident_location_id')->nullable()->constrained('flight_locations');
            $table->text('chronology');
            $table->enum('current_status', ['reported', 'on_progress', 'resolved'])->default('reported');
            $table->enum('condition_status', ['good', 'damaged_replace', 'out_of_service']);
            $table->text('note')->nullable();
            $table->json('evidences')->nullable();
            $table->timestamps();
        });

        // 8. MAINTENANCE LOGS
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets');
            $table->foreignId('technician_id')->constrained('users');
            $table->enum('maintenance_type', ['hardware_inspection', 'software_update', 'full_maintenance']);
            $table->string('firmware_version_before')->nullable();
            $table->string('firmware_version_after')->nullable();
            $table->enum('software_status', ['stable', 'beta', 'issues_detected'])->nullable();
            $table->enum('oos_damage_severity', ['minor', 'moderate', 'major'])->nullable();
            $table->date('oos_incident_date')->nullable();
            $table->string('oos_location')->nullable();
            $table->text('oos_chronology')->nullable();
            $table->text('technical_notes')->nullable();
            $table->json('photos_evidence')->nullable();
            $table->timestamps();
        });

        // 9. MAINTENANCE HARDWARE ITEMS
        Schema::create('maintenance_hardware_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_log_id')->constrained('maintenance_logs')->cascadeOnDelete();
            $table->string('component_name');
            $table->enum('current_status', ['reported', 'on_progress', 'resolved']);
            $table->enum('condition', ['good', 'damaged_replace', 'out_of_service']);
            $table->foreignId('replaced_with_sparepart_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_hardware_items');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('damage_reports');
        Schema::dropIfExists('flight_logs');
        Schema::dropIfExists('flight_locations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('drones');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('companies');
    }
};