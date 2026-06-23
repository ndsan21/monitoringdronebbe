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
use App\Filament\Pages\Auth\RequestPasswordReset;

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
            ->passwordReset(RequestPasswordReset::class)
            
            // 🎯 LOGO & BRANDING DINAMIS
            ->brandLogo(function () {
                $user = auth()->user();
                if ($user && $user->subscriptionGroup && $user->subscriptionGroup->logo_path) {
                    return asset('storage/' . $user->subscriptionGroup->logo_path);
                }
                if ($user && $user->company && $user->company->logo_path) {
                    return asset('storage/' . $user->company->logo_path);
                }
                return asset('LOGO LOGDRONE.png'); 
            })
            ->brandLogoHeight('2.5rem') 
            ->brandName('LogDrone System BBE') 
            ->favicon(asset('logdrone-logo.jpg?v=999')) 
            ->maxContentWidth('full') 
            
            ->colors([
                'primary' => Color::Emerald, 
                'gray' => Color::Slate,
            ])
            
            ->sidebarCollapsibleOnDesktop()

            ->navigationGroups([
                NavigationGroup::make()->label('Log Operasional')->collapsible(true)->collapsed(false), 
                NavigationGroup::make()->label('Master Data')->collapsible(true)->collapsed(true), 
                NavigationGroup::make()->label('Inventory & Asset Management')->collapsible(true)->collapsed(true),
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([\App\Filament\Pages\Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class,
                AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class,
                SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="manifest" href="/manifest.json?v=2">
                    <meta name="theme-color" content="#10B981">
                    <style>
                        /* 🚀 NUCLEAR FIX LOGO: Paksa Logo Proporsional */
                        .fi-logo img, 
                        .fi-simple-header img,
                        .fi-simple-main img {
                            width: auto !important; 
                            height: auto !important;
                            max-height: 80px !important;
                            max-width: 100% !important;
                            object-fit: contain !important;
                            display: block !important;
                            margin: 0 auto !important;
                        }
                        
                        .fi-logo, .fi-simple-header {
                            display: flex !important;
                            justify-content: center !important;
                            width: 100% !important;
                        }

                        /* 🎨 STYLE GLOBAL UNTUK LOGIN & AUTH */
                        .dji-clean-container {
                            position: fixed !important; top: 0 !important; left: 0 !important; 
                            width: 100vw !important; height: 100vh !important;
                            background-color: #030712 !important; z-index: 99999 !important; 
                            display: flex !important; font-family: sans-serif !important; overflow: hidden !important;
                        }
                        
                        /* 🔥 FIX PROPORSIONAL: Form 50%, Foto 50% */
                        .panel-form {
                            width: 50% !important; /* Diubah menjadi 50% agar seimbang */
                            min-width: 400px !important; 
                            height: 100vh !important; display: flex !important; flex-direction: column !important; 
                            justify-content: center !important; align-items: center !important; 
                            padding: 40px !important; background-color: #030712 !important; position: relative !important; z-index: 2 !important;
                        }
                        
                        .panel-image {
                            width: 50% !important; /* Diubah menjadi 50% agar seimbang */
                            height: 100vh !important; 
                            background-image: url("/—Pngtree—an image of a camera_12941482.jpg") !important;
                            background-size: cover !important; 
                            background-position: center center !important; 
                            background-repeat: no-repeat !important;
                            position: relative !important;
                        }
                        
                        .panel-image::before {
                            content: "" !important; position: absolute !important; inset: 0 !important;
                            background: linear-gradient(90deg, #030712 0%, rgba(3, 7, 18, 0.9) 10%, rgba(16, 185, 129, 0.18) 45%, rgba(16, 185, 129, 0.05) 70%, rgba(3, 7, 18, 0) 100%) !important; z-index: 1 !important;
                        }
                        
                        @media (max-width: 1024px) {
                            .panel-form { width: 100vw !important; min-width: 100vw !important; padding: 40px 20px !important; }
                            .panel-image { display: none !important; }
                        }
                    </style>'
            )
            
            ->renderHook(
                PanelsRenderHook::BODY_END,
                function(): string {
                    if (!auth()->check()) { return ''; }
                    return Blade::render('
                        <script>
                            if ("serviceWorker" in navigator) {
                                window.addEventListener("load", function() {
                                    navigator.serviceWorker.register("/sw.js").catch(function(err) {
                                        console.log("SW failed: ", err);
                                    });
                                });
                            }
                        </script>

                        <style>
                            /* PWA PROMPT STYLES */
                            #pwa-install-prompt {
                                position: fixed; bottom: 0; left: 0; right: 0; z-index: 99999;
                                padding: 1.5rem; transform: translateY(150%);
                                transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
                                display: flex; justify-content: center; pointer-events: none;
                            }
                            #pwa-install-prompt.pwa-slide-up { transform: translateY(0); pointer-events: auto; }
                            .pwa-card {
                                background-color: #0f172a; border: 1px solid rgba(51, 65, 85, 0.6);
                                border-radius: 1.5rem; padding: 1.25rem; width: 100%; max-width: 28rem;
                                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); font-family: ui-sans-serif, system-ui, sans-serif;
                                position: relative; overflow: hidden;
                            }
                            .pwa-glow { position: absolute; top: -2rem; right: -2rem; width: 8rem; height: 8rem; background-color: rgba(16, 185, 129, 0.15); border-radius: 9999px; filter: blur(24px); pointer-events: none; }
                            .pwa-header { display: flex; align-items: center; gap: 1rem; position: relative; z-index: 10; margin-bottom: 1.25rem; }
                            .pwa-icon { width: 3.5rem; height: 3.5rem; background: white; border-radius: 1rem; padding: 0.25rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
                            .pwa-icon img { width: 100%; height: 100%; object-fit: cover; border-radius: 0.75rem; }
                            .pwa-text h3 { color: white; font-weight: 900; font-size: 1.125rem; margin: 0 0 0.25rem 0; }
                            .pwa-text p { color: #94a3b8; font-size: 0.75rem; margin: 0; line-height: 1.4; }
                            .pwa-actions { display: flex; gap: 0.75rem; position: relative; z-index: 10; }
                            .pwa-btn { flex: 1; padding: 0.75rem 1rem; border-radius: 1rem; font-weight: bold; font-size: 0.8rem; border: none; cursor: pointer; text-align: center; transition: all 0.2s; }
                            .pwa-btn-cancel { background-color: #1e293b; color: #cbd5e1; border: 1px solid #334155; }
                            .pwa-btn-install { background-color: #10b981; color: white; box-shadow: 0 4px 14px 0 rgba(16,185,129,0.39); }
                        </style>

                        <div id="pwa-install-prompt">
                            <div class="pwa-card">
                                <div class="pwa-glow"></div>
                                <div class="pwa-header">
                                    <div class="pwa-icon">
                                        <img src="/icons/icon-512x512.png" alt="App Icon">
                                    </div>
                                    <div class="pwa-text">
                                        <h3>LogDrone App</h3>
                                        <p>Install aplikasi ini di Layar Utama HP-mu untuk akses lebih cepat dan mudah.</p>
                                    </div>
                                </div>
                                <div class="pwa-actions">
                                    <button id="pwa-close-btn" class="pwa-btn pwa-btn-cancel">Nanti Saja</button>
                                    <button id="pwa-install-btn" class="pwa-btn pwa-btn-install">Install App</button>
                                </div>
                            </div>
                        </div>

                        <script>
                            let deferredPrompt;
                            const installPrompt = document.getElementById("pwa-install-prompt");
                            const installBtn = document.getElementById("pwa-install-btn");
                            const closeBtn = document.getElementById("pwa-close-btn");
                            const isDismissed = localStorage.getItem("pwa_prompt_dismissed");
                            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                            function showPrompt() {
                                if (!isDismissed) {
                                    setTimeout(() => { installPrompt.classList.add("pwa-slide-up"); }, 1500);
                                }
                            }

                            function hidePrompt() {
                                installPrompt.classList.remove("pwa-slide-up");
                                localStorage.setItem("pwa_prompt_dismissed", "true");
                            }

                            window.addEventListener("beforeinstallprompt", (e) => {
                                e.preventDefault();
                                deferredPrompt = e;
                                showPrompt();
                            });

                            document.addEventListener("DOMContentLoaded", () => {
                                if (isMobile && !deferredPrompt) { showPrompt(); }
                            });

                            installBtn.addEventListener("click", async () => {
                                if (deferredPrompt) {
                                    deferredPrompt.prompt();
                                    await deferredPrompt.userChoice;
                                    deferredPrompt = null;
                                    hidePrompt();
                                } else {
                                    alert("Untuk menginstall: Tekan ikon Share di browser, lalu pilih Add to Home Screen.");
                                    hidePrompt();
                                }
                            });

                            closeBtn.addEventListener("click", () => { hidePrompt(); });
                        </script>
                    ');
                }
            ); 
    }
}