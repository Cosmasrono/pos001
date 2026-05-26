<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Wing POS | Retail Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.public_key') ?? '' }}"></script>
    <style>
        :root { 
            --primary: #6366f1; 
            --success: #10b981; 
            --border: #e2e8f0; 
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Sora, sans-serif; 
            background: linear-gradient(135deg, #f8fafc, #f1f5f9); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
            position: relative; 
        }
        body::before { 
            content: ''; 
            position: fixed; 
            width: 500px; 
            height: 500px; 
            background: radial-gradient(circle, rgba(99, 102, 241, 0.08), transparent); 
            border-radius: 50%; 
            top: -150px; 
            left: -150px; 
            z-index: 0; 
            animation: float 8s infinite ease-in-out; 
        }
        .navbar { 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            z-index: 100; 
            background: transparent; 
            padding: 25px 40px; 
        }
        .navbar-brand { 
            font-weight: 700; 
            font-size: 1.5rem; 
            color: #0f172a !important; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .navbar-brand i { 
            background: linear-gradient(135deg, var(--primary), #ec4899); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }
        .container-main { 
            position: relative; 
            z-index: 1; 
            width: 100%; 
            max-width: 480px; 
        }
        .register-card { 
            background: white; 
            border: 1px solid var(--border); 
            border-radius: 16px; 
            padding: 50px 40px; 
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05); 
            max-height: 90vh; 
            overflow-y: auto; 
        }
        .register-header { text-align: center; margin-bottom: 35px; }
        .register-header h1 { font-size: 1.8rem; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
        .register-header p { color: #64748b; font-size: 0.95rem; }
        .form-group { margin-bottom: 20px; }
        .form-label { font-weight: 600; color: #0f172a; margin-bottom: 10px; }
        .form-control { border: 1.5px solid var(--border); border-radius: 10px; padding: 12px 16px; transition: all 0.3s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        .form-control.is-invalid { border-color: #ef4444; }
        .invalid-feedback { display: block; color: #ef4444; font-size: 0.85rem; margin-top: 6px; font-weight: 500; }
        .password-strength { height: 4px; background: var(--border); border-radius: 2px; margin-top: 8px; overflow: hidden; }
        .password-strength-bar { height: 100%; width: 0; transition: all 0.3s; border-radius: 2px; }
        .password-strength-bar.weak { width: 33%; background: #ef4444; }
        .password-strength-bar.medium { width: 66%; background: #f59e0b; }
        .password-strength-bar.strong { width: 100%; background: var(--success); }
        .form-check { margin-bottom: 25px; }
        .form-check-input { cursor: pointer; }
        .form-check-label { color: #64748b; font-size: 0.9rem; margin-left: 8px; cursor: pointer; }
        .form-check-label a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .btn-register { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), #4f46e5); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); margin-top: 10px; }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(99, 102, 241, 0.4); color: white; }
        .register-footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border); }
        .register-footer a { color: var(--primary); text-decoration: none; font-weight: 700; }
        .alert { border-radius: 10px; padding: 15px; margin-bottom: 20px; border: none; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        @keyframes float { 50% { transform: translateY(30px); } }
        @media (max-width: 576px) { .register-card { padding: 35px 25px; } .register-header h1 { font-size: 1.5rem; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="navbar-brand" href="#"><i class="bi bi-shop"></i> Wing POS</a>
    </nav>
    <div class="container-main">
        <div class="register-card">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Join Wing POS and manage your retail business</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-circle-fill" style="margin-right: 10px;"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label">Business Name</label>
                    <input type="text" class="form-control @error('shop_name') is-invalid @enderror" name="shop_name" value="{{ old('shop_name') }}" placeholder="Your Business Name" required autofocus>
                    @error('shop_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Your Full Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="you@business.com" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="+254 7XX XXX XXX">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required>
                    <div class="password-strength"><div class="password-strength-bar"></div></div>
                    @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="••••••••" required>
                    @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" name="terms" id="terms" {{ old('terms') ? 'checked' : '' }} required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                    @error('terms') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <!-- reCAPTCHA hidden field -->
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-register">

                <button type="submit" class="btn-register" id="register-btn"><i class="bi bi-person-plus" style="margin-right: 8px;"></i> Create Account</button>
            </form>

            <div class="register-footer">
                <p>Already have account? <a href="{{ route('login') }}">Sign in here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('password')?.addEventListener('input', function() {
            const strength = this.value.length < 6 ? 'weak' : (this.value.length < 10 ? 'medium' : 'strong');
            const bar = document.querySelector('.password-strength-bar');
            bar.className = 'password-strength-bar ' + strength;
        });
    </script>

    {{-- reCAPTCHA v3 for registration --}}
    @if(config('services.recaptcha.public_key'))
    <script>
        document.getElementById('register-btn').addEventListener('click', function(e) {
            const publicKey = '{{ config("services.recaptcha.public_key") }}';
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute(publicKey, { action: 'register' }).then(function(token) {
                    document.getElementById('g-recaptcha-response-register').value = token;
                    document.querySelector('form').submit();
                });
            });
        });
    </script>
    @endif
</body>
</html>
