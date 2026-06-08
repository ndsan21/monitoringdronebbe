<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FlightLogExportController;
use App\Models\Asset;
use App\Models\FlightLocation;
use App\Models\User;

// ------------------------------------------------------------------------
// FALLBACK ROUTE: Penangkap Error "Route [login] not defined"
// Jika tamu tak diundang mencoba mengakses rute yang dilindungi, 
// Laravel akan melemparnya ke sini, lalu diteruskan ke form login Admin.
// ------------------------------------------------------------------------
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Redirect halaman depan langsung ke panel admin
Route::redirect('/', '/admin');

// ------------------------------------------------------------------------
// RUTE EXPORT LAPORAN
// ------------------------------------------------------------------------
Route::get('/export/flight/{id}/pdf', [ExportController::class, 'downloadFlightBeritaAcara'])
    ->name('export.flight.pdf');

// ------------------------------------------------------------------------
// RUTE PORTAL OPERASIONAL PILOT (PWA Offline-First)
// ------------------------------------------------------------------------
Route::prefix('pilot')->name('pilot.')->group(function () {
    
    // Halaman Utama 3-Modul Pilot
    Route::get('/portal', function () {
        $drones = Asset::where('category', 'DRONE')->get(['id', 'asset_name', 'serial_number']);
        $flightLocations = FlightLocation::get(['id', 'location_name']);
        
        return view('pilot.portal', compact('drones', 'flightLocations'));
    })->name('portal');

    // Rute Bypass Login Terkunci (Gunakan ini sekali saat online di basecamp)
    Route::get('/mock-login/{id}', function ($id) {
        $user = User::findOrFail($id);
        
        return view('pilot.mock_login', ['user' => $user]);
    })->name('mock-login');
    
    // Rute Export Rekap Log Penerbangan
    Route::get('/flight-logs/recap-export', [FlightLogExportController::class, 'export'])
        ->name('recap-export');
});