<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightLocation extends Model
{
    protected $fillable = ['location_name', 'iup_number', 'company_id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}