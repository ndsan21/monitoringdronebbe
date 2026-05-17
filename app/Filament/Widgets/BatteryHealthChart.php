<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class BatteryHealthChart extends ChartWidget
{
    protected static ?string $heading = 'Battery Health Status';
    protected static ?int $sort = 6;
    protected static ?string $maxHeight = '300px'; 

    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 6,
    ];

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }

    // ⚡ INI DIA TEMPAT KODE MASTER BERADA
    protected function getData(): array
    {
        $batteries = \App\Models\Asset::where('category', 'SPAREPART')
            ->where('sparepart_type', 'LIKE', '%Battery%')
            ->get();

        $healthy = 0; $warning = 0; $replace = 0;

        foreach ($batteries as $batt) {
            // Hitung akumulasi detik terbang baterai ini dari log penerbangan
            $secondsFlown = \App\Models\FlightLog::where('battery_serial_id', $batt->serial_number)->sum('duration');
            $hoursFlown = $secondsFlown / 3600;

            if ($hoursFlown < 300) {
                $healthy++;
            } elseif ($hoursFlown >= 300 && $hoursFlown <= 500) {
                $warning++;
            } else {
                $replace++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Battery Health Count',
                    'data' => [$healthy, $warning, $replace],
                    'backgroundColor' => ['#10b981', '#f59e0b', '#ef4444'], // Hijau, Kuning, Merah
                ],
            ],
            'labels' => ['Healthy (<300h)', 'Warning (300h-500h)', 'Replace (>500h)'],
        ];
    }

    // Jenis Chart (Doughnut / Donat)
    protected function getType(): string
    {
        return 'doughnut'; 
    }
}