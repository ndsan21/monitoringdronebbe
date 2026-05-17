<?php

namespace App\Filament\Widgets;

use App\Models\FlightLog;
use Filament\Widgets\ChartWidget;

class MissionPurposeChart extends ChartWidget
{
    protected static ?string $heading = 'Mission Purpose Distribution';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '300px'; 

    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 6, // Di Desktop membelah layar 50/50 (6 dari 12 kolom)
    ];

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }

    protected function getData(): array
    {
        // Hitung total log berdasarkan kolom purpose
        $logs = FlightLog::query()
            ->selectRaw('purpose, count(*) as total')
            ->groupBy('purpose')
            ->pluck('total', 'purpose')
            ->toArray();

        // Mapping label agar lebih rapi saat dibaca di chart
        $labels = array_map(function($key) {
            return match($key) {
                'patrol' => 'Update Pekerjaan / Patroli',
                'documentation' => 'Dokumentasi Acara',
                'mapping' => 'Orthophoto / Pemetaan',
                default => ucfirst($key)
            };
        }, array_keys($logs));

        return [
            'datasets' => [
                [
                    'label' => 'Missions Count',
                    'data' => array_values($logs),
                    'backgroundColor' => [
                        '#0ea5e9', // Blue Sky
                        '#10b981', // Emerald Green
                        '#f59e0b', // Amber Orange
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}