<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceHardwareItem extends Model
{
    protected $fillable = [
        'maintenance_log_id', 'component_name', 'current_status', 'condition', 'replaced_with_sparepart_id'
    ];

    public function maintenanceLog(): BelongsTo { return $this->belongsTo(MaintenanceLog::class); }
    public function replacedSparepart(): BelongsTo { return $this->belongsTo(Asset::class, 'replaced_with_sparepart_id'); }

    protected static function booted(): void
    {
        static::saved(function (MaintenanceHardwareItem $item) {
            $log = $item->maintenanceLog;
            if ($log && $log->asset) {
                $drone = $log->asset;
                if ($item->condition === 'out_of_service') {
                    $drone->update(['status' => 'out_of_service']);
                } elseif ($item->condition === 'damaged_replace' && $drone->status !== 'out_of_service') {
                    $drone->update(['status' => 'on_repaired']);
                }
            }
            if ($item->replaced_with_sparepart_id && $item->condition === 'damaged_replace') {
                Asset::where('id', $item->replaced_with_sparepart_id)->update(['status' => 'in_use']);
            }
        });
    }
}