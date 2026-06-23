<?php

namespace App\Filament\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * 🚀 HADANG & LEMPAR: Mengecek role user sebelum halaman dimuat.
     */
    public function mount(): void
    {
        $user = auth()->user();

        // JIKA USER ADALAH PILOT: Langsung banting setir ke halaman Create Flight Log!
        if ($user && $user->role === 'pilot') {
            $this->redirect(FlightLogResource::getUrl('create'));
        }
    }

    /**
     * ⚡ KUNCI UTAMA RESPONSIVITAS TINGKAT TINGGI
     */
    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,  // Di HP/Mobile, layout otomatis menumpuk 1 kolom ke bawah (rapi)
            'lg' => 12,      // Di Desktop/Layar Lebar, aktifkan sistem grid 12 kolom
        ];
    }
    
    // 🔥 PERBAIKAN: 
    // Fungsi getWidgets() dan getHeaderWidgets() DIHAPUS TOTAL dari sini.
    // Filament akan otomatis menampilkan widget 1x saja dari AdminPanelProvider.
}