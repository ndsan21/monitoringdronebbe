<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FlightLogExportController;

Route::redirect('/', '/admin');

Route::get('/export/flight/{id}/pdf', [ExportController::class, 'downloadFlightBeritaAcara'])->name('export.flight.pdf');
// Rute Portal Operasional Pilot (PWA Offline-First)
Route::prefix('pilot')->name('pilot.')->group(function () {
    // Halaman Utama 3-Modul Pilot
    Route::get('/portal', function () {
        $drones = \App\Models\Asset::where('category', 'DRONE')->get(['id', 'asset_name', 'serial_number']);
        $flightLocations = \App\Models\FlightLocation::get(['id', 'location_name']);
        return view('pilot.portal', compact('drones', 'flightLocations'));
    })->name('portal');

    // Rute Bypass Login Terunci (Gunakan ini sekali saat online di basecamp)
    Route::get('/mock-login/{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);
        return view('pilot.mock_login', ['user' => $user]);
    });
    Route::get('/flight-logs/recap-export', [FlightLogExportController::class, 'export']);
});
