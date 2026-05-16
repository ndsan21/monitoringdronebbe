<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller; // Pastikan baris import parent controller ini ada
use App\Models\FlightLog;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function downloadFlightBeritaAcara($id)
    {
        $log = FlightLog::with(['pilot', 'drone'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.berita_acara_flight', compact('log'));
        return $pdf->download("BERITA_ACARA_FLIGHT_{$log->id}.pdf");
    }
}