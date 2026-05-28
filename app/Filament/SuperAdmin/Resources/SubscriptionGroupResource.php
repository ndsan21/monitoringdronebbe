<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SubscriptionGroupResource\Pages;
use App\Models\SubscriptionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionGroupResource extends Resource
{
    protected static ?string $model = SubscriptionGroup::class;
    protected static ?string $navigationGroup = 'SaaS Settings'; // Kelompokkan di menu baru
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 0; // Muncul paling atas

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Langganan Klien')->schema([
                Forms\Components\TextInput::make('group_name')
                    ->label('Nama Grup Pelanggan')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Select::make('package_type')
    ->label('Tipe Paket')
    ->options([
        'standard' => 'Standard (1 Perusahaan)',
        'premium'  => 'Premium (Banyak Perusahaan)',
        'personal' => 'Personal (Pribadi)',
    ])
    ->default('standard')
    ->required()
    ->native(false),

                Forms\Components\FileUpload::make('logo_path')
                    ->label('Logo Klien')
                    ->image()
                    ->directory('subscription-logos')
                    ->columnSpanFull(),
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('logo_path')->label('Logo')->circular(),
            Tables\Columns\TextColumn::make('group_name')->label('Grup Pelanggan')->searchable(),
            Tables\Columns\TextColumn::make('package_type')->label('Paket'),
            Tables\Columns\TextColumn::make('companies_count')->counts('companies')->label('Jumlah PT'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionGroups::route('/'),
            'create' => Pages\CreateSubscriptionGroup::route('/create'),
            'edit' => Pages\EditSubscriptionGroup::route('/{record}/edit'),
        ];
    }
}