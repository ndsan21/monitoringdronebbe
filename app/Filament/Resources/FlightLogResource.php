<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightLogResource\Pages;
use App\Models\FlightLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\Carbon;

class FlightLogResource extends Resource
{
    protected static ?string $model = FlightLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationGroup = 'Log Operasional';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- 1. IDENTITY & TIME ---
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Section::make('Flight Identity')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Select::make('pilot_id')
                            ->relationship('pilot', 'full_name')
                            ->label('Pilot')
                            ->default(fn () => auth()->id())
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\Select::make('co_pilot_id')->relationship('coPilot', 'full_name')->searchable()->required(),
                        Forms\Components\Select::make('drone_id')->relationship('drone', 'model')->required(),
                        
                        Forms\Components\TextInput::make('location_name_bridge')
                            ->label('Flight Location')
                            ->placeholder('Ketik lokasi... (Otomatis tersimpan jika baru)')
                            ->datalist(fn () => \App\Models\FlightLocation::query()
                                ->whereNotNull('location_name')
                                ->where('location_name', '!=', '')
                                ->distinct()
                                ->pluck('location_name')
                                ->toArray()
                            )
                            ->required()
                            ->formatStateUsing(fn ($record) => $record?->flightLocation?->location_name)
                            ->dehydrateStateUsing(function ($state, Set $set) {
                                if (!empty($state)) {
                                    $location = \App\Models\FlightLocation::firstOrCreate([
                                        'location_name' => $state,
                                    ], [
                                        'company_id' => \App\Models\Company::first()?->id ?? 1,
                                    ]);
                                    $set('flight_location_id', $location->id);
                                }
                                return null;
                            }),
                        
                        Forms\Components\Hidden::make('flight_location_id'),

                        Forms\Components\Select::make('purpose')
                            ->options([
                                'patrol' => 'Update Pekerjaan / Patroli',
                                'documentation' => 'Dokumentasi Acara',
                                'mapping' => 'Orthophoto / Pemetaan'
                            ])->required(),
                        Forms\Components\Select::make('flight_mode')
                            ->options([
                                'auto' => 'Auto',
                                'tc' => 'T/C (Tripod/Cinema)',
                                'pn' => 'P/N (Positioning/Normal)',
                                'sa' => 'S/A (Sport/Attitude)'
                            ])->required(),
                    ]),

                Forms\Components\Section::make('Time & Coordinates')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\DatePicker::make('date')->default(now())->required(),
                        
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TimePicker::make('takeoff_time')
                                ->label('Take-off Time')
                                ->seconds()
                                ->format('H:i:s')
                                ->displayFormat('H:i:s')
                                ->live()
                                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateDuration($set, $get))
                                ->required(),
                            Forms\Components\TimePicker::make('landing_time')
                                ->label('Landing Time')
                                ->seconds()
                                ->format('H:i:s')
                                ->displayFormat('H:i:s')
                                ->live()
                                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateDuration($set, $get)),
                        ]),

                        Forms\Components\Placeholder::make('duration_display')
                            ->label('Flight Duration')
                            ->content(function (Get $get) {
                                $seconds = (int) ($get('duration') ?? 0);
                                
                                if ($seconds <= 0) return '0 seconds';
                                if ($seconds < 60) return "{$seconds} seconds";

                                if ($seconds < 3600) {
                                    $minutes = floor($seconds / 60);
                                    $restSeconds = $seconds % 60;
                                    return "{$minutes} minutes" . ($restSeconds > 0 ? " {$restSeconds} seconds" : "");
                                }

                                $hours = floor($seconds / 3600);
                                $minutes = floor(($seconds % 3600) / 60);
                                $hourText = $hours > 1 ? 'hours' : 'hour';
                                $minText = $minutes > 1 ? 'minutes' : 'minute';
                                
                                return "{$hours} {$hourText}" . ($minutes > 0 ? " {$minutes} {$minText}" : "");
                            })
                            ->extraAttributes(['class' => 'text-xl font-bold text-primary-600']),
                        
                        Forms\Components\Hidden::make('duration'),

                        Forms\Components\Fieldset::make('Take Off Geolocation')->schema([
                            Forms\Components\TextInput::make('takeoff_lat')->numeric()->required()->id('takeoff_lat')
                                ->extraAttributes([
                                    'x-init' => "
                                        if (!\$wire.data.takeoff_lat) {
                                            navigator.geolocation.getCurrentPosition(async (pos) => {
                                                const lat = pos.coords.latitude;
                                                const lng = pos.coords.longitude;
                                                const apiKey = '1c7f474ddb2f26c8644c9c1b4c97db31';
                                                \$wire.set('data.takeoff_lat', lat.toFixed(8));
                                                \$wire.set('data.takeoff_lng', lng.toFixed(8));
                                                try {
                                                    const resAddr = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=\${lat}&lon=\${lng}`);
                                                    const addr = await resAddr.json();
                                                    if (addr.display_name) \$wire.set('data.address_detail', addr.display_name);

                                                    const resW = await fetch(`https://api.openweathermap.org/data/2.5/weather?lat=\${lat}&lon=\${lng}&appid=\${apiKey}&units=metric`);
                                                    const w = await resW.json();
                                                    if (w.main) {
                                                        \$wire.set('data.temp_c', w.main.temp);
                                                        \$wire.set('data.humidity', w.main.humidity);
                                                        \$wire.set('data.wind_speed', (w.wind.speed * 3.6).toFixed(2));
                                                        \$wire.set('data.visibility_km', (w.visibility / 1000).toFixed(1));
                                                        
                                                        const deg = w.wind.deg;
                                                        const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
                                                        const dir = directions[Math.round(deg / 45) % 8];
                                                        \$wire.set('data.wind_dir', dir + ' (' + deg + '°)');

                                                        const rain = w.rain ? (w.rain['1h'] || w.rain['3h'] || 0) : 0;
                                                        \$wire.set('data.rain_prob', rain + ' mm/h');

                                                        if (w.weather && w.weather[0]) {
                                                            const desc = w.weather[0].description;
                                                            \$wire.set('data.sky_condition', desc.toUpperCase());
                                                        }
                                                        
                                                        new FilamentNotification().title('Maps & Weather Synced!').success().send();
                                                    }
                                                } catch (e) { console.error(e); }
                                            });
                                        }
                                    "
                                ]),
                            Forms\Components\TextInput::make('takeoff_lng')->numeric()->required()->id('takeoff_lng'),
                            Forms\Components\TextInput::make('address_detail')->columnSpanFull()->id('address_detail'),
                        ]),
                    ]),
            ]),

            // --- 2. ENVIRONMENT & WEATHER ---
            Forms\Components\Section::make('C. Environment & Weather Condition')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('temp_c')->label('Temp (°C)')->numeric()->required(),
                        Forms\Components\TextInput::make('wind_speed')->label('Wind Speed (km/h)')->numeric()->required(),
                        Forms\Components\TextInput::make('humidity')->label('Humidity (%)')->numeric()->required(),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('sky_condition')->label('Sky Condition')->required(),
                        Forms\Components\CheckboxList::make('visual_condition')
                            ->label('Actual Visual Condition')
                            ->options(['sunny' => 'Sunny', 'cloudy' => 'Cloudy', 'overcast' => 'Overcast', 'windy' => 'Windy'])
                            ->columns(2)->required(),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('wind_dir')->label('Wind Direction')->required(),
                        Forms\Components\TextInput::make('rain_prob')->label('Precipitation (Rain Prob. %)')->required(),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\CheckboxList::make('visibility')
                            ->label('Actual Visibility')
                            ->hint(fn (Get $get) => 'Satellite: ' . ($get('visibility_km') ?? '--') . ' km')
                            ->hintColor('primary')
                            ->options(['clear' => 'Clear (>10km)', 'foggy' => 'Foggy', 'limited' => 'Limited (<10km)', 'smoky' => 'Smoky'])
                            ->columns(2)->required(),
                        Forms\Components\CheckboxList::make('ground_safety')
                            ->label('Ground Area Safety')
                            ->options(['clear_airspace' => 'Clear Airspace', 'non_magnetic' => 'Non-Magnetic Area', 'flat_surface' => 'Safe/Flat Surface', 'no_bird' => 'No Bird Activity'])
                            ->columns(2)->required(),
                    ]),
                    Forms\Components\Hidden::make('visibility_km'),
                ]),

            // --- 3. SAFETY & COMPLIANCE ---
            Forms\Components\Section::make('D. Safety & Compliance')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\CheckboxList::make('pilot_health')->label('1. Pilot Health')->options(['ppe' => 'PPE Ready', 'imsafe' => 'IM SAFE Condition'])->required(),
                        Forms\Components\CheckboxList::make('observer_health')->label('2. Observer Health')->options(['ppe' => 'PPE Ready', 'imsafe' => 'IM SAFE Condition'])->required(),
                        Forms\Components\CheckboxList::make('clearance')->label('3. Flight Clearance')->options(['supervisor' => 'Supervisor Approval', 'owner' => 'Site/Owner Permission'])->required(),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Toggle::make('notam')->label('NOTAM')->live(),
                        Forms\Components\TextInput::make('notam_details')->label('NOTAM Details')->required(fn (Get $get) => $get('notam'))->visible(fn (Get $get) => $get('notam')),
                    ]),
                ]),

            // --- 4. POST-FLIGHT CHECKLIST ---
            // KUSTOMISASI: Ditambahkan ->collapsed() agar otomatis tertutup saat form pertama kali dibuka
            Forms\Components\Section::make('Post-Flight Checklist')
                ->collapsible()
                ->collapsed() 
                ->schema([
                    Forms\Components\Fieldset::make('A. Post-Flight Inspection')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Toggle::make('is_motor_ok')
                                    ->label('1. Motors is OK')
                                    ->default(true)
                                    ->onColor('success'),
                                    
                                Forms\Components\Toggle::make('is_propeller_ok')
                                    ->label('2. Propellers is OK')
                                    ->default(true)
                                    ->onColor('success'),
                                    
                                Forms\Components\Toggle::make('is_airframe_ok')
                                    ->label('3. Airframe is OK')
                                    ->default(true)
                                    ->onColor('success'),
                            ]),
                            Forms\Components\Grid::make(2)->schema([
                                // KUSTOMISASI: ->required() dihapus (opsional / tidak wajib diisi)
                                Forms\Components\TextInput::make('rc_battery_finish')
                                    ->label('4. Remaining RC Batt (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->placeholder('Input sisa baterai remote controller'),

                                // KUSTOMISASI: ->required() dihapus (opsional / tidak wajib diisi)
                                Forms\Components\TextInput::make('drone_battery_finish')
                                    ->label('5. Remaining Drone Batt (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->placeholder('Input sisa baterai drone'),
                            ]),
                        ]),
                ]),

            // --- 5. FLIGHT EVIDENCE GALLERY ---
            Forms\Components\Section::make('Flight Evidence (Gallery / Camera)')
                ->schema([
                    Forms\Components\FileUpload::make('flight_evidences')
                        ->label('Upload File / Capture Camera')
                        ->multiple()
                        ->image()
                        ->directory('flight-evidences')
                        ->columnSpanFull()
                ]),

            // --- 6. BOTTOM ACTIONS ---
            // KUSTOMISASI: ->fullWidth() dihapus agar kedua tombol sejajar horizontal (inline side-by-side)
            Forms\Components\Actions::make([
                Action::make('update_location_manual')
                    ->label('Update Lokasi & Cuaca')
                    ->icon('heroicon-m-map-pin')
                    ->color('success')
                    ->extraAttributes([
                        'x-on:click.stop' => "
                            const apiKey = '1c7f474ddb2f26c8644c9c1b4c97db31';
                            navigator.geolocation.getCurrentPosition(async (pos) => {
                                const lat = pos.coords.latitude;
                                const lng = pos.coords.longitude;
                                \$wire.set('data.takeoff_lat', lat.toFixed(8));
                                \$wire.set('data.takeoff_lng', lng.toFixed(8));
                                try {
                                    const resW = await fetch(`https://api.openweathermap.org/data/2.5/weather?lat=\${lat}&lon=\${lng}&appid=\${apiKey}&units=metric`);
                                    const w = await resW.json();
                                    if (w.main) {
                                        \$wire.set('data.temp_c', w.main.temp);
                                        \$wire.set('data.humidity', w.main.humidity);
                                        \$wire.set('data.wind_speed', (w.wind.speed * 3.6).toFixed(2));
                                        
                                        const deg = w.wind.deg;
                                        const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
                                        const dir = directions[Math.round(deg / 45) % 8];
                                        \$wire.set('data.wind_dir', dir + ' (' + deg + '°)');
                                        
                                        \$wire.set('data.visibility_km', (w.visibility / 1000).toFixed(1));
                                        if (w.weather && w.weather[0]) {
                                            \$wire.set('data.sky_condition', w.weather[0].description.toUpperCase());
                                        }
                                        
                                        new FilamentNotification().title('Data Successfully Updated!')->success().send();
                                    }
                                } catch (e) { console.error(e); }
                            ]);
                        "
                    ]),

                Action::make('flight_timer')
                    ->label(fn (Get $get) => empty($get('takeoff_time')) ? 'Start Flight' : (empty($get('landing_time')) ? 'Stop Flight' : 'Reset Timer'))
                    ->icon(fn (Get $get) => empty($get('takeoff_time')) ? 'heroicon-m-play' : (empty($get('landing_time')) ? 'heroicon-m-stop' : 'heroicon-m-arrow-path'))
                    ->color(fn (Get $get) => empty($get('takeoff_time')) ? 'info' : (empty($get('landing_time')) ? 'danger' : 'gray'))
                    ->action(function (Set $set, Get $get) {
                        $now = now()->format('H:i:s'); 
                        if (empty($get('takeoff_time'))) {
                            $set('takeoff_time', $now);
                            Notification::make()->title('Flight Started!')->body("Take-off at $now WITA")->success()->send();
                        } elseif (empty($get('landing_time'))) {
                            $set('landing_time', $now);
                            self::updateDuration($set, $get);
                            Notification::make()->title('Flight Stopped!')->body("Landing at $now WITA")->danger()->send();
                        } else {
                            $set('takeoff_time', null); $set('landing_time', null); $set('duration', 0);
                            Notification::make()->title('Timer Reset')->info()->send();
                        }
                    }),
            ]),
        ]);
    }

    protected static function updateDuration(Set $set, Get $get)
    {
        $start = $get('takeoff_time');
        $end = $get('landing_time');
        if ($start && $end) {
            $startTime = Carbon::parse($start);
            $endTime = Carbon::parse($end);
            if ($endTime->lessThan($startTime)) $endTime->addDay();
            
            $set('duration', $startTime->diffInSeconds($endTime));
        }
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('pilot.full_name')->searchable(),
                Tables\Columns\BadgeColumn::make('result')->colors(['success' => 'safe_to_fly', 'warning' => 'postpone', 'danger' => 'cancel']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('clickToView')
                    ->modalActions([
                        Tables\Actions\EditAction::make()
                            ->button()
                            ->color('warning'),
                    ])
                    ->extraAttributes(['class' => 'hidden']),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info')
                        ->icon('heroicon-m-eye')
                        ->modalActions([
                            Tables\Actions\EditAction::make()
                                ->button()
                                ->color('warning'),
                        ]),
                    
                    Tables\Actions\Action::make('downloadPdf')
                        ->label('Berita Acara')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->url(fn ($record) => route('export.flight.pdf', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                        
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->recordAction('clickToView');
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListFlightLogs::route('/'), 
            'create' => Pages\CreateFlightLog::route('/create'), 
            'edit' => Pages\EditFlightLog::route('/{record}/edit')
        ];
    }
}