<?php

namespace App\Filament\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * 🚀 HADANG & LEMPAR: Fungsi ini berjalan otomatis 
     * sebelum halaman dirender untuk mengecek role user.
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
     * Widget hanya akan dimuat jika user bukan pilot 
     * (atau jika kamu ingin admin tetap melihat dashboard ini).
     */
    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardHeaderWidget::class,
            \App\Filament\Widgets\DashboardStatsOverview::class,
            \App\Filament\Widgets\DroneHoursChart::class,
            \App\Filament\Widgets\PilotHoursChart::class,
            \App\Filament\Widgets\FlightDurationTrendChart::class,
            \App\Filament\Widgets\MissionPurposeChart::class,
            \App\Filament\Widgets\BatteryHealthChart::class,
            \App\Filament\Widgets\RecentFlightsTable::class,
        ];
    }
}