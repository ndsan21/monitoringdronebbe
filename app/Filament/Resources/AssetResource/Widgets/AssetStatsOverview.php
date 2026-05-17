<?php

namespace App\Filament\Resources\AssetResource\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $droneReady  = Asset::where('category', 'DRONE')->where('status', 'ready')->count();
        $droneInUse  = Asset::where('category', 'DRONE')->where('status', 'in_use')->count();
        $droneRepair = Asset::where('category', 'DRONE')->where('status', 'on_repaired')->count();
        $droneBroken = Asset::where('category', 'DRONE')->where('status', 'out_of_service')->count();
        $totalDrone  = Asset::where('category', 'DRONE')->count();

        $partReady  = Asset::where('category', 'SPAREPART')->where('status', 'ready')->count();
        $partInUse  = Asset::where('category', 'SPAREPART')->where('status', 'in_use')->count();
        $partRepair = Asset::where('category', 'SPAREPART')->where('status', 'on_repaired')->count();
        $partBroken = Asset::where('category', 'SPAREPART')->where('status', 'out_of_service')->count();
        $totalPart  = Asset::where('category', 'SPAREPART')->count();

        return [
            Stat::make('Total Drone Units', $totalDrone . ' Units')
                ->description("🟢 {$droneReady} Ready | 🔵 {$droneInUse} In Use")
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary')
                ->url(route('filament.admin.resources.inventory-assets.index', ['tableFilters[category][value]' => 'DRONE'])),

            Stat::make('Drones Flying / In Use', $droneInUse . ' Units')
                ->description('Active drone operations in field')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('info')
                ->url(route('filament.admin.resources.inventory-assets.index', ['tableFilters[category][value]' => 'DRONE', 'tableFilters[status][values][0]' => 'in_use'])),

            Stat::make('Drone Under Maintenance', ($droneRepair + $droneBroken) . ' Units')
                ->description("🟡 {$droneRepair} Repair | 🔴 {$droneBroken} Broken")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(($droneRepair + $droneBroken) > 0 ? 'danger' : 'gray')
                ->url(route('filament.admin.resources.inventory-assets.index', ['tableFilters[category][value]' => 'DRONE', 'tableFilters[status][values][0]' => 'on_repaired', 'tableFilters[status][values][1]' => 'out_of_service'])),

            Stat::make('Total Spareparts', $totalPart . ' Pcs')
                ->description("📦 Total inventory items in warehouse")
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->url(route('filament.admin.resources.inventory-assets.index', ['tableFilters[category][value]' => 'SPAREPART'])),

            Stat::make('Spareparts Ready Stock', $partReady . ' Pcs')
                ->description("🟢 Available items in warehouse storage")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(route('filament.admin.resources.inventory-assets.index', ['tableFilters[category][value]' => 'SPAREPART', 'tableFilters[status][values][0]' => 'ready'])),

            Stat::make('Sparepart Maintenance', ($partRepair + $partBroken) . ' Pcs')
                ->description("🟡 {$partRepair} Repair | 🔴 {$partBroken} Broken")
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color(($partRepair + $partBroken) > 0 ? 'danger' : 'gray')
                ->url(route('filament.admin.resources.inventory-assets.index', ['tableFilters[category][value]' => 'SPAREPART', 'tableFilters[status][values][0]' => 'on_repaired', 'tableFilters[status][values][1]' => 'out_of_service'])),
        ];
    }
}