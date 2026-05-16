<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageReportResource\Pages;
use App\Models\DamageReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DamageReportResource extends Resource
{
    protected static ?string $model = DamageReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Log Operasional';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Asset Identification')->schema([
                Forms\Components\Select::make('asset_id')->relationship('asset', 'asset_name')->required(),
                Forms\Components\Select::make('reported_by_id')->relationship('reportedBy', 'full_name')->required(),
                Forms\Components\DatePicker::make('report_date')->default(now())->required(),
            ])->columns(3),

            Forms\Components\Section::make('Damage Analysis & Status Mapping')->schema([
                Forms\Components\Select::make('damage_severity')->options(['minor' => 'Minor', 'moderate' => 'Moderate', 'major' => 'Major'])->required(),
                Forms\Components\DatePicker::make('incident_date')->required(),
                Forms\Components\TimePicker::make('incident_time')->required(),
                Forms\Components\TextInput::make('incident_location_name')->label('Location of Incident')->required(),
                Forms\Components\Select::make('current_status')->options(['reported' => 'Reported', 'on_progress' => 'On Progress', 'resolved' => 'Resolved'])->required(),
                Forms\Components\Select::make('condition_status')
                    ->label('Condition Target Matrix')
                    ->options([
                        'good' => 'Good (Ready)',
                        'damaged_replace' => 'Damaged / Needs Replace (On Repaired)',
                        'out_of_service' => 'Out Of Service'
                    ])->required(),
                Forms\Components\Textarea::make('chronology')->required()->columnSpanFull(),
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('asset.asset_name'),
            Tables\Columns\BadgeColumn::make('damage_severity'),
            Tables\Columns\BadgeColumn::make('current_status'),
        ])->actions([Tables\Actions\EditAction::make()]);
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