<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Pilot & Staff';
    protected static ?string $modelLabel = 'Pilot & Staff';
    protected static ?int $navigationSort = 1;

    /**
     * 🔒 OTORISASI BACKEND: Memblokir akses URL langsung (Anti-Tembak URL manual)
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Ambil nama grup langganan dan paksa jadi huruf kecil semua
        $groupName = strtolower($user->subscriptionGroup?->group_name ?? '');

        // ⛔ BLACKLIST AKUN PRIBADI: Jika nama grup mengandung kata ini, blokir total hak aksesnya!
        if (
            str_contains($groupName, 'pribadi') || 
            str_contains($groupName, 'personal') || 
            str_contains($groupName, 'saya ceo')
        ) {
            return false;
        }

        return true;
    }

    /**
     * 🔒 OTORISASI NAVIGASI SIDEBAR: Menyembunyikan menu dari pandangan mata
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // 1. Jika terdeteksi akun pribadi via fungsi canViewAny, langsung sembunyikan!
        if (! static::canViewAny()) {
            return false;
        }

        // 2. Tetap pertahankan filter role bawaan lu (Hanya Super Admin & Admin PT yang boleh lihat)
        return in_array($user->role, ['super_admin', 'admin']);
    }

    /**
     * 🔒 FILTER DATA: Mengizinkan Admin PT melihat kru internal + User baru mendaftar (Grup masih NULL)
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // 🔒 JIKA YANG LOGIN BUKAN SUPER ADMIN (Alias Admin PT / Tenant)
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('role', '!=', 'super_admin')
                  ->where(function (Builder $subQuery) {
                      $subQuery->where('subscription_group_id', auth()->user()->subscription_group_id)
                               ->orWhereNull('subscription_group_id'); // ◄--- KUNCI UTAMA: Supaya user baru regis yang grupnya masih kosong bisa kelihatan!
                  });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- SECTION 1: IDENTITY & COMPANY ---
            Forms\Components\Section::make('Pilot / Employee Identity')->schema([
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Employee Photo')
                    ->image()
                    ->directory('employee-photos')
                    ->avatar(),
                Forms\Components\TextInput::make('full_name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('employee_id')
                    ->label('Employee ID (NIK)')
                    ->required()
                    ->unique(ignoreRecord: true),
                
                // Input Grup: Otomatis mengunci user baru ke grup milik Admin yang memprosesnya
                Forms\Components\Select::make('subscription_group_id')
                    ->relationship('subscriptionGroup', 'group_name')
                    ->label('Grup')
                    ->default(fn () => auth()->user()->subscription_group_id)
                    ->disabled(fn () => !auth()->user()->isSuperAdmin())
                    ->dehydrated() // Tetap dikirim ke database saat disave meskipun statusnya disabled
                    ->required(),

                // Input PT: Pilihan dibatasi hanya PT yang berada di dalam grup sang admin saja
                Forms\Components\Select::make('company_id')
                    ->relationship(
                        'company', 
                        'name',
                        fn (Builder $query) => auth()->user()->isSuperAdmin()
                            ? $query
                            : $query->where('subscription_group_id', auth()->user()->subscription_group_id)
                    )
                    ->label('Company (PT Parent)')
                    ->default(fn () => auth()->user()->company_id)
                    ->required(),

                // Input Departemen
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->required(),
            ]), 

            // --- SECTION 2: PILOT LICENSE & SIGNATURE ---
            Forms\Components\Section::make('Pilot License & Certification')->schema([
                Forms\Components\TextInput::make('license_number')
                    ->label('License / Certificate Number')
                    ->placeholder('e.g., PERHUB-UAV-12345'),
                Forms\Components\TextInput::make('license_issued_by')
                    ->label('Issued By')
                    ->placeholder('e.g., DKUPPU Kemenhub'),
                Forms\Components\DatePicker::make('license_expiration_date')
                    ->label('Expiration Date'),
                
                Forms\Components\FileUpload::make('digital_signature')
                    ->label('Digital Signature (Tanda Tangan PNG)')
                    ->image()
                    ->acceptedFileTypes(['image/png']) 
                    ->directory('signatures') 
                    ->maxSize(1024) 
                    ->placeholder('Unggah file TTD digital transparan (.png)')
                    ->imageEditor(), 
            ]),

            // --- SECTION 3: SYSTEM ACCESS ---
            Forms\Components\Section::make('System Access Authorization')
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                    
                    Forms\Components\Select::make('role')
                        ->options(function () {
                            if (auth()->user()->isSuperAdmin()) {
                                return [
                                    'super_admin' => 'Super Admin',
                                    'admin' => 'Admin PT',
                                    'pilot' => 'Drone Pilot'
                                ];
                            }
                            return [
                                'admin' => 'Admin PT',
                                'pilot' => 'Drone Pilot'
                            ];
                        })
                        ->required(),
                        
                    Forms\Components\Toggle::make('is_approved')
                        ->label('Approve User Access')
                        ->default(true), 
                        
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn (?string $state) => filled($state) ? bcrypt($state) : null)
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->label('Password / Update Password'),
                ]), 
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')->label('Photo')->circular(),
                Tables\Columns\TextColumn::make('full_name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('employee_id')->label('NIK')->searchable(),
                
                Tables\Columns\TextColumn::make('subscriptionGroup.group_name')
                    ->label('Grup')
                    ->badge()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: fn () => !auth()->user()->isSuperAdmin()),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('PT')
                    ->badge()
                    ->color('info')
                    ->default('-') // Memberikan tanda strip jika PT masih kosong/baru register
                    ->toggleable(isToggledHiddenByDefault: fn () => !auth()->user()->isSuperAdmin()), 
                
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'admin',
                        'success' => 'pilot',
                    ]),
                
                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('Approved')
                    ->disabled(fn () => !auth()->user()?->isSuperAdmin() && !auth()->user()?->isAdmin())
                    ->afterStateUpdated(function (User $record, $state) {
                        if ($state === true) {
                            $record->notify(new \App\Notifications\AccountApprovedNotification());
                            
                            Notification::make()
                                ->title('Account Approved & Activated!')
                                ->body("Notification email successfully sent to {$record->email}")
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('clickToView')
                    ->modalActions([
                        Tables\Actions\EditAction::make()
                            ->button()
                            ->color('warning'),
                    ])
                    ->extraAttributes(['class' => 'hidden']),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info')
                        ->icon('heroicon-m-eye') 
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
            // Sesuai screenshot image_25e9bb.png, ini mengarah ke file penanganan halaman utamamu
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}