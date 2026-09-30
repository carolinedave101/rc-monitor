<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — ROYALTRICO Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-64.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --brand-1: #0d3bbf; --brand-2: #1b6ff5; --ink: #141b2d; --muted: #64748b; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #f2f5fc;
            color: var(--ink);
            min-height: 100vh;
        }
        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 250px; flex-shrink: 0;
            background: linear-gradient(180deg, #0b2a7a, var(--brand-1) 40%, #123fb0);
            color: #dbe6ff;
            display: flex; flex-direction: column;
            position: sticky; top: 0; height: 100vh;
        }
        .admin-sidebar .brand {
            font-weight: 800; color: #fff; text-decoration: none;
            padding: 1.25rem 1.25rem 1rem; display: flex; align-items: center; gap: .6rem;
        }
        .admin-sidebar .brand img { border-radius: .45rem; }
        .admin-sidebar .badge-admin {
            font-size: .62rem; letter-spacing: .08em; text-transform: uppercase;
            background: rgba(255,255,255,.16); color: #fff; border-radius: 999px;
            padding: .2rem .55rem; font-weight: 700;
        }
        .admin-nav { padding: .5rem .75rem; display: flex; flex-direction: column; gap: .15rem; }
        .admin-nav a {
            color: #c6d6ff; text-decoration: none; font-weight: 600; font-size: .92rem;
            padding: .6rem .8rem; border-radius: .7rem; display: flex; align-items: center; gap: .6rem;
        }
        .admin-nav a:hover { background: rgba(255,255,255,.09); color: #fff; }
        .admin-nav a.active { background: rgba(255,255,255,.16); color: #fff; }
        .admin-sidebar .sidebar-footer { margin-top: auto; padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,.12); font-size: .82rem; }
        .admin-sidebar .sidebar-footer a { color: #c6d6ff; text-decoration: none; }
        .admin-main { flex-grow: 1; min-width: 0; padding: 1.75rem 2rem 3rem; }
        .page-head { font-weight: 800; letter-spacing: -.02em; }
        .card { border: 0; border-radius: 1rem; box-shadow: 0 .55rem 1.6rem rgba(13, 59, 191, .07); }
        .stat-icon {
            width: 3rem; height: 3rem; border-radius: .95rem;
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.35rem;
        }
        .icon-primary { background: #eaf2ff; color: var(--brand-2); }
        .icon-success { background: #e7f8ee; color: #14a05c; }
        .icon-danger { background: #fdecef; color: #dc3d4f; }
        .icon-warning { background: #fff5e0; color: #e09117; }
        .icon-info { background: #e7f6fb; color: #1598c0; }
        .table thead th { text-transform: uppercase; font-size: .72rem; letter-spacing: .05em; color: var(--muted); }
        .badge { border-radius: 999px; font-weight: 600; }
        @media (max-width: 991.98px) {
            .admin-shell { flex-direction: column; }
            .admin-sidebar { width: 100%; height: auto; position: static; }
            .admin-main { padding: 1.25rem; }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <img src="{{ asset('images/logo-transparent.png') }}" alt="ROYALTRICO" height="34">
            ROYALTRICO <span class="badge-admin">Admin</span>
        </a>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.features.index') }}" class="{{ request()->routeIs('admin.features.*') ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap"></i> Features
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="mb-2"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}</div>
            <div class="d-flex gap-3">
                <a href="{{ route('dashboard') }}"><i class="bi bi-arrow-left-right me-1"></i>View site</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" style="color:#c6d6ff;">Log out</button>
                </form>
            </div>
        </div>
    </aside>

    <main class="admin-main">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="page-head h3 mb-1">@yield('heading', 'Admin')</h1>
                <p class="text-muted mb-0 small">@yield('subheading', '')</p>
            </div>
            <div>@yield('actions')</div>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show rounded-4">
                <i class="bi bi-check-circle-fill me-1"></i>
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
