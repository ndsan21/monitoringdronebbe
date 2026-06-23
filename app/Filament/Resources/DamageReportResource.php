<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageReportResource\Pages;
use App\Models\DamageReport;
use App\Models\Asset;
use App\Models\FlightLocation;
use App\Models\FlightLog;
use App\Services\FuzzyClassifierService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

class DamageReportResource extends Resource
{
    protected static ?string $model = DamageReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Log Operasional';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- SECTION 1: INCIDENT & TARGET INFORMATION ---
            Forms\Components\Section::make('💥 Incident & Incident Target Information')->schema([
                Forms\Components\Select::make('asset_id')
                    ->label('Target Asset (Drone/Part)')
                    ->options(Asset::pluck('asset_name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        // Reset flight log & hasil fuzzy saat asset diganti
                        $set('flight_log_id', null);
                        $set('fuzzy_severity_label', null);
                        $set('fuzzy_severity_score', null);
                    })
                    ->required(),

                Forms\Components\Select::make('reported_by_id')
                    ->label('Reported By')
                    ->relationship('reportedBy', 'name')
                    ->default(fn() => auth()->id())
                    ->required(),

                Forms\Components\DatePicker::make('report_date')
                    ->label('Report Date')
                    ->default(now())
                    ->required(),
            ])->columns(3),

            // --- SECTION 1B: FUZZY CLASSIFICATION ---
            Forms\Components\Section::make('🧠 Klasifikasi Cerdas (Logika Fuzzy)')
                ->description('Pilih log penerbangan terkait untuk mendapatkan rekomendasi tingkat keparahan secara otomatis. Hasilnya akan otomatis mengisi "Damage Severity Level" di bawah.')
                ->schema([
                    Forms\Components\Select::make('flight_log_id')
                        ->label('Log Penerbangan Terkait')
                        ->options(function (Get $get) {
                            $assetId = $get('asset_id');
                            if (!$assetId) {
                                return [];
                            }
                            return FlightLog::where('drone_id', $assetId)
                                ->orderByDesc('date')
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(function ($log) {
                                    $label = $log->date?->format('d M Y') . ' — Durasi ' . ($log->duration ?? 0) . ' menit';
                                    return [$log->id => $label];
                                });
                        })
                        ->searchable()
                        ->placeholder('Pilih log penerbangan...')
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            if (!$state) {
                                $set('fuzzy_severity_label', null);
                                $set('fuzzy_severity_score', null);
                                return;
                            }

                            $flightLog = FlightLog::find($state);

                            if (!$flightLog) {
                                return;
                            }

                            $service = app(FuzzyClassifierService::class);
                            $result = $service->classifyFromFlightLog($flightLog);

                            if ($result === null) {
                                $set('fuzzy_severity_label', null);
                                $set('fuzzy_severity_score', null);
                                \Filament\Notifications\Notification::make()
                                    ->title('Klasifikasi fuzzy gagal')
                                    ->body('Data suhu/baterai/durasi pada log ini tidak lengkap, atau server klasifikasi tidak bisa dihubungi.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $set('fuzzy_severity_label', $result['label']);
                            $set('fuzzy_severity_score', $result['score']);
                            $set('fuzzy_input_snapshot', $result['input']);

                            // Auto-fill damage_severity sesuai hasil fuzzy (tetap bisa diubah manual)
                            $mapping = [
                                'ringan' => 'minor',
                                'sedang' => 'moderate',
                                'berat' => 'major',
                            ];
                            $set('damage_severity', $mapping[$result['label']] ?? null);

                            \Filament\Notifications\Notification::make()
                                ->title('Klasifikasi fuzzy berhasil')
                                ->body('Damage Severity Level otomatis diisi: ' . strtoupper($result['label']) . ' (skor ' . $result['score'] . '). Anda tetap bisa mengubahnya secara manual jika perlu.')
                                ->success()
                                ->send();
                        }),

                    Forms\Components\Placeholder::make('fuzzy_result_display')
                        ->label('Hasil Rekomendasi Sistem')
                        ->content(function (Get $get) {
                            $label = $get('fuzzy_severity_label');
                            $score = $get('fuzzy_severity_score');

                            if (!$label) {
                                return new HtmlString('<span style="color: #9ca3af;">Belum ada klasifikasi. Pilih log penerbangan di atas.</span>');
                            }

                            $color = match ($label) {
                                'ringan' => '#059669',
                                'sedang' => '#d97706',
                                'berat' => '#dc2626',
                                default => '#6b7280',
                            };

                            return new HtmlString(
                                '<span style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:6px;background:' . $color . '1a;color:' . $color . ';font-weight:600;text-transform:uppercase;font-size:13px;">'
                                . strtoupper($label) . ' &middot; skor ' . $score
                                . '</span><br><span style="color:#9ca3af;font-size:12px;">Sudah otomatis mengisi "Damage Severity Level" di bawah.</span>'
                            );
                        }),

                    // Kolom tersembunyi yang sebenarnya menyimpan hasil fuzzy ke database
                    Forms\Components\Hidden::make('fuzzy_severity_label'),
                    Forms\Components\Hidden::make('fuzzy_severity_score'),
                    Forms\Components\Hidden::make('fuzzy_input_snapshot'),
                ])
                ->columns(2),

            // --- SECTION 2: CHRONOLOGY & SEVERITY MAPPING ---
            Forms\Components\Section::make('📋 Chronology & Severity Mapping')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('damage_severity')
                        ->label('Damage Severity Level')
                        ->helperText('Otomatis terisi dari klasifikasi fuzzy. Ubah manual jika penilaian Anda berbeda dari rekomendasi sistem.')
                        ->options(['minor' => 'Minor', 'moderate' => 'Moderate', 'major' => 'Major Level'])
                        ->required(),

                    Forms\Components\DatePicker::make('incident_date')->label('Incident Date')->required(),
                    Forms\Components\TextInput::make('incident_time')->label('Incident Time (HH:MM)')->placeholder('e.g., 14:20')->required(),
                ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('incident_location_name')->label('Specific Location/Area String Name')->placeholder('e.g., Pit A Block North')->required(),
                    Forms\Components\Select::make('incident_location_id')
                        ->label('Link Mapping Flight Location (Optional)')
                        ->options(FlightLocation::pluck('location_name', 'id'))
                        ->searchable()
                        ->preload(),
                ]),

                Forms\Components\Textarea::make('chronology')->label('Full Chronology & Incident Details')->rows(3)->required(),
            ]),

            // --- SECTION 3: WORKFLOW STATUS CONTROL ---
            Forms\Components\Section::make('🔧 Workflow Status Control (Sync Master Data)')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('current_status')
                        ->label('Repair Progress Status')
                        ->options(['reported' => 'Reported', 'on_progress' => 'On Progress', 'resolved' => 'Resolved'])
                        ->required(),

                    Forms\Components\Select::make('condition_status')
                        ->label('Condition Category Status')
                        ->options([
                            'damaged_replace' => 'Damaged / Needs Replace',
                            'out_of_service' => 'Out of Service'
                        ])
                        ->required(),
                ]),

                Forms\Components\Textarea::make('note')
                    ->label('Safety Special Notes / Remarks')
                    ->rows(2),

                Forms\Components\FileUpload::make('evidences')
                    ->label('Incident Evidence Photos')
                    ->multiple() 
                    ->image()
                    ->directory('damage-evidences'),
            ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('asset.asset_name')->label('Asset Name')->sortable(),
                Tables\Columns\TextColumn::make('damage_severity')
                    ->label('Severity')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'minor' => 'info', 
                        'moderate' => 'warning', 
                        'major' => 'danger'
                    }),
                Tables\Columns\TextColumn::make('fuzzy_severity_label')
                    ->label('Rekomendasi Fuzzy')
                    ->badge()
                    ->placeholder('—')
                    ->color(fn($state) => match($state) {
                        'ringan' => 'success',
                        'sedang' => 'warning',
                        'berat' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('current_status')->label('Progress')->badge(),
                Tables\Columns\TextColumn::make('incident_date')->label('Incident Date')->date(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    
                    Tables\Actions\Action::make('print_ba')
                        ->label('Print BA')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('danger')
                        ->action(function ($record) {
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.damage-report-ba', ['record' => $record]);
                            $pdf->setPaper('A4', 'portrait');
                            
                            $fileName = 'Official-Report-Damage-' . $record->id . '.pdf';
                            return response()->streamDownload(fn () => print($pdf->output()), $fileName);
                        }),

                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning'),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Delete'),
                        
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDamageReports::route('/'),
        ];
    }
}