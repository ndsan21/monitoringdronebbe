<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getWidgets(): array
    {
        return [];
    }

    // ⚡ KUNCI UTAMA RESPONSIVITAS TINGKAT TINGGI
    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,  // Di HP/Mobile, layout otomatis menumpuk 1 kolom ke bawah (rapi)
            'lg' => 12,      // Di Desktop/Layar Lebar, aktifkan sistem grid 12 kolom
        ];
    }
}