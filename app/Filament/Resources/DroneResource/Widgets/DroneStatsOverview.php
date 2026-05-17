<?php

namespace App\Filament\Resources\DroneResource\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DroneStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Kunci query hanya untuk data ber-category DRONE
        $droneReady  = Asset::where('category', 'DRONE')->where('status', 'ready')->count();
        $droneInUse  = Asset::where('category', 'DRONE')->where('status', 'in_use')->count();
        $droneRepair = Asset::where('category', 'DRONE')->where('status', 'on_repaired')->count();
        $droneBroken = Asset::where('category', 'DRONE')->where('status', 'out_of_service')->count();
        $totalDrone  = Asset::where('category', 'DRONE')->count();

        return [
            // Kotak 1: Total Drone Units
            Stat::make('Total Drone Units', $totalDrone . ' Units')
                ->description('Registered units in fleet')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary')
                ->url(route('filament.admin.resources.drones.index')),

            // Kotak 2: Operational & Flying (Ready + In Use)
            Stat::make('Operational Drones', ($droneReady + $droneInUse) . ' Units')
                ->description("🟢 {$droneReady} Ready | 🔵 {$droneInUse} In Use")
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('success')
                ->url(route('filament.admin.resources.drones.index', [
                    'tableFilters[status][values][0]' => 'ready',
                    'tableFilters[status][values][1]' => 'in_use',
                ])),

            // Kotak 3: Under Maintenance / Broken
            Stat::make('Drones Under Maintenance', ($droneRepair + $droneBroken) . ' Units')
                ->description("🟡 {$droneRepair} Repair | 🔴 {$droneBroken} Broken")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(($droneRepair + $droneBroken) > 0 ? 'danger' : 'gray')
                ->url(route('filament.admin.resources.drones.index', [
                    'tableFilters[status][values][0]' => 'on_repaired',
                    'tableFilters[status][values][1]' => 'out_of_service',
                ])),
        ];
    }
}