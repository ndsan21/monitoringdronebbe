<?php

namespace App\Filament\Resources\FlightLogResource\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFlightLog extends CreateRecord
{
    protected static string $resource = FlightLogResource::class;
}
