<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\FlightLog;
use Illuminate\Support\Facades\Auth;

class PwaDashboard extends Page
{
    // Hilangkan menu ini dari sidebar admin biasa
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.pwa-dashboard';
    
    protected static ?string $title = 'LogDrone App';
    protected static ?string $navigationLabel = 'Dashboard';

    public $recentFlights = [];

    /**
     * 🚀 FUNGSI HADANG & LEMPAR (MIDDLEWARE LOKAL)
     * Berjalan otomatis sebelum halaman dirender.
     */
    public function mount()
    {
        $user = Auth::user();

        // 1. PASTIKAN USER LOGIN
        if (!$user) {
            return redirect()->route('filament.admin.auth.login');
        }

        // 2. LOGIKA REDIRECT: Lempar Admin/Manager ke Dashboard Utama JIKA mereka tidak mengakses via URL khusus PWA
        // (Asumsinya URL PWA adalah /admin/pwa-dashboard)
        if ($user->role !== 'pilot' && request()->path() !== 'admin/pwa-dashboard') {
            return redirect('/admin'); 
        }

        // 3. LOAD DATA UNTUK PILOT / PWA USER
        $this->recentFlights = FlightLog::query()
            // Jika yang buka pilot, tampilkan log dia saja. Jika admin, tampilkan semua.
            ->when($user->role === 'pilot', function($query) use ($user) {
                return $query->where('pilot_id', $user->id);
            })
            ->with('drone')
            ->latest()
            ->take(5)
            ->get();
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}