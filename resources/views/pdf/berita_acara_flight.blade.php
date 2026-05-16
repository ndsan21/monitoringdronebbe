<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Penerbangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .heading { text-align: center; font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table td { padding: 8px; border: 1px solid #ddd; }
        .table td.label { font-weight: bold; background-color: #f9f9f9; width: 30%; }
    </style>
</head>
<body>
    <div class="heading">Berita Acara Penerbangan Drone</div>
    <p>Sistem mencatat dokumen penerbangan log formal dengan data validasi sebagai berikut:</p>
    
    <table class="table">
        <tr>
            <td class="label">Tanggal Log</td>
            <td>{{ $log->date ? $log->date->format('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pilot Pelapor</td>
            <td>{{ $log->pilot->full_name ?? 'N/A' }} (NIK: {{ $log->pilot->employee_id ?? '-' }})</td>
        </tr>
        <tr>
            <td class="label">Drone Perangkat</td>
            <td>{{ $log->drone->brand ?? 'DJI' }} - {{ $log->drone->model ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi Operasional</td>
            <td>{{ $log->flightLocation->location_name ?? $log->flight_area_name }}</td>
        </tr>
        <tr>
            <td class="label">Durasi Terbang</td>
            <td>{{ $log->duration ?? 0 }} Detik</td>
        </tr>
        <tr>
            <td class="label">Hasil Akhir</td>
            <td><strong>{{ strtoupper($log->result ?? 'postpone') }}</strong></td>
        </tr>
    </table>
</body>
</html>