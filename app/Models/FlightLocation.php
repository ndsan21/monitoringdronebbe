<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightLocation extends Model
{
    use HasFactory;

    // Bersih dari company_id
    protected $fillable = [
        'location_name', 
        'iup_number'
    ];

    /**
     * Relasi ke FlightLog: Satu lokasi universal bisa dipakai oleh banyak log terbang
     */
    public function flightLogs(): HasMany
    {
        return $this->hasMany(FlightLog::class, 'flight_location_id');
    }
}