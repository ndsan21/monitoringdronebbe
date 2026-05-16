<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightLog extends Model
{
    protected $fillable = [
        'drone_id', 'pilot_id', 'co_pilot_id', 'requester_id', 'authorized_by_id',
        'purpose', 'flight_mode', 'flight_area_name', 'flight_location_id', 'date',
        'takeoff_time', 'landing_time', 'duration', 'takeoff_lat', 'takeoff_lng',
        'result', 'note', 'sky_condition', 'wind_speed_kmh', 'wind_direction',
        'humidity_percent', 'temperature_c', 'rain_prob', 'visibility_km',
        'hardware_checklist', 'system_function_checklist', 'environment_checklist',
        'safety_permit_checklist', 'flight_evidences'
    ];

    protected $casts = [
        'hardware_checklist' => 'array', 'system_function_checklist' => 'array',
        'environment_checklist' => 'array', 'safety_permit_checklist' => 'array',
        'flight_evidences' => 'array', 'date' => 'date'
    ];

    public function drone(): BelongsTo { return $this->belongsTo(Drone::class); }
    public function pilot(): BelongsTo { return $this->belongsTo(User::class, 'pilot_id'); }
    public function coPilot(): BelongsTo { return $this->belongsTo(User::class, 'co_pilot_id'); }
    public function flightLocation(): BelongsTo { return $this->belongsTo(FlightLocation::class); }
}