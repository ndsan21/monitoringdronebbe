<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\ResetPassword as FilamentResetPassword;

class ResetPassword extends FilamentResetPassword
{
    // Mengarahkan ke file blade kustom kamu untuk halaman ganti password
    protected static string $view = 'filament.auth.reset-password'; 
}