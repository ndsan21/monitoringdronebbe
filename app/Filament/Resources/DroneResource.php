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
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class DroneResource extends Resource
{
    protected static ?string $model = Asset::class; 
    protected static ?string $slug = 'drones'; 
    protected static ?string $navigationLabel = 'Drones';
    protected static ?string $modelLabel = 'Drone';
    protected static ?string $pluralModelLabel = 'Drones';
    protected static ?string $navigationGroup = 'Inventory & Asset Management';
    protected static ?string $navigationIcon = 'tabler-drone';
    protected static ?int $navigationSort = 1;
    // Tambahkan ini di dalam class Resource yang ingin disembunyikan dari pilot

public static function canViewAny(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    // 🔒 PENGUNCI PILOT: Hanya role super_admin dan admin yang boleh melihat/mengakses menu ini!
    return in_array($user->role, ['super_admin', 'admin']);
}

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'DRONE');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Asset General Information')->schema([
                Forms\Components\TextInput::make('asset_id')->label('Asset ID / Code')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('serial_number')->label('Serial Number')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('asset_name')->label('Asset Custom Name')->required(),
                Forms\Components\Select::make('category')->label('Category Asset')->options(['DRONE' => 'Drone Unit'])->default('DRONE')->disabled()->dehydrated()->required(),
                Forms\Components\DatePicker::make('entry_date')->label('Entry date')->default(now())->required(),
            ])->columns(2),

            Forms\Components\Section::make('ðŸ¤– Drone Technical Details')
                ->schema([
                    Forms\Components\Select::make('mission_type')
                        ->label('Mission Type')
                        ->options(['patrol' => 'Update Pekerjaan / Patroli', 'documentation' => 'Dokumentasi Acara', 'mapping' => 'Orthophoto / Pemetaan']),

                    // âš¡ FIX: Gunakan saveRelationships() secara eksplisit
                    Forms\Components\Select::make('spareparts_ids')
                        ->label('Installed Parts / Components')
                        ->relationship('spareparts', 'asset_name') // Mengambil data dari relasi spareparts
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->afterStateHydrated(function ($component, $record) {
                            if ($record) {
                                // Memastikan saat form dibuka (edit), data yang terpilih benar-benar muncul
                                $component->state($record->spareparts->pluck('id')->toArray());
                            }
                        })
                        ->saveRelationshipsUsing(function ($record, $state) {
                            // 1. Reset relasi lama (set ke null)
                            Asset::where('drone_id', $record->id)->update(['drone_id' => null]);
                            // 2. Hubungkan sparepart yang baru
                            if ($state) {
                                Asset::whereIn('id', $state)->update(['drone_id' => $record->id]);
                            }
                        }),

                    Forms\Components\Select::make('status')
                        ->label('Drone Operational Status')
                        ->options(['ready' => 'Ready', 'in_use' => 'In Use', 'on_repaired' => 'On Repaired', 'out_of_service' => 'Out of Service'])
                        ->default('ready')
                        ->columnSpan(2), 
                ])->columns(2),

            Forms\Components\Section::make('Ownership & Documentation')->schema([
                Forms\Components\Select::make('owner_company_id')->label('Company')->relationship('company', 'name')->required(),
                Forms\Components\Select::make('department_id')->label('Department')->relationship('department', 'name')->required(),
                Forms\Components\DatePicker::make('received_date')->label('Received date')->required(),
                Forms\Components\TextInput::make('received_by')->label('Received by')->required(),
                Forms\Components\FileUpload::make('photo_path')->label('Photo path')->image()->directory('asset-photos')->columnSpanFull()
            ])->columns(2)
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Grid::make(['default' => 1, 'md' => 3])->schema([
                Section::make()->columnSpan(['md' => 1])->schema([
                    ImageEntry::make('photo_path')->hiddenLabel()->height(320)->width('100%'),
                ]),
                Grid::make(1)->columnSpan(['md' => 2])->schema([
                    Section::make('Drone Identity & Status')->columns(2)->schema([
                        TextEntry::make('asset_name')->label('Drone Name'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('asset_id')->label('Unit Code / ID'),
                        TextEntry::make('serial_number')->label('Serial Number'),
                    ]),
                    Section::make('Installed Parts & Components')->schema([
                        RepeatableEntry::make('spareparts')->label('')->contained(false)->columns(3)->schema([
                            TextEntry::make('sparepart_type')->label('Component Type')->badge()->color('gray'),
                            TextEntry::make('serial_number')->label('Serial Number'),
                            TextEntry::make('asset_name')->label('Custom Name'),
                        ]),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_name')->label('Drone Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('asset_id')->label('Unit Code')->searchable(),
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

    public static function getPages(): array {
        return ['index' => Pages\ListDrones::route('/'), 'create' => Pages\CreateDrone::route('/create'), 'edit' => Pages\EditDrone::route('/{record}/edit')];
    }
}