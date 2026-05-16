<?php

namespace App\Filament\Resources\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Department;
use Filament\Notifications\Notification;
// FIX: Menggunakan nama kontrak response yang benar dan sah milik Filament v3 (RegistrationResponse)
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class CustomRegister extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form->schema([
            // INJEKSI CSS: Menargetkan langsung '.fi-simple-main' bawaan Filament 3 agar lebar di Desktop
            Placeholder::make('desktop_layout_fix')
                ->label('')
                ->content(new HtmlString('
                    <style>
                        @media (min-width: 1024px) {
                            .fi-simple-main, 
                            .fi-simple-layout main,
                            main {
                                max-width: 72rem !important; 
                                width: 100% !important;
                            }
                        }
                    </style>
                '))
                ->columnSpanFull(),

            // --- SECTION 1: IDENTITY & COMPANY ---
            Section::make('Pilot / Employee Identity')->schema([
                FileUpload::make('photo_path')
                    ->label('Employee Photo')
                    ->image()
                    ->directory('employee-photos')
                    ->avatar(),
                TextInput::make('full_name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('employee_id')
                    ->label('Employee ID (NIK)')
                    ->required()
                    ->unique('users', 'employee_id'),
                
                Select::make('company_id')
                    ->label('Company (PT Parent)')
                    ->options(Company::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Select::make('department_id')
                    ->label('Department')
                    ->options(Department::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
            ])->columns(2),

            // --- SECTION 2: PILOT LICENSE & CERTIFICATION ---
            Section::make('Pilot License & Certification')->schema([
                TextInput::make('license_number')
                    ->label('License / Certificate Number')
                    ->placeholder('e.g., PERHUB-UAV-12345'),
                TextInput::make('license_issued_by')
                    ->label('Issued By')
                    ->placeholder('e.g., DKUPPU Kemenhub'),
                DatePicker::make('license_expiration_date')
                    ->label('Expiration Date'),
                
                FileUpload::make('digital_signature')
                    ->label('Digital Signature (Tanda Tangan PNG)')
                    ->image()
                    ->acceptedFileTypes(['image/png'])
                    ->directory('signatures')
                    ->maxSize(1024)
                    ->placeholder('Unggah file TTD digital transparan (.png)')
                    ->imageEditor()
                    ->columnSpanFull(),
            ])->columns(3),

            // --- SECTION 3: ACCOUNT CREDENTIALS ---
            Section::make('Account Credentials')->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ])->columns(2),
        ]);
    }

    /**
     * MUTASI DATA UTAMA (REGISTRATION LIFECYCLE)
     */
    protected function handleRegistration(array $data): Model
    {
        $data['name'] = $data['full_name'];
        return $this->getUserModel()::create($data);
    }

    /**
     * OVERRIDE MESIN UTAMA REGISTRASI FILAMENT 3 (100% SINKRON)
     * Menggunakan '?RegistrationResponse' agar sama persis dengan fungsi bawaan vendor Filament.
     */
    public function register(): ?RegistrationResponse
    {
        // 1. Ambil data bersih dari inputan form
        $data = $this->form->getState();

        // 2. Jalankan pembuatan user ke database (Otomatis is_approved = false)
        $user = $this->handleRegistration($data);

        // 3. Picu event registrasi bawaan Laravel
        event(new \Illuminate\Auth\Events\Registered($user));

        // 4. Siapkan Flash Notification untuk halaman login nanti
        Notification::make()
            ->title('Registration Successful!')
            ->body('Akun Anda berhasil dibuat. Silakan hubungi Super Admin untuk proses aktivasi akses.')
            ->success()
            ->persistent()
            ->send();

        // 5. Mengembalikan objek response kustom untuk memicu redirect paksa kembali ke Login Page
        return new class implements RegistrationResponse {
            public function toResponse($request)
            {
                return redirect()->route('filament.admin.auth.login');
            }
        };
    }
}