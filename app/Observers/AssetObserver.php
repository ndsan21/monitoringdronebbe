<?php

namespace App\Observers;

use App\Models\Asset;

class AssetObserver
{
    /**
     * Berjalan otomatis saat data Asset BARU dibuat
     */
    public function created(Asset $asset): void
    {
        // Logika lama Drone::create() di sini SUDAH DIHAPUS 
        // karena drone sekarang adalah asset itu sendiri.
    }
}