<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    protected $fillable = [
        'asset_id', 'reported_by_id', 'report_date', 'damage_severity', 'incident_date',
        'incident_time', 'incident_location_name', 'incident_location_id', 'chronology',
        'current_status', 'condition_status', 'note', 'evidences'
    ];

    protected $casts = [
        'evidences' => 'array', 
        'report_date' => 'date', 
        'incident_date' => 'date'
    ];

    // --- RELASI DATABASE ---
    
    /**
     * Relasi ke Asset yang rusak (Drone / Sparepart)
     */
    public function asset(): BelongsTo 
    { 
        return $this->belongsTo(Asset::class); 
    }
    
    /**
     * Relasi ke User (Pilot/Admin yang melaporkan)
     */
    public function reportedBy(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'reported_by_id'); 
    }
    
    /**
     * Relasi ke Master Data Lokasi (Opsional, jika kerusakan terjadi di area terdaftar)
     */
    public function incidentLocation(): BelongsTo 
    { 
        return $this->belongsTo(FlightLocation::class, 'incident_location_id'); 
    }

    // --- ENGINE OTOMATISASI ---

    /**
     * Boot the model: Memicu aksi otomatis setiap kali data Laporan disimpan
     */
    protected static function booted(): void
    {
        static::saved(function (DamageReport $report) {
            $asset = $report->asset;
            if (!$asset) return;

            // Sinkronisasi status Laporan ke status Aset Fisik
            $asset->status = match ($report->condition_status) {
                'good' => 'ready',
                'damaged_replace' => 'on_repaired',
                'out_of_service' => 'out_of_service',
            };
            
            $asset->save();
        });
    }
}