<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('General Information')->schema([
                Forms\Components\TextInput::make('asset_id')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('serial_number')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('asset_name')->required(),
                Forms\Components\Select::make('category')->options(['DRONE' => 'DRONE', 'SPAREPART' => 'SPAREPART'])->live()->required(),
                Forms\Components\TextInput::make('sparepart_type')->visible(fn(Get $get) => $get('category') === 'SPAREPART')->required(fn(Get $get) => $get('category') === 'SPAREPART'),
                Forms\Components\Select::make('drone_id')->relationship('drone', 'model')->visible(fn(Get $get) => $get('category') === 'DRONE')->required(fn(Get $get) => $get('category') === 'DRONE'),
                Forms\Components\DatePicker::make('entry_date')->default(now())->required(),
            ])->columns(2),

            Forms\Components\Section::make('Ownership & Technical Details')->schema([
                Forms\Components\Select::make('status')->options(['ready' => 'Ready', 'in_use' => 'In Use', 'on_repaired' => 'On Repaired', 'out_of_service' => 'Out of Service'])->required(),
                Forms\Components\Select::make('owner_company_id')->relationship('company', 'name')->required(),
                Forms\Components\Select::make('department_id')->relationship('department', 'name')->required(),
                Forms\Components\DatePicker::make('received_date')->required(),
                Forms\Components\TextInput::make('received_by')->required(),
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('asset_id'),
            Tables\Columns\TextColumn::make('asset_name'),
            Tables\Columns\BadgeColumn::make('status'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}