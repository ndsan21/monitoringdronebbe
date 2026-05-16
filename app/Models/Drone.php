<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Drone extends Model
{
    protected $fillable = ['model'];

    public function asset(): HasOne
    {
        return $this->hasOne(Asset::class, 'drone_id');
    }
}