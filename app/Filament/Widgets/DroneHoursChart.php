<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\ChartWidget;

class DroneHoursChart extends ChartWidget
{
    protected static ?string $heading = 'Total Flight Hours per Drone';
    protected static ?int $sort = 2;
    
    // Kunci tinggi maksimal container chart
    protected static ?string $maxHeight = '260px'; 

    // Konfigurasi Grid Responsif
    protected int | string | array $columnSpan = [
        'default' => 1,  // Di HP memakan 1 kolom penuh (100% lebar)
        'lg' => 4,       // Di Desktop memakan 4 dari 12 kolom (sepertiga lebar)
    ];

    // ⚡ PAKSA CHART.JS MENGIKUTI TINGGI CONTAINER MAKSIMAL
    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }

    protected function getData(): array
    {
        // Hanya ambil aset berkategori DRONE
        $drones = Asset::where('category', 'DRONE')
            ->withSum('flightLogs as total_seconds', 'duration')
            ->get();

        $labels = [];
        $data = [];

        foreach ($drones as $drone) {
            $labels[] = $drone->asset_name;
            $data[] = round(($drone->total_seconds ?? 0) / 3600, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hours Flown',
                    'data' => $data,
                    'backgroundColor' => '#8b5cf6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}