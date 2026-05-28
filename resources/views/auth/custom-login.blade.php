<div> 
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        /* Kontainer Utama Fullscreen */
        .dji-clean-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: #030712;
            z-index: 99999;
            display: flex;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            overflow: hidden;
        }

        /* Sisi Kiri: Panel Kontrol Form */
        .panel-form {
            width: 42%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center; 
            align-items: center;
            padding: 60px;
            background-color: #030712;
            position: relative;
            z-index: 2;
            box-shadow: 20px 0 50px rgba(0,0,0,0.5);
        }

        /* Sisi Kanan: Panel Gambar */
        .panel-image {
            width: 58%;
            height: 100%;
            /* 🛠️ FIX PATH DESKTOP: Menggunakan nama file asli Pngtree Anda */
            background-image: url("/—Pngtree—an image of a camera_12941482.jpg");
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Gradasi Hijau Sinematik Menyelimuti Badan Drone */
        .panel-image::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #030712 0%, rgba(3, 7, 18, 0.9) 10%, rgba(16, 185, 129, 0.18) 45%, rgba(16, 185, 129, 0.05) 70%, rgba(3, 7, 18, 0) 100%);
            z-index: 1;
        }

        /* Bungkus Konten */
        .login-content-wrapper {
            width: 100%;
            max-width: 380px; 
            display: flex;
            flex-direction: column;
            gap: 36px;
        }

        /* Membuat logo dan teks berjejer ke samping (horizontal) */
.brand-section {
    display: flex;
    flex-direction: row; 
    align-items: center;    /* ◄--- Mengunci logo dan blok teks tetap sejajar di satu garis tengah vertikal */
    justify-content: flex-start;
    gap: 16px;              /* ◄--- Jarak diperketat sedikit agar logo dan teks tidak terasa lepas */
    width: 100%;
    margin-bottom: 10px;    /* ◄--- Memberi napas ke form di bawahnya */
}

.logo-box {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;         /* ◄--- KUNCI: Menjaga logo tidak tertekan gepeng oleh teks kanan */
}

.logo-box img {
    height: 72px;           /* ◄--- Ukuran golden ratio untuk disandingkan dengan teks 2 baris */
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.25));
}

.text-box {
    display: flex;
    flex-direction: column;
    justify-content: center; /* ◄--- Memastikan judul dan sub-judul terdistribusi seimbang dari tengah */
    min-height: 72px;        /* ◄--- Tingginya disamakan persis dengan tinggi logo agar simetris sempurna */
}

.brand-title {
    font-size: 1.65rem;      /* ◄--- Ukuran di-scale ke titik paling pas untuk lebar form */
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.01em;
    line-height: 1;          /* ◄--- Mengecilkan line-height agar tidak membuang space vertikal */
    text-transform: uppercase;
    margin: 0;
    padding: 0;
    white-space: nowrap; 
}

.brand-sub {
    font-size: 0.74rem;      
    color: #64748b;          /* ◄--- Warna digeser ke slate agar teks utama "COMMAND" lebih menonjol */
    margin-top: 4px;         /* ◄--- Jarak pendek yang pas agar tidak terlihat melayang pisah */
    font-weight: 500;
    line-height: 1.2;
    letter-spacing: 0.01em;
}

        /* Form Input Styling */
        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            color: #cbd5e1;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 13px 16px;
            color: #ffffff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #10b981;
            background-color: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
            transform: translateY(-1px);
        }

        /* Tombol Login Custom */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: 1px solid #065f46;
            border-radius: 8px;
            padding: 14px;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.2);
            display: block;
            text-align: center;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .register-box {
            margin-top: 24px;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }

        .register-box a {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            margin-left: 4px;
        }

        .register-box a:hover {
            color: #34d399;
            text-decoration: underline;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 8px;
            display: block;
            font-weight: 500;
        }

        /* Footer */
        .footer-clean {
            position: absolute;
            bottom: 35px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 0.7rem;
            color: #475569;
            letter-spacing: 0.05em;
            font-weight: 500;
        }
        
        .footer-clean span {
            margin: 0 6px;
            color: #334155;
        }

        /* Responsif Ponsel / Tablet */
        @media (max-width: 1024px) {
            .panel-form {
                width: 100%;
                padding: 40px 20px;
            }
            .panel-image {
                display: none;
            }
            .login-content-wrapper {
                max-width: 100%;
                padding: 0 20px;
            }
            /* 🛠️ FIX PATH MOBILE: Samakan juga dengan nama file asli Pngtree Anda */
            .dji-clean-container {
                background-image: linear-gradient(180deg, rgba(3, 7, 18, 0.95) 0%, #030712 100%), 
                                  url("/—Pngtree—an image of a camera_12941482.jpg");
                background-size: cover;
                background-position: center;
            }
            .brand-section {
                align-items: center;
            }
            .brand-title, .brand-sub {
                text-align: center;
            }
            .footer-clean {
                bottom: 25px;
            }
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

                <div class="form-container">
                    <form wire:submit="authenticate">
                        
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" id="email" wire:model="data.email" class="form-input" placeholder="name@company.com" required autofocus>
                            @error('data.email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" wire:model="data.password" class="form-input" placeholder="••••••••" required>
                            @error('data.password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn-submit">
                            <span wire:loading.remove>Log In</span>
                            <span wire:loading>Authenticating...</span>
                        </button>
                    </form>

                    @if (filament()->hasRegistration())
                        <div class="register-box">
                            Don't have an account? <a href="{{ filament()->getRegistrationUrl() }}">Register here</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="footer-clean">
                LOGDRONE COMMAND SMD © 2026 <span>|</span> PRIVACY POLICY <span>|</span> TERMS OF USE
            </div>
        </div>

        <div class="panel-image"></div>
        
    </div>
</div>