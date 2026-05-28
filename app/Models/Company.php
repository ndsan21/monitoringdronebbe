<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini

class Company extends Model
{
    // Tambahkan 'subscription_group_id' ke fillable agar bisa disimpan
    protected $fillable = ['name', 'logo_path', 'subscription_group_id'];

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['super_admin', 'admin']);
    }

    /**
     * Relasi ke Grup Langganan (Setiap PT punya 1 Grup Induk)
     */
    public function subscriptionGroup(): BelongsTo
    {
        return $this->belongsTo(SubscriptionGroup::class, 'subscription_group_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Relasi ke tabel Assets (Drone dan Sparepart milik PT ini)
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'owner_company_id');
    }
}