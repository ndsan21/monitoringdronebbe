<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DroneResource\Pages;
use App\Models\Asset; // ◄--- KUNCI: Menunjuk ke model Asset
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
    
    // FIX UTAMA: Memberikan rute url unik agar tidak tabrakan dengan AssetResource
    protected static ?string $slug = 'drones'; 

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Master Data';
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
            Forms\Components\Section::make('Drone Information')->schema([
                Forms\Components\TextInput::make('asset_id')
                    ->label('Drone ID / Unit Code')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('asset_name')
                    ->label('Drone Model / Custom Name')
                    ->required(),

                // Otomatis menyuntikkan kategori DRONE saat simpan data baru
                Forms\Components\Hidden::make('category')->default('DRONE'),
                
                // --- INSTALLED PARTS ANTI-MENTAL ---
                Forms\Components\Select::make('spareparts_ids')
                    ->label('Installed Parts / Components')
                    ->placeholder('Pilih komponen yang akan dipasang ke drone ini...')
                    ->options(function (?Asset $record) {
                        return \App\Models\Asset::query()
                            ->where('category', 'SPAREPART')
                            ->where(function ($query) use ($record) {
                                $query->whereNull('drone_id');
                                if ($record) {
                                    $query->orWhere('drone_id', $record->id);
                                }
                            })
                            ->pluck('asset_name', 'id');
                    })
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

                Forms\Components\DatePicker::make('entry_date')->default(now())->required(),
            ])->columns(2),

            Forms\Components\Section::make('Status & Ownership')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'ready' => 'Ready', 
                        'in_use' => 'In Use', 
                        'on_repaired' => 'On Repaired', 
                        'out_of_service' => 'Out of Service'
                    ])
                    ->default('ready')
                    ->required(),
                Forms\Components\Select::make('owner_company_id')->relationship('company', 'name')->required(),
                Forms\Components\Select::make('department_id')->relationship('department', 'name')->required(),
                Forms\Components\DatePicker::make('received_date')->required(),
                Forms\Components\TextInput::make('received_by')->required(),
                Forms\Components\FileUpload::make('photo_path')->image()->directory('asset-photos')->columnSpanFull()
            ])->columns(2)
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // FIX TOTAL: Ganti Split dengan Grid 3 kolom agar pembagiannya pasti dan presisi di dalam modal
                Grid::make([
                    'default' => 1,
                    'md' => 3,
                ])->schema([
                    
                    // --- KOLOM KIRI: KHUSUS FOTO DRONE (Memakan 1 dari 3 Kolom) ---
                    Section::make()
                        ->columnSpan(['md' => 1])
                        ->schema([
                            ImageEntry::make('photo_path')
                                ->label('')
                                ->hiddenLabel()
                                ->height(320) // Mengunci tinggi foto agar terlihat gagah dan proporsional
                                ->width('100%')
                                ->extraImgAttributes([
                                    'class' => 'rounded-xl object-cover w-full shadow-sm border border-gray-200',
                                ]),
                        ]),

                    // --- KOLOM KANAN: IDENTITAS & SPAREPARTS (Memakan 2 dari 3 Kolom) ---
                    Grid::make(1)
                        ->columnSpan(['md' => 2])
                        ->schema([
                            // Card 1: Identitas Utama Drone
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

                            // Card 2: Daftar Seluruh Komponen Terpasang
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['company']))
            ->actions([
                // 1. Trik Jembatan: Membuat baris Action Tersembunyi khusus melayani klik row browser
                Tables\Actions\ViewAction::make('rowView')
                    ->extraAttributes(['class' => 'hidden']),

                // 2. MENU DROPDOWN TITIK TIGA UTAMA (Semua tombol rapi di dalam sini)
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
            ->recordAction('rowView'); // ◄--- KUNCI: Klik row diarahkan ke aksi jembatan tersembunyi
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrones::route('/'),
        ];
    }
}