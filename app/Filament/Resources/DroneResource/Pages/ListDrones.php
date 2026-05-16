<?php

namespace App\Filament\Resources\DroneResource\Pages;

use App\Filament\Resources\DroneResource;
use Filament\Resources\Pages\ListRecords;

class ListDrones extends ListRecords
{
    // Tambahkan kata kunci 'static' di sini agar sesuai dengan blueprint Filament
    protected static string $resource = DroneResource::class;

    /**
     * OVERRIDE FUNGSI: Mengosongkan tombol bawaan page header
     * Ini akan melenyapkan tombol "New drone" yang paling atas sendiri.
     */
    protected function getHeaderActions(): array
    {
        return [
            // Kosongkan array ini agar tombol duplikat di atas hilang
        ];
    }
}