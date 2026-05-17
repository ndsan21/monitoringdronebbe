<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Filament\Resources\AssetResource\Widgets\AssetStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    // 1. KOSONGKAN Header Actions (Ini menghilangkan tombol di pojok kanan atas)
    protected function getHeaderActions(): array
    {
        return [];
    }

    // 2. Tampilkan Widget Overview di Atas
    protected function getHeaderWidgets(): array
    {
        return [
            AssetStatsOverview::class,
        ];
    }
}