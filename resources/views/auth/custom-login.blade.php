<div>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    /* Kontainer Utama */
    .dji-clean-container { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: #030712; z-index: 99999; display: flex; font-family: 'Inter', sans-serif; overflow: hidden; }
    
    /* 🔥 FIX PROPORSIONAL: Form 50%, Foto 50% */
    .panel-form { width: 50%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 60px; background-color: #030712; position: relative; z-index: 2; box-shadow: 20px 0 50px rgba(0,0,0,0.5); }
    .panel-image { width: 50%; height: 100%; background-image: url("/—Pngtree—an image of a camera_12941482.jpg"); background-size: cover; background-position: center; position: relative; }
    
    .panel-image::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #030712 0%, rgba(3, 7, 18, 0.9) 10%, rgba(16, 185, 129, 0.18) 45%, rgba(16, 185, 129, 0.05) 70%, rgba(3, 7, 18, 0) 100%); z-index: 1; }
    
    /* Wrapper punya gap:36px, ini yang bikin tadi renggang kalau di luar form */
    .login-content-wrapper { width: 100%; max-width: 380px; display: flex; flex-direction: column; gap: 36px; }
    
    /* Perbaikan Logo (Supaya tidak gepeng) */
    .brand-section { display: flex; flex-direction: row; align-items: center; justify-content: flex-start; gap: 16px; width: 100%; margin-bottom: 10px; }
    .logo-box { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 80px; }
    .logo-box img { height: 72px !important; width: auto !important; max-width: 100% !important; object-fit: contain !important; filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.25)); }
    
    .text-box { display: flex; flex-direction: column; justify-content: center; min-height: 72px; }
    .brand-title { font-size: 1.65rem; font-weight: 800; color: #ffffff; letter-spacing: -0.01em; line-height: 1; text-transform: uppercase; margin: 0; padding: 0; white-space: nowrap; }
    .brand-sub { font-size: 0.74rem; color: #64748b; margin-top: 4px; font-weight: 500; line-height: 1.2; letter-spacing: 0.01em; }
    
    .form-group { margin-bottom: 22px; }
    .form-label { display: block; color: #cbd5e1; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
    .form-input { width: 100%; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(10px); border: 1px solid #1e293b; border-radius: 8px; padding: 13px 16px; color: #ffffff; font-size: 0.95rem; outline: none; }
    
    /* Tombol Utama */
    .btn-submit { width: 100%; background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 8px; padding: 14px; color: #ffffff; font-weight: 600; cursor: pointer; border: none; transition: all 0.3s ease; }
    .btn-submit:hover { opacity: 0.9; }

    .error-message { color: #ef4444; font-size: 0.75rem; margin-top: 8px; display: block; }
    .footer-clean { position: absolute; bottom: 35px; left: 0; width: 100%; text-align: center; font-size: 0.7rem; color: #475569; }
    
    @media (max-width: 1024px) {
        .panel-form { width: 100%; padding: 40px 20px; }
        .panel-image { display: none; }
        .brand-section { align-items: center; flex-direction: column; }
        .logo-box { width: 100%; }
        .logo-box img { height: 90px !important; }
        .brand-title, .brand-sub { text-align: center; white-space: normal; }
    }
</style>

<div class="dji-clean-container">
    <div class="panel-form">
        <div class="login-content-wrapper">
            <div class="brand-section">
                <div class="logo-box">
                    <img src="/LOGO LOGDRONE.png" alt="Company Logo">
                </div>
                <div class="text-box">
                    <h1 class="brand-title">LOGDRONE COMMAND</h1>
                    <p class="brand-sub">Multi-Tenant Drone Fleet & Operational Command Center</p>
                </div>
            </div>

            <form wire:submit="authenticate">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" wire:model="data.email" class="form-input" required autofocus>
                    @error('data.email') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" wire:model="data.password" class="form-input" required>
                    <div style="margin-top: 10px; text-align: right;">
                        <a href="{{ filament()->getRequestPasswordResetUrl() }}" style="color: #10b981; font-size: 0.75rem; text-decoration: none; font-weight: 600;">Forgot Password?</a>
                    </div>
                    @error('data.password') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                
                <button type="submit" class="btn-submit">Log In</button>

                <!-- 🔥 TEKS REGISTRASI DIPINDAH KE DALAM FORM BIAR DEKAT -->
                <div style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: #cbd5e1;">
                    Don't have an account? 
                    <a href="{{ filament()->getRegistrationUrl() }}" style="color: #10b981; font-weight: 600; text-decoration: none; transition: color 0.3s ease;" onmouseover="this.style.color='#34d399'" onmouseout="this.style.color='#10b981'">
                        Create New Account
                    </a>
                </div>
            </form>
            
        </div>
        <div class="footer-clean">LOGDRONE COMMAND SMD © 2026 | PRIVACY POLICY</div>
    </div>
    <div class="panel-image"></div>
</div>
</div>