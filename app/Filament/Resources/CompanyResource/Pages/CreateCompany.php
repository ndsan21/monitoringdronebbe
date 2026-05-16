<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    // ❌ Hilangkan tombol "Create & create another"
    protected static bool $canCreateAnother = false;

    // ↩️ Otomatis kembali ke halaman list awal setelah sukses menyimpan
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}