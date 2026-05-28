<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name'];

    // --- RELASI DATABASE ---

    /**
     * Relasi ke tabel Users (Satu departemen bisa menaungi banyak karyawan)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke tabel Assets (Satu departemen bisa dihubungkan ke banyak aset/drone)
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'department_id');
    }
}