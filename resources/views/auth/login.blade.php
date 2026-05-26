<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wing POS | Retail Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.public_key') ?? '' }}"></script>
    <style>
        :root { --primary: #6366f1; --border: #e2e8f0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Sora, sans-serif; background: linear-gradient(135deg, #f8fafc, #f1f5f9); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; position: relative; }
        body::before { content: ''; position: fixed; width: 500px; height: 500px; background: radial-gradient(circle, rgba(99, 102, 241, 0.08), transparent); border-radius: 50%; top: -150px; left: -150px; z-index: 0; animation: float 8s infinite ease-in-out; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 100; background: transparent; padding: 25px 40px; }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; color: #0f172a !important; }
        .container-main { position: relative; z-index: 1; width: 100%; max-width: 420px; }
        .login-card { background: white; border: 1px solid var(--border); border-radius: 16px; padding: 50px 40px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05); }
        .login-header { text-align: center; margin-bottom: 35px; }
        .login-header h1 { font-size: 1.8rem; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
        .login-header p { color: #64748b; font-size: 0.95rem; }
        .form-group { margin-bottom: 20px; }
        .form-label { font-weight: 600; color: #0f172a; margin-bottom: 10px; }
        .form-control { border: 1.5px solid var(--border); border-radius: 10px; padding: 12px 16px; transition: all 0.3s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        .btn-login { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), #4f46e5); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(99, 102, 241, 0.4); }
        .login-footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border); }
        .login-footer a { color: var(--primary); text-decoration: none; font-weight: 700; }
        @keyframes float { 50% { transform: translateY(30px); } }
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="navbar-brand" href="#"><i class="bi bi-shop"></i> Wing POS</a>
    </nav>
    <div class="container-main">
        <div class="login-card">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to manage your business</p>
            </div>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <div>
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Keep signed in</label>
                    </div>
                    <a href="{{ route('password.request') }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">Forgot?</a>
                </div>

                <!-- reCAPTCHA hidden field -->
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error:</strong>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <button type="submit" class="btn-login" id="login-btn">Sign In <i class="bi bi-arrow-right" style="margin-left: 8px;"></i></button>
            </form>
            <div class="login-footer">
                <p>Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- reCAPTCHA v3 Script --}}
    @if(config('services.recaptcha.public_key'))
    <script>
        document.getElementById('login-btn').addEventListener('click', function(e) {
            const publicKey = '{{ config("services.recaptcha.public_key") }}';
            
            if (!publicKey) {
                return; // Skip if no key configured
            }

            e.preventDefault();
            
            grecaptcha.ready(function() {
                grecaptcha.execute(publicKey, { action: 'login' }).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.querySelector('form').submit();
                });
            });
        });
    </script>
    @endif
</body>
</html>
