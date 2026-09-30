<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ROYALTRICO — Consent-Based Device Monitoring')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon-64.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand-1: #0d3bbf;
            --brand-2: #1b6ff5;
            --brand-3: #3b82f6;
            --ink: #141b2d;
            --muted: #64748b;
            --surface: #ffffff;
            --radius: 1.1rem;
        }
        * { -webkit-font-smoothing: antialiased; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #f8faff 0%, #eef2fb 100%);
            min-height: 100vh;
            color: var(--ink);
            background-attachment: fixed;
        }
        ::selection { background: rgba(27, 111, 245, .18); }
        ::-webkit-scrollbar { width: .6rem; height: .6rem; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c3d2f5; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #9db7ea; }

        /* App navbar */
        #app-nav {
            background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
            box-shadow: 0 .5rem 1.75rem rgba(11, 47, 149, .28);
        }
        #app-nav .navbar-brand { font-weight: 800; letter-spacing: -.01em; }
        #app-nav .navbar-brand img { border-radius: .45rem; }
        #app-nav .nav-link { font-weight: 600; }
        #app-nav .nav-link:hover, #app-nav .nav-link.active { color: #fff; text-shadow: 0 0 .5rem rgba(255,255,255,.25); }
        #app-nav .navbar-text { font-weight: 600; }
        #app-nav .btn-outline-light { border-radius: 999px; font-weight: 600; }

        /* Cards */
        .card {
            border: 0;
            border-radius: var(--radius);
            box-shadow: 0 .55rem 1.6rem rgba(13, 59, 191, .07);
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(15, 23, 42, .06);
            font-weight: 700;
        }
        .card-header.bg-white { background: transparent !important; }
        .stat-card {
            border: 0;
            border-radius: var(--radius);
            box-shadow: 0 .55rem 1.6rem rgba(13, 59, 191, .07);
            transition: transform .16s ease, box-shadow .16s ease;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 .9rem 2.1rem rgba(13, 59, 191, .12); }

        /* Buttons */
        .btn { border-radius: .8rem; font-weight: 600; }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            border: 0;
            box-shadow: 0 .4rem .9rem rgba(13, 59, 191, .22);
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background: linear-gradient(135deg, var(--brand-2), var(--brand-3)) !important;
            transform: translateY(-1px);
            box-shadow: 0 .55rem 1.2rem rgba(13, 59, 191, .28);
        }
        .btn-outline-primary {
            --bs-btn-color: var(--brand-2);
            --bs-btn-border-color: var(--brand-2);
            --bs-btn-hover-bg: var(--brand-2);
            --bs-btn-hover-border-color: var(--brand-2);
        }
        .btn-outline-danger, .btn-outline-success, .btn-outline-warning, .btn-outline-secondary { border-radius: .8rem; }

        /* Forms */
        .form-control, .form-select {
            border-radius: .75rem;
            border-color: #dbe3f0;
            padding-block: .6rem;
            background-color: #fcfdff;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand-3);
            box-shadow: 0 0 0 .25rem rgba(27, 111, 245, .14);
        }
        .form-check-input:checked { background-color: var(--brand-2); border-color: var(--brand-2); }
        .form-check-input:focus { box-shadow: 0 0 0 .25rem rgba(27,111,245,.14); }
        .form-label { font-weight: 600; color: #2c3a52; }

        /* Badges & dots */
        .badge { border-radius: 999px; font-weight: 600; }
        .status-badge { font-size: .7rem; }
        .status-dot {
            display: inline-block;
            width: .55rem;
            height: .55rem;
            border-radius: 50%;
            background: #94a3b8;
            vertical-align: middle;
            box-shadow: 0 0 0 .2rem rgba(100, 116, 139, .12);
        }
        .status-dot.online { background: #22c55e; box-shadow: 0 0 0 .2rem rgba(34, 197, 94, .18); }
        .status-dot.offline { background: #94a3b8; }

        /* Tables */
        .table { --bs-table-hover-bg: #f4f8ff; }
        .table thead th { text-transform: uppercase; font-size: .72rem; letter-spacing: .05em; color: var(--muted); border-bottom-width: 2px; }
        .table-light { --bs-table-bg: #f1f5fe; }

        /* Tabs */
        .nav-tabs { border: 0; gap: .35rem; }
        .nav-tabs .nav-link {
            border: 0;
            border-radius: .8rem;
            font-weight: 600;
            color: var(--muted);
            padding: .6rem 1.15rem;
            transition: all .15s ease;
        }
        .nav-tabs .nav-link:hover:not(.active) { color: var(--brand-2); background: #eef4ff; }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: #fff;
            box-shadow: 0 .35rem .8rem rgba(13, 59, 191, .25);
        }

        /* Helpers */
        .page-head { font-weight: 800; letter-spacing: -.02em; }
        .stat-icon {
            width: 3rem; height: 3rem; border-radius: .95rem;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
        }
        .icon-primary { background: #eaf2ff; color: var(--brand-2); }
        .icon-success { background: #e7f8ee; color: #14a05c; }
        .icon-danger { background: #fdecef; color: #dc3d4f; }
        .icon-warning { background: #fff5e0; color: #e09117; }
        .icon-info { background: #e7f6fb; color: #1598c0; }
        .os-avatar {
            width: 2.9rem; height: 2.9rem; border-radius: .95rem;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0;
        }
        .gradient-text {
            background: linear-gradient(135deg, var(--brand-1), var(--brand-3));
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .text-muted, .text-secondary { color: var(--muted) !important; }
        .auth-shell { max-width: 26rem; margin-inline: auto; }
        .auth-logo { width: 4.2rem; height: 4.2rem; border-radius: 1.1rem; object-fit: cover; box-shadow: 0 .6rem 1.4rem rgba(13,59,191,.25); }
        footer a { color: var(--muted); }
        footer a:hover { color: var(--brand-2); }
    </style>
</head>
<body>
@auth
<nav id="app-nav" class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo-transparent.png') }}" alt="ROYALTRICO" height="30">
            ROYALTRICO
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto ms-lg-3">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('devices.index') }}">Devices</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('alerts.index') }}">Alerts</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('alerts.rules') }}">Alert Rules</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('journey.index') }}">My Plan</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('shares.index') }}">Sharing</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('billing.index') }}">Billing</a></li>
                @php $unreadNotificationCount = auth()->user()->unreadNotifications()->count(); @endphp
                <li class="nav-item">
                    <a class="nav-link position-relative" href="{{ route('notifications.index') }}" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span id="nav-unread-badge" class="badge rounded-pill bg-danger position-absolute {{ $unreadNotificationCount ? '' : 'd-none' }}" style="top:.1rem; left:1.15rem; font-size:.6rem;">{{ $unreadNotificationCount }}</span>
                    </a>
                </li>
            </ul>
            <form method="POST" action="{{ route('logout') }}" class="d-inline d-flex align-items-center gap-2">
                @csrf
                <span class="navbar-text me-1 text-white-50 d-none d-md-inline"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}</span>
                <button class="btn btn-sm btn-outline-light px-3">Log out</button>
            </form>
        </div>
    </div>
</nav>
@endauth

<main class="py-4">
    <div class="container">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4">
                <i class="bi bi-check-circle-fill me-1"></i>
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>