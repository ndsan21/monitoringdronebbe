<div>
<style>
    .dji-clean-container { min-height: 100vh; width: 100%; display: flex; background-color: #030712; font-family: sans-serif; overflow: hidden; position: relative; }
    .panel-form { width: 42%; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; background-color: #030712; z-index: 10; }
    .panel-image { flex: 1; background-image: url("/—Pngtree—an image of a camera_12941482.jpg"); background-size: cover; background-position: center; position: relative; }
    .panel-image::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #030712 0%, rgba(3, 7, 18, 0.9) 10%, rgba(16, 185, 129, 0.18) 45%, rgba(16, 185, 129, 0.05) 70%, rgba(3, 7, 18, 0) 100%); }
    
    .login-content-wrapper { width: 100%; max-width: 350px; }
    .brand-section { display: flex; flex-direction: column; align-items: center; margin-bottom: 30px; }
    .logo-box img { height: 70px !important; width: auto !important; object-fit: contain !important; filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.25)); }
    
    .btn-submit { width: 100%; background: #059669; color: white; padding: 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; }
    .btn-submit:hover { background: #047857; }
    /* Efek tombol redup saat loading */
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
    
    @keyframes muter { to { transform: rotate(360deg); } }
    .spinner { animation: muter 1s linear infinite; margin-right: 8px; height: 18px; width: 18px; color: white; }
    
    @media (max-width: 768px) { .panel-form { width: 100%; padding: 40px 20px; } .panel-image { display: none; } }
</style>

<div class="dji-clean-container">
    <div class="panel-form">
        <div class="login-content-wrapper">
            <div class="brand-section">
                <div class="logo-box">
                    <img src="/LOGO LOGDRONE.png" alt="Logo">
                </div>
            </div>
            
            <form wire:submit="request">
                {{ $this->form }}

                <button type="submit" class="btn-submit" style="margin-top: 20px;">
                    <span wire:loading.remove wire:target="request">Send Password Reset Link</span>
                    
                    <span wire:loading wire:target="request" style="display: flex; align-items: center;">
                        <svg class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle opacity="0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path opacity="0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending Email...
                    </span>
                </button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="/admin/login" style="color: #10b981; font-size: 0.8rem; text-decoration: none;">← Back to login</a>
            </div>
        </div>
    </div>
    <div class="panel-image"></div>
</div>

<div style="position: absolute; top: 0; right: 0; z-index: 999999; padding: 20px;">
    @livewire('notifications')
</div>
</div>