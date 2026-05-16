<?php

namespace App\Filament\Resources\DroneResource\Pages;

use App\Filament\Resources\DroneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrones extends ListRecords
{
    protected static string $resource = DroneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
