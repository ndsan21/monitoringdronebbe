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
            ->login(\App\Filament\Pages\Auth\Login::class)
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
            
            // 🎯 PREMIUM BALANCED AUTH COMPONENT ENGINE
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
                            background: #040812 !important;
                        }

                        /* ======================================================= */
                        /* 🖥️ DESKTOP EXCLUSIVE HIGH-END APPLICATION INTERFACE     */
                        /* ======================================================= */
                        @media (min-width: 1024px) {
                            .fi-simple-layout {
                                background: #040812 !important;
                                display: flex !important;
                                flex-direction: row !important;
                                align-items: center !important;
                                justify-content: flex-start !important; 
                                min-height: 100vh !important;
                                width: 100% !important;
                                overflow: hidden !important;
                                padding: 0 !important;
                                position: relative !important;
                            }
                            
                            /* Penempatan Gambar Drone Sisi Kanan Tanpa Menabrak Form */
                            .fi-simple-layout::before {
                                content: "" !important;
                                position: absolute !important;
                                top: 0 !important;
                                right: 0 !important;
                                width: 52% !important; /* Mengurangi lebar agar menjauh dari form */
                                height: 100% !important;
                                background-image: 
                                    linear-gradient(90deg, #040812 0%, rgba(4,8,18,0.3) 20%, rgba(4,8,18,0) 100%),
                                    url("/pngtree-an-image-of-a-camera-mounted-to-a-black-drone-image_13113348.jpg") !important;
                                background-size: cover !important;
                                background-position: center center !important;
                                background-repeat: no-repeat !important;
                                pointer-events: none !important;
                                z-index: 1 !important;
                            }

                            /* Bypass Container Utama Filament */
                            .fi-simple-layout > div {
                                width: 100% !important;
                                max-width: 100% !important;
                                background: transparent !important;
                                border: none !important;
                                box-shadow: none !important;
                                margin: 0 !important;
                                padding: 0 !important;
                            }

                            /* Geser Blok Konten ke Area Gelap Sisi Kiri */
                            .fi-simple-layout main {
                                width: 100% !important;
                                max-width: 440px !important; 
                                margin: 0 0 0 10s% !important; 
                                padding: 0 !important;
                                background: transparent !important;
                                position: relative !important;
                                z-index: 10 !important; 
                            }

                            /* Bersihkan Total Background Card */
                            .fi-simple-layout main section,
                            .fi-simple-main-ctn {
                                background: transparent !important;
                                border: none !important;
                                box-shadow: none !important;
                                padding: 0 !important;
                            }

                            /* Header Teks Utama Rata Kiri */
                            .fi-simple-header-heading {
                                text-align: left !important;
                                font-size: 2.2rem !important;
                                font-weight: 800 !important;
                                color: #ffffff !important;
                                letter-spacing: -0.02em !important;
                                margin-bottom: 8px !important;
                            }
                            
                            /* Deskripsi Teks */
                            .fi-simple-header p {
                                text-align: left !important;
                                font-size: 0.95rem !important;
                                color: #94a3b8 !important;
                                margin: 0 0 30px 0 !important;
                            }
                            
                            /* Sembunyikan Tautan Navigasi Luar yang Berantakan */
                            .fi-simple-header nav,
                            .fi-simple-header div:has(a) {
                                display: none !important;
                            }

                            /* Teks Label Input (Email / Password) Dibuat Elegan & Jelas */
                            .fi-fo-field-wrp-label span {
                                color: #94a3b8 !important;
                                font-size: 0.85rem !important;
                                font-weight: 500 !important;
                                letter-spacing: 0.05em !important;
                                text-transform: uppercase !important;
                            }

                            /* Kolom Input Lebar Penuh (Mengunci Eror Lingkaran Kecil) */
                            .fi-simple-layout input {
                                background-color: #0f172a !important;
                                border: 1px solid #1e293b !important;
                                border-radius: 6px !important;
                                color: #ffffff !important;
                                padding: 12px 14px !important;
                                font-size: 0.95rem !important;
                                width: 100% !important; /* Paksa lebar 100% */
                                display: block !important;
                            }
                            
                            .fi-simple-layout input:focus-within {
                                border-color: #10b981 !important;
                                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15) !important;
                            }

                            /* Sembunyikan Checkbox Remember Me Agar Form Padat */
                            .fi-simple-layout label:has(input[type="checkbox"]),
                            .fi-simple-layout .justify-between:has(a) {
                                display: none !important;
                            }

                            /* Tombol Sign In Lebar Penuh yang Kokoh */
                            .fi-simple-layout button[type="submit"] {
                                background-color: #10b981 !important; /* Hijau Emerald Kebanggaan */
                                border-radius: 6px !important;
                                padding: 12px !important;
                                font-weight: 600 !important;
                                font-size: 0.95rem !important;
                                color: #ffffff !important;
                                width: 100% !important; /* Lebar penuh penyeimbang */
                                margin-top: 15px !important;
                                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2) !important;
                            }
                            
                            /* Footer Hak Cipta */
                            .fi-simple-layout::after {
                                content: "LOGDRONE OPERATION SYSTEM © 2026 PRIVACY POLICY" !important;
                                position: absolute !important;
                                bottom: 25px !important;
                                left: 10% !important;
                                font-size: 0.75rem !important;
                                color: #475569 !important;
                                letter-spacing: 0.05em !important;
                                z-index: 10 !important;
                            }
                        }

                        /* ======================================================= */
                        /* 📱 MOBILE RESPONSIVE ENGINE                             */
                        /* ======================================================= */
                        @media (max-width: 1023px) {
                            .fi-simple-layout {
                                background-image: 
                                    linear-gradient(180deg, rgba(4,8,18,0.85) 0%, rgba(4,8,18,0.95) 100%),
                                    url("/pngtree-an-image-of-a-camera-mounted-to-a-black-drone-image_13113348.jpg") !important;
                                background-size: cover !important;
                                background-position: center center !important;
                                display: flex !important;
                                align-items: center !important;
                                justify-content: center !important;
                                min-height: 100vh !important;
                                padding: 20px !important;
                            }
                            .fi-simple-layout::before { display: none !important; }
                            .fi-simple-layout main { width: 100% !important; max-width: 380px !important; margin: 0 auto !important; }
                            .fi-simple-layout main section { background: rgba(15, 23, 42, 0.8) !important; backdrop-filter: blur(20px) !important; -webkit-backdrop-filter: blur(20px) !important; padding: 30px 20px !important; border-radius: 8px !important; }
                            .fi-simple-layout input { background-color: #0f172a !important; border: 1px solid #334155 !important; border-radius: 6px !important; color: #ffffff !important; padding: 12px !important; width: 100% !important; }
                            .fi-simple-layout button[type="submit"] { background-color: #10b981 !important; border-radius: 6px !important; padding: 12px !important; width: 100% !important; }
                            .fi-simple-header-heading { text-align: center !important; font-size: 1.8rem !important; color: #ffffff !important; }
                            .fi-simple-header p { text-align: center !important; font-size: 0.95rem !important; color: #aaaaaa !important; }
                            .fi-simple-layout label:has(input[type="checkbox"]), .fi-simple-header nav { display: none !important; }
                        }
                    </style>

                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const changeText = () => {
                                const heading = document.querySelector(".fi-simple-header-heading");
                                if (heading && (heading.innerText === "Sign in" || heading.innerText === "SIGN IN")) {
                                    heading.innerText = "LOGDRONE SYSTEM";
                                    const sub = heading.nextElementSibling;
                                    if (sub) sub.innerText = "Operational & Drone Fleet Management Command";
                                }
                            };

                            changeText();
                            
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
                                    changeText();
                                    handleDashboardHeader(); 
                                });
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
                                navigator.serviceWorker.register("/sw.js");
                            });
                        }
                    </script>
                '),
            );
    }
}