<?php

namespace App\Filament\Resources\MaintenanceLogResource\Pages;

use App\Filament\Resources\MaintenanceLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceLog extends CreateRecord
{
    protected static string $resource = MaintenanceLogResource::class;

    // ❌ Hilangkan total tombol Create Another agar user langsung terlempar ke depan
    protected static bool $canCreateAnother = false;

    // ↩️ Setelah klik Create, langsung putar balik halamannya ke tabel index utama
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}