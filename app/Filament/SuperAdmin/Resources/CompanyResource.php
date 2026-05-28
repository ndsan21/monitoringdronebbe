<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Perusahaan')->schema([
                // ◄--- INI FIELD YANG HARUS MUNCUL
                Forms\Components\Select::make('subscription_group_id')
                    ->label('Pilih Grup Langganan')
                    ->relationship('subscriptionGroup', 'group_name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('name')
                    ->label('Company Name (PT)')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Company Logo')
                    ->image()
                    ->directory('company-logos')
                    ->columnSpanFull(),
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('logo_path')->label('Logo')->circular(),
            Tables\Columns\TextColumn::make('name')->label('Company Name')->searchable(),
            Tables\Columns\TextColumn::make('subscriptionGroup.group_name')
                ->label('Grup Langganan')
                ->badge()
                ->color('primary'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}