<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Flight Log Summary Report</title>
    <style>
        @page { margin: 10px; } /* Memaksimalkan kertas agar muat banyak kolom */
        body { font-family: Arial, sans-serif; font-size: 6.5px; line-height: 1.15; color: #000; }
        
        /* HEADER REKAP */
        .header-title { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px; }
        .header-title h1 { margin: 0; font-size: 12px; color: #064e3b; }
        .header-title p { margin: 1px 0 0 0; font-size: 8px; font-weight: bold; color: #374151; }
        .header-title .period { font-size: 7px; color: #4b5563; font-weight: normal; }

        /* TABEL MODEL EXCEL GRID */
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 3px 2px; vertical-align: top; word-wrap: break-word; }
        th { background-color: #f3f4f6; color: #000; font-weight: bold; text-align: center; font-size: 7px; }
        
        /* SPESIFIKASI LEBAR KOLOM (TOTAL 100% DISESUAIKAN DENGAN DETAIL BA) */
        .col-no { width: 2%; text-align: center; }
        .col-log { width: 3.5%; text-align: center; }
        .col-date { width: 5.5%; text-align: center; }
        .col-time { width: 8.5%; text-align: center; }
        .col-misi { width: 8%; }
        .col-personil { width: 11%; }
        .col-company { width: 10%; }
        .col-loc { width: 13%; }
        .col-drone { width: 8%; }
        .col-hardware { width: 9%; }
        .col-env { width: 10%; }
        .col-status { width: 6%; text-align: center; }
        .col-notes { width: 6%; }

        /* WARNA STATUS */
        .status-safe { color: #047857; font-weight: bold; text-align: center; }
        .status-warn { color: #b45309; font-weight: bold; text-align: center; }
        .status-danger { color: #b91c1c; font-weight: bold; text-align: center; }
        
        .footer { text-align: right; font-size: 5.5px; color: #6b7280; margin-top: 4px; font-style: italic; }
        .sub-info { font-size: 5.8px; color: #374151; margin-top: 1px; }
        .bold-text { font-weight: bold; color: #000; }
    </style>
</head>
<body>

    <div class="header-title">
        <h1>BERITA ACARA PENERBANGAN DRONE (REKAPITULASI TABEL)</h1>
        <p>PT BUKIT BAIDURI ENERGI - LOGDRONE OPERATIONAL SYSTEM</p>
        <div class="period">Periode Laporan: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-log">Log No</th>
                <th class="col-date">Tanggal</th>
                <th class="col-time">Waktu & Durasi</th>
                <th class="col-misi">Tujuan Misi & Mode</th>
                <th class="col-personil">Personil (PIC / Pemohon / Co-Pilot)</th>
                <th class="col-company">Perusahaan / Dept</th>
                <th class="col-loc">Area / Lokasi / RTH</th>
                <th class="col-drone">Unit Drone & Batt Drop</th>
                <th class="col-hardware">Checklist Hardware</th>
                <th class="col-env">System & Environment (Cuaca)</th>
                <th class="col-status">Status Kelayakan</th>
                <th class="col-notes">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $record)
                @php
                    // Helper: YES for true, OK/NULL, etc.
                    $yn = fn($val) => $val ? 'OK' : '-';

                    // ENGLISH AUTO-TRANSLATOR DICTIONARY 
                    $kamus = [
                        'rc_link_connected' => 'RC Linked', 'gps_locked' => 'GPS Locked', 'video_feed_clear' => 'Video Feed Clear',
                        'gps_ok' => 'GPS OK', 'microsd_inserted' => 'MicroSD Inserted', 'camera_setting_ok' => 'Camera Settings OK',
                        'gimbal_clamp_removed' => 'Gimbal Clamp Removed', 'hovering_stable' => 'Hovering Stable',
                        'home_point_set' => 'Home Point Saved', 'control_responsive' => 'Control Responsive',
                        'rth_set' => 'RTH Ready', 'drone' => 'Drone', 'remote' => 'Remote', 'sensors' => 'Sensors', 'camera' => 'Camera',
                    ];
                    $humanize = fn($str) => $kamus[$str] ?? ucwords(str_replace('_', ' ', $str));
                    
                    // HELPER UTAMA: Mengubah Array Pilihan Berbanyak menjadi String Teks (Penyelamat dari Layar Merah!)
                    $p = fn($val) => is_array($val) ? implode(', ', array_map($humanize, $val)) : ($val ? $humanize($val) : '-');

                    // 1. CARI DATA PT PEMOHON
                    $reqCompanyModel = $record->requestingCompany ?? $record->company ?? null;
                    if (!$reqCompanyModel) {
                        $companyId = $record->requesting_company_id ?? $record->company_id ?? null;
                        if ($companyId && class_exists('\App\Models\Company')) {
                            $reqCompanyModel = \App\Models\Company::find($companyId);
                        }
                    }

                    $reqCompany = 'PT BUKIT BAIDURI ENERGI';
                    if ($reqCompanyModel) {
                        $reqCompany = $reqCompanyModel->name ?? 'PT BUKIT BAIDURI ENERGI';
                    }

                    // 2. SINKRONISASI DATA WEATHER (Bungkus pakai $p untuk jaga-jaga kalau datanya array)
                    $weatherInfo = $p($record->weather ?? $record->weather_condition ?? '-');
                    if (!empty($record->temp_c)) {
                        $weatherInfo .= ' | ' . $record->temp_c . '°C';
                    }
                    if (!empty($record->wind_speed)) {
                        $weatherInfo .= ' | ' . $record->wind_speed . ' km/h';
                    }

                    // 3. TIMING & DURASI
                    $timeRange = ($record->takeoff_time ?? '-') . ' - ' . ($record->landing_time ?? '-');
                    $durationStr = $record->duration;
                    if (is_numeric($durationStr)) {
                        $durationStr = $durationStr < 60 ? $durationStr . ' Detik' : round($durationStr / 60, 1) . ' Menit';
                    }

                    // 4. CHECKLIST HARDWARE (Konversi tipe Boolean database dari angka 1/0 menjadi tulisan OK/-)
                    $motorCheck     = 'Motor: ' . ($record->pre_drone_motors || $record->is_motor_ok ? 'OK' : '-');
                    $propellerCheck = 'Prop: ' . ($record->pre_drone_propellers || $record->is_propeller_ok ? 'OK' : '-');
                    $bodyCheck      = 'Body: ' . ($record->pre_drone_airframe || $record->is_body_ok ? 'OK' : '-');
                    
                    // 5. STATUS KELAYAKAN
                    $dbStatus = strtolower($record->result ?? $record->status ?? 'success');
                    $statusClass = 'status-safe';
                    $statusText = 'SAFE - SAFE TO FLY';

                    if (in_array($dbStatus, ['cancel', 'abort', 'danger', 'aborted', 'postpone'])) {
                        $statusClass = 'status-warn';
                        $statusText = strtoupper(str_replace('_', ' ', $dbStatus));
                        if(in_array($dbStatus, ['cancel', 'abort', 'danger'])) {
                            $statusClass = 'status-danger';
                        }
                    }
                @endphp
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    
                    <td class="col-log">{{ $record->log_number ?? $record->id }}</td>
                    
                    <td class="col-date">{{ \Carbon\Carbon::parse($record->date)->format('d F Y') }}</td>
                    
                    <td class="col-time">
                        <span class="bold-text">{{ $timeRange }}</span><br>
                        <span style="color: #4b5563;">T. Durasi: {{ $durationStr }}</span>
                    </td>
                    
                    <td class="col-misi">
                        <span class="bold-text">{{ $record->purpose ?? '-' }}</span><br>
                        <span style="color: #4b5563;">Mode: {{ $record->flight_mode ?? 'T/C' }}</span>
                    </td>
                    
                    <td class="col-personil">
                        <div><span class="bold-text">PIC:</span> {{ $record->pilot->full_name ?? $record->pilot_name ?? '-' }}</div>
                        <div class="sub-info"><span class="bold-text">Mohon:</span> {{ $record->pic_requester_name ?? $record->requester_name ?? '-' }}</div>
                        <div class="sub-info"><span class="bold-text">Co-Pilot:</span> {{ $record->co_pilot_name ?? $record->observer_name ?? '-' }}</div>
                    </td>
                    
                    <td class="col-company">
                        <span class="bold-text">{{ strtoupper($record->requestingCompany->name ?? $record->company->name ?? '-') }}</span><br>
                        <span style="color: #4b5563;">Dept: {{ strtoupper($record->department->name ?? $record->department->code ?? '-') }}</span>
                    </td>
                    
                    <td class="col-loc">
                        <span class="bold-text">{{ $record->flightLocation->location_name ?? $record->location ?? '-' }}</span>
                        @if(!empty($record->latitude) && !empty($record->longitude))
                            <div style="font-size: 5.5px; color: #4b5563; margin-top: 2px;">
                                Lat: {{ $record->latitude }}, Lon: {{ $record->longitude }}
                            </div>
                        @endif
                    </td>
                    
                    <td class="col-drone">
                        <span class="bold-text">{{ $record->drone->asset_name ?? $record->drone_name ?? '-' }}</span><br>
                        <div style="margin-top: 2px; color: #b45309;">
                            Batt: {{ $record->drone_battery_start ?? '0' }}% -> {{ $record->drone_battery_end ?? '0' }}%
                        </div>
                    </td>
                    
                    <td class="col-hardware">
                        <div>• {{ $motorCheck }}</div>
                        <div>• {{ $propellerCheck }}</div>
                        <div>• {{ $bodyCheck }}</div>
                    </td>
                    
                    <td class="col-env">
                        <span class="bold-text">{{ $weatherInfo }}</span><br>
                        <div class="sub-info"><span class="bold-text">GPS:</span> {{ $p($record->link_gps ?? $record->gps_status ?? 'OK') }}</div>
                        <div class="sub-info"><span class="bold-text">Vis:</span> {{ $p($record->visibility ?? '10 Km') }}</div>
                        <div class="sub-info"><span class="bold-text">Zone:</span> {{ $p($record->ground_safety ?? '-') }}</div>
                    </td>
                    
                    <td class="col-status {{ $statusClass }}">{{ $statusText }}</td>
                    
                    <td class="col-notes">{{ $record->flight_operation_notes ?? $record->notes ?? 'sip' }}</td>
                </tr>
            @endforeach
            
            @if($records->isEmpty())
                <tr>
                    <td colspan="13" style="text-align: center; padding: 15px; color: #6b7280; font-style: italic;">
                        Tidak ada data rekapan Berita Acara pada rentang tanggal ini.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh LogDrone System pada {{ now()->timezone('Asia/Makassar')->format('d/m/Y H:i') }} WITA | Total: {{ $records->count() }} Data BA.
    </div>

</body>
</html>