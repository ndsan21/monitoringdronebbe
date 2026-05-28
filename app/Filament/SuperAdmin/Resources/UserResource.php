<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kredensial Login Akun')->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true) // Memicu perubahan setelah admin selesai mengetik nama
                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                        // Otomatis isi field 'name' tersembunyi jika sedang membuat user baru
                        if ($operation === 'create') {
                            $set('name', $state);
                        }
                    }),

                // Field tersembunyi untuk mengamankan kolom 'name' di database MySQL
                Forms\Components\Hidden::make('name'),

                Forms\Components\TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label('Password Akun')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Penempatan Tenant & Hak Akses (Multi-Tenancy)')->schema([
                
                // 1. Pilih Grup Langganan (Tenant Utama)
                Forms\Components\Select::make('subscription_group_id')
                    ->label('Grup Langganan (Tenant)')
                    ->relationship('subscriptionGroup', 'group_name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set) => $set('company_id', null)), // Reset PT jika grup diganti

                // 2. Pilih PT (Hanya muncul jika grup sudah dipilih)
                Forms\Components\Select::make('company_id')
                    ->label('Ditempatkan di Perusahaan (PT)')
                    ->options(function (Get $get) {
                        $groupId = $get('subscription_group_id');
                        return Company::where('subscription_group_id', $groupId)->pluck('name', 'id');
                    })
                    ->required(fn (Get $get) => in_array($get('role'), ['admin', 'pilot']))
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('role')
                    ->label('Hak Akses Tingkat Sistem (Role)')
                    ->options([
                        'super_admin' => 'Super Admin (Pemilik Aplikasi)',
                        'admin'       => 'Admin Perusahaan (Klien PT)',
                        'pilot'       => 'Pilot Drone (Klien PT)',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\Toggle::make('is_approved')
                    ->label('Status Aktivasi Akun')
                    ->default(true),
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('subscriptionGroup.group_name')->label('Grup')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('company.name')->label('PT')->badge()->color('info'),
                Tables\Columns\TextColumn::make('role')->badge()->color(fn (string $state): string => match ($state) {
                    'super_admin' => 'danger',
                    'admin' => 'warning',
                    'pilot' => 'success',
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}