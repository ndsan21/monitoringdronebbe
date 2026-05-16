<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\Drone;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        // Jika asset yang dibuat berkategori DRONE, otomatis buatkan datanya di tabel Drones
        if (strtoupper($asset->category) === 'DRONE') {
            Drone::create([
                'drone_name' => $asset->asset_name,
                'serial_number' => $asset->serial_number,
                'status' => 'ready', // Status awal default bawaan drone baru
            ]);
        }
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        // Jika data asset diubah, pastikan data drone yang terhubung ikut ter-update namanya
        if (strtoupper($asset->category) === 'DRONE') {
            $drone = Drone::where('serial_number', $asset->serial_number)->first();
            if ($drone) {
                $drone->update([
                    'drone_name' => $asset->asset_name,
                ]);
            }
        }
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        // Jika asset dihapus, hapus juga data drone-nya agar tidak menjadi data sampah
        if (strtoupper($asset->category) === 'DRONE') {
            Drone::where('serial_number', $asset->serial_number)->delete();
        }
    }
}