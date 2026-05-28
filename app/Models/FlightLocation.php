<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightLocation extends Model
{
    use HasFactory;

    // Bersih dari company_id (Data universal lintas PT)
    protected $fillable = [
        'location_name', 
        'iup_number'
    ];

    // --- RELASI DATABASE ---

    /**
     * Relasi ke Flight Logs: Satu lokasi universal bisa dipakai oleh banyak log terbang
     */
    public function flightLogs(): HasMany
    {
        return $this->hasMany(FlightLog::class, 'flight_location_id');
    }

    /**
     * Relasi ke Damage Reports: Satu lokasi bisa menjadi tempat terjadinya beberapa insiden
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'incident_location_id');
    }
}