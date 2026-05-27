<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DJI FLIGHTHUB 2 - LogDrone</title>
    <link rel="icon" type="image/jpeg" href="/favicon.jpg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #040812; height: 100vh; width: 100%; display: flex; align-items: center; position: relative; overflow: hidden; }
        
        /* Background Drone Sinematik Sisi Kanan */
        .drone-bg {
            position: absolute; top: 0; right: 0; width: 55%; height: 100%;
            background-image: linear-gradient(90deg, #040812 0%, rgba(4,8,18,0.2) 20%, rgba(4,8,18,0) 100%), 
                              url("/pngtree-an-image-of-a-camera-mounted-to-a-black-drone-image_13113348.jpg");
            background-size: cover; background-position: center; z-index: 1;
        }

        /* Container Form Rata Kiri murni DJI */
        .login-wrapper { width: 100%; max-width: 440px; margin-left: 10%; position: relative; z-index: 10; }
        .brand-title { font-size: 2.2rem; font-weight: 800; color: #ffffff; letter-spacing: -0.01em; margin-bottom: 6px; text-transform: uppercase; }
        .brand-sub { font-size: 0.95rem; color: #94a3b8; margin-bottom: 35px; }
        
        /* Form Styling */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #94a3b8; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .form-control { width: 100%; background-color: #0f172a; border: 1px solid #1e293b; border-radius: 6px; padding: 12px 16px; color: #ffffff; font-size: 0.95rem; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15); }
        
        /* Button DJI style */
        .btn-submit { width: 100%; background-color: #2b6cb0; border: none; border-radius: 6px; padding: 14px; color: #ffffff; font-weight: 600; font-size: 0.95rem; cursor: pointer; margin-top: 10px; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #3182ce; }

        /* Footer Copyright */
        .footer-text { position: absolute; bottom: 25px; left: 10%; font-size: 0.75rem; color: #475569; letter-spacing: 0.05em; z-index: 10; }

        /* Responsive Mobile Layout */
        @media (max-width: 1023px) {
            body { background-image: linear-gradient(180deg, rgba(4,8,18,0.85) 0%, rgba(4,8,18,0.95) 100%), url("/pngtree-an-image-of-a-camera-mounted-to-a-black-drone-image_13113348.jpg"); background-size: cover; background-position: center; justify-content: center; padding: 20px; }
            .drone-bg { display: none; }
            .login-wrapper { margin-left: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(20px); padding: 35px 25px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); }
            .brand-title, .brand-sub { text-align: center; }
            .footer-text { left: 50%; transform: translateX(-50%); text-align: center; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="drone-bg"></div>

    <div class="login-wrapper">
        <h1 class="brand-title">DJI FLIGHTHUB 2</h1>
        <p class="brand-sub">Operational & Drone Fleet Management Command</p>

        <form method="POST" action="{{ route('filament.admin.auth.login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn-submit">Log In</button>
        </form>
    </div>

    <div class="footer-text">
        DJI FLIGHTHUB CLONE © 2026 PRIVACY POLICY TERMS OF USE
    </div>

</body>
</html>