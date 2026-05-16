<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;

Route::redirect('/', '/admin');

Route::get('/export/flight/{id}/pdf', [ExportController::class, 'downloadFlightBeritaAcara'])->name('export.flight.pdf');