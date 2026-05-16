<?php

namespace App\Filament\Resources\DroneResource\Pages;

use App\Filament\Resources\DroneResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDrone extends CreateRecord
{
    protected static string $resource = DroneResource::class;
    // ❌ Hilangkan tombol "Create & create another"
    protected static bool $canCreateAnother = false;

    // ↩️ Otomatis kembali ke halaman list awal setelah sukses menyimpan
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    }
