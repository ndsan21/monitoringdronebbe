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
            
            // ⚡ FLUID LAYOUT: Memaksa dashboard melebar penuh 100% ke kanan-kiri monitor
            ->maxContentWidth('full') 
            
            ->colors([
                // ⚡ Aksen Utama: Hijau Emerald Neon khas Command Center
                'primary' => Color::Emerald, 
                // ⚡ KUNCI UTAMA JALUR BIRU TUA: Mengganti warna dasar abu-abu/hitam bawaan Filament
                'gray' => Color::Slate,
            ])
            
            ->sidebarCollapsibleOnDesktop()

            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Log Operasional')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->collapsible(true)
                    ->collapsed(false), 

                NavigationGroup::make()
                    ->label('Master Data')
                    ->icon('heroicon-o-circle-stack')
                    ->collapsible(true)
                    ->collapsed(true), 

                NavigationGroup::make()
                    ->label('Inventory')
                    ->icon('heroicon-o-archive-box')
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
                // Jajaran kustom widget terurut 
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
            ->resources([
                \App\Filament\Resources\MaintenanceLogResource::class,
                \App\Filament\Resources\DamageReportResource::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('
                    <link rel="manifest" href="/manifest.json">
                    
                    <style>
                        /* ======================================================= */
                        /* TEMA WARNA BIRU TUA GELAP (COMMAND CENTER SEJATI)      */
                        /* ======================================================= */
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

                        /* ======================================================= */
                        /* LIGHT MODE ADJUSTMENTS                                  */
                        /* ======================================================= */
                        .fi-wi-stats-overview-stat {
                            transition: all 0.2s ease-in-out;
                        }
                        .fi-wi-stats-overview-stat:hover {
                            transform: translateY(-2px);
                        }

                        /* ======================================================= */
                        /* HIASAN SIDEBAR & PEMBUNUH GARIS POHON                  */
                        /* ======================================================= */
                        .fi-sidebar-group-label span {
                            color: #059669 !important; 
                            font-weight: 700 !important;
                        }

                        .clean-sidebar-group .fi-sidebar-group-items,
                        .clean-sidebar-group ul {
                            border-inline-start-width: 0px !important;
                            border-left: none !important;
                            padding-inline-start: 0px !important;
                            padding-left: 0px !important;
                            margin-left: 0px !important;
                        }
                    </style>

                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            // ⚡ FUNGSI SAKTI: Menghapus teks kaku "Dashboard" bawaan vendor agar naik rata atas
                            const handleDashboardHeader = () => {
                                const path = window.location.pathname.replace(/\/$/, ""); 
                                if (path.endsWith("/admin")) {
                                    const header = document.querySelector(".fi-header");
                                    if (header) header.style.display = "none";
                                }
                            };

                            const injectSidebarIconsOnly = () => {
                                document.querySelectorAll(".fi-sidebar-group").forEach(group => {
                                    const groupLabel = group.querySelector(".fi-sidebar-group-label")?.textContent?.trim();
                                    
                                    if (groupLabel === "Log Operasional" || groupLabel === "Master Data") {
                                        group.classList.add("clean-sidebar-group");
                                        const itemsList = group.querySelector(".fi-sidebar-group-items, ul");
                                        if (itemsList) {
                                            itemsList.style.borderLeft = "none";
                                            itemsList.style.paddingLeft = "0px";
                                            itemsList.style.marginLeft = "0px";
                                        }

                                        group.querySelectorAll(".fi-sidebar-item").forEach(item => {
                                            const itemText = item.querySelector(".fi-sidebar-item-label")?.textContent?.trim();
                                            const nativeIcon = item.querySelector(".fi-sidebar-item-icon:not(.injected-sidebar-icon)");
                                            if (nativeIcon) {
                                                nativeIcon.remove();
                                            }

                                            if (!item.querySelector(".injected-sidebar-icon")) {
                                                let svgMarkup = "";
                                                if (itemText === "Flight Logs") {
                                                    svgMarkup = `<svg class="injected-sidebar-icon h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>`;
                                                } else if (itemText === "Maintenance Logs") {
                                                    svgMarkup = `<svg class="injected-sidebar-icon h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A1.79 1.79 0 1 0 19.8 18.47l-5.83-5.83.55-.55a2.19 2.19 0 0 0 0-3.11 2.2 2.2 0 0 0-3.11 0l-.55.55-1.55-1.55a4.32 4.32 0 1 0-6.11 6.11l1.55 1.55-.55.55a2.2 2.2 0 0 0 0 3.11 2.2 2.2 0 0 0 3.11 0l.55-.55Zm3.83-6.26h.01M9.06 14.94h.01" /></svg>`;
                                                } else if (itemText === "Damage Reports") {
                                                    svgMarkup = `<svg class="injected-sidebar-icon h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>`;
                                                } else if (itemText === "Companies") {
                                                    svgMarkup = `<svg class="injected-sidebar-icon h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21h10.5V6.75H6.75V21Zm4.5-11.25h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>`;
                                                } else if (itemText === "Departments") {
                                                    svgMarkup = `<svg class="injected-sidebar-icon h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>`;
                                                } else if (itemText === "Flight Locations") {
                                                    svgMarkup = `<svg class="injected-sidebar-icon h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" /></svg>`;
                                                } else if (itemText === "Employee & System Access") {
                                                    svgMarkup = `<svg class="injected-sidebar-icon h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>`;
                                                }
                                                
                                                if (svgMarkup) {
                                                    const linkTag = item.querySelector("a, button");
                                                    if (linkTag) {
                                                        linkTag.style.display = "flex";
                                                        linkTag.style.alignItems = "center";
                                                        linkTag.style.gap = "12px";
                                                        linkTag.style.paddingLeft = "8px"; 
                                                        linkTag.insertAdjacentHTML("afterbegin", svgMarkup);
                                                    }
                                                }
                                            }
                                        });
                                    }
                                });
                            };

                            // Eksekusi saat pertama muat halaman
                            handleDashboardHeader();
                            injectSidebarIconsOnly();
                            
                            // Jalankan ulang saat rute Livewire berubah agar tidak muncul lagi
                            if (window.Livewire) {
                                window.Livewire.hook("commit.done", () => {
                                    handleDashboardHeader();
                                    injectSidebarIconsOnly();
                                });
                            }
                        });
                    </script>
                '),
            );
    }
}