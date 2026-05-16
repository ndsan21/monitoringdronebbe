<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name'];

    /**
     * Relasi ke User: Satu departemen bisa digendong oleh banyak User/Karyawan
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke Asset: Satu departemen bisa memiliki banyak Asset/Drone/Sparepart
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'department_id');
    }
}