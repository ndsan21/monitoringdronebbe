<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FlightLog;
use App\Models\Asset;

class FlightLogController extends Controller
{
    // Menampilkan Dashboard PWA
    public function index()
    {
        // Pastikan pilot login
        $recentFlights = FlightLog::where('pilot_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
            
        return view('pwa.dashboard', compact('recentFlights'));
    }

}