<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'full_name', 'employee_id', 'photo_path',
        'company_id', 'department_id', 'license_number', 'license_issued_by',
        'license_expiration_date', 'digital_signature', 'role', 'is_approved'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * BOOT ENGINE: Logika otomatis saat user melakukan registrasi mandiri
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Jika pendaftaran via registrasi mandiri (form register Filament hanya mengirim 'name')
            if (empty($user->full_name) && !empty($user->name)) {
                $user->full_name = $user->name;
            }
            
            // Set default akses jika mendaftar sendiri
            if (empty($user->role)) {
                $user->role = 'pilot'; 
            }
            
            // User baru mendaftar mandiri otomatis statusnya FALSE (menunggu approval)
            if (!isset($user->is_approved)) {
                $user->is_approved = false;
            }
        });
    }

    /**
     * GERBANG KEAMANAN DASHBOARD
     * Mencegah akun yang belum disetujui oleh admin untuk masuk
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_approved;
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isPilot(): bool { return $this->role === 'pilot'; }
}