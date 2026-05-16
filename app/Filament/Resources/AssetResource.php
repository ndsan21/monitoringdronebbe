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
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('General Information')->schema([
                Forms\Components\TextInput::make('asset_id')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('serial_number')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('asset_name')->label('Asset Custom Name')->required(),
                Forms\Components\Select::make('category')->options(['DRONE' => 'DRONE', 'SPAREPART' => 'SPAREPART'])->live()->required(),
                
                // STANDARISASI UX: Menggunakan TextInput + datalist() gabungan data master bawaan & database asli
                Forms\Components\TextInput::make('sparepart_type')
                    ->label('Sparepart Type')
                    ->placeholder('Ketik tipe sparepart... (contoh: Battery, Remote)')
                    ->datalist(fn () => array_unique(array_merge([
                        'Frame',
                        'Arms',
                        'Landing body',
                        'Camera',
                        'Battery',
                        'Motor',
                        'Gimbal',
                        'Tas',
                        'Baling-baling (Propeller)',
                        'Remote',
                        'Landing Gear',
                        'Prop Guard',
                        'Gimbal & Kamera',
                    ], \App\Models\Asset::query()
                        ->whereNotNull('sparepart_type')
                        ->where('sparepart_type', '!=', '')
                        ->distinct()
                        ->pluck('sparepart_type')
                        ->toArray()
                    )))
                    ->visible(fn (Get $get) => $get('category') === 'SPAREPART')
                    ->required(fn (Get $get) => $get('category') === 'SPAREPART'),

                Forms\Components\DatePicker::make('entry_date')->default(now())->required(),
            ])->columns(2),
            Forms\Components\Section::make('Technical Details')->schema([
                Forms\Components\Select::make('status')->options(['ready' => 'Ready', 'in_use' => 'In Use', 'on_repaired' => 'On Repaired', 'out_of_service' => 'Out of Service'])->default('ready')->required(),
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
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('asset_name')
                    ->label('Drone Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('asset_id')
                    ->label('Unit Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Type')
                    ->sortable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Owner')
                    ->searchable(),

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
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->color('info')
                    ->icon('heroicon-m-eye')
                    ->modalActions([
                        Tables\Actions\EditAction::make()
                            ->button()
                            ->color('warning'),
                    ]),

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
            ])
            ->recordUrl(null)
            ->recordAction('view');
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