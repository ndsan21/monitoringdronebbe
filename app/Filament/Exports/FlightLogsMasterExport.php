<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FlightLogsMasterExport implements FromCollection, WithHeadings, WithMapping
{
    protected $records;

    // Menangkap data lemparan dari Filament
    public function __construct($records)
    {
        $this->records = $records;
    }

    // Mengembalikan koleksi data untuk diisi ke Excel
    public function collection()
    {
        return $this->records;
    }

    // ⚡ DEKLARASI JUDUL KOLOM EXCEL (Persis data Berita Acara Master)
    public function headings(): array
    {
        return [
            'No Log',
            'Tanggal',
            'Jam Mulai',
            'Jam Selesai',
            'Total Durasi',
            'Tujuan Misi',
            'Flight Mode',
            'Pilot (PIC)',
            'Co-Pilot / Observer',
            'Nama Pemohon',
            'Perusahaan Pemohon',
            'Departemen',
            'Lokasi / Area',
            'Unit Drone',
            'Battery Start (%)',
            'Battery End (%)',
            'Checklist Motor',
            'Checklist Propeller',
            'Checklist Body',
            'Kondisi Cuaca',
            'Suhu (C)',
            'Status Kelayakan',
            'Catatan Operation'
        ];
    }

    // ⚡ MAPPING: Menyuntikkan nilai riil database Master ke baris Excel
    public function map($row): array
    {
        // Helper pembongkar array / boolean agar keluar tulisan OK atau teks rapi
        $p = fn($val) => is_array($val) ? implode(', ', $val) : ($val ?: '-');
        $yn = fn($val) => $val ? 'OK' : '-';

        // Durasi konversi menit
        $durationStr = $row->duration;
        if (is_numeric($durationStr)) {
            $durationStr = $durationStr < 60 ? $durationStr . ' Detik' : round($durationStr / 60, 1) . ' Menit';
        }

        // Penentuan status kelayakan
        $dbStatus = strtolower($row->result ?? $row->status ?? 'success');
        $statusText = 'SAFE TO FLY';
        if (in_array($dbStatus, ['cancel', 'abort', 'danger', 'aborted'])) {
            $statusText = 'ABORTED / DANGER';
        } elseif ($dbStatus === 'postpone') {
            $statusText = 'POSTPONE';
        }

        return [
            $row->log_number ?? $row->id,
            $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y') : '-',
            $row->takeoff_time ?? '-',
            $row->landing_time ?? '-',
            $durationStr,
            $row->purpose ?? '-',
            strtoupper($row->flight_mode ?? 'T/C'),
            $row->pilot->full_name ?? $row->pilot_name ?? '-',
            $row->co_pilot_name ?? $row->observer_name ?? '-',
            $row->pic_requester_name ?? $row->requester_name ?? '-',
            strtoupper($row->requestingCompany->name ?? $row->company->name ?? '-'),
            strtoupper($row->department->name ?? $row->department->code ?? '-'),
            $row->flightLocation->location_name ?? $row->location ?? '-',
            $row->drone->asset_name ?? $row->drone_name ?? '-',
            ($row->drone_battery_start ?? '0') . '%',
            ($row->drone_battery_end ?? '0') . '%',
            $row->pre_drone_motors || $row->is_motor_ok ? 'OK' : '-',
            $row->pre_drone_propellers || $row->is_propeller_ok ? 'OK' : '-',
            $row->pre_drone_airframe || $row->is_body_ok ? 'OK' : '-',
            $p($row->weather ?? $row->weather_condition ?? '-'),
            $row->temp_c ? $row->temp_c . '°C' : '-',
            $statusText,
            $row->flight_operation_notes ?? $row->notes ?? '-'
        ];
    }
}