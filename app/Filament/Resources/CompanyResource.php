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
    protected static ?string $navigationGroup = 'Master Data';
protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
protected static ?int $navigationSort = 2;

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
                    
                Tables\Actions\DeleteAction::make()
    ->before(function ($record, Tables\Actions\DeleteAction $action) {
        // ⚡ Cek apakah PT ini masih dipakai di tabel Asset?
        $assetTerikat = \App\Models\Asset::where('owner_company_id', $record->id)->count();
        
        if ($assetTerikat > 0) {
            // ⚡ Munculkan pop-up merah elegan di pojok kanan atas
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Gagal Menghapus PT!')
                ->body("PT ini tidak bisa dihapus karena masih digunakan oleh {$assetTerikat} data Aset. Silakan pindahkan atau hapus asetnya terlebih dahulu.")
                ->send();
            
            // ⚡ Batalkan proses hapus secara paksa agar tidak layar merah!
            $action->halt();
        }
    }),
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