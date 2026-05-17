<?php

namespace App\Filament\Widgets;

use App\Models\FlightLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FlightDurationTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Flight Duration Trend (Minutes)';
    protected static ?int $sort = 4;
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
        // 1. Ambil data log 30 hari terakhir dari database
        $rawLogs = FlightLog::query()
            ->selectRaw('DATE(date) as flight_date, SUM(duration) as total_duration')
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('flight_date')
            ->orderBy('flight_date', 'ASC')
            ->pluck('total_duration', 'flight_date')
            ->toArray();

        $labels = [];
        $data = [];

        // 2. Loop manual untuk mengisi hari yang kosong dengan angka 0 agar grafik tidak patah
        for ($i = 30; $i >= 0; $i--) {
            $dateString = now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($dateString)->format('d M'); // Contoh: 17 May
            
            // Jika ada penerbangan, konversi detik ke menit. Jika tidak, set 0.
            $seconds = $rawLogs[$dateString] ?? 0;
            $data[] = round($seconds / 60, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Flight Duration (Minutes)',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}