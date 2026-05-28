<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-2">
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="w-16 h-16 bg-white dark:bg-white rounded-xl shadow-sm overflow-hidden flex-shrink-0 flex items-center justify-center border border-gray-200">
                    @if(auth()->user()?->subscriptionGroup?->logo_path)
                        <img src="{{ asset('storage/' . auth()->user()->subscriptionGroup->logo_path) }}" 
                             alt="Group Logo" 
                             class="w-full h-full object-contain p-1.5 filter drop-shadow(0 0 4px rgba(0,0,0,0.05))">
                    @else
                        <div class="p-3 text-emerald-600 animate-pulse">
                            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </div>
                    @endif
                </div>
                
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
                        LogDrone Command Center
                    </h2>
                    <p class="text-sm text-emerald-500/90 dark:text-emerald-400/80">
                        Real-time Operational & Fleet Monitoring System
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 flex-shrink-0 w-full md:w-auto justify-end">
                <div class="px-4 py-1.5 border border-gray-300 dark:border-white/20 rounded-full flex items-center justify-center">
                    <span class="text-xs font-bold tracking-wider text-gray-900 dark:text-white uppercase font-mono">
                        SYSTEM ACTIVE
                    </span>
                </div>

                <a href="#" class="flex items-center gap-2 text-gray-900 dark:text-white hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors duration-200 font-bold text-sm">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Summary Report
                </a>
            </div>
            
        </div>
    </x-filament::section>
</x-filament-widgets::widget>