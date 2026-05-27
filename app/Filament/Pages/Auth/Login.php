<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    // 🎯 Mengarahkan halaman login Filament ke file blade buatan kita sendiri
    protected static string $view = 'auth.custom-login';
}