<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name', 'logo_path'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}