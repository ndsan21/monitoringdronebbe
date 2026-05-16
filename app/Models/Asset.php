<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $fillable = [
        'asset_id', 'serial_number', 'asset_name', 'category', 'sparepart_type',
        'drone_id', 'entry_date', 'status', 'owner_company_id', 'department_id',
        'received_date', 'received_by', 'photo_path'
    ];

    public function drone(): BelongsTo { return $this->belongsTo(Drone::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class, 'owner_company_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
}