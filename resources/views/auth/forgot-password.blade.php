<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wing POS - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --secondary-gradient: linear-gradient(135deg, #ff6b35 0%, #ff8555 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f1e 50%, #1a1a3e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-card {
            z-index: 1;
            width: 100%;
            max-width: 450px;
            background: rgba(20, 20, 35, 0.85);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 107, 53, 0.6);
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 0 40px rgba(255, 107, 53, 0.4), 0 20px 60px rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-title h1 { color: #fff; font-size: 28px; font-weight: 700; margin-bottom: 15px; text-align: center; }
        .auth-subtitle { color: #b0b0b0; font-size: 14px; text-align: center; margin-bottom: 35px; line-height: 1.6; }

        .form-label { font-weight: 600; color: #e0e0e0; font-size: 13px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1.5px solid rgba(255, 107, 53, 0.3);
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            color: #fff;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.15);
            outline: none;
            color: #fff;
        }

        .btn-auth {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8555 100%);
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            font-weight: 700;
            font-size: 16px;
            color: white;
            margin-top: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
            width: 100%;
            text-transform: uppercase;
        }
        .btn-auth:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(255, 107, 53, 0.5); }

        .back-link { color: #ff9b7d; text-decoration: none; font-size: 14px; display: block; text-align: center; margin-top: 25px; transition: color 0.3s ease; }
        .back-link:hover { color: #ff6b35; }

        .alert { border-radius: 12px; font-size: 14px; margin-bottom: 25px; border: none; }
        .alert-success { background: rgba(76, 175, 80, 0.15); color: #81c784; border: 1px solid rgba(76, 175, 80, 0.3); }
        .alert-danger { background: rgba(244, 67, 54, 0.15); color: #ef5350; border: 1px solid rgba(244, 67, 54, 0.3); }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-title">
            <h1>Forgot Password?</h1>
        </div>
        <p class="auth-subtitle">No problem. Just let us know your email address and we will email you a password reset link.</p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your registered email" value="{{ old('email') }}" required autofocus>
            </div>
            <button type="submit" class="btn btn-auth">
                Email Password Reset Link
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <i class="bi bi-arrow-left me-2"></i>Back to Login
        </a>
    </div>
</body>
</html>
