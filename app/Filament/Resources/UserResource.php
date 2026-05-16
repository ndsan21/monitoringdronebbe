<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Control Panel';
    protected static ?string $navigationLabel = 'Employee & System Access';

    // Otorisasi Navigasi: Hanya Super Admin dan Admin yang bisa melihat menu ini
    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['super_admin', 'admin']);
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
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Company (PT Parent)')
                    ->required(),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->required(),
            ])->columns(2),

            // --- SECTION 2: PILOT LICENSE ---
            Forms\Components\Section::make('Pilot License (Certification)')->schema([
                Forms\Components\TextInput::make('license_number')
                    ->label('License / Certificate Number')
                    ->placeholder('e.g., PERHUB-UAV-12345'),
                Forms\Components\TextInput::make('license_issued_by')
                    ->label('Issued By')
                    ->placeholder('e.g., DKUPPU Kemenhub'),
                Forms\Components\DatePicker::make('license_expiration_date')
                    ->label('Expiration Date'),
                Forms\Components\Textarea::make('digital_signature')
                    ->label('Digital Signature Token / Notes')
                    ->rows(2),
            ])->columns(3),

            // --- SECTION 3: SYSTEM ACCESS ---
            Forms\Components\Section::make('System Access Authorization')
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('role')
                        ->options([
                            'super_admin' => 'Super Admin',
                            'admin' => 'Admin',
                            'pilot' => 'Drone Pilot'
                        ])
                        ->disabled(fn () => !auth()->user()?->isSuperAdmin()) // Hanya Super Admin yang bisa ubah role
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
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')->label('Photo')->circular(),
                Tables\Columns\TextColumn::make('full_name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('employee_id')->label('NIK')->searchable(),
                Tables\Columns\TextColumn::make('company.name')->label('PT'),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'admin',
                        'success' => 'pilot',
                    ]),
                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('Approved')
                    ->disabled(fn () => !auth()->user()?->isSuperAdmin()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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