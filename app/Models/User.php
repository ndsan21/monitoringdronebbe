<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants; 
use Filament\Panel;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Collection; 

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'full_name', 'employee_id', 'photo_path',
        'company_id', 'department_id', 'license_number', 'license_issued_by',
        'license_expiration_date', 'digital_signature', 'role', 'is_approved',
        'subscription_group_id'
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_approved' => 'boolean'];

    /**
     * BOOT ENGINE: Jaring pengaman otomatis di tingkat database
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Jika name kosong tapi full_name ada, salin nilainya
            if (empty($user->name) && !empty($user->full_name)) {
                $user->name = $user->full_name;
            }
            // Kebalikannya, jika full_name kosong tapi name ada
            if (empty($user->full_name) && !empty($user->name)) {
                $user->full_name = $user->name;
            }
            
            if (empty($user->role)) {
                $user->role = 'pilot'; 
            }
            
            if (!isset($user->is_approved)) {
                $user->is_approved = false;
            }
        });

        static::updating(function ($user) {
            if (empty($user->name) && !empty($user->full_name)) {
                $user->name = $user->full_name;
            }
        });
    }

    // --- TENANT MANAGEMENT ---
    public function getTenants(Panel $panel): array|Collection
    {
        return $this->isSuperAdmin() ? SubscriptionGroup::all() : collect([$this->subscriptionGroup])->filter();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->isSuperAdmin() || $this->subscription_group_id === $tenant->id;
    }

    // --- RELATIONS ---
    public function subscriptionGroup(): BelongsTo { return $this->belongsTo(SubscriptionGroup::class, 'subscription_group_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class, 'company_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }

    // --- RELASI TRANS-OPERASIONAL ---
    public function flightLogs(): HasMany { return $this->hasMany(FlightLog::class, 'pilot_id'); }
    public function coPilotFlightLogs(): HasMany { return $this->hasMany(FlightLog::class, 'co_pilot_id'); }
    public function damageReports(): HasMany { return $this->hasMany(DamageReport::class, 'reported_by_id'); }
    public function maintenanceLogs(): HasMany { return $this->hasMany(MaintenanceLog::class, 'technician_id'); }

    // --- ACCESS CONTROL ---
    public function canAccessPanel(Panel $panel): bool
    {
        $id = $panel->getId();
        if ($id === 'super-admin') return $this->isSuperAdmin();
        if ($id === 'admin') return ($this->isAdmin() || $this->isSuperAdmin()) && $this->is_approved;
        return $this->isPilot() && $this->is_approved;
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isPilot(): bool { return $this->role === 'pilot'; }
}