<?php

namespace App\Filament\Resources\FlightLogResource\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\FlightLocation;
use App\Models\Company;

class EditFlightLog extends EditRecord
{
    protected static string $resource = FlightLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // LENGKAPI LOGIKA INI: Memproses teks jembatan saat proses edit data
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['location_name_bridge'])) {
            $location = \App\Models\FlightLocation::firstOrCreate([
                'location_name' => $data['location_name_bridge'],
            ], [
                'company_id' => \App\Models\Company::first()?->id ?? 1,
            ]);
            
            $data['flight_location_id'] = $location->id;
        }

        // FIX MUTLAK: Hapus field dari array data agar tidak ikut ditembak ke query SQL UPDATE
        unset($data['location_name_bridge']);

        return $data;
    }
}