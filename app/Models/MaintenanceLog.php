<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceLog extends Model
{
    // Membuka gerbang fillable untuk semua field inputan baru di form
    protected $fillable = [
        'date',
        'asset_id',
        'technician_id',
        'maintenance_type',
        'maintenance_date',
        'maintenance_status',
        // Tambahkan field penampung checkbox baru
        'software_app_checklist',
        'sensors_calibration_checklist',
        'technical_notes',
        'photos_evidence'
    ];

    protected $casts = [
        'photos_evidence' => 'array',
        'date' => 'date',
        'maintenance_date' => 'date',
        // 🔥 WAJIB: Cast kedua field checkbox ini menjadi array PHP
        'software_app_checklist' => 'array',
        'sensors_calibration_checklist' => 'array',
    ];

    /**
     * Relasi ke Asset: Merujuk ke unit Drone utama yang sedang diservis
     */
    public function asset(): BelongsTo 
    { 
        return $this->belongsTo(Asset::class, 'asset_id'); 
    }

    /**
     * Relasi ke User: Teknisi pembuat log
     */
    public function technician(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'technician_id'); 
    }

    /**
     * Relasi ke Anak: Menampung daftar komponen item repeater yang dicek
     */
    public function hardwareItems(): HasMany 
    { 
        return $this->hasMany(MaintenanceHardwareItem::class, 'maintenance_log_id'); 
    }
}