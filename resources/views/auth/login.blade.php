<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wing POS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ipoa-navy:   #1B2B4B;
            --ipoa-navy-d: #0F1726;
            --ipoa-gold:   #C9A227;
            --ipoa-gold-d: #a07d1a;
        }

        /* ---- Navbar ---- */
        .navbar {
            position: fixed;
            top: 0; left: 0; width: 100%;
            background: transparent;
            backdrop-filter: blur(0px);
            padding: 15px 30px;
            border-bottom: 1px solid rgba(255,255,255,0);
            z-index: 1000;
            transition: all 0.4s ease;
        }
        .navbar.scrolled {
            background: rgba(15,23,38,0.92);
            backdrop-filter: blur(14px);
            padding: 10px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 18px !important;
            border-radius: 8px;
        }
        .nav-link:hover { color: #fff !important; background: rgba(255,255,255,0.08); }
        .btn-nav-primary {
            background: linear-gradient(135deg, var(--ipoa-gold) 0%, var(--ipoa-gold-d) 100%);
            color: #fff !important;
            border: none;
            box-shadow: 0 4px 14px rgba(201,162,39,0.35);
            transition: all 0.3s ease;
        }
        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(201,162,39,0.5);
        }

        /* ---- Body / background ---- */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(150deg, #1B2B4B 0%, #0F1726 55%, #162035 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding-top: 80px;
        }

        /* Decorative blobs */
        body::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(201,162,39,0.13) 0%, transparent 65%);
            border-radius: 50%;
            top: -120px; right: -120px;
            animation: float 7s ease-in-out infinite;
            z-index: 0;
        }
        body::after {
            content: '';
            position: absolute;
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(26,188,156,0.07) 0%, transparent 65%);
            border-radius: 50%;
            bottom: -140px; left: -80px;
            animation: float 9s ease-in-out infinite reverse;
            z-index: 0;
        }

        /* Dot-grid overlay */
        .bg-dots {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 26px 26px;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(28px); }
        }

        /* ---- Wrapper ---- */
        .login-wrapper {
            z-index: 1;
            width: 100%;
            max-width: 1060px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 60px;
        }

        /* ---- Lamp character ---- */
        .lamp-container {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            flex: 1;
            min-height: 480px;
            position: relative;
        }

        /* Brand tagline beside lamp */
        .lamp-brand {
            position: absolute;
            top: 50px;
            left: 0; right: 0;
            text-align: center;
        }
        .lamp-brand .overline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--ipoa-gold);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            margin-bottom: 0.8rem;
        }
        .lamp-brand .overline::before,
        .lamp-brand .overline::after {
            content: '';
            display: block;
            flex: 1;
            height: 1px;
            background: rgba(201,162,39,0.4);
        }
        .lamp-brand h2 {
            color: #fff;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .lamp-brand h2 em {
            color: var(--ipoa-gold);
            font-style: normal;
        }
        .lamp-brand p {
            color: rgba(255,255,255,0.5);
            font-size: 0.84rem;
            margin-top: 0.5rem;
        }

        .lamp {
            position: relative;
            width: 120px;
            height: 180px;
            animation: lampGlow 3.5s ease-in-out infinite;
            margin-top: 180px;
        }

        .lamp-bulb {
            position: absolute;
            width: 100px; height: 90px;
            background: linear-gradient(135deg, #fff9e6 0%, #ffe6aa 100%);
            border-radius: 50% 50% 50% 40%;
            top: 0; left: 10px;
            box-shadow:
                0 0 35px rgba(201,162,39,0.75),
                0 0 60px rgba(201,162,39,0.4),
                inset -2px -2px 10px rgba(0,0,0,0.08),
                inset 8px 8px 18px rgba(255,255,255,0.3);
        }

        .lamp-face {
            position: absolute;
            top: 20px; left: 14px;
            width: 92px; height: 50px;
            display: flex;
            align-items: center;
            justify-content: space-around;
        }
        .lamp-eye {
            width: 18px; height: 18px;
            background: #1B2B4B;
            border-radius: 50%;
            position: relative;
            animation: blink 3.5s ease-in-out infinite;
        }
        .lamp-eye::after {
            content: '';
            position: absolute;
            width: 6px; height: 6px;
            background: white;
            border-radius: 50%;
            top: 3px; left: 5px;
        }
        @keyframes blink {
            0%, 48%, 52%, 100% { height: 18px; }
            50% { height: 3px; }
        }
        .lamp-smile {
            position: absolute;
            width: 28px; height: 14px;
            border: 2px solid #1B2B4B;
            border-top: none;
            border-radius: 0 0 28px 28px;
            bottom: 10px; left: 36px;
        }
        .lamp-pole {
            position: absolute;
            width: 8px; height: 120px;
            background: linear-gradient(to bottom, #e0e0e0, #c8c8c8);
            left: 56px; top: 85px;
            border-radius: 4px;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.25);
        }
        .lamp-pole::before {
            content: '';
            position: absolute;
            width: 2px; height: 55px;
            background: linear-gradient(to bottom, var(--ipoa-gold) 0%, var(--ipoa-gold-d) 100%);
            left: 3px; top: -28px;
            border-radius: 2px;
            box-shadow: 0 0 8px rgba(201,162,39,0.7);
            animation: cordSway 2.5s ease-in-out infinite;
        }
        @keyframes cordSway {
            0%, 100% { transform: rotate(0deg); transform-origin: center top; }
            50% { transform: rotate(2.5deg); transform-origin: center top; }
        }
        .lamp-switch {
            position: absolute;
            width: 20px; height: 34px;
            background: linear-gradient(to right, #2a2a2a, #444);
            border-radius: 10px;
            top: 50px; right: -26px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);
            border: 1px solid #555;
        }
        .lamp-switch:hover { background: linear-gradient(to right, #3a3a3a, #555); transform: scale(1.05); }
        .lamp-switch::before {
            content: '';
            position: absolute;
            width: 14px; height: 14px;
            background: #888;
            border-radius: 50%;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
        }
        .lamp-base {
            position: absolute;
            width: 80px; height: 58px;
            background: radial-gradient(ellipse, #3a3a3a 0%, #1a1a1a 100%);
            border-radius: 50%;
            top: 180px; left: 20px;
            box-shadow: 0 10px 28px rgba(0,0,0,0.6);
        }
        @keyframes lampGlow {
            0%, 100% { filter: drop-shadow(0 0 28px rgba(201,162,39,0.55)) drop-shadow(0 0 55px rgba(201,162,39,0.25)); }
            50%       { filter: drop-shadow(0 0 45px rgba(201,162,39,0.75)) drop-shadow(0 0 80px rgba(201,162,39,0.4)); }
        }
        .lamp.off .lamp-bulb {
            background: linear-gradient(135deg, #4a4a4a, #333);
            box-shadow: 0 0 8px rgba(80,80,80,0.2);
        }
        .lamp.off { animation: none; filter: none; }

        /* ---- Login card ---- */
        .login-card {
            flex: 1;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(201,162,39,0.35);
            border-radius: 20px;
            padding: 48px 42px;
            box-shadow:
                0 0 40px rgba(201,162,39,0.18),
                0 20px 60px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.06);
            animation: slideUp 0.55s ease-out, cardGlow 3.5s ease-in-out infinite;
            max-width: 440px;
        }
        @keyframes cardGlow {
            0%, 100% { box-shadow: 0 0 35px rgba(201,162,39,0.18), 0 20px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.06); }
            50%       { box-shadow: 0 0 55px rgba(201,162,39,0.3),  0 20px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.09); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-logo {
            text-align: center;
            margin-bottom: 0.75rem;
        }
        .card-logo-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--ipoa-gold) 0%, var(--ipoa-gold-d) 100%);
            border-radius: 14px;
            margin-bottom: 0.5rem;
            box-shadow: 0 6px 20px rgba(201,162,39,0.4);
        }
        .card-logo-mark i { color: #fff; font-size: 1.5rem; }

        .login-title {
            text-align: center;
            margin-bottom: 8px;
        }
        .login-title h1 {
            color: #fff;
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .login-subtitle {
            color: rgba(255,255,255,0.5);
            font-size: 0.86rem;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Gold underline accent */
        .gold-line {
            width: 40px; height: 2px;
            background: var(--ipoa-gold);
            border-radius: 1px;
            margin: 0.6rem auto 1.8rem;
        }

        .form-label {
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .form-group { margin-bottom: 18px; }

        .form-control {
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(201,162,39,0.2);
            border-radius: 11px;
            padding: 13px 16px;
            font-size: 0.92rem;
            color: #fff;
            font-family: 'Inter', sans-serif;
            transition: all 0.25s ease;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3); }
        .form-control:focus {
            background: rgba(255,255,255,0.09);
            border-color: var(--ipoa-gold);
            box-shadow: 0 0 0 3px rgba(201,162,39,0.14);
            outline: none;
            color: #fff;
        }

        .form-check-input {
            background: rgba(255,255,255,0.08);
            border: 1.5px solid rgba(201,162,39,0.25);
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .form-check-input:checked {
            background: var(--ipoa-gold);
            border-color: var(--ipoa-gold);
            box-shadow: 0 0 8px rgba(201,162,39,0.45);
        }
        .form-check-label {
            color: rgba(255,255,255,0.55);
            font-size: 0.82rem;
            margin-left: 6px;
            cursor: pointer;
        }
        .form-check-input:checked ~ .form-check-label { color: var(--ipoa-gold); }

        .btn-login {
            background: linear-gradient(135deg, var(--ipoa-gold) 0%, var(--ipoa-gold-d) 100%);
            border: none;
            border-radius: 11px;
            padding: 14px 20px;
            font-weight: 700;
            font-size: 0.97rem;
            color: #fff;
            margin-top: 22px;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(201,162,39,0.35);
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(201,162,39,0.5);
            background: linear-gradient(135deg, #d4ad2e 0%, #b5901d 100%);
        }
        .btn-login:active { transform: translateY(-1px); }

        .forgot-link {
            color: var(--ipoa-gold);
            text-decoration: none;
            font-size: 0.82rem;
            transition: color 0.2s;
            font-weight: 500;
        }
        .forgot-link:hover { color: #d4ad2e; }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            color: rgba(255,255,255,0.4);
            font-size: 0.82rem;
        }
        .register-link {
            color: var(--ipoa-gold);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }
        .register-link:hover { color: #d4ad2e; }

        .alert {
            border-radius: 11px;
            font-size: 0.84rem;
            border: none;
            padding: 14px 16px;
            margin-bottom: 18px;
            animation: slideDown 0.35s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-success {
            background: rgba(39,174,96,0.14);
            border: 1px solid rgba(39,174,96,0.3);
            color: #6fcf97;
        }
        .alert-danger {
            background: rgba(192,57,43,0.14);
            border: 1px solid rgba(192,57,43,0.3);
            color: #eb5757;
        }
        .btn-close { filter: invert(1) brightness(1.5); opacity: 0.6; }
        .btn-close:hover { opacity: 1; }

        /* ---- Responsive ---- */
        @media (max-width: 820px) {
            .login-wrapper { flex-direction: column; gap: 28px; }
            .lamp-container { min-height: 280px; }
            .lamp-brand { top: 0; }
            .lamp { margin-top: 160px; width: 100px; height: 150px; }
            .lamp-bulb { width: 82px; height: 74px; }
            .lamp-pole { height: 100px; top: 72px; }
            .lamp-base { width: 70px; height: 50px; top: 150px; }
            .login-card { padding: 36px 28px; }
        }
    </style>
</head>
<body>
<div class="bg-dots"></div>

@include('layouts.partials.guest-nav')

<div class="login-wrapper">
    <!-- Lamp character -->
    <div class="lamp-container">
        <div class="lamp-brand">
            <div class="overline">Wing POS System</div>
            <h2>Point of <em>Sale</em></h2>
            <p>Retail management made effortless</p>
        </div>
        <div class="lamp" id="lamp">
            <div class="lamp-bulb">
                <div class="lamp-face">
                    <div class="lamp-eye"></div>
                    <div class="lamp-eye"></div>
                </div>
                <div class="lamp-smile"></div>
            </div>
            <div class="lamp-pole">
                <div class="lamp-switch" id="lampSwitch"></div>
            </div>
            <div class="lamp-base"></div>
        </div>
    </div>

    <!-- Login form -->
    <div class="login-card">
        <div class="card-logo">
            <div class="card-logo-mark">
                <i class="bi bi-shop"></i>
            </div>
        </div>
        <div class="login-title">
            <h1>Welcome Back</h1>
        </div>
        <div class="gold-line"></div>
        <p class="login-subtitle">Sign in to access Wing POS dashboard</p>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-circle-fill me-2" style="flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="login_role" class="form-label">Login As</label>
                <select id="login_role" name="login_role" class="form-control">
                    <option value="super_admin" selected>Super Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="name@company.com"
                       value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback d-block" style="color:#eb5757; font-size:11px; margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Enter your password" required>
                @error('password')
                    <div class="invalid-feedback d-block" style="color:#eb5757; font-size:11px; margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label class="form-check-label" for="remember">Keep me signed in</label>
                </div>
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-login">
                Sign In &nbsp;<i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="{{ route('register') }}" class="register-link">Create one</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const lamp = document.querySelector('#lamp');
    const lampSwitch = document.querySelector('#lampSwitch');
    const form = document.querySelector('form');
    let lampIsOn = true;

    lampSwitch.addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        lampIsOn = !lampIsOn;
        lamp.classList.toggle('off', !lampIsOn);
    });

    form.addEventListener('focus', () => {
        if (lampIsOn) lamp.style.filter = 'drop-shadow(0 0 50px rgba(201,162,39,0.85))';
    }, true);
    form.addEventListener('blur', () => {
        if (lampIsOn) lamp.style.filter = '';
    }, true);

    form.addEventListener('submit', function() {
        this.querySelector('.btn-login').style.opacity = '0.8';
    });

    window.addEventListener('scroll', function() {
        document.querySelector('.navbar').classList.toggle('scrolled', window.scrollY > 50);
    });
</script>
</body>
</html>
