<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    // FIX MUTLAK: Fungsi mutateFormDataBeforeCreate yang menembak ke tabel drones lama dihapus,
    // karena sekarang entitas Drone tersimpan langsung sebagai row utama di tabel assets.
}