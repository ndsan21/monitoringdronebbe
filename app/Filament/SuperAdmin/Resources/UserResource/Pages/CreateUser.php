<?php

namespace App\Filament\SuperAdmin\Resources; // Sesuaikan namespace folder Anda

namespace App\Filament\SuperAdmin\Resources\UserResource\Pages;

use App\Filament\SuperAdmin\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Otomatis amankan password sebelum masuk DB
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $data;
    }
}