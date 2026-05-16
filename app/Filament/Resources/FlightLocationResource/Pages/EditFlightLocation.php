<?php

namespace App\Filament\Resources\FlightLocationResource\Pages;

use App\Filament\Resources\FlightLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlightLocation extends EditRecord
{
    protected static string $resource = FlightLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
