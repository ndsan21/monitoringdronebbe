<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Forms\Form;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    // Mengarahkan ke halaman LUPA PASSWORD (yang cuma minta email)
    protected static string $view = 'auth.request-password-reset'; 

    // Mengunci form agar HANYA meminta email
    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getEmailFormComponent(),
        ]);
    }
}