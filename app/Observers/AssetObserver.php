<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\Drone;

class AssetObserver
{
    /**
     * Berjalan otomatis saat data Asset BARU dibuat
     */
    public function created(Asset $asset): void
    {
        if (strtoupper($asset->category) === 'DRONE') {
            // Otomatis buat data di tabel Drones
            $drone = Drone::create([
                'brand' => 'Auto-Sync (Update Manual)', // Nilai default karena di form asset tidak ada brand
                'model' => $asset->asset_name,         // Nama asset dijadikan model drone
                'type'  => 'multirotor',               // Nilai default
            ]);

            // Diam-diam update kolom drone_id di tabel asset agar saling terhubung
            // (updateQuietly digunakan agar tidak memicu observer "updated" dan terjadi looping/error)
            $asset->updateQuietly(['drone_id' => $drone->id]);
        }
    }

    /**
     * Berjalan otomatis saat data Asset DIUBAH
     */
    public function updated(Asset $asset): void
    {
        if (strtoupper($asset->category) === 'DRONE' && $asset->drone_id) {
            $drone = Drone::find($asset->drone_id);
            if ($drone) {
                $drone->update([
                    'model' => $asset->asset_name,
                ]);
            }
        }
    }

    /**
     * Berjalan otomatis saat data Asset DIHAPUS
     */
    public function deleted(Asset $asset): void
    {
        if (strtoupper($asset->category) === 'DRONE' && $asset->drone_id) {
            // Jika asset dihapus, hapus juga data di master drone agar rapi
            Drone::where('id', $asset->drone_id)->delete();
        }
    }
}