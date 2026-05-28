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
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $slug = 'inventory-assets';
    
    protected static ?string $navigationLabel = 'All Assets';
    protected static ?string $modelLabel = 'Asset';
    protected static ?string $pluralModelLabel = 'Assets';
    
    protected static ?string $navigationGroup = 'Inventory & Asset Management';
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Asset General Information')->schema([
                Forms\Components\TextInput::make('asset_id')->label('Asset ID / Code')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('serial_number')->label('Serial Number')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('asset_name')->label('Asset Custom Name')->required(),

                Forms\Components\Select::make('category')
                    ->label('Category Asset')
                    ->options(['SPAREPART' => 'Sparepart & Component', 'DRONE' => 'Drone Unit'])
                    ->live() 
                    ->required(),
                    
                Forms\Components\TextInput::make('sparepart_type')
                    ->label('Sparepart Type')
                    ->placeholder('Type sparepart type... (e.g. Battery, Remote)')
                    ->datalist(fn () => \App\Models\Asset::query()->whereNotNull('sparepart_type')->distinct()->pluck('sparepart_type')->toArray())
                    ->visible(fn (Get $get) => $get('category') === 'SPAREPART'),

                Forms\Components\Select::make('drone_id')
                    ->label('Attach to Drone Unit')
                    ->relationship('drone', 'asset_name', fn ($query) => $query->where('category', 'DRONE'))
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get) => $get('category') === 'SPAREPART'),

                Forms\Components\DatePicker::make('entry_date')->default(now())->required(),
            ])->columns(2),

            Forms\Components\Section::make('🤖 Drone Technical Details')
                ->visible(fn (Get $get) => $get('category') === 'DRONE')
                ->schema([
                    Forms\Components\Select::make('mission_type')
                        ->label('Mission Type')
                        ->options(['patrol' => 'Update Pekerjaan / Patroli', 'documentation' => 'Dokumentasi Acara', 'mapping' => 'Orthophoto / Pemetaan']),

                    // ⚡ FIX: spareparts_ids tidak akan error lagi karena kita dehydrate
                    Forms\Components\Select::make('spareparts_ids')
                        ->label('Installed Parts / Components')
                        ->options(\App\Models\Asset::where('category', 'SPAREPART')->pluck('asset_name', 'id'))
                        ->multiple()
                        ->dehydrated(false) // Penting: Jangan simpan ini ke database
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record) {
                                $component->state($record->spareparts->pluck('id')->toArray());
                            }
                        }),

                    Forms\Components\Select::make('status')
                        ->label('Drone Operational Status')
                        ->options(['ready' => 'Ready', 'in_use' => 'In Use', 'on_repaired' => 'On Repaired', 'out_of_service' => 'Out of Service'])
                        ->default('ready')
                        ->columnSpan(2), 
                ])->columns(2),

            Forms\Components\Section::make('🔧 Sparepart Status')
                ->visible(fn (Get $get) => $get('category') === 'SPAREPART')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Component Status')
                        ->options(['ready' => 'Ready', 'in_use' => 'In Use', 'on_repaired' => 'On Repaired', 'out_of_service' => 'Out of Service'])
                        ->default('ready'),
                ]),

            Forms\Components\Section::make('Ownership & Documentation')->schema([
                Forms\Components\Select::make('owner_company_id')->relationship('company', 'name')->required(),
                Forms\Components\Select::make('department_id')->relationship('department', 'name')->required(),
                Forms\Components\DatePicker::make('received_date')->required(),
                Forms\Components\TextInput::make('received_by')->required(),
                Forms\Components\FileUpload::make('photo_path')->image()->directory('asset-photos')->columnSpanFull()
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('asset_name')->label('Asset Name')->searchable()->sortable()->weight('bold')->color('primary'),
                Tables\Columns\TextColumn::make('category')->label('Category')->badge()->colors(['success' => 'DRONE', 'info' => 'SPAREPART']),
                Tables\Columns\TextColumn::make('serial_number')->label('Serial Number')->searchable(),
                Tables\Columns\TextColumn::make('drone.asset_name')->label('Installed On')->default('-'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ]);
    }

    // ⚡ MAGIC: Mengkoneksikan Sparepart otomatis saat klik Save
    public static function afterSave($record, $data): void
    {
        if (isset($data['spareparts_ids'])) {
            // Lepas semua relasi lama
            Asset::where('drone_id', $record->id)->update(['drone_id' => null]);
            // Pasang relasi baru
            Asset::whereIn('id', $data['spareparts_ids'])->update(['drone_id' => $record->id]);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'view' => Pages\ViewAsset::route('/{record}'), 
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}