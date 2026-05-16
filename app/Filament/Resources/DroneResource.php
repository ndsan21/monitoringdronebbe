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
                // 1. Kolom NO (Otomatis increment)
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                // 2. Drone Name (Dari kolom model di tabel drones)
                Tables\Columns\TextColumn::make('model')
                    ->label('Drone Name')
                    ->searchable()
                    ->sortable(),

                // 3. Unit Code (Mengambil asset_id dari tabel assets)
                Tables\Columns\TextColumn::make('asset.asset_id')
                    ->label('Unit Code')
                    ->searchable()
                    ->sortable(),

                // 4. Type (Mengambil kategori dari tabel assets, pasti DRONE)
                Tables\Columns\TextColumn::make('asset.category')
                    ->label('Type'),

                // 5. Owner (Mengambil nama PT dari relasi company di dalam asset)
                Tables\Columns\TextColumn::make('asset.company.name')
                    ->label('Owner')
                    ->searchable(),

                // 6. Status Badge (Sinkron dari status aset)
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
            // Eager loading berlapis untuk mencegah query lambat (N+1 Issues)
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['asset.company']))
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Drone $record): string => $record->asset ? AssetResource::getUrl('edit', ['record' => $record->asset->id]) : '#')
                    ->disabled(fn (Drone $record): bool => !$record->asset),
            ])
            ->headerActions([
                Action::make('create_drone_via_asset')
                    ->label('New drone')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->url(fn (): string => AssetResource::getUrl('create')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrones::route('/'),
        ];
    }
}