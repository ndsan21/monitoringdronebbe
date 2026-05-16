<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    // KUNCI 1: Menghilangkan total tombol "Create & create another" dari form Sparepart
    protected static bool $canCreateAnother = false;

    // KUNCI 2: Setelah user klik "Create", otomatis lompat kembali ke halaman awal list tabel
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}