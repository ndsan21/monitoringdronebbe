<?php

namespace App\Filament\Resources\FlightLocationResource\Pages;

use App\Filament\Resources\FlightLocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFlightLocation extends CreateRecord
{
    protected static string $resource = FlightLocationResource::class;

    // ❌ Hilangkan tombol "Create & create another"
    protected static bool $canCreateAnother = false;

    // ↩️ Otomatis kembali ke halaman list awal setelah sukses menyimpan
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}