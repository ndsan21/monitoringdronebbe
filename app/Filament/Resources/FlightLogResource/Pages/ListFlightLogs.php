<?php

namespace App\Filament\Resources\FlightLogResource\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\FlightLog;
use App\Exports\FlightLogsMasterExport;
use Filament\Notifications\Notification;


class ListFlightLogs extends ListRecords
{
    protected static string $resource = FlightLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ⚡ TOMBOL REKAP SUPER NATIVE (TIDAK PERLU ROUTE/WEB.PHP LAGI!)
            Actions\Action::make('recap_report')
                ->label('Flight Recap Report')
                ->icon('heroicon-m-document-chart-bar')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('start_date')
                        ->label('From Date')
                        ->default(now()->startOfMonth())
                        ->required(),
                        
                    \Filament\Forms\Components\DatePicker::make('end_date')
                        ->label('To Date')
                        ->default(now())
                        ->required(),
                        
                    \Filament\Forms\Components\Select::make('export_format')
                        ->label('Export Format')
                        ->options([
                            'pdf' => 'PDF Document (Booklet Layout)',
                            'excel' => 'Excel Spreadsheet (Master Data Table)',
                        ])
                        ->default('pdf')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $startDate = $data['start_date'];
                    $endDate = $data['end_date'];

                    // 1. Tarik Data
                    $records = FlightLog::whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'asc')
                        ->get();

                    // 2. Cek kalau datanya kosong
                    if ($records->isEmpty()) {
                        Notification::make()
                            ->warning()
                            ->title('Data Kosong')
                            ->body("Tidak ada data penerbangan dari tanggal {$startDate} sampai {$endDate}.")
                            ->send();
                        return;
                    }

                    // 3. JALUR EXCEL
                    // 3. JALUR EXCEL (VERSI INLINE - TANPA PERLU FILE EXPORTS EXTERNAL)
                    // 3. JALUR EXCEL (VERSI INLINE FULL DATABASE & AUTO-WIDTH)
                    if ($data['export_format'] === 'excel') {
                        
                        // ⚡ 1. HEADINGS: Definisikan seluruh kolom database persis seperti isi BA Lengkap
                        $headings = [
                            'No Log', 'Tanggal', 'Jam Mulai', 'Jam Selesai', 'Total Durasi', 
                            'Tujuan Misi', 'Flight Mode', 'Pilot (PIC)', 'Co-Pilot / Observer', 
                            'Nama Pemohon', 'Perusahaan Pemohon', 'Departemen', 'Area / Lokasi', 
                            'Unit Drone', 'Battery Start (%)', 'Battery End (%)', 
                            'Checklist Motor', 'Checklist Propeller', 'Checklist Body', 
                            'Kondisi Cuaca', 'Suhu (C)', 'Status Kelayakan', 'Catatan Operation'
                        ];
                        
                        $exportData = [];
                        foreach ($records as $index => $row) {
                            // Helper pembongkar data kaku
                            $p = fn($val) => is_array($val) ? implode(', ', $val) : ($val ?: '-');
                            
                            // Bersihkan format Jam Mulai & Jam Selesai jika membawa teks tanggal panjang
                            $cleanTakeoff = $row->takeoff_time;
                            if (strlen($cleanTakeoff) > 8) { $cleanTakeoff = date('H:i:s', strtotime($cleanTakeoff)); }
                            
                            $cleanLanding = $row->landing_time;
                            if (strlen($cleanLanding) > 8) { $cleanLanding = date('H:i:s', strtotime($cleanLanding)); }

                            // Format Durasi Menit/Detik
                            $durationStr = $row->duration;
                            if (is_numeric($durationStr)) {
                                $durationStr = $durationStr < 60 ? $durationStr . ' Detik' : round($durationStr / 60, 1) . ' Menit';
                            }
                            
                            // Penentuan teks Status Kelayakan
                            $dbStatus = strtolower($row->result ?? $row->status ?? 'success');
                            $statusText = 'SAFE - SAFE TO FLY';
                            if (in_array($dbStatus, ['cancel', 'abort', 'danger', 'aborted'])) {
                                $statusText = 'ABORTED / DANGER';
                            } elseif ($dbStatus === 'postpone') {
                                $statusText = 'POSTPONE';
                            }

                            // ⚡ 2. MAPPING DATA: Masukkan seluruh isi field database Master
                            $exportData[] = [
                                $row->log_number ?? $row->id,
                                $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y') : '-',
                                $cleanTakeoff ?? '-',
                                $cleanLanding ?? '-',
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

                        // ⚡ 3. CLASS ANONIM DENGAN MANTRALAN CONCERNS AUTO-SIZE LEBAR KOLOM EXCEL
                        $inlineExport = new class($exportData, $headings) implements 
                            \Maatwebsite\Excel\Concerns\FromCollection, 
                            \Maatwebsite\Excel\Concerns\WithHeadings,
                            \Maatwebsite\Excel\Concerns\ShouldAutoSize // Mantra pengatur jarak otomatis!
                        {
                            protected $data; protected $headings;
                            public function __construct($data, $headings) { $this->data = collect($data); $this->headings = $headings; }
                            public function collection() { return $this->data; }
                            public function headings(): array { return $this->headings; }
                        };

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            $inlineExport, 
                            "Flight_Logs_Recap_{$startDate}_to_{$endDate}.xlsx"
                        );
                    }

                    // 4. JALUR PDF (Menggunakan Barryvdh DOMPDF)
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.flight-log-recap-booklet', [
                        'records' => $records,
                        'startDate' => $startDate,
                        'endDate' => $endDate
                    ])->setPaper('A4', 'landscape');

                    // Langsung muntahkan filenya tanpa pindah halaman!
                    return response()->streamDownload(
                        fn () => print($pdf->output()), 
                        "Flight_Logs_Recap_{$startDate}_to_{$endDate}.pdf"
                    );
                }),

            // Tombol Default Create
            Actions\CreateAction::make(),
        ];
    }
}