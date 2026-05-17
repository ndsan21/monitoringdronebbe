<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DroneResource\Pages;
use App\Models\Asset; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;

// IMPORT UNTUK KOMPONEN INFOLIST (DETAIL VIEW)
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class DroneResource extends Resource
{
    protected static ?string $model = Asset::class; 
    
    protected static ?string $slug = 'drones'; 

    protected static ?string $navigationIcon = null; // Di-null-kan sesuai request pembersihan tree-line kemarin
    protected static ?string $navigationGroup = 'Inventory'; 
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Drones';
    protected static ?string $pluralLabel = 'Drones';
    protected static ?string $modelLabel = 'Drone';

    /**
     * AMANKAN DATA: Memaksa menu ini HANYA menampilkan aset berkategori DRONE
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'DRONE');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- SECTION 1: ASSET GENERAL INFORMATION (Sesuai Gambar 2) ---
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

                // Mengunci visual kategori menjadi Drone Unit agar kembar dengan Gambar 2
                Forms\Components\Select::make('category')
                    ->label('Category Asset')
                    ->options([
                        'DRONE' => 'Drone Unit',
                        'SPAREPART' => 'Sparepart & Component',
                    ])
                    ->default('DRONE')
                    ->disabled() // Dikunci biar user ga bisa ganti ke sparepart di menu drone
                    ->dehydrated() // Tetap dikirim saat klik simpan ke database
                    ->required(),

                Forms\Components\DatePicker::make('entry_date')
                    ->label('Entry date')
                    ->default(now())
                    ->required(),
            ])->columns(2),

            // --- SECTION 2: DRONE TECHNICAL DETAILS (Sesuai Gambar 2) ---
            Forms\Components\Section::make('🤖 Drone Technical Details')->schema([
                Forms\Components\Select::make('mission_type')
                    ->label('Mission Type')
                    ->options([
                        'patrol' => 'Update Pekerjaan / Patroli',
                        'documentation' => 'Dokumentasi Acara',
                        'mapping' => 'Orthophoto / Pemetaan'
                    ])
                    ->required(),

                Forms\Components\Select::make('spareparts_ids')
                    ->label('Installed Parts / Components')
                    ->placeholder('Pilih komponen yang terpasang di unit drone ini...')
                    ->options(\App\Models\Asset::where('category', 'SPAREPART')->whereNull('drone_id')->pluck('asset_name', 'id'))
                    ->multiple() 
                    ->preload()
                    ->searchable()
                    ->columnSpanFull()
                    
                    ->afterStateHydrated(function (Forms\Components\Select $component, ?Asset $record) {
                        if ($record) {
                            $assignedIds = \App\Models\Asset::where('drone_id', $record->id)
                                ->pluck('id')
                                ->toArray();
                            $component->state($assignedIds);
                        }
                    })
                    
                    ->saveRelationshipsUsing(function (Asset $record, $state) {
                        $selectedIds = is_array($state) ? $state : [];

                        \App\Models\Asset::where('drone_id', $record->id)
                            ->whereNotIn('id', $selectedIds)
                            ->update(['drone_id' => null]);

                        if (! empty($selectedIds)) {
                            \App\Models\Asset::whereIn('id', $selectedIds)
                                ->update(['drone_id' => $record->id]);
                        }
                    }),

                Forms\Components\Select::make('status')
                    ->label('Drone Operational Status')
                    ->options([
                        'ready' => 'Ready', 
                        'in_use' => 'In Use', 
                        'on_repaired' => 'On Repaired', 
                        'out_of_service' => 'Out of Service'
                    ])
                    ->default('ready')
                    ->required(),
            ])->columns(2),

            // --- SECTION 3: OWNERSHIP & DOCUMENTATION (Sesuai Gambar 2) ---
            Forms\Components\Section::make('Ownership & Documentation')->schema([
                Forms\Components\Select::make('owner_company_id')
                    ->label('Company')
                    ->relationship('company', 'name')
                    ->required(),

                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->required(),

                Forms\Components\DatePicker::make('received_date')
                    ->label('Received date')
                    ->required(),

                Forms\Components\TextInput::make('received_by')
                    ->label('Received by')
                    ->required(),

                Forms\Components\FileUpload::make('photo_path')
                    ->label('Photo path')
                    ->image()
                    ->directory('asset-photos')
                    ->columnSpanFull()
            ])->columns(2)
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 3,
                ])->schema([
                    
                    Section::make()
                        ->columnSpan(['md' => 1])
                        ->schema([
                            ImageEntry::make('photo_path')
                                ->label('')
                                ->hiddenLabel()
                                ->height(320) 
                                ->width('100%')
                                ->extraImgAttributes([
                                    'class' => 'rounded-xl object-cover w-full shadow-sm border border-gray-200',
                                ]),
                        ]),

                    Grid::make(1)
                        ->columnSpan(['md' => 2])
                        ->schema([
                            Section::make('Drone Identity & Status')
                                ->columns(2)
                                ->icon('heroicon-m-identification')
                                ->schema([
                                    TextEntry::make('asset_name')->label('Drone Name')->weight('bold')->size(TextEntry\TextEntrySize::Large),
                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'ready' => 'success',
                                            'in_use' => 'info',
                                            'on_repaired' => 'warning',
                                            'out_of_service' => 'danger',
                                            default => 'gray',
                                        }),
                                    TextEntry::make('asset_id')->label('Unit Code / ID'),
                                    TextEntry::make('serial_number')->label('Serial Number'),
                                    TextEntry::make('company.name')->label('Owner Company'),
                                    TextEntry::make('department.name')->label('Department'),
                                ]),

                            Section::make('Installed Parts & Components')
                                ->icon('heroicon-m-cpu-chip')
                                ->schema([
                                    RepeatableEntry::make('spareparts')
                                        ->label('')
                                        ->contained(false) 
                                        ->columns(3)
                                        ->schema([
                                            TextEntry::make('sparepart_type')->label('Component Type')->badge()->color('gray'),
                                            TextEntry::make('serial_number')->label('Serial Number')->weight('bold'),
                                            TextEntry::make('asset_name')->label('Custom Name'),
                                        ])
                                        ->placeholder('Belum ada sparepart yang terpasang di unit drone ini.'),
                                ]),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('asset_name')->label('Drone Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('asset_id')->label('Unit Code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('company.name')->label('Owner')->searchable(),
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
            ->filters([
            // ⚡ WAJIB: Pasang filter status agar ketika widget di-klik, tabel bisa merespon
            Tables\Filters\MultiSelectFilter::make('status')
                ->options([
                    'ready' => 'Ready',
                    'in_use' => 'In Use',
                    'on_repaired' => 'On Repaired',
                    'out_of_service' => 'Out of Service',
                ]),
        ])
        ->headerActions([
            // ⚡ Tombol New Drone sekarang nangkring rapi di bawah Overview
            Tables\Actions\CreateAction::make()
                ->label('New Drone')
                ->icon('heroicon-m-plus')
                ->button(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(), // Agar bisa di-klik viewonly seperti Asset Management
            Tables\Actions\EditAction::make(),
        ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['company']))
            ->actions([
                Tables\Actions\ViewAction::make('rowView')
                    ->extraAttributes(['class' => 'hidden']),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make('view')
                        ->label('View')
                        ->color('info') 
                        ->icon('heroicon-m-eye'),
                    
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
            ])
            ->recordUrl(null) 
            ->recordAction('rowView');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrones::route('/'),
        ];
    }
}