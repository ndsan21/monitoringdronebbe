<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;
// ❌ Hilangkan tombol "Create & create another"
    protected static bool $canCreateAnother = false;

    // ↩️ Otomatis kembali ke halaman list awal setelah sukses menyimpan
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    }
