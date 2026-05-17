<?php

namespace App\Filament\Widgets;

use App\Models\FlightLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentFlightsTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Flight Logs';
    
    protected static ?int $sort = 7; // Paling bawah setelah semua chart selesai
    
    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 12,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(FlightLog::query()->latest()->limit(5)) // Hanya ambil 5 log penerbangan terbaru
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->label('Date'),
                Tables\Columns\TextColumn::make('pilot.full_name')->label('Pilot'),
                Tables\Columns\TextColumn::make('drone.asset_name')->label('Drone'),
                Tables\Columns\TextColumn::make('flightLocation.location_name')->label('Location')->default('-'),
                Tables\Columns\TextColumn::make('purpose')->label('Purpose')->badge(),
                Tables\Columns\TextColumn::make('result')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'safe_to_fly',
                        'warning' => 'postpone',
                        'danger' => 'cancel'
                    ]),
            ]);
    }
}