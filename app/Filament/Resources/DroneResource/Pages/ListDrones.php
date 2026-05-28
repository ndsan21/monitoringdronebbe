<?php

namespace App\Filament\Resources\DroneResource\Pages;

use App\Filament\Resources\DroneResource;
use App\Filament\Resources\DroneResource\Widgets\DroneStatsOverview; // ⚡ Import Widget Baru
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrones extends ListRecords
{
    protected static string $resource = DroneResource::class;

    // 1. Kosongkan ini agar tombol bawaan di kanan atas hilang
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make() // ◄--- INI DIA YANG KETINGGALAN JIR!
                ->label('New Drone'),    // Label tombol di pojok kanan atas
        ];
    }
    // 2. Munculkan widget statistik di atas tabel
    protected function getHeaderWidgets(): array
    {
        return [
            DroneStatsOverview::class,
        ];
    }
}