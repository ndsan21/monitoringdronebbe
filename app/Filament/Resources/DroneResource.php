<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DroneResource\Pages;
use App\Models\Drone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DroneResource extends Resource
{
    protected static ?string $model = Drone::class;
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Master Data Aviation';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Drone Hardware Specifications')->schema([
                Forms\Components\TextInput::make('brand')
                    ->label('Brand / Manufacturer')
                    ->required()
                    ->placeholder('e.g., DJI, Quantum Systems'),
                Forms\Components\TextInput::make('model')
                    ->label('Model Name')
                    ->required()
                    ->placeholder('e.g., Matrice 350 RTK, Trinity F90+'),
                Forms\Components\Select::make('type')
                    ->label('Aviation System Type')
                    ->options([
                        'multirotor' => 'Multirotor',
                        'fixed_wing' => 'Fixed Wing / VTOL',
                        'helicopter' => 'Helicopter Drone'
                    ])->required(),
            ])->columns(3)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('brand')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('model')->searchable(),
            Tables\Columns\BadgeColumn::make('type')->colors([
                'primary' => 'multirotor',
                'warning' => 'fixed_wing',
                'success' => 'helicopter',
            ]),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrones::route('/'),
            'create' => Pages\CreateDrone::route('/create'),
            'edit' => Pages\EditDrone::route('/{record}/edit'),
        ];
    }
}