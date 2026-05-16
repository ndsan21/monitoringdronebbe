<?php

namespace App\Filament\Resources\FlightLogResource\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreateFlightLog extends CreateRecord
{
    protected static string $resource = FlightLogResource::class;

    // KUNCI 1: Mengarahkan user kembali ke halaman awal (tabel list) setelah sukses membuat data
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * MENYUNTIKKAN TOMBOL KUSTOM INTERNASIONAL DI FOOTER FORM
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            
            // FIX: Baris $this->getCreateAnotherFormAction() SUDAH DIHAPUS TOTAL dari sini agar tombolnya hilang!

            // 🌦️ TOMBOL 1: SYNC LOKASI & CUACA
            Action::make('syncLocationWeatherBottom')
                ->label('Sync Weather & GPS')
                ->icon('heroicon-m-cloud-arrow-down')
                ->color('success')
                ->alpineClickHandler("
                    if (navigator.geolocation) {
                        new FilamentNotification().title('Connecting to Satellite...').info().send();
                        navigator.geolocation.getCurrentPosition(async (pos) => {
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            const apiKey = '1c7f474ddb2f26c8644c9c1b4c97db31';
                            
                            \$wire.set('data.takeoff_lat', lat.toFixed(8));
                            \$wire.set('data.takeoff_lng', lng.toFixed(8));
                            
                            try {
                                const resAddr = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=\${lat}&lon=\${lng}`);
                                const addr = await resAddr.json();
                                if (addr.display_name) {
                                    \$wire.set('data.address_detail', addr.display_name);
                                }

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
                                        \$wire.set('data.sky_condition', w.weather[0].description.toUpperCase());
                                    }
                                    
                                    new FilamentNotification().title('GPS & Weather Synced Successfully!').success().send();
                                }
                            } catch (e) { 
                                console.error(e);
                                new FilamentNotification().title('Failed to fetch weather data').danger().send();
                            }
                        }, (err) => {
                            new FilamentNotification().title('GPS Access Denied').danger().send();
                        });
                    } else {
                        new FilamentNotification().title('Browser does not support GPS').danger().send();
                    }
                "),

            // ⏱️ TOMBOL SMART STOPWATCH 2-IN-1
            Action::make('smartFlightStopwatch')
                ->label(function () {
                    $start = $this->data['takeoff_time'] ?? null;
                    $end = $this->data['landing_time'] ?? null;

                    if (!$start) { return '⏱️ Takeoff Now'; }
                    if (!$end) { return '⏱️ Landing Now'; }
                    return '⏱️ Update Landing';
                })
                ->color('warning')
                ->action(function () {
                    $start = $this->data['takeoff_time'] ?? null;
                    if (!$start) {
                        $this->data['takeoff_time'] = now()->format('H:i:s');
                    } else {
                        $this->data['landing_time'] = now()->format('H:i:s');
                    }
                    $this->recalculateDuration();
                }),

            $this->getCancelFormAction(),
        ];
    }

    protected function recalculateDuration(): void
    {
        $start = $this->data['takeoff_time'] ?? null;
        $end = $this->data['landing_time'] ?? null;
        
        if ($start && $end) {
            $startTime = Carbon::parse($start);
            $endTime = Carbon::parse($end);
            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }
            $this->data['duration'] = $startTime->diffInSeconds($endTime);
        }
    }
}