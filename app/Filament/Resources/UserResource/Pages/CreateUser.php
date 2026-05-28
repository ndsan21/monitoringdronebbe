<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * KEAMANAN 2: Menyuntikkan company_id dan subscription_group_id secara otomatis 
     * berdasarkan akun Admin yang sedang login saat data disimpan.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentUser = auth()->user();

        $data['company_id'] = $currentUser->company_id;
        $data['subscription_group_id'] = $currentUser->subscription_group_id;
        $data['name'] = $data['full_name']; // Menghindari error 1364 field name kosong
        $data['is_approved'] = true; // Langsung aktifkan jika dibuat oleh admin internal

        return $data;
    }
}