<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'asset_id', 'technician_id', 'maintenance_type', 'firmware_version_before',
        'firmware_version_after', 'software_status', 'oos_damage_severity',
        'oos_incident_date', 'oos_location', 'oos_chronology', 'technical_notes', 'photos_evidence'
    ];

    protected $casts = ['photos_evidence' => 'array', 'oos_incident_date' => 'date'];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
    public function hardwareItems(): HasMany { return $this->hasMany(MaintenanceHardwareItem::class); }
}