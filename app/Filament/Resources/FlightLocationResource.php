<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightLocationResource\Pages;
use App\Models\FlightLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlightLocationResource extends Resource
{
    protected static ?string $model = FlightLocation::class;

    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Flight Locations';
    protected static ?string $pluralLabel = 'Flight Locations';
    protected static ?string $modelLabel = 'Flight Location';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Registered Area Information')
                ->schema([
                    Forms\Components\TextInput::make('location_name')
                        ->label('Location Name')
                        ->placeholder('e.g., Block A Mining Area, North Plantation')
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('iup_number')
                        ->label('IUP (Company License Number)')
                        ->placeholder('e.g., IUP-REG-2026-X'),
                ])->columns(2), // Layout otomatis membagi 2 rata kanan-kiri yang presisi
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('location_name')
                    ->label('Location Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('iup_number')
                    ->label('IUP Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            // UPGRADE: MENJADI DROP DOWN TITIK TIGA VERTIKAL
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->color('warning'),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlightLocations::route('/'),
            'create' => Pages\CreateFlightLocation::route('/create'),
            'edit' => Pages\EditFlightLocation::route('/{record}/edit'),
        ];
    }
}