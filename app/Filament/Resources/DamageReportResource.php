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
            Forms\Components\Section::make('Incident & Incident Target Information')->schema([
                Forms\Components\Select::make('asset_id')
                    ->label('Target Asset (Drone/Part)')
                    ->options(Asset::pluck('asset_name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
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

            Forms\Components\Section::make('Smart Severity Classification')
                ->description('Select the related flight log to automatically get a severity level recommendation. The result will auto-fill the Damage Severity Level field below.')
                ->schema([
                    Forms\Components\Select::make('flight_log_id')
                        ->label('Related Flight Log')
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
                                    $label = $log->date?->format('d M Y') . ' - Duration ' . ($log->duration ?? 0) . ' min';
                                    return [$log->id => $label];
                                });
                        })
                        ->searchable()
                        ->placeholder('Select a flight log...')
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
                                    ->title('Fuzzy classification failed')
                                    ->body('Temperature, battery, or duration data on this log is incomplete, or the classification server could not be reached.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $set('fuzzy_severity_label', $result['label']);
                            $set('fuzzy_severity_score', $result['score']);
                            $set('fuzzy_input_snapshot', $result['input']);

                            $mapping = [
                                'ringan' => 'minor',
                                'sedang' => 'moderate',
                                'berat' => 'major',
                            ];
                            $set('damage_severity', $mapping[$result['label']] ?? null);

                            $bodyText = $result['explanation'] ?? ('Damage Severity Level auto-filled: ' . strtoupper($result['label']) . ' (score ' . $result['score'] . '). You can still change it manually.');

                            \Filament\Notifications\Notification::make()
                                ->title('Fuzzy classification: ' . strtoupper($result['label']))
                                ->body($bodyText)
                                ->success()
                                ->duration(8000)
                                ->send();
                        }),

                    Forms\Components\Placeholder::make('fuzzy_result_display')
                        ->label('System Recommendation')
                        ->content(function (Get $get) {
                            $label = $get('fuzzy_severity_label');
                            $score = $get('fuzzy_severity_score');

                            if (!$label) {
                                return new HtmlString('<span style="color: #9ca3af;">No classification yet. Select a flight log above.</span>');
                            }

                            $color = match ($label) {
                                'ringan' => '#059669',
                                'sedang' => '#d97706',
                                'berat' => '#dc2626',
                                default => '#6b7280',
                            };

                            $labelEn = match ($label) {
                                'ringan' => 'minor',
                                'sedang' => 'moderate',
                                'berat' => 'major',
                                default => $label,
                            };

                            $html = '<span style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:6px;background:' . $color . '1a;color:' . $color . ';font-weight:600;text-transform:uppercase;font-size:13px;">';
                            $html .= strtoupper($labelEn) . ' - score ' . $score;
                            $html .= '</span><br><span style="color:#9ca3af;font-size:12px;">Auto-filled the Damage Severity Level field below.</span>';

                            return new HtmlString($html);
                        }),

                    Forms\Components\Hidden::make('fuzzy_severity_label'),
                    Forms\Components\Hidden::make('fuzzy_severity_score'),
                    Forms\Components\Hidden::make('fuzzy_input_snapshot'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Chronology & Severity Mapping')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('damage_severity')
                        ->label('Damage Severity Level')
                        ->helperText('Auto-filled from the fuzzy classification. Change it manually if your assessment differs from the system recommendation.')
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

            Forms\Components\Section::make('Workflow Status Control (Sync Master Data)')->schema([
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
                    ->label('Fuzzy Recommendation')
                    ->badge()
                    ->placeholder('-')
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