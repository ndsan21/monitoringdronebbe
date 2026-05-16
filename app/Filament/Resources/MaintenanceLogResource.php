<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceLogResource\Pages;
use App\Models\MaintenanceLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Log Operasional';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('General Information')->schema([
                Forms\Components\Select::make('asset_id')
                    ->relationship('asset', 'asset_name', fn($query) => $query->where('category', 'DRONE'))
                    ->label('Drone Unit')
                    ->required(),
                Forms\Components\Select::make('technician_id')->relationship('technician', 'full_name')->required(),
                Forms\Components\Select::make('maintenance_type')
                    ->options([
                        'hardware_inspection' => 'Hardware Inspection',
                        'software_update' => 'Software Update',
                        'full_maintenance' => 'Full Maintenance'
                    ])->live()->required(),
            ])->columns(3),

            Forms\Components\Section::make('Hardware Inspection List')
                ->visible(fn (Get $get) => in_array($get('maintenance_type'), ['hardware_inspection', 'full_maintenance']))
                ->schema([
                    Forms\Components\Repeater::make('hardwareItems')
                        ->relationship('hardwareItems')
                        ->schema([
                            Forms\Components\TextInput::make('component_name')->required(),
                            Forms\Components\Select::make('current_status')->options(['reported' => 'Reported', 'on_progress' => 'On Progress', 'resolved' => 'Resolved'])->required(),
                            Forms\Components\Select::make('condition')
                                ->options([
                                    'good' => 'Good (Ready)',
                                    'damaged_replace' => 'Damaged / Replace (On Repaired)',
                                    'out_of_service' => 'Out of Service'
                                ])->live()->required(),
                            Forms\Components\Select::make('replaced_with_sparepart_id')
                                ->label('Replace with Sparepart')
                                ->relationship('replacedSparepart', 'asset_name', fn($query) => $query->where('category', 'SPAREPART'))
                                ->visible(fn (Get $get) => $get('condition') === 'damaged_replace'),
                        ])->columns(4)
                ]),

            Forms\Components\Section::make('Analysis & Software Status')
                ->visible(fn (Get $get) => in_array($get('maintenance_type'), ['software_update', 'full_maintenance']))
                ->schema([
                    Forms\Components\TextInput::make('firmware_version_before')->label('Firmware Sebelum'),
                    Forms\Components\TextInput::make('firmware_version_after')->label('Firmware Sesudah'),
                    Forms\Components\Select::make('software_status')->options(['stable' => 'Stable', 'beta' => 'Beta', 'issues_detected' => 'Issues Detected'])
                ])->columns(3)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('asset.asset_name')->label('Drone'),
            Tables\Columns\TextColumn::make('maintenance_type'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceLogs::route('/'),
            'create' => Pages\CreateMaintenanceLog::route('/create'),
            'edit' => Pages\EditMaintenanceLog::route('/{record}/edit'),
        ];
    }
}