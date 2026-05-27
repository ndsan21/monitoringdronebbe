<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use App\Filament\Resources\Pages\Auth\CustomRegister;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration(CustomRegister::class)
            
            // 🎯 FIX MUTLAK CACHE BUSTER: Logo bumi dijamin musnah!
            ->favicon(asset('favicon.jpg?v=2')) 
            
            // ⚡ FLUID LAYOUT: Memaksa dashboard melebar penuh 100% ke kanan-kiri monitor
            ->maxContentWidth('full') 
            
            ->colors([
                // ⚡ Aksen Utama: Hijau Emerald Neon khas Command Center
                'primary' => Color::Emerald, 
                // ⚡ KUNCI UTAMA JALUR BIRU TUA: Mengganti warna dasar abu-abu/hitam bawaan Filament
                'gray' => Color::Slate,
            ])
            
            ->sidebarCollapsibleOnDesktop()

            // Memperbarui nama grup agar sesuai dengan file Resource yang baru
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Log Operasional')
                    ->collapsible(true)
                    ->collapsed(false), 

                NavigationGroup::make()
                    ->label('Master Data')
                    ->collapsible(true)
                    ->collapsed(true), 

                NavigationGroup::make()
                    ->label('Inventory & Asset Management')
                    ->collapsible(true)
                    ->collapsed(true),
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            
            ->pages([
                Pages\Dashboard::class,
            ])
            
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\DashboardHeaderWidget::class,
                \App\Filament\Widgets\DashboardStatsOverview::class,
                \App\Filament\Widgets\DroneHoursChart::class,
                \App\Filament\Widgets\PilotHoursChart::class,
                \App\Filament\Widgets\FlightDurationTrendChart::class,
                \App\Filament\Widgets\MissionPurposeChart::class,
                \App\Filament\Widgets\BatteryHealthChart::class,
                \App\Filament\Widgets\RecentFlightsTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            
            // 🎯 PWA METADATA & INTEGRASI BACKGROUND DI HEAD
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('
                    <link rel="manifest" href="/manifest.json">
                    <meta name="theme-color" content="#1e3a8a">
                    <meta name="apple-mobile-web-app-capable" content="yes">
                    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
                    <link rel="apple-touch-icon" href="/icons/icon-192x192.jpg">
                    
                    <style>
                        .dark .fi-body, .dark .fi-sidebar {
                            background-color: #050b18 !important;
                        }
                        .dark .fi-sidebar-nav {
                            background-color: #070f21 !important;
                        }
                        .dark .fi-section, .dark .fi-wi-stats-overview-stat, .dark .fi-ta-ctn {
                            background-color: #0b1528 !important;
                            border-color: #112240 !important;
                            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3) !important;
                        }
                        .dark .fi-wi-stats-overview-stat div, .dark .fi-section div, .dark .fi-ta-ctn text, .dark .fi-ta-ctn span {
                            color: #e2e8f0 !important;
                        }

                        .fi-wi-stats-overview-stat {
                            transition: all 0.2s ease-in-out;
                        }
                        .fi-wi-stats-overview-stat:hover {
                            transform: translateY(-2px);
                        }
                    </style>

                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const handleDashboardHeader = () => {
                                const path = window.location.pathname.replace(/\/$/, ""); 
                                if (path.endsWith("/admin")) {
                                    const header = document.querySelector(".fi-header");
                                    if (header) header.style.display = "none";
                                }
                            };

                            handleDashboardHeader();
                            
                            if (window.Livewire) {
                                window.Livewire.hook("commit.done", () => {
                                    handleDashboardHeader();
                                });
                            }
                        });
                    </script>
                '),
            )
            
            // 🎯 AUTOMATIC PWA SERVICE WORKER REGISTRATION DI BODY END
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('
                    <script>
                        if ("serviceWorker" in navigator) {
                            window.addEventListener("load", function() {
                                navigator.serviceWorker.register("/sw.js").then(function(registration) {
                                    console.log("LogDrone PWA registered successfully with scope: ", registration.scope);
                                }, function(err) {
                                    console.log("LogDrone PWA registration failed: ", err);
                                });
                            });
                        }
                    </script>
                '),
            );
    }
}