<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Drone;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Otomatis membuat baris data di tabel Drone dengan menyalin asset_name ke kolom model
        if ($data['category'] === 'DRONE') {
            $drone = Drone::create([
                'model' => $data['asset_name'],
            ]);
            
            // Simpan ID drone yang baru saja tercipta ke dalam foreign key asset
            $data['drone_id'] = $drone->id;
        }

        return $data;
    }
}