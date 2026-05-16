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
     * Relasi ke Company (Pemilik Asset)
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
     * FIX UTAMA: Jika asset ini adalah SPAREPART, ini akan mengambil data DRONE induknya
     * Mengarah ke Asset::class karena data drone Anda disimpan di dalam tabel assets dengan category='DRONE'
     */
    public function drone(): BelongsTo 
    { 
        return $this->belongsTo(Asset::class, 'drone_id'); 
    }

    /**
     * Relasi Kebalikan: Jika asset ini adalah DRONE, ini akan mengambil semua komponen (SPAREPART) yang menempel padanya
     * Berfungsi penuh untuk merender dan menyimpan multi-select Array di Filament Resource Anda
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
}