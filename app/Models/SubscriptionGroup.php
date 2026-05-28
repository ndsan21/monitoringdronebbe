<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionGroup extends Model
{
    protected $fillable = ['group_name', 'package_type', 'logo_path'];

    /**
     * Relasi ke PT / Perusahaan (Satu grup bisa punya banyak PT)
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'subscription_group_id');
    }

    /**
     * Relasi ke User
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'subscription_group_id');
    }

    /**
     * Relasi ke Aset
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'subscription_group_id');
    }
}