<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogDrone Offline Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #030712; color: #f8fafc; padding-bottom: 120px; }
        .form-card { background-color: #0f172a; border: 1px solid #1e293b; border-radius: 16px; padding: 24px; margin-bottom: 28px; }
        .input-box { width: 100%; background-color: #1e293b; border: 1px solid #334155; color: white; border-radius: 8px; padding: 12px; margin-top: 6px; font-size: 0.95rem; }
        .input-box:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
        .select-box { width: 100%; background-color: #1e293b; border: 1px solid #334155; color: white; border-radius: 8px; padding: 12px; margin-top: 6px; font-size: 0.95rem; appearance: auto; }
        .select-box:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
        .checkbox-card { display: flex; align-items: center; space-x: 3px; background-color: #1e293b; padding: 12px; border-radius: 8px; border: 1px solid #334155; cursor: pointer; }
        
        /* 🔥 BINTANG MERAH WAJIB ISI 🔥 */
        .label-req::after { content: " *"; color: #f43f5e; font-size: 1.1em; font-weight: 800; }

        .step-container { display: none; animation: fadeIn 0.4s ease-in-out; }
        .step-container.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="p-4 md:p-8 bg-[#030712]">

    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-emerald-400 tracking-tight">LOGDRONE COMMAND</h1>
            <p class="text-slate-400 mt-2 text-sm md:text-base">Sistem Penginputan Log Operasional Lapangan</p>
        </div>

        <div class="mb-6 bg-[#0f172a] p-4 rounded-xl border border-slate-800">
            <div class="flex justify-between items-center mb-2">
                <span id="stepTitle" class="text-emerald-400 font-bold text-sm md:text-base uppercase tracking-wider">Langkah 1: Flight Identity</span>
                <span id="stepCounter" class="text-slate-400 font-bold text-sm">1 / 5</span>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-2.5">
                <div id="progressBar" class="bg-emerald-500 h-2.5 rounded-full transition-all duration-300" style="width: 20%"></div>
            </div>
        </div>

        <form id="formFlightLog">
            
            <div class="step-container active" id="step-0">
                <div class="form-card shadow-xl shadow-emerald-500/5">
                    <h2 class="text-xl font-bold text-slate-200 mb-6 border-b border-slate-700 pb-2">1. Flight Identity & Time</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 label-req">Pilot</label>
                            <select class="select-box bg-slate-800 text-emerald-400 font-bold pointer-events-none" disabled>
                                @if(auth()->check())
                                    <option selected>{{ auth()->user()->full_name ?? auth()->user()->name }}</option>
                                @else
                                    <option>-- Guest / Belum Login --</option>
                                @endif
                            </select>
                            <input type="hidden" name="pilot_id" value="{{ auth()->check() ? auth()->user()->id : '' }}" required>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 label-req">Co-Pilot / Observer</label>
                            <select name="co_pilot_id" class="select-box" required>
                                <option value="">-- Pilih Co-Pilot --</option>
                                @forelse($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name ?? $user->name }}</option>
                                @empty
                                    <option value="" disabled>⚠️ Data User Kosong!</option>
                                @endforelse
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 label-req">Drone Unit</label>
                            <select name="drone_id" id="drone_id" class="select-box" required>
                                <option value="">-- Pilih Drone --</option>
                                @forelse($drones ?? [] as $drone)
                                    <option value="{{ $drone->id }}">{{ $drone->asset_name }}</option>
                                @empty
                                    <option value="" disabled>⚠️ Data Drone Kosong!</option>
                                @endforelse
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 label-req">Flight Location</label>
                            <input type="text" name="location_name_bridge" list="location_list" class="input-box" placeholder="Ketik lokasi baru atau pilih..." autocomplete="off" required>
                            <datalist id="location_list">
                                @foreach($locations ?? [] as $loc)
                                    <option value="{{ $loc->location_name }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 label-req">Purpose</label>
                            <select name="purpose" class="select-box" required>
                                <option value="patrol">Update Pekerjaan / Patroli</option>
                                <option value="documentation">Dokumentasi Acara</option>
                                <option value="mapping">Orthophoto / Pemetaan</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 label-req">Flight Mode</label>
                            <select name="flight_mode" class="select-box" required>
                                <option value="auto">Auto</option>
                                <option value="tc">T/C (Tripod/Cinema)</option>
                                <option value="pn">P/N (Positioning/Normal)</option>
                                <option value="sa">S/A (Sport/Attitude)</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-slate-300 mb-4 mt-8">Time & Coordinates</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div><label class="text-xs font-bold uppercase tracking-wider text-slate-400 label-req">Date</label><input type="date" name="date" id="flight_date" class="input-box" required></div>
                        <div><label class="text-xs font-bold uppercase text-indigo-400 label-req">Take-off Time</label><input type="time" name="takeoff_time" id="takeoff_time" step="1" class="input-box border-indigo-500/50" required></div>
                        <div><label class="text-xs font-bold uppercase text-rose-400">Landing Time</label><input type="time" name="landing_time" id="landing_time" step="1" class="input-box border-rose-500/50"></div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl mb-6 flex justify-between items-center">
                        <span class="text-sm font-semibold text-slate-400">Flight Duration:</span>
                        <span id="duration_display" class="text-xl font-extrabold text-emerald-400">0 seconds</span>
                        <input type="hidden" name="duration" id="duration_seconds" value="0">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div><label class="text-xs font-bold uppercase text-blue-400 label-req">Take Off Latitude</label><input type="text" name="takeoff_lat" id="takeoff_lat" class="input-box bg-slate-900 border-blue-500/50" readonly required></div>
                        <div><label class="text-xs font-bold uppercase text-blue-400 label-req">Take Off Longitude</label><input type="text" name="takeoff_lng" id="takeoff_lng" class="input-box bg-slate-900 border-blue-500/50" readonly required></div>
                    </div>
                    <input type="text" name="address_detail" id="address_detail" class="input-box mb-4" placeholder="Address Detail (Auto / Manual)">
                </div>
            </div>

            <div class="step-container" id="step-1">
                <div class="form-card shadow-xl shadow-emerald-500/5">
                    <h2 class="text-xl font-bold text-slate-200 mb-6 border-b border-slate-700 pb-2">2. Pre-Flight Checklist</h2>
                    <h3 class="text-base font-bold text-slate-300 uppercase mb-3">A. Hardware Inspection</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <input type="hidden" name="check_hardware[pre_motor]" value="0"><label class="checkbox-card"><input type="checkbox" name="check_hardware[pre_motor]" value="1" class="w-4 h-4 mr-3 text-emerald-500 rounded"><span class="text-sm label-req">1. Drone motors</span></label>
                        <input type="hidden" name="check_hardware[pre_propeller]" value="0"><label class="checkbox-card"><input type="checkbox" name="check_hardware[pre_propeller]" value="1" class="w-4 h-4 mr-3 text-emerald-500 rounded"><span class="text-sm label-req">2. Drone propellers</span></label>
                        <input type="hidden" name="check_hardware[pre_body]" value="0"><label class="checkbox-card"><input type="checkbox" name="check_hardware[pre_body]" value="1" class="w-4 h-4 mr-3 text-emerald-500 rounded"><span class="text-sm label-req">3. Drone airframe</span></label>
                    </div>
                    <div class="mb-6"><input type="hidden" name="check_hardware[pre_phone_battery]" value="0"><label class="checkbox-card"><input type="checkbox" name="check_hardware[pre_phone_battery]" value="1" class="w-4 h-4 mr-3 text-emerald-500 rounded"><span class="text-sm label-req">6. Phone device battery (≥ 30%)</span></label></div>

                    <h3 class="text-base font-bold text-slate-300 uppercase mb-3">Remote & Battery Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="text-xs text-slate-400 label-req">RC Serial/ID</label>
                            <select name="check_hardware[pre_remote_id]" id="rc_select" class="select-box" required>
                                <option value="">-- Pilih Drone Dulu --</option>
                            </select>
                        </div>
                        <div><label class="text-xs text-slate-400 label-req">RC Battery (%)</label><input type="number" name="check_hardware[pre_remote_percent]" class="input-box" required></div>
                        
                        <div>
                            <label class="text-xs text-slate-400 label-req">Batt Serial/ID</label>
                            <select name="check_hardware[pre_battery_id]" id="batt_select" class="select-box" required>
                                <option value="">-- Pilih Drone Dulu --</option>
                            </select>
                        </div>
                        <div><label class="text-xs text-slate-400 label-req">Drone Battery (%)</label><input type="number" name="check_hardware[pre_battery_percent]" class="input-box" required></div>
                    </div>
                    <div class="mb-6"><label class="text-xs text-slate-400 label-req">Temp (°C)</label><input type="number" name="check_hardware[pre_battery_temp]" class="input-box" required></div>

                    <h3 class="text-base font-bold text-slate-300 uppercase mb-3">B. System Functionality</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 text-sm">
                        <div><label class="text-xs font-bold text-slate-400 block mb-2 label-req">1. App Readiness</label>
                            <label><input type="checkbox" name="check_system[fungsi_aplikasi][]" value="App stable"> App stable</label><br>
                            <label><input type="checkbox" name="check_system[fungsi_aplikasi][]" value="Safe Fly DB"> Safe Fly database</label><br>
                            <label><input type="checkbox" name="check_system[fungsi_aplikasi][]" value="Firmware stable"> Firmware stable</label>
                        </div>
                        <div><label class="text-xs font-bold text-slate-400 block mb-2 label-req">2. Calibration</label>
                            <label><input type="checkbox" name="check_system[fungsi_sensor][]" value="Compass"> Compass is OK</label><br>
                            <label><input type="checkbox" name="check_system[fungsi_sensor][]" value="IMU"> IMU is OK</label><br>
                            <label><input type="checkbox" name="check_system[fungsi_sensor][]" value="ESC"> ESC is OK</label>
                        </div>
                    </div>

                    <h3 class="text-base font-bold text-slate-300 uppercase tracking-wider mb-3">4. Battery Voltage Detail</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><label class="text-xs text-slate-400 label-req">Low Cell (V)</label><input type="text" name="check_system[batt_volt_low]" class="input-box" required></div>
                        <div><label class="text-xs text-slate-400 label-req">High Cell (V)</label><input type="text" name="check_system[batt_volt_high]" class="input-box" required></div>
                        <div><label class="text-xs text-slate-400 label-req">Total Voltage</label><input type="text" name="check_system[batt_volt_t]" class="input-box" required></div>
                        <div><label class="text-xs text-slate-400 label-req">Battery Cycles</label><input type="text" name="check_system[batt_volt_c]" class="input-box" required></div>
                    </div>
                </div>
            </div>

            <div class="step-container" id="step-2">
                <div class="form-card shadow-xl shadow-emerald-500/5">
                    <h2 class="text-xl font-bold text-slate-200 mb-6 border-b border-slate-700 pb-2">3. Environment & Weather</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div><label class="text-xs text-slate-400 label-req">Temp (°C)</label><input type="number" step="0.1" name="temp_c" id="temp_c" class="input-box" required></div>
                        <div><label class="text-xs text-slate-400 label-req">Wind Speed (km/h)</label><input type="number" step="0.1" name="wind_speed" id="wind_speed" class="input-box" required></div>
                        <div><label class="text-xs text-slate-400 label-req">Humidity (%)</label><input type="number" name="check_environment[humidity]" id="humidity" class="input-box" required></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div><label class="text-xs text-slate-400 label-req">Sky Condition (API)</label><input type="text" name="check_environment[weather_api]" id="sky_condition" class="input-box" required></div>
                        <div><label class="text-xs text-slate-400 block mb-2">Visual Condition</label>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <label><input type="checkbox" name="check_environment[weather_manual][]" value="Sunny"> Sunny</label>
                                <label><input type="checkbox" name="check_environment[weather_manual][]" value="Cloudy"> Cloudy</label>
                                <label><input type="checkbox" name="check_environment[weather_manual][]" value="Overcast"> Overcast</label>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div><label class="text-xs text-slate-400 label-req">Wind Direction</label><input type="text" name="check_environment[arah_angin]" id="wind_dir" class="input-box" required></div>
                        <div><label class="text-xs text-slate-400 label-req">Precipitation (%)</label><input type="number" name="check_environment[presipitasi_percent]" id="rain_prob" class="input-box" required></div>
                    </div>
                </div>
            </div>

            <div class="step-container" id="step-3">
                <div class="form-card shadow-xl shadow-emerald-500/5">
                    <h2 class="text-xl font-bold text-slate-200 mb-6 border-b border-slate-700 pb-2">4. Safety & Compliance</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 text-sm">
                        <div><label class="text-xs font-bold text-slate-400 block mb-2">1. Pilot Health</label>
                            <label><input type="checkbox" name="check_safety[pilot][]" value="PPE"> PPE Ready</label><br>
                            <label><input type="checkbox" name="check_safety[pilot][]" value="IMSAFE"> IM SAFE Condition</label>
                        </div>
                        <div><label class="text-xs font-bold text-slate-400 block mb-2">3. Clearance</label>
                            <label><input type="checkbox" name="check_safety[permit][]" value="Supervisor"> Supervisor Approval</label><br>
                            <label><input type="checkbox" name="check_safety[permit][]" value="Owner"> Site Permission</label>
                        </div>
                    </div>
                    <div class="border-t border-slate-800 pt-4">
                        <input type="hidden" name="check_safety[notam_check]" value="0">
                        <label class="checkbox-card mb-4"><input type="checkbox" name="check_safety[notam_check]" id="notam_check" value="1" class="w-4 h-4 mr-3 text-emerald-500 rounded"><span class="text-sm font-semibold">Gunakan NOTAM</span></label>
                        <div id="notam_detail_wrapper" class="hidden"><input type="text" name="check_safety[notam_detail]" class="input-box" placeholder="Detail NOTAM"></div>
                    </div>
                </div>
            </div>

            <div class="step-container" id="step-4">
                <div class="form-card shadow-xl shadow-emerald-500/5">
                    <h2 class="text-xl font-bold text-slate-200 mb-6 border-b border-slate-700 pb-2">5. Post-Flight & Result</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div><label class="text-xs text-slate-400">Remaining RC Batt (%)</label><input type="number" name="rc_battery_finish" class="input-box"></div>
                        <div><label class="text-xs text-slate-400">Remaining Drone Batt (%)</label><input type="number" name="drone_battery_finish" class="input-box"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 mt-8 border-t border-slate-700 pt-6">
                        <div>
                            <label class="text-xs font-bold text-slate-400 label-req">Company</label>
                            <select name="requesting_company_id" class="select-box" required>
                                <option value="">-- Pilih Perusahaan --</option>
                                @forelse($companies ?? [] as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @empty
                                    <option value="" disabled>⚠️ Data Company Kosong!</option>
                                @endforelse
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-400 label-req">Department</label>
                            <select name="requesting_department_id" class="select-box" required>
                                <option value="">-- Pilih Departemen --</option>
                                @forelse($departments ?? [] as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @empty
                                    <option value="" disabled>⚠️ Data Departemen Kosong!</option>
                                @endforelse
                            </select>
                        </div>
                        
                        <div>
                            <label class="text-xs font-bold text-slate-400 label-req">PIC Requester</label>
                            <input type="text" name="pic_requester_name" class="input-box" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div><label class="text-xs font-bold text-slate-400 label-req">Result Status</label>
                            <select name="result" class="select-box" required>
                                <option value="safe_to_fly">Safe to Fly</option>
                                <option value="postpone">Postpone</option>
                                <option value="cancel">Cancel</option>
                            </select>
                        </div>
                        <div><label class="text-xs font-bold text-slate-400">Operation Notes</label><textarea name="flight_operation_notes" rows="2" class="input-box"></textarea></div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 left-0 right-0 bg-[#0b1120] border-t border-slate-800 p-3 md:p-4 shadow-[0_-15px_40px_rgba(0,0,0,0.6)] z-50">
                <div class="max-w-4xl mx-auto flex flex-col gap-3">
                    
                    <div class="flex gap-2 w-full">
                        <button type="button" id="btnFlightTimer" class="flex-1 bg-indigo-600/20 hover:bg-indigo-600/40 border border-indigo-500/50 text-indigo-400 font-bold py-2 md:py-3 rounded-xl transition flex items-center justify-center gap-2 text-xs md:text-sm">
                            <span id="timerIcon" class="text-lg">🛫</span><span id="timerText">Start Flight</span>
                        </button>
                        <button type="button" id="btnGPS" class="flex-1 bg-blue-600/20 hover:bg-blue-600/40 border border-blue-500/50 text-blue-400 font-bold py-2 md:py-3 rounded-xl transition flex items-center justify-center gap-2 text-xs md:text-sm">
                            <span class="text-lg">📍</span><span>Lock GPS</span>
                        </button>
                    </div>

                    <div class="flex gap-2 w-full">
                        <button type="button" id="btnPrev" class="hidden w-1/3 bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 rounded-xl transition text-sm">
                            ⬅️ Kembali
                        </button>
                        <button type="button" id="btnNext" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition text-sm">
                            Lanjut Step Berikutnya ➡️
                        </button>
                        <button type="submit" id="btnSubmitForm" class="hidden w-2/3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-sm py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition-all">
                            🚀 SINKRONISASI LOG
                        </button>
                    </div>

                </div>
            </div>

        </form>
    </div>

    <script>
        // --- LOGIKA DYNAMIC DROPDOWN OFFLINE (DRONE -> RC & BATT) ---
        const allSpareparts = @json($spareparts ?? []);
        const droneSelect = document.getElementById('drone_id');
        const rcSelect = document.getElementById('rc_select');
        const battSelect = document.getElementById('batt_select');

        droneSelect.addEventListener('change', function() {
            const selectedDroneId = this.value;
            
            rcSelect.innerHTML = '<option value="">-- Pilih RC --</option>';
            battSelect.innerHTML = '<option value="">-- Pilih Baterai --</option>';

            if (!selectedDroneId) return;

            const relatedParts = allSpareparts.filter(part => part.drone_id == selectedDroneId);
            let rcFound = false, battFound = false;

            relatedParts.forEach(part => {
                const type = part.sparepart_type ? part.sparepart_type.toLowerCase() : '';
                if (type.includes('remote') || type.includes('rc')) {
                    rcSelect.innerHTML += `<option value="${part.serial_number}">${part.serial_number}</option>`;
                    rcFound = true;
                }
                if (type.includes('battery') || type.includes('batt') || type.includes('baterai')) {
                    battSelect.innerHTML += `<option value="${part.serial_number}">${part.serial_number}</option>`;
                    battFound = true;
                }
            });

            if (!rcFound) rcSelect.innerHTML = '<option value="" disabled>⚠️ RC belum diinput!</option>';
            if (!battFound) battSelect.innerHTML = '<option value="" disabled>⚠️ Baterai belum diinput!</option>';
        });


        // --- LOGIKA MULTI-STEP WIZARD ---
        let currentStep = 0;
        const steps = document.querySelectorAll('.step-container');
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const btnSubmitForm = document.getElementById('btnSubmitForm');
        const stepTitle = document.getElementById('stepTitle');
        const stepCounter = document.getElementById('stepCounter');
        const progressBar = document.getElementById('progressBar');

        const stepNames = ["Flight Identity", "Pre-Flight Checklist", "Environment & Weather", "Safety & Compliance", "Post-Flight & Result"];

        function showStep(n) {
            steps.forEach((step, index) => {
                step.classList.toggle('active', index === n);
            });

            stepTitle.innerText = `Langkah ${n + 1}: ${stepNames[n]}`;
            stepCounter.innerText = `${n + 1} / ${steps.length}`;
            progressBar.style.width = `${((n + 1) / steps.length) * 100}%`;

            if (n === 0) {
                btnPrev.classList.add('hidden');
                btnNext.classList.remove('w-2/3');
                btnNext.classList.add('w-full');
            } else {
                btnPrev.classList.remove('hidden');
                btnNext.classList.remove('w-full');
                btnNext.classList.add('w-2/3');
            }

            if (n === steps.length - 1) {
                btnNext.classList.add('hidden');
                btnSubmitForm.classList.remove('hidden');
            } else {
                btnNext.classList.remove('hidden');
                btnSubmitForm.classList.add('hidden');
            }
        }

        btnNext.addEventListener('click', () => {
            const currentInputs = steps[currentStep].querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            currentInputs.forEach(input => {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    isValid = false;
                }
            });

            if (isValid && currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        btnPrev.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        showStep(currentStep);

        // --- LOGIKA TIMER & DURASI ---
        document.getElementById('flight_date').value = new Date().toISOString().split('T')[0];
        const takeoffInput = document.getElementById('takeoff_time');
        const landingInput = document.getElementById('landing_time');
        const durationDisplay = document.getElementById('duration_display');
        const durationSeconds = document.getElementById('duration_seconds');
        const btnFlightTimer = document.getElementById('btnFlightTimer');
        const timerIcon = document.getElementById('timerIcon');
        const timerText = document.getElementById('timerText');
        let isFlightActive = false;

        function getCurrentTimeString() { return new Date().toTimeString().split(' ')[0]; }
        
        function calculateDuration() {
            const start = takeoffInput.value; const end = landingInput.value;
            if (start && end) {
                const today = document.getElementById('flight_date').value;
                const startTime = new Date(`${today}T${start}`); let endTime = new Date(`${today}T${end}`);
                if (endTime < startTime) endTime.setDate(endTime.getDate() + 1); 
                const diffSeconds = Math.floor((endTime - startTime) / 1000);
                durationSeconds.value = diffSeconds;
                durationDisplay.innerText = diffSeconds < 60 ? `${diffSeconds} seconds` : `${Math.floor(diffSeconds / 60)} min ${diffSeconds % 60} sec`;
            }
        }

        btnFlightTimer.addEventListener('click', () => {
            if (!isFlightActive) {
                takeoffInput.value = getCurrentTimeString(); landingInput.value = ''; durationDisplay.innerText = "Tracking..."; durationSeconds.value = "0";
                btnFlightTimer.className = "flex-1 bg-rose-600/20 hover:bg-rose-600/40 border border-rose-500/50 text-rose-400 font-bold py-2 md:py-3 rounded-xl transition flex items-center justify-center gap-2 text-xs md:text-sm";
                timerIcon.innerText = '🛬'; timerText.innerText = 'Finish Flight'; isFlightActive = true;
            } else {
                landingInput.value = getCurrentTimeString(); calculateDuration();
                btnFlightTimer.className = "flex-1 bg-indigo-600/20 hover:bg-indigo-600/40 border border-indigo-500/50 text-indigo-400 font-bold py-2 md:py-3 rounded-xl transition flex items-center justify-center gap-2 text-xs md:text-sm";
                timerIcon.innerText = '🛫'; timerText.innerText = 'Start Flight'; isFlightActive = false;
            }
        });
        takeoffInput.addEventListener('change', calculateDuration); landingInput.addEventListener('change', calculateDuration);
        document.getElementById('notam_check').addEventListener('change', function() { document.getElementById('notam_detail_wrapper').classList.toggle('hidden', !this.checked); });

        // --- LOGIKA GPS HYBRID ---
        document.getElementById('btnGPS').addEventListener('click', () => {
            if (!navigator.geolocation) { alert('❌ Browser tidak mendukung GPS!'); return; }
            const btn = document.getElementById('btnGPS'); const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="text-sm">⏳ Mengunci...</span>';
            const gpsOptions = { enableHighAccuracy: true, timeout: 45000, maximumAge: 300000 };

            navigator.geolocation.getCurrentPosition(async (pos) => {
                const lat = pos.coords.latitude; const lng = pos.coords.longitude;
                document.getElementById('takeoff_lat').value = lat.toFixed(8); document.getElementById('takeoff_lng').value = lng.toFixed(8);
                if (navigator.onLine) {
                    try {
                        const resAddr = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
                        const addr = await resAddr.json(); if (addr.display_name) document.getElementById('address_detail').value = addr.display_name;
                        const apiKey = "1c7f474ddb2f26c8644c9c1b4c97db31";
                        const resW = await fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=${apiKey}&units=metric`);
                        const w = await resW.json();
                        if (w.main) {
                            document.getElementById('temp_c').value = w.main.temp; document.getElementById('wind_speed').value = (w.wind.speed * 3.6).toFixed(2);
                            const humidityEl = document.querySelector('input[name="check_environment[humidity]"]'); if(humidityEl) humidityEl.value = w.main.humidity;
                            const rainEl = document.querySelector('input[name="check_environment[presipitasi_percent]"]'); if(rainEl) rainEl.value = w.rain ? (w.rain["1h"] || 0) : 0;
                            const skyEl = document.querySelector('input[name="check_environment[weather_api]"]'); if(skyEl) skyEl.value = w.weather[0].description.toUpperCase();
                            const dirEl = document.querySelector('input[name="check_environment[arah_angin]"]'); if(dirEl) dirEl.value = ["N", "NE", "E", "SE", "S", "SW", "W", "NW"][Math.round(w.wind.deg / 45) % 8] + ` (${w.wind.deg}°)`;
                        }
                        alert('📍 Koordinat & Cuaca terkunci (Online)!');
                    } catch (e) { alert('📍 Koordinat terkunci! (Cuaca gagal ditarik, isi manual)'); }
                } else {
                    document.getElementById('address_detail').value = "Mode Offline";
                    const skyEl = document.querySelector('input[name="check_environment[weather_api]"]'); if(skyEl) skyEl.value = "Offline (Isi Manual)";
                    alert('📍 Koordinat Satelit terkunci (OFFLINE)!\nSilakan isi cuaca manual.');
                }
                btn.innerHTML = originalHTML;
            }, (err) => { alert('❌ Gagal mengambil lokasi GPS! Pastikan berada di luar ruangan.'); btn.innerHTML = originalHTML; }, gpsOptions);
        });

        // --- LOGIKA SUBMIT OFFLINE/SYNC ---
        document.getElementById('formFlightLog').addEventListener('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const dataEntries = Array.from(formData.entries());

            if (navigator.onLine) {
                submitToServer(formData);
            } else {
                try {
                    await saveToLocalDB(dataEntries);
                    if ('serviceWorker' in navigator && 'SyncManager' in window) {
                        const swRegistration = await navigator.serviceWorker.ready;
                        await swRegistration.sync.register('sync-flight-logs');
                        alert('📡 OFFLINE! Data disimpan di HP & akan otomatis di-upload saat sinyal kembali.');
                    } else { alert('✅ Tersimpan offline di browser.'); }
                    this.reset();
                    currentStep = 0; showStep(0);
                    document.getElementById('flight_date').value = new Date().toISOString().split('T')[0];
                } catch (error) { alert('Gagal menyimpan offline.'); }
            }
        });

        async function saveToLocalDB(dataEntries) {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open('LogDroneDB', 1);
                request.onupgradeneeded = (e) => {
                    if (!e.target.result.objectStoreNames.contains('offlineLogs')) e.target.result.createObjectStore('offlineLogs', { keyPath: 'id', autoIncrement: true });
                };
                request.onsuccess = (e) => {
                    const tx = e.target.result.transaction('offlineLogs', 'readwrite');
                    tx.objectStore('offlineLogs').add({ data: dataEntries, timestamp: new Date().toISOString() });
                    tx.oncomplete = resolve;
                };
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async function submitToServer(formData) {
            try {
                const response = await fetch('/api/sync-flight-logs', { method: 'POST', headers: { 'Accept': 'application/json' }, body: formData });
                if (response.ok) {
                    alert('🎉 Sukses! Log tersinkronisasi ke server.');
                    document.getElementById('formFlightLog').reset();
                    currentStep = 0; showStep(0);
                    document.getElementById('flight_date').value = new Date().toISOString().split('T')[0];
                } else { alert('❌ Gagal sinkronisasi. Cek data wajib.'); }
            } catch (e) { alert('Koneksi terputus. Beralih ke offline memory.'); }
        }
    </script>
</body>
</html>