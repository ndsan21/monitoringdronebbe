<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>

    <div class="w-full max-w-md mx-auto min-h-screen bg-[#070b14] text-slate-200 font-sans pb-20 shadow-2xl relative overflow-hidden">
        
        <style>
            /* Membunuh sisa elemen Filament */
            .fi-topbar, .fi-sidebar, .fi-header, .fi-breadcrumbs, .fi-page-header-widgets { display: none !important; }
            .fi-main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; background-color: transparent !important; }
            .fi-page { padding: 0 !important; }
            /* Menyembunyikan scrollbar agar persis seperti native app */
            ::-webkit-scrollbar { display: none; }
        </style>

        <div class="pt-10 pb-6 px-6 relative z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 p-[2px] shadow-lg shadow-emerald-500/20">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Pilot') }}&background=070b14&color=10B981&bold=true" alt="Avatar" class="w-full h-full rounded-full border-2 border-[#070b14]">
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase mb-0.5">LogDrone BBE</p>
                        <h1 class="text-base font-black text-white leading-tight tracking-wide">
                            Halo, {{ explode(' ', auth()->user()->name ?? 'Pilot')[0] }}
                        </h1>
                    </div>
                </div>
                
                <div class="w-11 h-11 rounded-full overflow-hidden border border-slate-700/50 bg-white/5 flex items-center justify-center p-1.5 shadow-inner backdrop-blur-sm">
                    <img src="{{ asset('LOGO LOGDRONE.png') }}" alt="Logo" class="w-full h-full object-contain drop-shadow-md">
                </div>
            </div>
        </div>

        <div class="px-5 space-y-4 relative z-20">

            <a href="/admin/flight-logs/create" class="relative block w-full bg-gradient-to-r from-emerald-600 to-teal-500 rounded-[28px] p-6 shadow-[0_10px_30px_-10px_rgba(16,185,129,0.5)] overflow-hidden active:scale-95 transition-transform duration-200">
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-white mb-1 tracking-tight">Mulai Misi</h2>
                        <p class="text-emerald-100/80 text-xs font-medium">Catat log penerbangan baru</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white shadow-inner">
                        <x-heroicon-s-paper-airplane class="w-6 h-6 transform -rotate-45 ml-1 mt-1" />
                    </div>
                </div>
            </a>

            <div class="grid grid-cols-2 gap-4">
                <a href="/admin/maintenance-logs" class="flex flex-col p-5 bg-slate-800/60 border border-slate-700/50 backdrop-blur-sm rounded-[24px] active:scale-95 transition-transform duration-200 shadow-lg hover:border-blue-500/50">
                    <div class="w-10 h-10 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center mb-3">
                        <x-heroicon-s-wrench-screwdriver class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-bold text-white mb-0.5">Perawatan</span>
                    <span class="text-[10px] text-slate-400 font-medium tracking-wide">Jadwal Servis</span>
                </a>

                <a href="/admin/damage-reports" class="flex flex-col p-5 bg-slate-800/60 border border-slate-700/50 backdrop-blur-sm rounded-[24px] active:scale-95 transition-transform duration-200 shadow-lg hover:border-red-500/50">
                    <div class="w-10 h-10 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center mb-3">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-bold text-white mb-0.5">Lapor Rusak</span>
                    <span class="text-[10px] text-slate-400 font-medium tracking-wide">Kendala Aset</span>
                </a>
            </div>
        </div>

        <div class="mt-8 px-5">
            <div class="flex justify-between items-center mb-4 px-1">
                <h3 class="text-sm font-bold text-slate-200 tracking-wide">Riwayat Misi</h3>
                <a href="/admin/flight-logs" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300 transition-colors bg-emerald-500/10 px-3 py-1 rounded-full">Lihat Semua</a>
            </div>
            
            <div class="bg-slate-800/40 border border-slate-700/50 backdrop-blur-md rounded-[28px] p-2 shadow-xl">
                <div class="space-y-1">
                    @forelse($recentFlights ?? [] as $flight)
                        @php
                            $statusText = strtolower($flight->result ?? '');
                            $statusTheme = match(true) {
                                str_contains($statusText, 'safe') || str_contains($statusText, 'success') => ['text' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10', 'icon' => 'heroicon-s-check-circle'],
                                str_contains($statusText, 'postpone') || str_contains($statusText, 'delay') => ['text' => 'text-amber-400', 'bg' => 'bg-amber-500/10', 'icon' => 'heroicon-s-clock'],
                                str_contains($statusText, 'damage') || str_contains($statusText, 'cancel') => ['text' => 'text-red-400', 'bg' => 'bg-red-500/10', 'icon' => 'heroicon-s-x-circle'],
                                default => ['text' => 'text-slate-400', 'bg' => 'bg-slate-500/10', 'icon' => 'heroicon-s-information-circle'],
                            };
                        @endphp

                        <div class="flex items-center justify-between p-3 hover:bg-slate-700/40 rounded-[20px] transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-900 border border-slate-700/80 flex items-center justify-center {{ $statusTheme['text'] }} shadow-inner">
                                    <x-dynamic-component :component="$statusTheme['icon']" class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-100 tracking-wide">{{ $flight->drone->asset_name ?? 'Unknown Drone' }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $flight->created_at ? $flight->created_at->diffForHumans() : 'Baru saja' }}</p>
                                </div>
                            </div>
                            <div class="px-3 py-1 rounded-full {{ $statusTheme['bg'] }} border border-transparent">
                                <span class="text-[9px] font-black uppercase tracking-wider {{ $statusTheme['text'] }}">
                                    {{ str_replace('_', ' ', $flight->result ?? 'Logged') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <div class="w-14 h-14 bg-slate-800/80 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-700/50 shadow-inner">
                                <x-heroicon-o-document-text class="w-6 h-6 text-slate-500" />
                            </div>
                            <p class="text-sm font-bold text-slate-300">Belum ada riwayat</p>
                            <p class="text-[11px] text-slate-500 mt-1 max-w-[200px] mx-auto">Data penerbangan pertamamu akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>