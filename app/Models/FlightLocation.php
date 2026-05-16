<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightLocation extends Model
{
    protected $fillable = ['location_name', 'iup_number', 'company_id'];
}
