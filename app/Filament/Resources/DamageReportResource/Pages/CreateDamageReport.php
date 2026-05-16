<?php

namespace App\Filament\Resources\DamageReportResource\Pages;

use App\Filament\Resources\DamageReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDamageReport extends CreateRecord
{
    protected static string $resource = DamageReportResource::class;
    // ❌ Hilangkan tombol "Create & create another"
    protected static bool $canCreateAnother = false;

    // ↩️ Otomatis kembali ke halaman list awal setelah sukses menyimpan
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }}
