<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#030712] text-white min-h-screen pb-20">
    <header class="p-6">
        <h1 class="text-2xl font-bold">LogDrone <span class="text-emerald-500">BBE</span></h1>
        <p class="text-xs text-gray-400 uppercase tracking-widest">Pilot: {{ auth()->user()->name }}</p>
    </header>

    <div class="px-6">
        <a href="{{ route('filament.admin.resources.flight-logs.create') }}" 
   class="block w-full bg-emerald-600 p-5 rounded-2xl text-center font-bold shadow-lg shadow-emerald-900/20 active:scale-95 transition">
    + NEW FLIGHT LOG
</a>
    </div>

    <div class="px-6 mt-6">
        <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Recent Missions</h3>
        <div class="space-y-3">
            @foreach($recentFlights as $flight)
            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 flex justify-between items-center">
                <div>
                    <p class="font-bold">{{ $flight->drone_model }}</p>
                    <p class="text-xs text-gray-500">{{ $flight->date->format('d M') }} • {{ $flight->duration }} min</p>
                </div>
                <div class="text-emerald-400 text-sm font-bold">Log</div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>