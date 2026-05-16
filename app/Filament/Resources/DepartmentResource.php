<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Master Data Corporate';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Department Connection')->schema([
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Linked Company')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Department Name')
                    ->required()
                    ->maxLength(255),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')->label('Company Parent')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Department Name')->searchable(),
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}