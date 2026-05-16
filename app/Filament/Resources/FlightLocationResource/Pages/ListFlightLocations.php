<?php

namespace App\Filament\Resources\FlightLocationResource\Pages;

use App\Filament\Resources\FlightLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlightLocations extends ListRecords
{
    protected static string $resource = FlightLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
