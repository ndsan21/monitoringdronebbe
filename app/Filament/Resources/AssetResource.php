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
    
    // Rute URL tunggal untuk manajemen inventory terpadu
    protected static ?string $slug = 'inventory-assets'; 

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Inventory'; 
    protected static ?int $navigationSort = 2;
    
    // Label global yang mencakup Drone dan Sparepart
    protected static ?string $navigationLabel = 'Assets Management';
    protected static ?string $pluralLabel = 'Assets Management';
    protected static ?string $modelLabel = 'Asset';

    /**
     * Menampilkan seluruh data Asset baik SPAREPART maupun DRONE
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Asset General Information')->schema([
                Forms\Components\TextInput::make('asset_id')
                    ->label('Asset ID / Code')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('asset_name')
                    ->label('Asset Custom Name')
                    ->required(),

                // ⚡ PEMICU DINAMIS: Dropdown Kategori di-set Live
                Forms\Components\Select::make('category')
                    ->label('Category Asset')
                    ->options([
                        'SPAREPART' => 'Sparepart & Component',
                        'DRONE' => 'Drone Unit',
                    ])
                    ->live() // Pemicu perubahan form real-time
                    ->required(),
                    
                Forms\Components\TextInput::make('sparepart_type')
                    ->label('Sparepart Type')
                    ->placeholder('Type sparepart type... (e.g. Battery, Remote)')
                    ->datalist(fn () => array_unique(array_merge([
                        'Frame', 'Arms', 'Landing body', 'Camera', 'Battery', 'Motor',
                        'Gimbal', 'Tas', 'Baling-baling (Propeller)', 'Remote', 'Landing Gear',
                        'Prop Guard', 'Gimbal & Kamera',
                    ], \App\Models\Asset::query()
                        ->whereNotNull('sparepart_type')
                        ->where('sparepart_type', '!=', '')
                        ->distinct()
                        ->pluck('sparepart_type')
                        ->toArray()
                    )))
                    // Hanya muncul dan wajib jika kategorinya SPAREPART
                    ->visible(fn (Get $get) => $get('category') === 'SPAREPART')
                    ->required(fn (Get $get) => $get('category') === 'SPAREPART'),

                Forms\Components\Select::make('drone_id')
                    ->label('Attach to Drone Unit (Optional)')
                    ->placeholder('Leave blank if stored in warehouse warehouse')
                    ->relationship('drone', 'asset_name', fn ($query) => $query->where('category', 'DRONE'))
                    ->searchable()
                    ->preload()
                    // Hanya muncul jika tipenya SPAREPART
                    ->visible(fn (Get $get) => $get('category') === 'SPAREPART'),

                Forms\Components\DatePicker::make('entry_date')
                    ->default(now())
                    ->required(),
            ])->columns(2),

            // ⚡ SELEKTIF SECTION: Hanya muncul dan wajib diisi apabila Category = DRONE
            Forms\Components\Section::make('🤖 Drone Technical Details')
                ->visible(fn (Get $get) => $get('category') === 'DRONE')
                ->schema([
                    Forms\Components\Select::make('mission_type')
                        ->label('Mission Type')
                        ->options([
                            'patrol' => 'Update Pekerjaan / Patroli',
                            'documentation' => 'Dokumentasi Acara',
                            'mapping' => 'Orthophoto / Pemetaan'
                        ])
                        ->required(fn (Get $get) => $get('category') === 'DRONE'),

                    Forms\Components\Select::make('spareparts_ids')
                        ->label('Installed Parts / Components')
                        ->placeholder('Select components installed on this drone unit...')
                        ->options(\App\Models\Asset::where('category', 'SPAREPART')->whereNull('drone_id')->pluck('asset_name', 'id'))
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('Drone Operational Status')
                        ->options([
                            'ready' => 'Ready', 
                            'in_use' => 'In Use', 
                            'on_repaired' => 'On Repaired', 
                            'out_of_service' => 'Out of Service'
                        ])
                        ->default('ready')
                        ->required(fn (Get $get) => $get('category') === 'DRONE'),
                ])->columns(2),

            // ⚡ SECTION SPAREPART STATUS: Hanya muncul jika Category = SPAREPART
            Forms\Components\Section::make('🔧 Sparepart Status')
                ->visible(fn (Get $get) => $get('category') === 'SPAREPART')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Component Status')
                        ->options([
                            'ready' => 'Ready (In Warehouse)', 
                            'in_use' => 'In Use (Attached)', 
                            'on_repaired' => 'On Repaired', 
                            'out_of_service' => 'Out of Service'
                        ])
                        ->default('ready')
                        ->required(fn (Get $get) => $get('category') === 'SPAREPART'),
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
                
                // Menjadikan Nama Asset bisa di-klik dan memiliki style link primer
                Tables\Columns\TextColumn::make('asset_name')
                    ->label('Asset Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                
                // Badge pembeda kategori utama di tabel indeks
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->colors([
                        'success' => 'DRONE',
                        'info' => 'SPAREPART',
                    ]),

                Tables\Columns\TextColumn::make('serial_number')->label('Serial Number')->searchable(),
                
                Tables\Columns\TextColumn::make('drone.asset_name')
                    ->label('Installed On')
                    ->default('-')
                    ->weight(fn($record) => $record?->drone_id ? 'bold' : 'normal')
                    ->color(fn($record) => $record?->drone_id ? 'warning' : 'gray')
                    ->visible(fn($record) => $record?->category === 'SPAREPART'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ready' => 'success',
                        'in_use' => 'info',
                        'on_repaired' => 'warning',
                        'out_of_service' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['company', 'drone']))
            ->filters([
                // ⚡ KUNCI INTERAKTIF: MultiSelectFilter untuk menangkap klik stat dari widget atas
                Tables\Filters\MultiSelectFilter::make('status')
                    ->options([
                        'ready' => 'Ready',
                        'in_use' => 'In Use',
                        'on_repaired' => 'On Repaired',
                        'out_of_service' => 'Out of Service',
                    ]),
            ])
            ->headerActions([
                // ⚡ NEW ASSET BUTTON: Pindah ke sini agar berada tepat di bawah Overview widget
                Tables\Actions\CreateAction::make()
                    ->label('New Asset')
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->actions([
                // Tombol aksi baris tabel (View Only, Edit, Delete)
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('primary'), // Lempar ke halaman Read-Only view
                    Tables\Actions\EditAction::make()->color('warning'),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            // ⚡ Mendaftarkan halaman viewonly agar tombol ViewAction bekerja sempurna
            'view' => Pages\ViewAsset::route('/{record}'), 
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}