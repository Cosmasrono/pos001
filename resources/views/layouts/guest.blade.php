<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Wing POS') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:          #1B2B4B;
            --primary-hover:    #0F1726;
            --primary-light:    #E8ECF2;
            --gold:             #C9A227;
            --gold-hover:       #a07d1a;
            --gradient-primary: linear-gradient(135deg, #1B2B4B 0%, #0F1726 100%);
            --gradient-gold:    linear-gradient(135deg, #C9A227 0%, #a07d1a 100%);
            --background: hsl(220,20%,97%);
            --surface: #fff;
            --text-main: hsl(215,25%,22%);
            --text-muted: hsl(215,16%,47%);
            --border-color: hsl(214,32%,91%);
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --success: hsl(142,71%,45%);
            --success-light: hsl(142,71%,95%);
            --danger: hsl(0,84%,60%);
            --danger-light: hsl(0,84%,95%);
            --info: hsl(45,78%,52%);
            --info-light: hsl(45,78%,95%);
            --warning: hsl(38,92%,50%);
            --warning-light: hsl(38,92%,95%);
            --transition-base: 200ms cubic-bezier(0.4,0,0.2,1);
        }

        * { -webkit-font-smoothing: antialiased; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, hsl(210,40%,98%) 0%, hsl(220,40%,96%) 100%);
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
        }

        .guest-header {
            padding: 1.25rem 2rem;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--gradient-gold);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 15px;
            flex-shrink: 0;
        }

        .brand-text {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .guest-main {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .card {
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            border-radius: 16px;
            background: var(--surface);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all var(--transition-base);
            border: none;
            letter-spacing: 0.02em;
        }

        .btn-primary {
            background: var(--gradient-primary);
            box-shadow: 0 4px 12px rgba(27,43,75,0.3);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(27,43,75,0.4); color: white; }
        .btn-gold {
            background: var(--gradient-gold);
            box-shadow: 0 4px 12px rgba(201,162,39,0.35);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all var(--transition-base);
        }
        .btn-gold:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(201,162,39,0.5); color: white; }

        .alert {
            border: none;
            border-radius: 10px;
            border-left: 4px solid;
        }
        .alert-success { background: var(--success-light); border-left-color: var(--success); color: hsl(142,71%,25%); }
        .alert-danger  { background: var(--danger-light);  border-left-color: var(--danger);  color: hsl(0,84%,30%); }
        .alert-info    { background: var(--info-light);    border-left-color: var(--info);    color: hsl(199,89%,28%); }
        .alert-warning { background: var(--warning-light); border-left-color: var(--warning); color: hsl(38,92%,25%); }
    </style>
    @stack('styles')
</head>
<body>
    <header class="guest-header">
        <div class="brand-icon">
            <i class="bi bi-shop"></i>
        </div>
        <span class="brand-text">Wing POS</span>
    </header>

    <main class="guest-main">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
