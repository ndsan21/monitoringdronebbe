<!DOCTYPE html>
<html>
<head>
    <title>Berita Acara Kerusakan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .no-border, .no-border td { border: none !important; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .section-title { font-weight: bold; background-color: #fee2e2; padding: 4px; border: 1px solid #000; margin-top: 10px; }
    </style>
</head>
<body>
    @php
        $logoPath = $record->asset->company->photo_path ?? null;
        $logoSrc = $logoPath ? storage_path('app/public/' . $logoPath) : ''; 
        $companyName = $record->asset->company->name ?? 'PT BUKIT BAIDURI ENERGI';
    @endphp

    <table class="no-border" style="margin-bottom: 20px;">
        <tr>
            <td style="width: 20%;">
                @if($logoSrc && file_exists($logoSrc))
                    <img src="{{ $logoSrc }}" style="max-height: 60px;">
                @else
                    <h2>LOGO</h2>
                @endif
            </td>
            <td style="width: 80%; text-align: center;">
                <div class="header-title">BERITA ACARA KERUSAKAN</div>
                <div class="header-title">DRONE & EQUIPMENT</div>
                <div style="font-size: 14px;">{{ $companyName }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">I. INFORMASI LAPORAN</div>
    <table>
        <tr>
            <td style="width: 25%; font-weight: bold;">ID Laporan</td><td style="width: 25%;">DMG-{{ sprintf('%04d', $record->id) }}</td>
            <td style="width: 25%; font-weight: bold;">Tgl Laporan</td><td style="width: 25%;">{{ \Carbon\Carbon::parse($record->report_date)->format('d F Y, H:i') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Unit Aset</td><td>{{ $record->asset->asset_name ?? '-' }}</td>
            <td style="font-weight: bold;">Pelapor</td><td>{{ $record->reporter->full_name ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">II. DETAIL KERUSAKAN</div>
    <table>
        <tr>
            <td style="width: 25%; font-weight: bold;">Tingkat Kerusakan</td>
            <td style="width: 25%; color: red; font-weight: bold;">{{ strtoupper($record->damage_severity ?? '-') }}</td>
            <td style="width: 25%; font-weight: bold;">Status Saat Ini</td>
            <td style="width: 25%;">{{ strtoupper($record->current_status ?? '-') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Lokasi Insiden</td><td colspan="3">{{ $record->incident_location_name ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="4" style="font-weight: bold; background-color: #f9f9f9;">Kronologi / Deskripsi:</td>
        </tr>
        <tr>
            <td colspan="4" style="min-height: 100px;">{{ $record->chronology ?? '-' }}</td>
        </tr>
    </table>

    <div style="margin-top: 40px;">
        <div style="width: 50%; float: left; text-align: center;">
            <p>Dilaporkan Oleh,</p><p>Pilot / Reporter</p><br><br><br>
            <p><strong>{{ $record->reporter->full_name ?? '_______________' }}</strong></p>
        </div>
        <div style="width: 50%; float: left; text-align: center;">
            <p>Diterima Oleh,</p><p>Supervisor Operasional</p><br><br><br>
            <p><strong>_____________________</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>