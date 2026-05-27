<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Flight Mission Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 9px; 
            line-height: 1.3; 
            color: #000;
        }
        /* Header border */
        .header-container {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
            width: 100%;
        }
        .header-table {
            width: 100%;
            border: none;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .title-text {
            text-align: center;
            line-height: 1.4;
        }
        
        /* Main tables */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 4px 6px; 
            vertical-align: middle;
        }
        .label-col {
            font-weight: bold;
            width: 20%;
        }
        .val-col {
            width: 30%;
        }
        
        /* Section Header (Green) */
        .section-header { 
            background-color: #f0fdf4; 
            border: 1px solid #000; 
            border-left: 5px solid #10b981; 
            color: #064e3b;
            font-weight: bold; 
            padding: 5px 8px; 
            margin-top: 12px; 
            margin-bottom: 5px; 
            font-size: 10px;
        }

        /* Sub Headers */
        .sub-header {
            text-align: center;
            font-weight: bold;
            background-color: #f9fafb;
        }

        /* Signatures */
        .ttd-table { 
            border: none; 
            margin-top: 30px; 
            width: 100%;
        }
        .ttd-table td { 
            border: none; 
            text-align: center; 
            width: 50%;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 60%;
            margin: 0 auto;
            margin-top: 60px;
            padding-top: 4px;
        }
        
        .footer-note {
            text-align: center;
            color: #6b7280;
            font-size: 8px;
            margin-top: 20px;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    @php
        $yn = fn($val) => $val ? 'OK' : '-';

        $kamus = [
            'rc_link_connected' => 'RC Linked', 'gps_locked' => 'GPS Locked', 'video_feed_clear' => 'Video Feed Clear',
            'gps_ok' => 'GPS OK', 'microsd_inserted' => 'MicroSD Inserted', 'camera_setting_ok' => 'Camera Settings OK',
            'gimbal_clamp_removed' => 'Gimbal Clamp Removed', 'hovering_stable' => 'Hovering Stable',
            'home_point_set' => 'Home Point Saved', 'control_responsive' => 'Control Responsive',
            'rth_set' => 'RTH Ready', 'drone' => 'Drone', 'remote' => 'Remote', 'sensors' => 'Sensors', 'camera' => 'Camera',
        ];
        $humanize = fn($str) => $kamus[$str] ?? ucwords(str_replace('_', ' ', $str));
        $p = fn($val) => is_array($val) ? implode(', ', array_map($humanize, $val)) : ($val ? $humanize($val) : '-');

        // 1. CARI DATA PT
        $reqCompanyModel = $record->requestingCompany ?? $record->company ?? null;
        if (!$reqCompanyModel) {
            $companyId = $record->requesting_company_id ?? $record->company_id ?? null;
            if ($companyId && class_exists('\App\Models\Company')) {
                $reqCompanyModel = \App\Models\Company::find($companyId);
            }
        }

        $reqCompany = 'PT BUKIT BAIDURI ENERGI';
        $reqLogoPath = null;
        
        if ($reqCompanyModel) {
            $reqCompany = $reqCompanyModel->name ?? 'PT BUKIT BAIDURI ENERGI';
            // Paksa baca langsung dari array asli database (biar gak salah nama kolom)
            $attrs = $reqCompanyModel->getAttributes();
            $reqLogoPath = $attrs['logo'] ?? $attrs['company_logo'] ?? $attrs['image'] ?? $attrs['logo_path'] ?? null; 
        }

        // 2. CONVERT BASE64 & DETEKTIF PATH
        $logoBase64 = null;
        $debugInfo = "Kolom Logo di DB Kosong/NULL"; // Pesan default

        if (!empty($reqLogoPath)) {
            // Bersihkan tanda miring di awal string jika ada
            $bersihPath = ltrim($reqLogoPath, '/');
            
            // Jalur 1: Jantung Storage Laragon Master
            $path1 = storage_path('app/public/' . $bersihPath);
            // Jalur 2: Folder Symlink Public
            $path2 = public_path('storage/' . $bersihPath);

            if (file_exists($path1)) {
                $type = pathinfo($path1, PATHINFO_EXTENSION);
                $mime = strtolower($type) === 'svg' ? 'image/svg+xml' : 'image/' . $type;
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path1));
            } elseif (file_exists($path2)) {
                $type = pathinfo($path2, PATHINFO_EXTENSION);
                $mime = strtolower($type) === 'svg' ? 'image/svg+xml' : 'image/' . $type;
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path2));
            } else {
                $debugInfo = "File tidak ditemukan di: " . $path1; // Tangkap errornya!
            }
        }
    @endphp

    <div class="header-container">
        <table class="header-table">
            <tr>
                <td style="width: 25%; text-align: left;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Company Logo" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                    @else
                        <h2 style="margin:0; color:#059669; font-style:italic; letter-spacing: -1px;">
                            {{ strtoupper($reqCompany) }}
                        </h2>
                        <div style="font-size: 7px; color: red; margin-top: 5px; max-width: 150px; word-wrap: break-word;">
                            Debug: {{ $debugInfo }}<br>
                            Path DB: {{ $reqLogoPath ?? 'KOSONG' }}
                        </div>
                    @endif
                </td>
                
                <td class="title-text" style="width: 50%;">
                    <div style="font-size: 12px;">OFFICIAL REPORT</div>
                    <div style="font-size: 13px; font-weight: bold;">DRONE FLIGHT MISSION</div>
                    <div style="font-size: 11px; font-weight: bold;">{{ strtoupper($reqCompany) }}</div>
                    <div style="font-size: 9px;">LOGDRONE OPERATIONAL SYSTEM</div>
                </td>
                <td style="width: 25%;"></td>
            </tr>
        </table>
    </div>

    <div class="section-header">I. GENERAL INFORMATION & MISSION</div>
    <table>
        <tr>
            <td class="label-col">Log Number</td>
            <td class="val-col">{{ $record->id }}</td>
            <td class="label-col">Flight Date</td>
            <td class="val-col">{{ \Carbon\Carbon::parse($record->date)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label-col">Time (Start - End)</td>
            <td class="val-col">{{ $record->takeoff_time ?? '-' }} - {{ $record->landing_time ?? '-' }}</td>
            <td class="label-col">Total Duration</td>
            <td class="val-col">{{ round($record->duration / 60, 2) }} Mins</td>
        </tr>
        <tr>
            <td class="label-col">Mission Purpose</td>
            <td class="val-col">{{ $record->purpose ?? '-' }}</td>
            <td class="label-col">Flight Mode</td>
            <td class="val-col">{{ strtoupper($record->flight_mode ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label-col">Area / Location</td>
            <td colspan="3">{{ $record->flightLocation->location_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Take Off Area (RTH)</td>
            <td colspan="3">Lat: {{ $record->takeoff_lat ?? '-' }}, Lon: {{ $record->takeoff_lng ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">II. PERSONNEL & AIRCRAFT UNIT</div>
    <table>
        <tr>
            <td class="label-col">Aircraft (Model/Type)</td>
            <td class="val-col">{{ $record->drone->asset_name ?? '-' }}</td>
            <td class="label-col">Pilot in Charge (PIC)</td>
            <td class="val-col">{{ $record->pilot->full_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Aircraft Owner (Asset)</td>
            <td class="val-col">{{ $record->drone->company->name ?? '-' }}</td>
            <td class="label-col">Co-Pilot / Observer</td>
            <td class="val-col">{{ $record->coPilot->full_name ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">III. PRE & POST-FLIGHT CHECKLIST</div>
    <table>
        <tr>
            <td colspan="4" class="sub-header">A. HARDWARE & POWER</td>
        </tr>
        <tr>
            <td class="label-col">1. Drone Motors</td>
            <td class="val-col">Pre: {{ $yn($record->pre_drone_motors) }} | Post: {{ $record->is_motor_ok ? 'OK' : '-' }}</td>
            <td class="label-col">4. Remote Battery</td>
            <td class="val-col">Pre: {{ $record->rc_battery_start ?? '-' }}% | Post: {{ $record->rc_battery_end ?? '-' }}%</td>
        </tr>
        <tr>
            <td class="label-col">2. Drone Propellers</td>
            <td class="val-col">Pre: {{ $yn($record->pre_drone_propellers) }} | Post: {{ $record->is_propeller_ok ? 'OK' : '-' }}</td>
            <td class="label-col">5. Drone Battery</td>
            <td class="val-col">
                ID: {{ $record->battery_id ?? '-' }} | Temp: {{ $record->temp_c ?? '-' }}°C<br>
                Pre: {{ $record->drone_battery_start ?? '-' }}% | Post: {{ $record->drone_battery_end ?? '-' }}%
            </td>
        </tr>
        <tr>
            <td class="label-col">3. Body / Airframe</td>
            <td class="val-col">Pre: {{ $yn($record->pre_drone_airframe) }} | Post: {{ $record->is_body_ok ?? '-' ? 'OK' : '-' }}</td>
            <td class="label-col">6. Volt (Low/High)</td>
            <td class="val-col">Pre: {{ $record->total_voltage_v ?? '-' }}V | Post: -</td>
        </tr>

        <tr>
            <td colspan="4" class="sub-header">B. SYSTEM & ENVIRONMENT</td>
        </tr>
        <tr>
            <td class="label-col">GPS Status</td>
            <td class="val-col">{{ $p($record->link_gps) }}</td>
            <td class="label-col">Camera & Gimbal</td>
            <td class="val-col">{{ $p($record->media_gimbal) }}</td>
        </tr>
        <tr>
            <td class="label-col">Flight Test & RTH</td>
            <td class="val-col">{{ $p($record->flight_test ?? 'drone, remote, sensors, camera') }}</td>
            <td class="label-col">Permit & Clearance</td>
            <td class="val-col">{{ $p($record->notam_details ?? 'Manager Approval') }}</td>
        </tr>
        <tr>
            <td class="label-col">Weather & Wind</td>
            <td class="val-col">{{ $record->weather_condition ?? '-' }} | {{ $record->temp_c ?? '-' }}°C | {{ $record->wind_speed ?? '-' }} km/h</td>
            <td class="label-col">Visibility</td>
            <td class="val-col">{{ $p($record->visibility) }}</td>
        </tr>
        <tr>
            <td class="label-col">Take-off Zone</td>
            <td colspan="3">{{ $p($record->ground_safety) }}</td>
        </tr>
    </table>

    <div class="section-header">IV. FINAL RESULT & ATTACHMENTS</div>
    <table>
        <tr>
            <td class="label-col">Requesting Company</td>
            <td class="val-col">{{ $reqCompany }}</td>
            <td class="label-col">Airworthiness Status</td>
            <td class="val-col" style="text-align: center; font-weight: bold; color: #059669; font-size: 10px;">
                {{ $record->result === 'success' || $record->result === 'safe_to_fly' ? 'SAFE - SAFE TO FLY' : strtoupper(str_replace('_', ' ', $record->result)) }}
            </td>
        </tr>
        <tr>
            <td class="label-col">Requesting Dept</td>
            <td class="val-col">{{ $record->department->name ?? '-' }}</td>
            <td class="label-col" rowspan="2">Remarks / Notes</td>
            <td class="val-col" rowspan="2" style="vertical-align: top;">{{ $record->flight_operation_notes ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Requester Name</td>
            <td class="val-col">{{ $record->pic_requester_name ?? '-' }}</td>
        </tr>
    </table>

    <table class="ttd-table">
        <tr>
            <td>
                Drone Pilot (PIC)
                <div class="signature-line"></div>
                <strong>{{ $record->pilot->full_name ?? 'GWH ADMIN' }}</strong><br>
                <span style="font-size: 8px;">License: -</span>
            </td>
            <td>
                Requester / Supervisor
                <div class="signature-line"></div>
                <strong>{{ $record->pic_requester_name ?? '-' }}</strong><br>
                <span style="font-size: 8px;">{{ $reqCompany }}</span>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        This document is automatically generated by the LogDrone System on {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>