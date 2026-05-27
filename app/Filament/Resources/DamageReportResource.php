<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageReportResource\Pages;
use App\Models\DamageReport;
use App\Models\Asset;
use App\Models\FlightLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

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

            // --- SECTION 2: CHRONOLOGY & SEVERITY MAPPING ---
            Forms\Components\Section::make('📋 Chronology & Severity Mapping')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('damage_severity')
                        ->label('Damage Severity Level')
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
                Tables\Columns\TextColumn::make('current_status')->label('Progress')->badge(),
                Tables\Columns\TextColumn::make('incident_date')->label('Incident Date')->date(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    
                    // 🎯 FIX MUTLAK: Merubah 'pdf.damage-report' menjadi 'pdf.damage-report-ba' sesuai file fisik Master
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