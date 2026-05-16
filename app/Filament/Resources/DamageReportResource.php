<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageReportResource\Pages;
use App\Models\DamageReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class DamageReportResource extends Resource
{
    protected static ?string $model = DamageReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Log Operasional';
    protected static ?string $navigationLabel = 'Damage Reports';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- SECTION 1: ASSET IDENTIFICATION ---
            Forms\Components\Section::make('Asset Identification')->schema([
                Forms\Components\Select::make('asset_id')
                    ->relationship('asset', 'asset_name')
                    ->label('Select Asset / Component')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('reported_by_id')
                    ->relationship('reportedBy', 'full_name')
                    ->label('Reported By')
                    ->default(auth()->id())
                    ->required(),
                Forms\Components\DatePicker::make('report_date')
                    ->default(now())
                    ->required(),
            ])->columns(3),

            // --- SECTION 2: DAMAGE ANALYSIS ---
            Forms\Components\Section::make('Damage Analysis')->schema([
                Forms\Components\Select::make('damage_severity')
                    ->options([
                        'minor' => 'Minor Damage',
                        'moderate' => 'Moderate Damage',
                        'major' => 'Major / Critical Damage'
                    ])->required(),
                Forms\Components\DatePicker::make('incident_date')->required(),
                Forms\Components\TimePicker::make('incident_time')->required(),
                
                // Menggunakan Opsi Add-on-the-fly yang sama dengan Flight Area pada FlightLog
                Forms\Components\Select::make('incident_location_id')
                    ->relationship('incidentLocation', 'location_name')
                    ->label('Location of Incident (Flight Area)')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('location_name')->required(),
                        Forms\Components\TextInput::make('iup_number')->label('IUP License'),
                        Forms\Components\Select::make('company_id')->relationship('company', 'name')->required()
                    ])
                    ->required(),
                
                Forms\Components\Textarea::make('chronology')
                    ->label('Incident Chronology & Details')
                    ->columnSpanFull()
                    ->required(),
            ])->columns(2),

            // --- SECTION 3: STATUS & DOCUMENTATION ---
            Forms\Components\Section::make('Status & Documentation Matrix').schema([
                Forms\Components\Select::make('current_status')
                    ->label('Current Status Laporan')
                    ->options([
                        'reported' => 'Reported',
                        'on_progress' => 'On Progress',
                        'resolved' => 'Resolved'
                    ])->default('reported')->required(),
                
                // Kolom Sinkronisasi Jalan Tengah Menuju Status Master Asset via Eloquent Booted Model Hook
                Forms\Components\Select::make('condition_status')
                    ->label('Condition Target Matrix (Sync to Master Asset)')
                    ->options([
                        'good' => 'Good Condition (Asset Status -> Ready)',
                        'damaged_replace' => 'Damaged / Needs Replace (Asset Status -> On Repaired)',
                        'out_of_service' => 'Out Of Service (Asset Status -> Out Of Service)'
                    ])->required(),
                
                Forms\Components\Textarea::make('note')->label('Technical Note')->columnSpanFull(),
                
                Forms\Components\FileUpload::make('evidences')
                    ->label('Evidence Photos (Gallery/Camera Capture)')
                    ->multiple()
                    ->image()
                    ->directory('damage-evidences')
                    ->columnSpanFull()
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.asset_name')->label('Component Name')->searchable(),
                Tables\Columns\TextColumn::make('reportedBy.full_name')->label('Reporter'),
                Tables\Columns\BadgeColumn::make('damage_severity')
                    ->colors([
                        'info' => 'minor',
                        'warning' => 'moderate',
                        'danger' => 'major',
                    ]),
                Tables\Columns\BadgeColumn::make('current_status')
                    ->colors([
                        'danger' => 'reported',
                        'warning' => 'on_progress',
                        'success' => 'resolved',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDamageReports::route('/'),
            'create' => Pages\CreateDamageReport::route('/create'),
            'edit' => Pages\EditDamageReport::route('/{record}/edit'),
        ];
    }
}