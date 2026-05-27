<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FlightLog;
use App\Exports\FlightLogsMasterExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class FlightLogExportController extends Controller
{
    // ⚡ Pastikan fungsi ini namanya 'export' sesuai rute di web.php
    public function export(Request $request) 
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Ambil data berdasarkan rentang tanggal
        $records = FlightLog::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        if ($records->isEmpty()) {
            return "Data kosong atau tidak ditemukan untuk rentang tanggal tersebut.";
        }

        // Jalur ekspor Excel
        if ($request->format === 'excel') {
            return Excel::download(new FlightLogsMasterExport($records), "Recap_{$startDate}_to_{$endDate}.xlsx");
        }

        // Jalur ekspor PDF Booklet
        $pdf = Pdf::loadView('pdf.flight-log-recap-booklet', compact('records', 'startDate', 'endDate'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Recap_Booklet_{$startDate}_to_{$endDate}.pdf");
    }
}