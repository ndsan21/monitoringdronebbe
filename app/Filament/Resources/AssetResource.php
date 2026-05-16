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
    
    // FIX UTAMA: Memisahkan rute url agar tidak tabrakan dengan DroneResource
    protected static ?string $slug = 'spareparts'; 

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    
    // Mengubah label tampilan agar merepresentasikan Suku Cadang gudang
    protected static ?string $navigationLabel = 'Spareparts & Components';
    protected static ?string $pluralLabel = 'Spareparts & Components';
    protected static ?string $modelLabel = 'Sparepart';

    /**
     * AMANKAN DATA: Memaksa menu ini HANYA menampilkan aset berkategori SPAREPART
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'SPAREPART');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Sparepart General Information')->schema([
                Forms\Components\TextInput::make('asset_id')
                    ->label('Component ID / Code')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('asset_name')
                    ->label('Component Custom Name')
                    ->required(),

                // Otomatis menyuntikkan kategori SPAREPART saat simpan data baru
                Forms\Components\Hidden::make('category')->default('SPAREPART'),
                
                Forms\Components\TextInput::make('sparepart_type')
                    ->label('Sparepart Type')
                    ->placeholder('Ketik tipe sparepart... (contoh: Battery, Remote)')
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
                    ->required(),

                // UX BONUS: Bisa menempelkan langsung ke drone dari form sparepart ini
                Forms\Components\Select::make('drone_id')
                    ->label('Attach to Drone Unit (Optional)')
                    ->placeholder('Biarkan kosong jika disimpan sebagai stok gudang')
                    ->relationship('drone', 'asset_name', fn ($query) => $query->where('category', 'DRONE'))
                    ->searchable()
                    ->preload(),

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
                    ])
                    ->default('ready')
                    ->required(),
            ]),

            Forms\Components\Section::make('Ownership & Documentation')->schema([
                Forms\Components\Select::make('owner_company_id')->relationship('company', 'name')->required(),
                Forms\Components\Select::make('department_id')->relationship('department', 'name')->required(),
                Forms\Components\DatePicker::make('received_date')->required(),
                Forms\Components\TextInput::make('received_by')->required(),
                Forms\Components\FileUpload::make('photo_path')->image()->directory('asset-photos')
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('asset_name')->label('Component Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sparepart_type')->label('Type')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('serial_number')->label('Serial Number')->searchable(),
                
                // Menampilkan informasi di tabel dia terpasang di drone mana
                Tables\Columns\TextColumn::make('drone.asset_name')
                    ->label('Installed On')
                    ->default('Di Gudang (Stok)')
                    ->weight(fn($record) => $record->drone_id ? 'bold' : 'normal')
                    ->color(fn($record) => $record->drone_id ? 'warning' : 'gray'),

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
            ->actions([
                Tables\Actions\ActionGroup::make([
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
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}