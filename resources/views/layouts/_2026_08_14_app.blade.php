<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — WA Keuangan Bot</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.icon.css') }}">


    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootbox.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">

    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>

    <!-- Bootstrap 5 -->

    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <!-- Bootstrap Icons -->

    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"> --}}
    <!-- Google Font -->

    {{-- <link rel="preconnect" href="https://fonts.googleapis.com"> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/inter.css') }}">
    {{-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> --}}
    <!-- Chart.js -->
    <script src="{{ asset('assets/js/chart.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script> --}}

    <style>
        :root {
            --brand-50: #f0fdf4;
            --brand-100: #dcfce7;
            --brand-200: #bbf7d0;
            --brand-500: #22c55e;
            --brand-600: #16a34a;
            --brand-700: #15803d;
            --sidebar-bg: #0f172a;
            --bs-primary: #16a34a;
            --bs-primary-rgb: 22, 163, 74;
            font-family: 'Inter', sans-serif;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #1e293b;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        /* ─── Sidebar ─── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 1030;
            transition: transform .25s ease;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .sidebar-brand .logo-box {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(34, 197, 94, .35);
        }

        .sidebar-brand h6 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: .95rem;
        }

        .sidebar-brand small {
            color: #94a3b8;
            font-size: .72rem;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1.25rem .85rem;
        }

        .sidebar-nav .nav-link {
            color: #cbd5e1;
            font-size: .875rem;
            font-weight: 500;
            padding: .65rem 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: .25rem;
            transition: all .15s ease;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.05rem;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255, 255, 255, .06);
            color: #fff;
        }

        .sidebar-nav .nav-link.active {
            background: var(--brand-600);
            color: #fff;
            box-shadow: 0 4px 10px rgba(22, 163, 74, .35);
        }

        .sidebar-nav .nav-section-label {
            color: #64748b;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: .5rem 1rem .35rem;
        }

        .sidebar-footer {
            padding: 1rem .85rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .5rem .75rem;
            margin-bottom: .5rem;
        }

        .sidebar-user .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--brand-500);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .sidebar-user span {
            color: #e2e8f0;
            font-size: .85rem;
            font-weight: 500;
        }

        .btn-logout {
            width: 100%;
            text-align: left;
            color: #fca5a5;
            background: transparent;
            border: none;
            font-size: .875rem;
            font-weight: 500;
            padding: .6rem 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, .12);
            color: #fecaca;
        }

        /* ─── Main ─── */
        .main-wrap {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .topbar h1 {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0;
            color: #0f172a;
        }

        .page-body {
            padding: 2rem;
            flex: 1;
        }

        .btn-toggle-sidebar {
            display: none;
        }

        /* ─── Cards ─── */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
        }

        .stat-card {
            border-radius: 16px;
            border: 1px solid #eef1f5;
            background: #fff;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .stat-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.02em;
        }

        .stat-label {
            font-size: .8rem;
            color: #64748b;
            font-weight: 600;
        }

        .stat-sub {
            font-size: .72rem;
            color: #94a3b8;
        }

        .badge-soft {
            font-weight: 600;
            padding: .4em .75em;
            border-radius: 999px;
        }

        table.table thead th {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
            font-weight: 700;
            border-bottom: 1px solid #eef1f5;
            padding-bottom: .75rem;
        }

        table.table td {
            vertical-align: middle;
            font-size: .875rem;
            border-color: #f1f4f8;
        }

        table.table tbody tr:hover {
            background: #f8fafc;
        }

        .progress {
            height: 6px;
            border-radius: 999px;
            background: #eef1f5;
        }

        .progress-bar-brand {
            background: linear-gradient(90deg, var(--brand-500), var(--brand-600));
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrap {
                margin-left: 0;
            }

            .btn-toggle-sidebar {
                display: inline-flex;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <div class="logo-box">
                <i class="bi bi-whatsapp text-white fs-5"></i>
            </div>
            <div>
                <h6>Monitoring Keluarga</h6>
                {{-- <small>Bot Dashboard</small> --}}
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="{{ route('transaksi') }}" class="nav-link {{ request()->routeIs('transaksi') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Transaksi
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar">A</div>
                <span>Admin</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="main-wrap">
        <header class="topbar">
            <button class="btn btn-toggle-sidebar btn-sm btn-outline-secondary me-3" type="button"
                onclick="document.getElementById('appSidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="flex-grow-1">@yield('page-title', 'Dashboard')</h1>
            <div class="d-flex align-items-center gap-3">
                <span
                    class="text-muted small d-none d-md-inline">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                <span class="badge badge-soft" style="background:var(--brand-100); color:var(--brand-700);">
                    <i class="bi bi-circle-fill" style="font-size:.5rem;"></i> Online
                </span>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-4 mt-4">
                <div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm rounded-3"
                    role="alert">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="page-body">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}
    @stack('scripts')
</body>

</html>
