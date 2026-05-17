<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-2">
            
            <div class="flex items-center gap-4">
                <div class="p-3 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl shadow-sm animate-pulse">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </div>
                
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Welcome back, <span class="text-emerald-600 dark:text-emerald-400">{{ auth()->user()->name }}</span>!
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Operational Flight Operations & Drone Fleet Monitoring Center.
                    </p>
                </div>
            </div>

            <div class="flex items-center bg-emerald-500/5 px-4 py-2 rounded-lg border border-emerald-500/10">
                <span class="text-2xl font-black tracking-widest text-emerald-500 dark:text-emerald-400 font-mono">
                    LOGDRONE
                </span>
            </div>
            
        </div>
    </x-filament::section>
</x-filament-widgets::widget>