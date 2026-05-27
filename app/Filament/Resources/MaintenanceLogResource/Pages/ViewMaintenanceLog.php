<?php

namespace App\Filament\Resources\MaintenanceLogResource\Pages;

use App\Filament\Resources\MaintenanceLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenanceLog extends ViewRecord
{
    protected static string $resource = MaintenanceLogResource::class;

    // Kita kosongkan saja tombol bagian atas Master
    protected function getHeaderActions(): array
    {
        return [];
    }
}