<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Company Identity')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Company Name (PT)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Company Logo')
                    ->image()
                    ->directory('company-logos'),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')->label('Logo'),
                Tables\Columns\TextColumn::make('name')->label('Company Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
            // 1. ACTION TERSEMBUNYI (Tetap biarkan untuk handle klik baris)
            Tables\Actions\ViewAction::make('clickToView')
                ->modalActions([
                    Tables\Actions\EditAction::make()
                        ->button()
                        ->color('warning'),
                ])
                ->extraAttributes(['class' => 'hidden']),

            // 2. MENU TITIK TIGA (Ubah di bagian sini)
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->color('info') // ◄--- KUNCI UTAMA: Membuat teks & ikon View di dalam dropdown berwarna BIRU
                    ->icon('heroicon-m-eye') // Menambahkan ikon mata agar semakin jelas
                    ->modalActions([
                        Tables\Actions\EditAction::make()
                            ->button()
                            ->color('warning'),
                    ]),
                
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                    
                Tables\Actions\DeleteAction::make(),
            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->color('gray'),
        ])
        
        ->recordUrl(null) 
        ->recordAction('clickToView'); 
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