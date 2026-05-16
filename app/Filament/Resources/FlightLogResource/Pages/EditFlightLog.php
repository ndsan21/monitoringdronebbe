<?php

namespace App\Filament\Resources\FlightLogResource\Pages;

use App\Filament\Resources\FlightLogResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class EditFlightLog extends EditRecord
{
    protected static string $resource = FlightLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    /**
     * Menyandingkan tombol kustom langsung sejajar dengan tombol Save & Cancel di halaman Edit
     */
    protected function getFormActions(): array
    {
        return [
            // Tombol 1: Update Lokasi & Cuaca
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
                        });
                    "
                ]),

            // Tombol 2: Start Flight
            Action::make('flight_timer')
                ->label(fn () => empty($this->data['takeoff_time']) ? 'Start Flight' : (empty($this->data['landing_time']) ? 'Stop Flight' : 'Reset Timer'))
                ->icon(fn () => empty($this->data['takeoff_time']) ? 'heroicon-m-play' : (empty($this->data['landing_time']) ? 'heroicon-m-stop' : 'heroicon-m-arrow-path'))
                ->color(fn () => empty($this->data['takeoff_time']) ? 'info' : (empty($this->data['landing_time']) ? 'danger' : 'gray'))
                ->action(function () {
                    $now = now()->format('H:i:s'); 
                    if (empty($this->data['takeoff_time'])) {
                        $this->data['takeoff_time'] = $now;
                        Notification::make()->title('Flight Started!')->body("Take-off at $now WITA")->success()->send();
                    } elseif (empty($this->data['landing_time'])) {
                        $this->data['landing_time'] = $now;
                        
                        if (!empty($this->data['takeoff_time'])) {
                            $startTime = Carbon::parse($this->data['takeoff_time']);
                            $endTime = Carbon::parse($now);
                            if ($endTime->lessThan($startTime)) $endTime->addDay();
                            $this->data['duration'] = $startTime->diffInSeconds($endTime);
                        }
                        
                        Notification::make()->title('Flight Stopped!')->body("Landing at $now WITA")->danger()->send();
                    } else {
                        $this->data['takeoff_time'] = null;
                        $this->data['landing_time'] = null;
                        $this->data['duration'] = 0;
                        Notification::make()->title('Timer Reset')->info()->send();
                    }
                }),

            // Tombol Bawaan Asli Footer Filament Edit (Save & Cancel)
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}