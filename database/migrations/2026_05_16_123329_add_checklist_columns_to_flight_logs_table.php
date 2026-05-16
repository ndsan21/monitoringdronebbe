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
        Schema::table('flight_logs', function (Blueprint $table) {
            
            // --- 1. IDENTITY & TIME ADDITIONAL ---
            $table->string('address_detail')->nullable()->after('takeoff_lng'); 

            // --- 2. PRE-FLIGHT CHECKLIST ---
            $table->boolean('pre_drone_motors')->nullable();
            $table->boolean('pre_drone_propellers')->nullable();
            $table->boolean('pre_drone_airframe')->nullable();
            $table->boolean('pre_phone_battery_ok')->nullable();

            $table->string('rc_serial_id')->nullable();
            $table->integer('rc_battery_start')->nullable();
            $table->string('battery_serial_id')->nullable();
            $table->integer('drone_battery_start')->nullable();
            $table->decimal('battery_temp', 5, 2)->nullable();

            // Format JSON karena di form berupa CheckboxList (Banyak centang)
            $table->json('app_readiness')->nullable();
            $table->json('calibration')->nullable();
            $table->json('link_gps')->nullable();
            $table->json('rc_sticks_switches')->nullable();
            $table->json('media_gimbal')->nullable();
            $table->json('app_self_check')->nullable();
            $table->json('flight_test')->nullable();

            $table->decimal('low_cell_v', 5, 2)->nullable();
            $table->decimal('high_cell_v', 5, 2)->nullable();
            $table->decimal('total_voltage_v', 5, 2)->nullable();
            $table->integer('battery_cycles')->nullable();

            // --- 3. ENVIRONMENT & WEATHER ADDITIONAL ---
            // (sky_condition, rain_prob, visibility_km dihapus dari sini karena sudah ada di file utama)
            $table->json('visual_condition')->nullable(); 
            $table->json('visibility')->nullable(); 
            $table->json('ground_safety')->nullable(); 
            $table->string('wind_dir')->nullable(); // Menyesuaikan form yang meminta 'wind_dir'

            // Mapping alternatif field cuaca agar klop dengan pemanggilan form Filament kita
            $table->decimal('temp_c', 5, 2)->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();

            // --- 4. SAFETY & COMPLIANCE ---
            $table->json('pilot_health')->nullable(); 
            $table->json('observer_health')->nullable(); 
            $table->json('clearance')->nullable(); 
            $table->boolean('notam')->default(false)->nullable();
            $table->text('notam_details')->nullable();

            // --- 5. POST-FLIGHT INSPECTION ---
            $table->boolean('is_motor_ok')->nullable();
            $table->boolean('is_propeller_ok')->nullable();
            $table->boolean('is_airframe_ok')->nullable();
            $table->integer('rc_battery_finish')->nullable();
            $table->integer('drone_battery_finish')->nullable();

            // --- 6. FINAL RESULT & ATTACHMENTS ---
            // (result & flight_evidences dihapus dari sini karena sudah ada di file utama)
            $table->foreignId('requesting_company_id')->nullable()->constrained('companies');
            $table->foreignId('requesting_department_id')->nullable()->constrained('departments');
            $table->string('pic_requester_name')->nullable();
            $table->text('flight_operation_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flight_logs', function (Blueprint $table) {
            $table->dropForeign(['requesting_company_id']);
            $table->dropForeign(['requesting_department_id']);
            
            $table->dropColumn([
                'address_detail',
                'pre_drone_motors', 'pre_drone_propellers', 'pre_drone_airframe', 'pre_phone_battery_ok',
                'rc_serial_id', 'rc_battery_start', 'battery_serial_id', 'drone_battery_start', 'battery_temp',
                'app_readiness', 'calibration', 'link_gps', 'rc_sticks_switches', 'media_gimbal', 'app_self_check', 'flight_test',
                'low_cell_v', 'high_cell_v', 'total_voltage_v', 'battery_cycles',
                'visual_condition', 'visibility', 'ground_safety', 'wind_dir',
                'temp_c', 'wind_speed', 'humidity',
                'pilot_health', 'observer_health', 'clearance', 'notam', 'notam_details',
                'is_motor_ok', 'is_propeller_ok', 'is_airframe_ok', 'rc_battery_finish', 'drone_battery_finish',
                'requesting_company_id', 'requesting_department_id', 'pic_requester_name', 'flight_operation_notes'
            ]);
        });
    }
};