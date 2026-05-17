<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\FlightLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; 
    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 12,
    ];

    protected function getStats(): array
    {
        $crewCount = \App\Models\User::where('role', 'pilot')->count();
        $incidentCount = \App\Models\Asset::whereIn('status', ['on_repaired', 'out_of_service'])->count();

        return [
            // ================= TOP ROW (3 CARDS) =================
            Stat::make('Number of Flights', \App\Models\FlightLog::count() . ' Missions')
                ->description('All flight logs are recorded')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('success')
                ->chart([10, 14, 12, 18, 15, 22, 25])
                ->url(route('filament.admin.resources.flight-logs.index'))
                ->extraAttributes([
                    'class' => 'border-b-4 border-emerald-500 dark:border-emerald-500 rounded-b-xl shadow-lg'
                ]),

            Stat::make('Total Flight Hours', round(\App\Models\FlightLog::sum('duration') / 3600, 1) . ' Hours')
                ->description('Total cumulative duration')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart([5, 12, 15, 14, 20, 24, 30])
                ->url(route('filament.admin.resources.flight-logs.index'))
                ->extraAttributes([
                    'class' => 'border-b-4 border-blue-500 dark:border-blue-500 rounded-b-xl shadow-lg'
                ]),

            Stat::make('Drone Status', \App\Models\Asset::where('category', 'DRONE')->where('status', 'ready')->count() . ' Units Ready')
                ->description('Active fleet ready to take off') // ⚡ Translated
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart([3, 3, 4, 4, 3, 4, 4])
                ->url(route('filament.admin.resources.drones.index'))
                ->extraAttributes([
                    'class' => 'border-b-4 border-teal-500 dark:border-teal-400 rounded-b-xl shadow-lg'
                ]),

            // ================= BOTTOM ROW (3 CARDS) =================
            Stat::make('Battery Ready', \App\Models\Asset::where('sparepart_type', 'Battery')->where('status', 'ready')->count() . ' Pcs')
                ->description('Battery stock secure in storage') // ⚡ Translated
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning')
                ->chart([8, 6, 7, 9, 8, 10, 12])
                ->url(route('filament.admin.resources.inventory-assets.index', [
                    'tableFilters[category][value]' => 'SPAREPART',
                    'tableFilters[status][values][0]' => 'ready',
                ]))
                ->extraAttributes([
                    'class' => 'border-b-4 border-amber-500 dark:border-amber-500 rounded-b-xl shadow-lg'
                ]),

            Stat::make('Active Incidents', $incidentCount . ' Cases')
                ->description($incidentCount > 0 ? 'Requires immediate maintenance' : 'No issues reported') // ⚡ Translated
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($incidentCount > 0 ? 'danger' : 'gray')
                ->chart([1, 4, 2, 5, 3, 4, 2])
                ->url(route('filament.admin.resources.inventory-assets.index', [
                    'tableFilters[status][values][0]' => 'on_repaired',
                    'tableFilters[status][values][1]' => 'out_of_service',
                ]))
                ->extraAttributes([
                    'class' => 'border-b-4 border-rose-600 dark:border-rose-600 rounded-b-xl shadow-lg'
                ]),

            Stat::make('Active Pilots / Crew', $crewCount . ' Personnel')
                ->description('Certified drone pilots in system')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart([2, 4, 4, 5, 5, 6, 6])
                ->url(route('filament.admin.resources.users.index'))
                ->extraAttributes([
                    'class' => 'border-b-4 border-emerald-400 dark:border-emerald-400 rounded-b-xl shadow-lg'
                ]),
        ];
    }
    }