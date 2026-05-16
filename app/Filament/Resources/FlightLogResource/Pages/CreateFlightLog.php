<?php

namespace App\Filament\Resources\FlightLogResource\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\FlightLocation;
use App\Models\Company;

class CreateFlightLog extends CreateRecord
{
    protected static string $resource = FlightLogResource::class;

    // LENGKAPI LOGIKA INI: Memproses teks jembatan menjadi ID lokasi nyata sebelum disimpan
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['location_name_bridge'])) {
            $location = \App\Models\FlightLocation::firstOrCreate([
                'location_name' => $data['location_name_bridge'],
            ], [
                'company_id' => \App\Models\Company::first()?->id ?? 1,
            ]);
            
            $data['flight_location_id'] = $location->id;
        }

        // FIX MUTLAK: Hapus field dari array data agar tidak ikut ditembak ke query SQL INSERT
        unset($data['location_name_bridge']);

        return $data;
    }
}