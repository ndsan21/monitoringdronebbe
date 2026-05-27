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
            
            // 🎯 Favicon PWA (.jpg)
            ->favicon(asset('logdrone-logo.jpg?v=999')) 
            
            // ⚡ FLUID LAYOUT
            ->maxContentWidth('full') 
            
            ->colors([
                'primary' => Color::Emerald, 
                'gray' => Color::Slate,
            ])
            
            ->sidebarCollapsibleOnDesktop()

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
            
            // 🎯 ULTRA CLEAN BREAKOUT ENGINE - NO BORDER, NO JUNK BOX
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('
                    <link rel="manifest" href="/manifest.json?v=3">
                    <meta name="theme-color" content="#1e3a8a">
                    <meta name="apple-mobile-web-app-capable" content="yes">
                    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
                    <link rel="apple-touch-icon" href="/icons/icon-192x192.jpg">
                    
                    <style>
                        html, body, .fi-body {
                            height: 100% !important;
                            margin: 0 !important;
                            padding: 0 !important;
                        }

                        /* ======================================================= */
                        /* 🖥️ DESKTOP MODE (ULTRA CLEAN DJI FLIGHTHUB)             */
                        /* ======================================================= */
                        @media (min-width: 1024px) {
                            .fi-simple-layout {
                                background: #050b14 !important;
                                display: flex !important;
                                flex-direction: row !important;
                                align-items: center !important;
                                justify-content: flex-start !important; 
                                position: relative !important;
                                min-height: 100vh !important;
                                width: 100% !important;
                                overflow: hidden !important;
                                padding: 0 !important;
                            }
                            
                            .fi-simple-layout::before {
                                content: "" !important;
                                position: absolute !important;
                                top: 0 !important;
                                right: 0 !important;
                                width: 55% !important; 
                                height: 100% !important;
                                background-image: 
                                    linear-gradient(180deg, rgba(5, 11, 20, 0.2) 0%, rgba(5, 11, 20, 0.7) 100%),
                                    url("/pngtree-an-image-of-a-camera-mounted-to-a-black-drone-image_13113348.jpg") !important;
                                background-size: cover !important;
                                background-position: center center !important;
                                background-repeat: no-repeat !important;
                                mask-image: linear-gradient(to left, rgba(0,0,0,1) 60%, rgba(0,0,0,0) 100%) !important;
                                -webkit-mask-image: linear-gradient(to left, rgba(0,0,0,1) 60%, rgba(0,0,0,0) 100%) !important;
                                pointer-events: none !important;
                                z-index: 1 !important;
                            }

                            .fi-simple-layout > div {
                                width: 100% !important;
                                max-width: 100% !important;
                                margin: 0 !important;
                                padding: 0 !important;
                                display: flex !important;
                                justify-content: flex-start !important;
                                background: transparent !important;
                                border: none !important;
                                box-shadow: none !important;
                            }

                            .fi-simple-layout main {
                                width: 100% !important;
                                max-width: 440px !important; 
                                margin: 0 0 0 12% !important; 
                                padding: 0 !important;
                                background: transparent !important;
                                position: relative !important;
                                z-index: 10 !important; 
                            }

                            /* 🛡️ BLOKIR TOTAL SEGALA BORDER & BACKGROUND BAWAAN FILAMENT */
                            .fi-simple-layout main section, 
                            .fi-simple-layout .fi-simple-main-ctn,
                            .fi-simple-main-ctn {
                                background: transparent !important; 
                                border: none !important; 
                                border-style: none !important;
                                box-shadow: none !important; 
                                padding: 0 !important; 
                            }

                            /* Panggil Logo Drone Murni Tanpa Bingkai Sisa Abu-abu */
                            .fi-simple-layout main::before {
                                content: "" !important;
                                display: block !important;
                                width: 75px !important;
                                height: 75px !important;
                                margin: 0 0 20px 0 !important; 
                                background-image: url("/favicon.jpg") !important;
                                background-size: cover !important;
                                background-position: center !important;
                                border-radius: 14px !important;
                                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                            }

                            .fi-simple-header-heading {
                                text-align: left !important;
                                font-size: 1.9rem !important;
                            }
                            
                            .fi-simple-header p, .fi-simple-header a, .fi-simple-header h1 {
                                text-align: left !important;
                                margin-left: 0 !important;
                            }
                        }

                        /* ======================================================= */
                        /* 📱 MOBILE & TABLET MODE (GLASS CLEAN OVERLAY)          */
                        /* ======================================================= */
                        @media (max-width: 1023px) {
                            .fi-simple-layout {
                                background-image: 
                                    linear-gradient(180deg, rgba(5, 11, 20, 0.8) 0%, rgba(5, 11, 20, 0.95) 100%),
                                    url("/pngtree-an-image-of-a-camera-mounted-to-a-black-drone-image_13113348.jpg") !important;
                                background-size: cover !important;
                                background-position: center center !important;
                                background-repeat: no-repeat !important;
                                display: flex !important;
                                align-items: center !important;
                                justify-content: center !important; 
                                min-height: 100vh !important;
                                padding: 24px !important;
                            }

                            .fi-simple-layout::before {
                                display: none !important;
                            }

                            .fi-simple-layout main {
                                width: 100% !important;
                                max-width: 400px !important;
                                margin: 0 auto !important;
                                z-index: 10 !important;
                            }

                            /* Di Mobile Menggunakan Glass Ringan tanpa Border Kasar */
                            .fi-simple-layout main section {
                                background: rgba(11, 21, 40, 0.75) !important;
                                backdrop-filter: blur(20px) !important;
                                -webkit-backdrop-filter: blur(20px) !important;
                                border: 1px solid rgba(255, 255, 255, 0.05) !important;
                                padding: 32px 24px !important;
                                border-radius: 16px !important;
                                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
                            }

                            .fi-simple-layout main::before {
                                content: "" !important;
                                display: block !important;
                                width: 75px !important;
                                height: 75px !important;
                                margin: 0 auto 20px auto !important; 
                                background-image: url("/favicon.jpg") !important;
                                background-size: cover !important;
                                background-position: center !important;
                                border-radius: 14px !important;
                                border: 1px solid rgba(255, 255, 255, 0.15) !important;
                            }

                            .fi-simple-header-heading {
                                text-align: center !important;
                                font-size: 1.75rem !important;
                            }
                            
                            .fi-simple-header p, .fi-simple-header a, .fi-simple-header h1 {
                                text-align: center !important;
                                margin: 0 auto !important;
                            }
                        }

                        /* ======================================================= */
                        /* 🛠️ CONTROLS & INPUT COMPONENT OVERRIDE                   */
                        /* ======================================================= */
                        .fi-simple-layout input {
                            background-color: #0b1322 !important;
                            border: 1px solid #1e293b !important;
                            border-radius: 8px !important;
                            color: #ffffff !important;
                            padding: 12px 14px !important;
                        }
                        .fi-simple-layout input:focus-within {
                            border-color: #10b981 !important;
                            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15) !important;
                        }
                        .fi-simple-layout button[type="submit"] {
                            border-radius: 8px !important;
                            padding: 12px !important;
                            font-weight: 600 !important;
                        }

                        /* ======================================================= */
                        /* 📊 DARK INTERNAL COMMAND CENTER THEME                 */
                        /* ======================================================= */
                        .dark .fi-body, .dark .fi-sidebar { background-color: #050b18 !important; }
                        .dark .fi-sidebar-nav { background-color: #070f21 !important; }
                        .dark .fi-section, .dark .fi-wi-stats-overview-stat, .dark .fi-ta-ctn {
                            background-color: #0b1528 !important;
                            border-color: #112240 !important;
                            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3) !important;
                        }
                        .dark .fi-wi-stats-overview-stat div, .dark .fi-section div, .dark .fi-ta-ctn text, .dark .fi-ta-ctn span { color: #e2e8f0 !important; }
                        .fi-wi-stats-overview-stat { transition: all 0.2s ease-in-out; }
                        .fi-wi-stats-overview-stat:hover { transform: translateY(-2px); }
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
                                window.Livewire.hook("commit.done", () => { handleDashboardHeader(); });
                            }
                        });
                    </script>
                '),
            )
            
            // 🎯 SERVICE WORKER REGISTRATION FOR AUTOMATIC PWA
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