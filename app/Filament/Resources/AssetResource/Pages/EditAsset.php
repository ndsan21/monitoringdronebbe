<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\Drone;

class EditAsset extends EditRecord
{
    protected static string $resource = AssetResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['category'] === 'DRONE') {
            if (!empty($data['drone_id'])) {
                Drone::where('id', $data['drone_id'])->update([
                    'model' => $data['asset_name'],
                ]);
            } else {
                $drone = Drone::create([
                    'model' => $data['asset_name'], 
                ]);
                $data['drone_id'] = $drone->id;
            }
        } else {
            $data['drone_id'] = null; 
        }

        return $data;
    }
}