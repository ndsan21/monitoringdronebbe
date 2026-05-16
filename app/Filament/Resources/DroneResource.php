<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DroneResource\Pages;
use App\Models\Drone;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class DroneResource extends Resource
{
    protected static ?string $model = Drone::class;
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Drones';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Drone Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('asset.asset_id')
                    ->label('Unit Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('asset.category')
                    ->label('Type'),

                Tables\Columns\TextColumn::make('asset.company.name')
                    ->label('Owner')
                    ->searchable(),

                Tables\Columns\TextColumn::make('asset.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ready' => 'success',
                        'in_use' => 'info',
                        'on_repaired' => 'warning',
                        'out_of_service' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['asset.company']))
            ->actions([
            // 1. ACTION TERSEMBUNYI (Tetap biarkan untuk handle klik baris)
            Tables\Actions\ViewAction::make('clickToView')
                ->modalActions([
                    Tables\Actions\EditAction::make()
                        ->button()
                        ->color('warning'),
                ])
                ->extraAttributes(['class' => 'hidden']),

            // 2. MENU TITIK TIGA (Ubah di bagian sini)
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->color('info') // ◄--- KUNCI UTAMA: Membuat teks & ikon View di dalam dropdown berwarna BIRU
                    ->icon('heroicon-m-eye') // Menambahkan ikon mata agar semakin jelas
                    ->modalActions([
                        Tables\Actions\EditAction::make()
                            ->button()
                            ->color('warning'),
                    ]),
                
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                    
                Tables\Actions\DeleteAction::make(),
            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->color('gray'),
        ])
        
        ->recordUrl(null) 
        ->recordAction('clickToView'); 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrones::route('/'),
        ];
    }
}