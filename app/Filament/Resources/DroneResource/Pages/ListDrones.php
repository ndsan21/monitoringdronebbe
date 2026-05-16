<?php

namespace App\Filament\Resources\DroneResource\Pages;

use App\Filament\Resources\DroneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrones extends ListRecords
{
    protected static string $resource = DroneResource::class;

    /**
     * Tombol "New Drone" di kanan atas halaman tabel list drone
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Drone')
                // ❌ Eksekusi mati tombol "Create & create another" di dalam modal!
                ->createAnother(false),
        ];
    }
}