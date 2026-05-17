<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightLogResource\Pages;
use App\Models\FlightLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\Carbon;
use Filament\Navigation\NavigationGroup;

class FlightLogResource extends Resource
{
    protected static ?string $model = FlightLog::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Log Operasional';
    protected static ?int $navigationSort = 1; // Angka urutan makin kecil = makin atas
    

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

                        Forms\Components\Select::make('co_pilot_id')
                            ->relationship('coPilot', 'full_name')
                            ->label('Co Pilot')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('drone_id')
                            ->relationship('drone', 'asset_name', fn ($query) => $query->where('category', 'DRONE'))
                            ->label('Drone Model / Name')
                            ->searchable()
                            ->preload()
                            ->live() 
                            ->required()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    // 1. Otomatis cari RC yang terhubung ke drone ini di tabel assets
                                    $rc = \App\Models\Asset::query()
                                        ->where('drone_id', $state)
                                        ->where('category', 'SPAREPART')
                                        ->where(function ($q) {
                                            $q->where('sparepart_type', 'LIKE', '%Remote%')
                                              ->orWhere('sparepart_type', 'LIKE', '%RC%');
                                        })
                                        ->first();

                                    $set('rc_serial_id', $rc?->serial_number);
                                    
                                    // Kosongkan pilihan baterai agar pilot memilih manual komponen yang dipakai saat itu
                                    $set('battery_serial_id', null); 
                                } else {
                                    $set('rc_serial_id', null);
                                    $set('battery_serial_id', null);
                                }
                            }),
                        
                        // FIX LOGIKA LOKASI: Diubah menjadi jembatan teks string agar tidak merusak foreign key integer database
                        Forms\Components\TextInput::make('location_name_bridge')
                            ->label('Flight Location')
                            ->placeholder('Ketik lokasi... (Otomatis tersimpan jika baru)')
                            ->datalist(fn () => \App\Models\FlightLocation::query()
                                ->whereNotNull('location_name')
                                ->distinct()
                                ->pluck('location_name')
                                ->toArray()
                            )
                            ->required()
                            ->formatStateUsing(fn ($record) => $record?->flightLocation?->location_name)
                            
                            // ◄--- KUNCI PEMBASMI ERROR SCREENSHOT MASTER ---►
                            ->dehydrated(false) // Mencegah kolom ini ikut disisipkan ke query SQL INSERT
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (blank($state)) {
                                    $set('flight_location_id', null);
                                    return;
                                }

                                // Ambil ID lokasi lama, atau otomatis buat baru jika teks tidak terdaftar di database
                                $location = \App\Models\FlightLocation::firstOrCreate([
                                    'location_name' => $state,
                                ]);

                                // Set nilai asli foreign key untuk disimpan ke database
                                $set('flight_location_id', $location->id);
                            }),

                        // Pastikan hidden input penampung ID asli database tetap ada di bawahnya
                        Forms\Components\Hidden::make('flight_location_id'),

                        Forms\Components\Hidden::make('flight_area_name')
                            ->default('-')
                            ->dehydrateStateUsing(fn () => '-'),

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
                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i:s') : null)
                                ->dehydrateStateUsing(fn ($state, Get $get) => $state && $get('date') ? Carbon::parse($get('date'))->format('Y-m-d') . ' ' . $state : $state)
                                ->required(),

                            Forms\Components\TimePicker::make('landing_time')
                                ->label('Landing Time')
                                ->seconds()
                                ->format('H:i:s')
                                ->displayFormat('H:i:s')
                                ->live()
                                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateDuration($set, $get))
                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i:s') : null)
                                ->dehydrateStateUsing(fn ($state, Get $get) => $state && $get('date') ? Carbon::parse($get('date'))->format('Y-m-d') . ' ' . $state : $state),
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
                                return "{$hours} " . ($hours > 1 ? 'hours' : 'hour') . ($minutes > 0 ? " {$minutes} " . ($minutes > 1 ? 'minutes' : 'minute') : "");
                            })
                            ->extraAttributes(['class' => 'text-xl font-bold text-primary-600']),
                        
                        Forms\Components\Hidden::make('duration'),

                        Forms\Components\Fieldset::make('Take Off Geolocation')->schema([
                            Forms\Components\TextInput::make('takeoff_lat')->numeric()->required()->id('takeoff_lat')
                                // FIX EROR [$data]: Pengaman closure PHP agar tidak disuntikkan ke halaman tabel utama
                                ->extraAttributes(function ($livewire) {
                                    if (! property_exists($livewire, 'data')) {
                                        return [];
                                    }
                                    return [
                                        'x-init' => '
                                            if (typeof $wire !== "undefined" && !$wire.get("data.takeoff_lat")) {
                                                navigator.geolocation.getCurrentPosition(async (pos) => {
                                                    const lat = pos.coords.latitude;
                                                    const lng = pos.coords.longitude;
                                                    const apiKey = "1c7f474ddb2f26c8644c9c1b4c97db31";
                                                    
                                                    $wire.set("data.takeoff_lat", lat.toFixed(8));
                                                    $wire.set("data.takeoff_lng", lng.toFixed(8));
                                                    
                                                    try {
                                                        const resAddr = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
                                                        const addr = await resAddr.json();
                                                        if (addr.display_name) {
                                                            $wire.set("data.address_detail", addr.display_name);
                                                        }

                                                        const resW = await fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=${apiKey}&units=metric`);
                                                        const w = await resW.json();
                                                        if (w.main) {
                                                            $wire.set("data.temp_c", w.main.temp);
                                                            $wire.set("data.humidity", w.main.humidity);
                                                            $wire.set("data.wind_speed", (w.wind.speed * 3.6).toFixed(2));
                                                            $wire.set("data.visibility_km", (w.visibility / 1000).toFixed(1));
                                                            
                                                            const deg = w.wind.deg;
                                                            const directions = ["N", "NE", "E", "SE", "S", "SW", "W", "NW"];
                                                            const dir = directions[Math.round(deg / 45) % 8];
                                                            $wire.set("data.wind_dir", dir + " (" + deg + "°)");

                                                            const rain = w.rain ? (w.rain["1h"] || w.rain["3h"] || 0) : 0;
                                                            $wire.set("data.rain_prob", rain + " mm/h");

                                                            if (w.weather && w.weather[0]) {
                                                                $wire.set("data.sky_condition", w.weather[0].description.toUpperCase());
                                                            }
                                                        }
                                                    } catch (e) { console.error(e); }
                                                });
                                            }
                                        '
                                    ];
                                }),
                            Forms\Components\TextInput::make('takeoff_lng')->numeric()->required()->id('takeoff_lng'),
                            Forms\Components\TextInput::make('address_detail')->columnSpanFull()->id('address_detail'),
                        ]),
                    ]),
            ]),

            // --- 2. PRE-FLIGHT CHECKLIST ---
            Forms\Components\Section::make('Pre-Flight Checklist')
                ->collapsible()
                ->schema([
                    Forms\Components\Fieldset::make('A. Hardware Inspection')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Toggle::make('pre_drone_motors')->label('1. Drone motors')->onColor('success')->required(),
                                Forms\Components\Toggle::make('pre_drone_propellers')->label('2. Drone propellers')->onColor('success')->required(),
                                Forms\Components\Toggle::make('pre_drone_airframe')->label('3. Drone airframe')->onColor('success')->required(),
                            ]),
                            Forms\Components\Toggle::make('pre_phone_battery_ok')->label('6. Phone device battery (≥ 30%)')->onColor('success')->required(),
                        ]),

                    Forms\Components\Fieldset::make('4. Remote & Battery Status')
                        ->schema([
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\Select::make('rc_serial_id')
                                    ->label('RC Serial/ID')
                                    ->options(function (Forms\Get $get) {
                                        $droneId = $get('drone_id');
                                        if (! $droneId) return [];

                                        return \App\Models\Asset::query()
                                            ->where('drone_id', $droneId)
                                            ->where('category', 'SPAREPART')
                                            ->where(function ($query) {
                                                $query->where('sparepart_type', 'LIKE', '%Remote%')
                                                      ->orWhere('sparepart_type', 'LIKE', '%RC%');
                                            })
                                            ->pluck('serial_number', 'serial_number');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('rc_battery_start')->label('RC Battery (%)')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),

                                Forms\Components\Select::make('battery_serial_id')
                                    ->label('Batt Serial/ID')
                                    ->options(function (Forms\Get $get) {
                                        $droneId = $get('drone_id');
                                        if (! $droneId) return [];

                                        return \App\Models\Asset::query()
                                            ->where('drone_id', $droneId)
                                            ->where('category', 'SPAREPART')
                                            ->where(function ($query) {
                                                $query->where('sparepart_type', 'LIKE', '%Battery%')
                                                      ->orWhere('sparepart_type', 'LIKE', '%Batt%')
                                                      ->orWhere('sparepart_type', 'LIKE', '%Baterai%');
                                            })
                                            ->pluck('serial_number', 'serial_number');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('drone_battery_start')->label('Drone Battery (%)')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
                            ]),
                            Forms\Components\TextInput::make('battery_temp')->label('Temp (°C)')->numeric()->suffix('°C')->required(),
                        ]),

                    Forms\Components\Fieldset::make('B. System Functionality')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\CheckboxList::make('app_readiness')
                                    ->label('1. App Readiness')
                                    ->options(['app_stable' => 'App stable', 'firmware_stable' => 'Firmware stable', 'safe_fly_database' => 'Safe Fly database'])
                                    ->columns(2)->required(),

                                Forms\Components\CheckboxList::make('calibration')
                                    ->label('2. Calibration')
                                    ->options(['compass_ok' => 'Compass is OK', 'esc_ok' => 'ESC is OK', 'imu_ok' => 'IMU is OK'])
                                    ->columns(2)->required(),

                                Forms\Components\CheckboxList::make('link_gps')
                                    ->label('3. Link & GPS')
                                    ->options(['rc_link_connected' => 'RC Link Connected', 'gps_locked' => 'GPS Locked (>10 sats)', 'video_feed_clear' => 'Video Feed Clear'])
                                    ->columns(2)->required(),

                                Forms\Components\CheckboxList::make('rc_sticks_switches')
                                    ->label('5. RC Sticks & Switches')
                                    ->options(['sticks_ok' => 'Sticks is OK', 'dials_ok' => 'Dials is OK', 'buttons_ok' => 'Buttons is OK', 'antennas_ok' => 'Antennas is OK'])
                                    ->columns(2)->required(),

                                Forms\Components\CheckboxList::make('media_gimbal')
                                    ->label('6. Media & Gimbal')
                                    ->options(['microsd_inserted' => 'MicroSD Inserted', 'camera_setting_ok' => 'Camera Setting is OK', 'gimbal_clamp_removed' => 'Gimbal Clamp Removed'])
                                    ->columns(2)->required(),

                                Forms\Components\CheckboxList::make('app_self_check')
                                    ->label('7. App Self-Check Result')
                                    ->options(['battery' => 'Battery', 'gps' => 'GPS', 'remote' => 'Remote', 'camera' => 'Camera', 'sensors' => 'Sensors', 'microsd' => 'MicroSD'])
                                    ->columns(2)->required(),

                                Forms\Components\CheckboxList::make('flight_test')
                                    ->label('8. Flight Test')
                                    ->options(['hovering_stable' => 'Hovering Stable', 'home_point_set' => 'Home Point Set (RTH)', 'control_responsive' => 'Control Responsive'])
                                    ->columns(2)->required(),
                            ]),
                        ]),

                    Forms\Components\Fieldset::make('4. Battery Voltage Detail')
                        ->schema([
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\TextInput::make('low_cell_v')->label('Low Cell (V)')->numeric()->required(),
                                Forms\Components\TextInput::make('high_cell_v')->label('High Cell (V)')->numeric()->required(),
                                Forms\Components\TextInput::make('total_voltage_v')->label('Total Voltage (V)')->numeric()->required(),
                                Forms\Components\TextInput::make('battery_cycles')->label('Battery Cycles')->numeric()->required(),
                            ]),
                        ]),
                ]),

            // --- 3. ENVIRONMENT & WEATHER ---
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
                            // FIX AMAN [$data]: Proteksi total properti di halaman tabel index
                            ->hint(function (Get $get, $livewire) {
                                if (! property_exists($livewire, 'data')) {
                                    return 'Satellite Data: -- km';
                                }
                                $val = $get('visibility_km');
                                return 'Satellite Data: ' . ($val ?? '--') . ' km';
                            })
                            ->hintColor('warning')
                            ->options(['clear' => 'Clear (>10km)', 'foggy' => 'Foggy', 'limited' => 'Limited (<10km)', 'smoky' => 'Smoky'])
                            ->columns(2)->required(),
                        Forms\Components\CheckboxList::make('ground_safety')
                            ->label('Ground Area Safety')
                            ->options(['clear_airspace' => 'Clear Airspace', 'non_magnetic' => 'Non-Magnetic Area', 'flat_surface' => 'Safe/Flat Surface', 'no_bird' => 'No Bird Activity'])
                            ->columns(2)->required(),
                    ]),
                    Forms\Components\TextInput::make('visibility_km')
                        ->hidden()
                        ->live(),
                ]),

            // --- 4. SAFETY & COMPLIANCE ---
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

            // --- 5. POST-FLIGHT CHECKLIST ---
            Forms\Components\Section::make('Post-Flight Checklist')
                ->collapsible()
                ->collapsed() 
                ->schema([
                    Forms\Components\Fieldset::make('A. Post-Flight Inspection')
                        ->columns(1) 
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Toggle::make('is_motor_ok')->label('1. Motors is OK')->onColor('success'),
                                Forms\Components\Toggle::make('is_propeller_ok')->label('2. Propellers is OK')->onColor('success'),
                                Forms\Components\Toggle::make('is_airframe_ok')->label('3. Airframe is OK')->onColor('success'),
                            ]),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('rc_battery_finish')->label('4. Remaining RC Batt (%)')->numeric()->minValue(0)->maxValue(100)->suffix('%')->placeholder('Input sisa baterai remote controller'),
                                Forms\Components\TextInput::make('drone_battery_finish')->label('5. Remaining Drone Batt (%)')->numeric()->minValue(0)->maxValue(100)->suffix('%')->placeholder('Input sisa baterai drone'),
                            ]),
                        ]),
                ]),

            // --- 6. FINAL RESULT & ATTACHMENTS ---
            Forms\Components\Section::make('Final Result & Attachments')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('requesting_company_id')
                            ->label('Requesting Company')
                            ->options(\App\Models\Company::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('requesting_department_id')
                            ->label('Requesting Department')
                            ->options(\App\Models\Department::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('pic_requester_name')
                            ->label('PIC / Requester Name')
                            ->placeholder('e.g., John Doe')
                            ->required(),
                    ]),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('result')
                            ->label('Flight Result Status')
                            ->options([
                                'safe_to_fly' => 'Safe to Fly',
                                'postpone' => 'Postpone',
                                'cancel' => 'Cancel',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('flight_operation_notes')
                            ->label('Flight Operation Notes')
                            ->placeholder('Tambahkan ringkasan hasil misi, kendala, atau penunjang teknis lainnya...')
                            ->rows(3),
                    ]),

                    Forms\Components\FileUpload::make('flight_evidences')
                        ->label('Flight Evidence (Gallery / Camera)')
                        ->multiple()
                        ->image()
                        ->directory('flight-evidences')
                        ->columnSpanFull()
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
                Tables\Columns\TextColumn::make('result')
                    ->badge()
                    ->colors([
                        'success' => 'safe_to_fly',
                        'warning' => 'postpone',
                        'danger' => 'cancel'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('clickToView')
                    ->modalActions([
                        Tables\Actions\EditAction::make()->button()->color('warning'),
                    ])
                    ->extraAttributes(['class' => 'hidden']),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info')
                        ->icon('heroicon-m-eye')
                        ->modalActions([
                            Tables\Actions\EditAction::make()->button()->color('warning'),
                        ]),
                    
                    Tables\Actions\Action::make('downloadPdf')
                        ->label('Berita Acara')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->url(fn ($record) => route('export.flight.pdf', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make()->color('warning'),
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