<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceLogResource\Pages;
use App\Models\MaintenanceLog;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;
    
    protected static ?string $navigationGroup = 'Log Operasional';
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- SECTION 1: GENERAL INFORMATION ---
            Forms\Components\Section::make('General Information')
                ->schema([
                    Forms\Components\Select::make('technician_id')
                        ->label('Technician')
                        ->relationship('technician', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn() => auth()->id())
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('asset_id')
                        ->label('Drone Unit')
                        ->options(Asset::where('category', 'DRONE')->pluck('asset_name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live() 
                        ->required()
                        
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (blank($state)) {
                                $set('hardwareItems', []);
                                return;
                            }

                            $installedParts = \App\Models\Asset::query()
                                ->where('category', 'SPAREPART')
                                ->where('drone_id', $state) 
                                ->get();

                            $repeaterRecords = $installedParts->map(function ($part) {
                                return [
                                    'asset_id' => $part->id, 
                                    'condition' => 'good',   
                                    'note' => null,          
                                ];
                            })->toArray();

                            $set('hardwareItems', $repeaterRecords);
                        }),

                    Forms\Components\Select::make('maintenance_type')
                        ->label('Maintenance Type')
                        ->options([
                            'hardware_inspection' => 'Hardware Inspection',
                            'software_update' => 'Software Update',
                            'full_maintenance' => 'Full Maintenance',
                        ])
                        ->live() 
                        ->required(),

                    Forms\Components\DatePicker::make('maintenance_date')
                        ->label('Maintenance Date')
                        ->placeholder('Select date'),

                    Forms\Components\Select::make('maintenance_status')
                        ->label('Maintenance Status')
                        ->options([
                            'scheduled' => 'Scheduled',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'on_hold' => 'On Hold',
                        ])
                        ->placeholder('Select an option'),
                ])->columns(2),

            // --- SECTION 2: ANALYSIS & SOFTWARE STATUS ---
            Forms\Components\Section::make('Analysis & Software Status')
                ->visible(fn(Get $get) => in_array($get('maintenance_type'), ['software_update', 'full_maintenance']))
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\CheckboxList::make('software_app_checklist')
                            ->label('Software & App Status')
                            ->options([
                                'app_stable' => 'App Stable',
                                'firmware_latest' => 'Firmware Latest',
                                'safe_fly_database' => 'Safe Fly Database OK',
                            ])
                            ->columns(3) 
                            ->required(fn(Get $get) => in_array($get('maintenance_type'), ['software_update', 'full_maintenance'])),

                        Forms\Components\CheckboxList::make('sensors_calibration_checklist')
                            ->label('Sensors & Calibration')
                            ->options([
                                'imu_ok' => 'IMU OK',
                                'gps_ok' => 'GPS OK',
                                'vision_sensors_ok' => 'Vision Sensors OK',
                                'compass_ok' => 'Compass OK',
                            ])
                            ->columns(2) 
                            ->required(fn(Get $get) => in_array($get('maintenance_type'), ['software_update', 'full_maintenance'])),
                    ]),
                ]),

            // --- SECTION 3: HARDWARE INSPECTION LIST ---
            Forms\Components\Section::make('Hardware Inspection List')
                ->visible(fn(Get $get) => in_array($get('maintenance_type'), ['hardware_inspection', 'full_maintenance']))
                ->schema([
                    Forms\Components\Repeater::make('hardwareItems')
                        ->relationship('hardwareItems')
                        ->addActionLabel('Add to')
                        ->itemLabel('New Component')
                        ->schema([
                            Forms\Components\Grid::make(12)->schema([
                                Forms\Components\Select::make('asset_id')
                                    ->label('Component')
                                    ->options(function(Get $get) {
                                        $droneId = $get('../../asset_id');
                                        if (!$droneId) return [];
                                        return Asset::where('id', $droneId)->orWhere('drone_id', $droneId)->pluck('asset_name', 'id');
                                    })
                                    ->required()
                                    ->live()
                                    ->columnSpan(4),

                                Forms\Components\ToggleButtons::make('condition')
                                    ->label('Condition')
                                    ->options([
                                        'good' => 'Good',
                                        'damaged_replace' => 'Damaged / Replace',
                                        'out_of_service' => 'Out of Service',
                                    ])
                                    ->colors([
                                        'good' => 'success',
                                        'damaged_replace' => 'warning',
                                        'out_of_service' => 'danger',
                                    ])
                                    ->inline() 
                                    ->required()
                                    ->live()
                                    ->columnSpan(8),
                            ]),

                            Forms\Components\Select::make('replaced_with_sparepart_id')
                                ->label('Replace with Part')
                                ->placeholder('Select available sparepart')
                                ->options(function(Get $get) {
                                    $componentId = $get('asset_id');
                                    if (!$componentId) return [];
                                    $comp = Asset::find($componentId);
                                    if (!$comp || !$comp->sparepart_type) return [];
                                    return Asset::where('category', 'SPAREPART')->where('sparepart_type', $comp->sparepart_type)->whereNull('drone_id')->where('status', 'ready')->pluck('asset_name', 'id');
                                })
                                ->visible(fn(Get $get) => $get('condition') === 'damaged_replace')
                                ->required(fn(Get $get) => $get('condition') === 'damaged_replace'),

                            Forms\Components\Grid::make(3)
                                ->visible(fn(Get $get) => in_array($get('condition'), ['damaged_replace', 'out_of_service']))
                                ->schema([
                                    Forms\Components\Select::make('damage_severity')->label('Damage Severity')->options(['minor' => 'Minor', 'moderate' => 'Moderate', 'major' => 'Major Level'])->required(),
                                    Forms\Components\DatePicker::make('oos_incident_date')->label('Incident Date')->default(now())->required(),
                                    Forms\Components\TextInput::make('oos_location')->label('Location')->placeholder('Input location details...')->required(),
                                ]),

                            Forms\Components\Textarea::make('oos_chronology')->label('Chronology & Details')->rows(3)->visible(fn(Get $get) => in_array($get('condition'), ['damaged_replace', 'out_of_service']))->required(),

                            Forms\Components\TextInput::make('note')->label('Note')->placeholder('Add special note for this component...'),
                        ]),
                ]),

            // --- SECTION 4: DOCUMENTATION ---
            Forms\Components\Section::make('Documentation')
                ->schema([
                    Forms\Components\Textarea::make('technical_notes')
                        ->label('Technical Notes')
                        ->rows(3),

                    Forms\Components\FileUpload::make('photos_evidence')
                        ->label('Photos Evidence')
                        ->multiple()
                        ->image()
                        ->directory('maintenance-photos'),
                ]),

            // 🎯 FIX: TOMBOL COMPACT DI POJOK KIRI BAWAH
            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('go_to_edit_page')
                    ->label('Edit Log')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\MaintenanceLogResource\Pages\ViewMaintenanceLog),
            ])
            ->alignment(\Filament\Support\Enums\Alignment::Left),

        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('asset.asset_name')->label('Drone Unit')->sortable(),
                Tables\Columns\TextColumn::make('maintenance_type')->label('Type')->badge(),
                Tables\Columns\TextColumn::make('technician.name')->label('Technician'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->color('info'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning'),
                    
                    Tables\Actions\Action::make('download_ba')
                        ->label('Print BA')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('danger')
                        ->action(function ($record) {
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.maintenance-log-ba', [
                                'records' => collect([$record])
                            ])->setPaper('A4', 'portrait');
                            
                            $droneName = $record->asset->asset_name ?? 'Drone';
                            $fileName = 'BA-Maintenance-' . str_replace(' ', '-', $droneName) . '-' . $record->id . '.pdf';

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, $fileName);
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Delete'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            // 🎯 SUNTIKAN SAKTI JALUR ROW CLICK: Mengalihkan klik baris langsung menuju halaman VIEW ONLY
            ->recordUrl(
                fn (MaintenanceLog $record): string => static::getUrl('view', ['record' => $record])
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceLogs::route('/'),
            'create' => Pages\CreateMaintenanceLog::route('/create'),
            'view' => Pages\ViewMaintenanceLog::route('/{record}'),
            'edit' => Pages\EditMaintenanceLog::route('/{record}/edit'),
        ];
    }
}