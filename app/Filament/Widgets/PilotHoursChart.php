<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;


class PilotHoursChart extends ChartWidget
{
    protected static ?string $heading = 'Flight Hours per Pilot';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '260px'; 

    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 4,
    ];

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }

    protected function getData(): array
    {
        // Mengambil user dengan role pilot beserta sum durasi log terbangnya
        $pilots = User::where('role', 'pilot')
            ->withSum('flightLogs as total_seconds', 'duration')
            ->get();

        $labels = [];
        $data = [];

        foreach ($pilots as $pilot) {
            $labels[] = $pilot->full_name ?? $pilot->name;
            // Konversi total detik menjadi jam dengan presisi 2 angka di belakang koma
            $data[] = round(($pilot->total_seconds ?? 0) / 3600, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Flight Hours',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6',
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