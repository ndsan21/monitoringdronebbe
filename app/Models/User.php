<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'full_name', 'employee_id', 'photo_path',
        'company_id', 'department_id', 'license_number', 'license_issued_by',
        'license_expiration_date', 'digital_signature', 'role', 'is_approved'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isPilot(): bool { return $this->role === 'pilot'; }
}