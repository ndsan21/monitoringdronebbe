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
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Master Data Aviation';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Registered Area Information')->schema([
                Forms\Components\TextInput::make('location_name')
                    ->label('Location Name')
                    ->required(),
                Forms\Components\TextInput::make('iup_number')
                    ->label('IUP (Company License Number)')
                    ->placeholder('e.g., IUP-REG-2026-X'),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Responsible Company')
                    ->searchable()
                    ->preload()
                    ->required(),
            ])->columns(3)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('location_name')->label('Location Name')->searchable(),
            Tables\Columns\TextColumn::make('iup_number')->label('IUP Number'),
            Tables\Columns\TextColumn::make('company.name')->label('Company Owner'),
        ])
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
            'index' => Pages\ListFlightLocations::route('/'),
            'create' => Pages\CreateFlightLocation::route('/create'),
            'edit' => Pages\EditFlightLocation::route('/{record}/edit'),
        ];
    }
}