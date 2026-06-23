<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\FlightLog;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or bootstrap/app.php
| (Laravel 11) and all of them will be assigned to the "api" middleware group.
|
*/

Route::post('/sync-flight-logs', function (Request $request) {
    
    // 1. PROSES VALIDASI DATA YANG DIKIRIM OLEH SERVICE WORKER
    $validator = Validator::make($request->all(), [
        // Identity & Time
        'pilot_id' => 'required|integer',
        'co_pilot_id' => 'required|integer',
        'drone_id' => 'required|integer',
        
        // 🔥 UBAH: Validasi untuk nama lokasi baru/lama dari datalist
        'location_name_bridge' => 'required|string', 
        'flight_location_id' => 'nullable|integer',
        
        'purpose' => 'required|string',
        'flight_mode' => 'required|string',
        'date' => 'required|date',
        'takeoff_time' => 'required',
        'landing_time' => 'nullable',
        'duration' => 'nullable|integer',
        'takeoff_lat' => 'required|numeric',
        'takeoff_lng' => 'required|numeric',
        'address_detail' => 'nullable|string',

        // Validasi struktur JSON Array
        'check_hardware' => 'required|array',
        'check_hardware.pre_motor' => 'required|boolean',
        'check_hardware.pre_propeller' => 'required|boolean',
        'check_hardware.pre_body' => 'required|boolean',
        'check_hardware.pre_remote_id' => 'required|string',
        'check_hardware.pre_remote_percent' => 'required|numeric',
        'check_hardware.pre_battery_id' => 'required|string',
        'check_hardware.pre_battery_percent' => 'required|numeric',
        'check_hardware.pre_battery_temp' => 'required|numeric',
        'check_hardware.pre_phone_battery' => 'required|boolean',

        'check_system' => 'required|array',
        'check_system.fungsi_aplikasi' => 'required|array',
        'check_system.fungsi_sensor' => 'required|array',
        'check_system.signal_gps' => 'required|array',
        'check_system.batt_volt_low' => 'required|string',
        'check_system.batt_volt_high' => 'required|string',
        'check_system.batt_volt_t' => 'required|string',
        'check_system.batt_volt_c' => 'required|string',
        'check_system.remote_control' => 'required|array',
        'check_system.storage_camera' => 'required|array',
        'check_system.preflight_autocheck' => 'nullable|array',
        'check_system.flight_test' => 'required|array',

        // Environment & Weather
        'temp_c' => 'required|numeric',
        'wind_speed' => 'required|numeric',
        'check_environment' => 'required|array',
        'check_environment.humidity' => 'required|numeric',
        'check_environment.weather_api' => 'required|string',
        'check_environment.weather_manual' => 'nullable|array', // Dibuat nullable karena opsional jika offline
        'check_environment.arah_angin' => 'required|string',
        'check_environment.presipitasi_percent' => 'required|numeric',
        'check_environment.jarak_pandang' => 'nullable|array', // Dibuat nullable
        'check_environment.zona_takeoff' => 'nullable|array', // Dibuat nullable

        // Safety
        'check_safety' => 'required|array',
        'check_safety.pilot' => 'nullable|array',
        'check_safety.copilot' => 'nullable|array',
        'check_safety.permit' => 'nullable|array',
        'check_safety.notam_check' => 'required|boolean',
        'check_safety.notam_detail' => 'nullable|string',

        // Post Flight & Attachments
        'is_motor_ok' => 'nullable|boolean',
        'is_propeller_ok' => 'nullable|boolean',
        'is_airframe_ok' => 'nullable|boolean',
        'rc_battery_finish' => 'nullable|numeric',
        'drone_battery_finish' => 'nullable|numeric',
        'requesting_company_id' => 'required|integer',
        'requesting_department_id' => 'required|integer',
        'pic_requester_name' => 'required|string',
        'result' => 'required|string',
        'flight_operation_notes' => 'nullable|string',
        'flight_evidences' => 'nullable|array',
    ]);

    // Jika validasi gagal, kirim error
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validasi gagal saat sinkronisasi data.',
            'errors' => $validator->errors()
        ], 422);
    }

    $validatedData = $validator->validated();

    // 2. PROSES EKSEKUSI PENYIMPANAN KE DATABASE
    DB::beginTransaction();
    try {
        
        // 🔥 LOGIKA OTOMATIS: Cari lokasi di database, atau buat baru jika diketik manual
        if (!empty($validatedData['location_name_bridge'])) {
            $location = \App\Models\FlightLocation::firstOrCreate([
                'location_name' => $validatedData['location_name_bridge']
            ]);
            
            // Suntikkan ID lokasi yang didapat ke dalam data yang akan disimpan
            $validatedData['flight_location_id'] = $location->id;
        }

        // 🔥 Hapus key 'location_name_bridge' agar tidak error "Column not found" saat save ke tabel flight_logs
        unset($validatedData['location_name_bridge']);


        // Eksekusi Simpan Data ke Database
        $flightLog = FlightLog::create($validatedData);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Flight Log berhasil disinkronisasi otomatis dari database lokal!',
            'data_id' => $flightLog->id
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan internal server saat menyimpan data.',
            'error_detail' => $e->getMessage()
        ], 500);
    }
});