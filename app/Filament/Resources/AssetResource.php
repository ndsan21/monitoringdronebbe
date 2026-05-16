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
                Forms\Components\TextInput::make('asset_id')
                    ->label('Asset ID')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('asset_name')
                    ->label('Asset Name')
                    ->required(),
                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options([
                        'DRONE' => 'DRONE',
                        'SPAREPART' => 'SPAREPART'
                    ])
                    ->live() // Mengaktifkan reaktivitas instan saat opsi diganti
                    ->required(),
                
                // Form Kondisional: Hanya muncul jika user memilih kategori SPAREPART
                Forms\Components\TextInput::make('sparepart_type')
                    ->label('Sparepart Type')
                    ->visible(fn (Get $get) => $get('category') === 'SPAREPART')
                    ->required(fn (Get $get) => $get('category') === 'SPAREPART'),

                // DROPDOWN DRONE SEBELUMNYA DI SINI TELAH DIHAPUS/DISEMBUNYIKAN SEPENUHNYA 
                // KETIKAL USER MEMILIH KATEGORI "DRONE" SEPERTI PERMINTAAN ANDA.

                Forms\Components\DatePicker::make('entry_date')
                    ->default(now())
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Technical Details')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'ready' => 'Ready',
                        'in_use' => 'In Use',
                        'on_repaired' => 'On Repaired',
                        'out_of_service' => 'Out of Service'
                    ])->default('ready')->required(),
            ]),

            Forms\Components\Section::make('Ownership & Documentation')->schema([
                Forms\Components\Select::make('owner_company_id')
                    ->relationship('company', 'name')
                    ->label('Owner Company')
                    ->required(),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->required(),
                Forms\Components\DatePicker::make('received_date')->required(),
                Forms\Components\TextInput::make('received_by')->required(),
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Photo / Camera')
                    ->image()
                    ->directory('asset-photos')
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_id')->searchable(),
                Tables\Columns\TextColumn::make('asset_name')->searchable(),
                Tables\Columns\BadgeColumn::make('category'),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'success' => 'ready',
                    'info' => 'in_use',
                    'warning' => 'on_repaired',
                    'danger' => 'out_of_service',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
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