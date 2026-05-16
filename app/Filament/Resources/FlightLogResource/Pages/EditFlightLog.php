<?php

namespace App\Filament\Resources\FlightLogResource\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlightLog extends EditRecord
{
    protected static string $resource = FlightLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
