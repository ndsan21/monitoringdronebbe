<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'asset_id', 
        'serial_number', 
        'asset_name', 
        'category', 
        'sparepart_type',
        'drone_id', 
        'entry_date', 
        'status', 
        'owner_company_id', 
        'department_id',
        'received_date', 
        'received_by', 
        'photo_path'
    ];

    /**
     * Relasi ke Company (Pemilik Asset) - KUNCI ISOLASI DATA SAAS
     */
    public function company(): BelongsTo 
    { 
        return $this->belongsTo(Company::class, 'owner_company_id'); 
    }

    /**
     * Relasi ke Department
     */
    public function department(): BelongsTo 
    { 
        return $this->belongsTo(Department::class); 
    }

    /**
     * Jika asset ini adalah SPAREPART, ini akan mengambil data DRONE induknya
     */
    public function drone(): BelongsTo 
    { 
        return $this->belongsTo(Asset::class, 'drone_id'); 
    }

    /**
     * Relasi Kebalikan: Jika asset ini adalah DRONE, ini mengambil semua komponen (SPAREPART) yang menempel padanya
     */
    public function spareparts(): HasMany
    {
        return $this->hasMany(Asset::class, 'drone_id');
    }

    /**
     * Relasi Alternatif (Alias dari fungsi drone())
     */
    public function currentDrone(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'drone_id');
    }
    
    /**
     * Relasi ke Flight Logs (Riwayat Penerbangan Drone)
     */
    public function flightLogs(): HasMany
    {
        return $this->hasMany(FlightLog::class, 'drone_id'); 
    }

    // --- RELASI TAMBAHAN UNTUK MODUL OPERASIONAL ---

    /**
     * Relasi ke Damage Reports (Laporan Kerusakan Asset)
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'asset_id');
    }

    /**
     * Relasi ke Maintenance Logs (Catatan Perawatan/Service Asset)
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'asset_id');
    }
}