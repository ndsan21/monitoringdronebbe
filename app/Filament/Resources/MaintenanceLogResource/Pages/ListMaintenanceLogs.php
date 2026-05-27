<?php

namespace App\Filament\Resources\MaintenanceLogResource\Pages;

use App\Filament\Resources\MaintenanceLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\MaintenanceLog; 
use Filament\Notifications\Notification;

class ListMaintenanceLogs extends ListRecords
{
    protected static string $resource = MaintenanceLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('maintenance_recap')
                ->label('Maintenance Recap Report')
                ->icon('heroicon-m-wrench-screwdriver')
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
                            'ba' => 'PDF Document (Official Report - Portrait)',
                            'pdf_table' => 'PDF Document (Excel Table Summary - Landscape)',
                            'excel' => 'Excel Spreadsheet (Auto-Width Table)',
                        ])
                        ->default('ba')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $startDate = $data['start_date'];
                    $endDate = $data['end_date'];

                    // 1. Fetch Maintenance records with eager loading
                    $records = MaintenanceLog::with(['asset', 'technician'])
                        ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                        ->orderBy('created_at', 'asc')
                        ->get();

                    if ($records->isEmpty()) {
                        Notification::make()
                            ->warning()
                            ->title('Data Empty')
                            ->body("No maintenance logs found from {$startDate} to {$endDate}.")
                            ->send();
                        return;
                    }

                    // 🟢 JALUR A: EXCEL SPREADSHEET (SUPER RADAR DATA_GET & ID CONVERTER)
                    if ($data['export_format'] === 'excel') {
                        $headings = [
                            'Doc No / ID', 'Date', 'Drone Unit', 'Serial Number', 
                            'Technician', 'Type', 'Issue / Chronology', 
                            'Action Taken / Note', 'Replaced Parts', 'Status'
                        ];
                        
                        $exportData = [];
                        foreach ($records as $row) {
                            // Extract hardwareItems repeater data
                            $items = $row->hardwareItems ?? $row->hardware_items ?? [];
                            if (is_string($items)) { $items = json_decode($items, true); }

                            $replacedParts = [];
                            $chronologies = [];
                            $actions = [];

                            if (!empty($items) && (is_array($items) || is_object($items))) {
                                foreach ($items as $itemIndex => $item) {
                                    // 🎯 Fetch Component ID or Name
                                    $compVal = data_get($item, 'component') ?? data_get($item, 'component_id') ?? data_get($item, 'asset_id');
                                    $compName = null;
                                    
                                    if (!empty($compVal)) {
                                        if (is_numeric($compVal)) {
                                            $fetchedAsset = \App\Models\Asset::find($compVal);
                                            $compName = $fetchedAsset ? ($fetchedAsset->asset_name ?? $fetchedAsset->name) : null;
                                        } else {
                                            $compName = $compVal;
                                        }
                                    }
                                    $compName = $compName ?? 'Component #' . ($itemIndex + 1);

                                    // Fetch condition, chronology, and note using data_get for maximum safety
                                    $cond = strtolower(data_get($item, 'condition', 'good'));
                                    
                                    $chrono = data_get($item, 'chronologyDetails') 
                                        ?? data_get($item, 'chronology_details') 
                                        ?? data_get($item, 'chronology') 
                                        ?? '';
                                    if (!empty($chrono)) { 
                                        $chronologies[] = strtoupper($compName) . ': ' . $chrono; 
                                    }
                                    
                                    $act = data_get($item, 'note') ?? data_get($item, 'notes') ?? '';
                                    if (!empty($act)) { 
                                        $actions[] = strtoupper($compName) . ': ' . $act; 
                                    }

                                    // Parse replacement parts if component is broken
                                    if (str_contains($cond, 'damage') || str_contains($cond, 'replace') || $cond === 'out of service') {
                                        $part = data_get($item, 'replaceWithPart') ?? data_get($item, 'replace_with_part') ?? data_get($item, 'replace_part') ?? '';
                                        
                                        $compStr = strtoupper(str_replace('_', ' ', $compName));
                                        if (!empty($part) && !str_contains(strtolower($part), 'select')) {
                                            if (is_numeric($part)) {
                                                $repAsset = \App\Models\Asset::find($part);
                                                $partStr = $repAsset ? ($repAsset->asset_name ?? $repAsset->name) : $part;
                                            } else {
                                                $partStr = $part;
                                            }
                                            $replacedParts[] = $compStr . ' -> ' . strtoupper($partStr);
                                        } else {
                                            $replacedParts[] = $compStr . ' (DAMAGED)';
                                        }
                                    }
                                }
                            }

                            // Fallback to main record strings if repeater fields are empty
                            $issueText = !empty($chronologies) ? implode('; ', $chronologies) : ($row->issue_description ?? $row->description ?? 'Routine maintenance check.');
                            $actionText = !empty($actions) ? implode('; ', $actions) : ($row->action_taken ?? $row->notes ?? 'Inspected. All components functional.');
                            $partText = !empty($replacedParts) ? implode(', ', $replacedParts) : 'None';

                            $dbStatus = strtolower($row->status ?? 'completed');
                            $finalStatus = in_array($dbStatus, ['ready', 'completed', 'safe', 'safe_to_fly']) ? 'READY TO FLY' : 'GROUNDED';

                            $exportData[] = [
                                $row->maintenance_number ?? 'MAIN-'.$row->id,
                                $row->created_at ? $row->created_at->format('d/m/Y') : '-',
                                $row->asset->asset_name ?? '-',
                                $row->asset->serial_number ?? '-',
                                $row->technician->name ?? $row->technician_name ?? '-',
                                strtoupper(str_replace('_', ' ', $row->maintenance_type ?? 'Routine')),
                                $issueText,
                                $actionText,
                                $partText,
                                $finalStatus
                            ];
                        }

                        // Create anonymous class for Excel styling & Auto-width layout
                        $inlineExport = new class($exportData, $headings) implements 
                            \Maatwebsite\Excel\Concerns\FromCollection, 
                            \Maatwebsite\Excel\Concerns\WithHeadings,
                            \Maatwebsite\Excel\Concerns\ShouldAutoSize 
                        {
                            protected $data; protected $headings;
                            public function __construct($data, $headings) { $this->data = collect($data); $this->headings = $headings; }
                            public function collection() { return $this->data; }
                            public function headings(): array { return $this->headings; }
                        };

                        return \Maatwebsite\Excel\Facades\Excel::download($inlineExport, "Maintenance_Recap_{$startDate}_to_{$endDate}.xlsx");
                    }

                    // 🔴 JALUR B: OFFICIAL REPORT BA (PDF PORTRAIT)
                    if ($data['export_format'] === 'ba') {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.maintenance-log-ba', [
                            'records'   => $records,
                            'startDate' => $startDate,
                            'endDate'   => $endDate
                        ])->setPaper('A4', 'portrait');

                        return response()->streamDownload(fn () => print($pdf->output()), "Official_Report_Maintenance_{$startDate}_to_{$endDate}.pdf");
                    }

                    // 🔵 JALUR C: TABEL SUMMARY REKAP (PDF LANDSCAPE)
                    if ($data['export_format'] === 'pdf_table') {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.maintenance-log-recap', [
                            'records'   => $records,
                            'startDate' => $startDate,
                            'endDate'   => $endDate
                        ])->setPaper('A4', 'landscape');

                        return response()->streamDownload(fn () => print($pdf->output()), "Summary_Table_Maintenance_{$startDate}_to_{$endDate}.pdf");
                    }
                }),

            Actions\CreateAction::make()->label('New Maintenance Log'),
        ];
    }
}