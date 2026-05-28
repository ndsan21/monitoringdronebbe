<x-filament-widgets::widget>
    <div class="w-full p-6 flex flex-col md:flex-row items-center justify-between gap-6 border transition-all duration-300 relative overflow-hidden custom-command-wrapper">
         
        <style>
            /* --- KONDISI STANDAR (MODE TERANG) --- */
            .custom-command-wrapper {
                border-radius: 1rem !important;
                background-color: #ffffff !important;
                border-color: #e2e8f0 !important;
                /* Cahaya tipis mode terang */
                box-shadow: 0 0 12px rgba(16, 185, 129, 0.08) !important; 
            }
            .custom-title-cc {
                color: #065f46 !important;
            }
            .custom-subtitle-cc {
                color: #047857 !important;
            }

            /* --- KONDISI SAAT FILAMENT MASUK MODE GELAP (.dark) --- */
            .dark .custom-command-wrapper {
                background-color: #050b18 !important; /* Deep Navy Premium */
                border-color: #16a34a !important;    /* Border hijau emerald tegas */
                /* CAHAYA TIPIS BARU: Blur diperkecil dari 50px ke 12px agar pas di samping-samping saja */
                box-shadow: 0 0 12px rgba(22, 163, 74, 0.4) !important; 
            }
            .dark .custom-title-cc {
                color: #10b981 !important; /* Hijau neon komando */
            }
            .dark .custom-subtitle-cc {
                color: #34d399 !important;
            }
        </style>
        
        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-[80px] pointer-events-none hidden dark:block"></div>

        <div class="flex items-center gap-6 min-w-0 relative z-10">
            <div class="w-20 h-20 bg-white border border-slate-200 rounded-xl flex flex-col items-center justify-center text-center shrink-0 shadow-md p-2 select-none relative overflow-hidden">
                @if(auth()->user()?->subscriptionGroup?->logo_path)
                    <img src="{{ asset('storage/' . auth()->user()->subscriptionGroup->logo_path) }}" 
                         alt="Group Logo" 
                         id="cmd-group-logo"
                         class="w-full h-full object-contain p-1"
                         onerror="this.style.display='none'; document.getElementById('cmd-fallback-logo').classList.remove('hidden');">
                    
                    <div id="cmd-fallback-logo" class="hidden flex flex-col items-center justify-center text-center w-full h-full">
                        <span class="text-[#059669] font-[900] text-base tracking-tighter leading-none">KHO</span>
                        <span class="text-slate-900 font-extrabold text-xs tracking-widest mt-0.5 leading-none">TAI</span>
                        <div class="w-6 h-0.5 bg-[#10b981] mt-1 rounded-full opacity-60"></div>
                    </div>
                @else
                    <span class="text-[#059669] font-[900] text-lg tracking-tighter leading-none">KHO</span>
                    <span class="text-slate-900 font-extrabold text-sm tracking-widest mt-0.5 leading-none">TAI</span>
                    <div class="w-8 h-0.5 bg-[#10b981] mt-1.5 rounded-full opacity-60"></div>
                @endif
            </div>

            <div class="flex flex-col min-w-0">
                <h1 class="text-2xl md:text-3xl font-black tracking-tight leading-tight whitespace-nowrap custom-title-cc">
                    LogDrone Command Center
                </h1>
                <p class="font-semibold text-sm md:text-base mt-1 leading-none whitespace-nowrap overflow-hidden text-ellipsis custom-subtitle-cc">
                    Real-time Operational & Fleet Monitoring System
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 shrink-0 relative z-10">
            <div class="flex items-center gap-2.5 bg-emerald-500/10 dark:bg-[#022c22]/60 px-4 py-2 rounded-full border border-emerald-500/20 dark:border-emerald-500/30 h-9">
                <span class="text-[10px] font-black tracking-[0.2em] text-[#10b981] uppercase font-mono whitespace-nowrap">SYSTEM ACTIVE</span>
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                </span>
            </div>

            <a href="#" class="inline-flex items-center justify-center gap-2 bg-[#10b981] hover:bg-emerald-400 active:bg-emerald-600 text-[#050b18] font-black text-sm px-5 py-2.5 rounded-xl transition duration-200 shadow-lg shadow-emerald-500/10 h-10 whitespace-nowrap">
                <svg class="w-4 h-4 text-[#050b18]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Download Summary Report</span>
            </a>
        </div>

    </div>
</x-filament-widgets::widget>