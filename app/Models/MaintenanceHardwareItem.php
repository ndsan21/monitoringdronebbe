<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceHardwareItem extends Model
{
    protected $fillable = [
        'maintenance_log_id',
        'asset_id',
        'current_status',
        'condition',
        'replaced_with_sparepart_id',
        'note' // Tambahkan note jika ada di form
    ];

    public function maintenanceLog(): BelongsTo 
    {
        return $this->belongsTo(MaintenanceLog::class);
    }

    public function asset(): BelongsTo 
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * ⚡ PIPELINE OTOMATISASI MASTER DATA & DAMAGE REPORT
     */
    protected static function booted(): void
    {
        // Pemicu otomatis saat teknisi mengklik simpan (data baru tercipta)
        static::created(function (MaintenanceHardwareItem $item) {
            self::syncAssetAndDamageReport($item);
        });

        // Pemicu otomatis saat data log maintenance di-edit/diperbarui
        static::updated(function (MaintenanceHardwareItem $item) {
            self::syncAssetAndDamageReport($item);
        });
    }

    /**
     * Logika Sinkronisasi Inti
     */
    protected static function syncAssetAndDamageReport(MaintenanceHardwareItem $item)
    {
        // 1. Ambil data Asset (Komponen/Drone) yang sedang diperiksa
        $asset = Asset::find($item->asset_id);
        if (!$asset) return;

        // 2. Ambil data induk Maintenance Log untuk tahu siapa teknisinya dan tanggalnya
        $parentLog = $item->maintenanceLog;

        // --- PIPELINE 1: SINKRONISASI STATUS MASTER DATA ASSET ---
        if ($item->condition === 'good') {
            $asset->update(['status' => 'ready']);
        } 
        
        elseif ($item->condition === 'damaged_replace') {
            $asset->update(['status' => 'on_repaired']);
            
            // Jika ada sparepart pengganti yang dipilih, pasang drone_id-nya dan ubah status sparepart tersebut
            if ($item->replaced_with_sparepart_id) {
                $sparepart = Asset::find($item->replaced_with_sparepart_id);
                if ($sparepart) {
                    $sparepart->update([
                        'status' => 'in_use',
                        'drone_id' => $asset->drone_id ?? $asset->id // Kaitkan ke drone induknya
                    ]);
                }
                
                // Komponen lama yang rusak total langsung dilepas dari drone induk dan di-afkir
                $asset->update([
                    'drone_id' => null,
                    'status' => 'out_of_service'
                ]);
            }
        } 
        
        elseif ($item->condition === 'out_of_service') {
            $asset->update(['status' => 'out_of_service']);
        }

        // --- PIPELINE 2: OTOMATIS MEMBUAT LAPORAN DI MENU DAMAGE REPORT ---
        if (in_array($item->condition, ['damaged_replace', 'out_of_service'])) {
            
            // Cek apakah laporan kerusakan untuk item ini sudah pernah dibuat otomatis (biar tidak duplikat)
            $existingReport = DamageReport::where('asset_id', $item->asset_id)
                ->where('incident_date', now()->format('Y-m-d'))
                ->exists();

            if (!$existingReport) {
                DamageReport::create([
                    'asset_id'              => $item->asset_id,
                    'reported_by_id'        => $parentLog->technician_id ?? auth()->id() ?? 1,
                    'report_date'           => now(),
                    'damage_severity'       => 'moderate', // Default severity awal, bisa di-edit di menu Damage Report
                    'incident_date'         => now(),
                    'incident_time'         => now()->format('H:i'),
                    'incident_location_name'=> 'Workshop / Maintenance Site Inspection',
                    'chronology'            => "Terdeteksi otomatis rusak pada saat dilakukan Maintenance dengan tipe '" . 
                                               ($parentLog->maintenance_type ?? 'Hardware Inspection') . 
                                               "' pada tanggal " . now()->format('d-m-Y') . ".",
                    'current_status'        => 'reported',
                    'condition_status'      => $item->condition, // damaged_replace atau out_of_service
                    'note'                  => "Auto-generated pipeline dari Maintenance Log nomor #" . $item->maintenance_log_id
                ]);
            }
        }
    }
}