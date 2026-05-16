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
    protected static ?string $navigationLabel = 'Maintenance Logs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- SECTION 1: GENERAL INFORMATION ---
            Forms\Components\Section::make('General Information')->schema([
                Forms\Components\Select::make('asset_id')
                    ->relationship('asset', 'asset_name', fn($query) => $query->where('category', 'DRONE'))
                    ->label('Drone Unit')
                    ->required(),
                Forms\Components\Select::make('technician_id')
                    ->relationship('technician', 'full_name')
                    ->label('Technician Name')
                    ->default(auth()->id())
                    ->required(),
                Forms\Components\Select::make('maintenance_type')
                    ->options([
                        'hardware_inspection' => 'Hardware Inspection',
                        'software_update' => 'Software Update',
                        'full_maintenance' => 'Full Maintenance (Hardware & Software)'
                    ])
                    ->live() // Memicu reaktivitas perubahan form di bawahnya secara realtime
                    ->required(),
            ])->columns(3),

            // --- SECTION 2: HARDWARE INSPECTION LIST (Kondisional) ---
            Forms\Components\Section::make('Hardware Inspection List')
                ->visible(fn (Get $get) => in_array($get('maintenance_type'), ['hardware_inspection', 'full_maintenance']))
                ->schema([
                    Forms\Components\Repeater::make('hardwareItems')
                        ->relationship('hardwareItems')
                        ->schema([
                            Forms\Components\TextInput::make('component_name')
                                ->label('Component / Sparepart Name')
                                ->required(),
                            Forms\Components\Select::make('current_status')
                                ->options([
                                    'reported' => 'Reported',
                                    'on_progress' => 'On Progress',
                                    'resolved' => 'Resolved'
                                ])->default('on_progress')->required(),
                            Forms\Components\Select::make('condition')
                                ->options([
                                    'good' => 'Good Condition',
                                    'damaged_replace' => 'Damaged / Replace Part',
                                    'out_of_service' => 'Out Of Service'
                                ])
                                ->live()
                                ->required(),
                            
                            // Form Replace with Part yang menyambung langsung ke kategori tipe sparepart master asset
                            Forms\Components\Select::make('replaced_with_sparepart_id')
                                ->label('Replace With Sparepart Asset')
                                ->relationship('replacedSparepart', 'asset_name', fn($query) => $query->where('category', 'SPAREPART')->where('status', 'ready'))
                                ->visible(fn (Get $get) => $get('condition') === 'damaged_replace')
                                ->required(fn (Get $get) => $get('condition') === 'damaged_replace')
                                ->searchable()
                                ->preload(),
                        ])->columns(4)
                        ->createItemButtonLabel('Opsi Tambah Komponen Perbaikan')
                ]),

            // --- SECTION 3: ANALYSIS & SOFTWARE STATUS (Kondisional) ---
            Forms\Components\Section::make('Analysis & Software Status')
                ->visible(fn (Get $get) => in_array($get('maintenance_type'), ['software_update', 'full_maintenance']))
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('firmware_version_before')
                            ->label('Firmware Version (Before Update)'),
                        Forms\Components\TextInput::make('firmware_version_after')
                            ->label('Firmware Version (After Update)'),
                        Forms\Components\Select::make('software_status')
                            ->options([
                                'stable' => 'Stable Flight System',
                                'beta' => 'Beta / Maintenance Flight Test',
                                'issues_detected' => 'Issues System Detected'
                            ])->required(fn (Get $get) => in_array($get('maintenance_type'), ['software_update', 'full_maintenance'])),
                    ]),
                ]),

            // --- SECTION 4: OUT OF SERVICE / DEGRADATION PAYLOAD LOG ---
            Forms\Components\Section::make('Incident Chronology Payload (Auto Link to Damage Engine)')
                ->visible(fn (Get $get) => $get('maintenance_type') === 'hardware_inspection' || $get('maintenance_type') === 'full_maintenance')
                ->description('Diisi apabila ditemukan komponen berstatus Rusak/Out of service untuk sinkronisasi otomatis dengan Berita Acara Kerusakan.')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('oos_damage_severity')
                            ->label('Damage Severity Link')
                            ->options(['minor' => 'Minor', 'moderate' => 'Moderate', 'major' => 'Major']),
                        Forms\Components\DatePicker::make('oos_incident_date')->label('Incident Date'),
                        Forms\Components\TextInput::make('oos_location')->label('Location / Incident Flight Area'),
                    ]),
                    Forms\Components\Textarea::make('oos_chronology')
                        ->label('Incident Chronology & Details Payload')
                        ->columnSpanFull(),
                ]),

            // --- SECTION 5: DOCUMENTATION PHOTO EVIDENCE ---
            Forms\Components\Section::make('Documentation & Notes').schema([
                Forms\Components\Textarea::make('technical_notes')
                    ->label('Technical Notes Pemeliharaan')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('photos_evidence')
                    ->label('Photos Evidence (Gallery/Camera)')
                    ->multiple()
                    ->image()
                    ->directory('maintenance-gallery')
                    ->columnSpanFull()
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.asset_name')->label('Drone Unit Name')->searchable(),
                Tables\Columns\TextColumn::make('technician.full_name')->label('Technician'),
                Tables\Columns\BadgeColumn::make('maintenance_type')
                    ->colors([
                        'primary' => 'hardware_inspection',
                        'warning' => 'software_update',
                        'success' => 'full_maintenance',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label('Execution Date')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
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