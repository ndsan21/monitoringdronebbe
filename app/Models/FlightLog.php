<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightLog extends Model
{
    use HasFactory;

    // 1. BUKA GERBANG KEAMANAN: Izinkan semua kolom dari form untuk disimpan ke database
    protected $guarded = []; 
    // Catatan: Jika sebelumnya Anda menggunakan protected $fillable = ['...'], 
    // silakan HAPUS/KOMEN baris $fillable tersebut dan ganti dengan $guarded = []

    // 2. CASTING DATA: Beritahu Laravel bahwa field di bawah ini berbentuk Array/Boolean
    protected $casts = [
        'date' => 'date',
        
        // Data dari CheckboxList (Wajib di-cast ke array agar tidak eror)
        'app_readiness' => 'array',
        'calibration' => 'array',
        'link_gps' => 'array',
        'rc_sticks_switches' => 'array',
        'media_gimbal' => 'array',
        'app_self_check' => 'array',
        'flight_test' => 'array',
        'visual_condition' => 'array',
        'visibility' => 'array',
        'ground_safety' => 'array',
        'pilot_health' => 'array',
        'observer_health' => 'array',
        'clearance' => 'array',
        
        // Data dari FileUpload multiple (Gallery)
        'flight_evidences' => 'array',
        
        // Data dari Toggle (Boolean)
        'pre_drone_motors' => 'boolean',
        'pre_drone_propellers' => 'boolean',
        'pre_drone_airframe' => 'boolean',
        'pre_phone_battery_ok' => 'boolean',
        'notam' => 'boolean',
        'is_motor_ok' => 'boolean',
        'is_propeller_ok' => 'boolean',
        'is_airframe_ok' => 'boolean',
    ];
// --- RELASI DATABASE (FIX NAMESPACE ERROR) ---

    /**
     * Relasi ke Asset (Data Drone yang diterbangkan)
     */
    public function drone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Mengarah ke Asset::class karena data drone ada di tabel assets sekarang
        return $this->belongsTo(Asset::class, 'drone_id'); 
    }

    /**
     * Relasi ke User (Pilot Utama)
     */
    public function pilot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'pilot_id');
    }

    /**
     * Relasi ke User (Co-Pilot / Asisten)
     */
    public function coPilot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'co_pilot_id');
    }

    /**
     * Relasi ke User (Pihak yang meminta penerbangan)
     */
    public function requester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relasi ke User (Pihak yang memberikan izin / otorisasi)
     */
    public function authorizedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_id');
    }

    /**
     * Relasi ke Master Data Lokasi
     */
    public function flightLocation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FlightLocation::class, 'flight_location_id');
    }
}