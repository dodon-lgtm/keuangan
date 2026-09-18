<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Keuangan Hijab')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
:root {
            --bg: #08090B;
            --panel: #0D0F12;
            --card: #101216;
            --input: #15181D;
            --border: #242832;
            --text: #F5F5F5;
            --muted: #9AA1AB;
            --accent: #E11D48;
            --accent-hover: #C8103F;
            --accent-soft: rgba(225, 29, 72, 0.12);
            --success: #22C55E;
            --error: #F87171;
            --radius: 20px;
            --font: 'Inter', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100dvh;
            margin: 0;
            color: var(--text);
            background:
                radial-gradient(90% 60% at 18% 0%, rgba(225, 29, 72, 0.06), transparent 60%),
                radial-gradient(60% 50% at 100% 100%, rgba(225, 29, 72, 0.045), transparent 55%),
                linear-gradient(180deg, #0B0C10 0%, #090A0D 45%, #08090B 100%);
            background-color: var(--bg);
            background-attachment: scroll;
            font-family: var(--font);
            -webkit-font-smoothing: antialiased;
        }

        /* Subtle fixed grain + grid texture overlays */
        .bg-grain,
        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .bg-grain { opacity: 0.04; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }
        .bg-grid { opacity: 0.05; background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px); background-size: 44px 44px; }

        .app-main { max-width: 1240px; margin: 0 auto; padding: 26px 18px 46px; }

        .flash { position: relative; padding: 13px 16px; border-radius: 12px; font-size: 14px; margin: 0 0 20px; }
        .flash-success { background: rgba(34, 197, 94, 0.10); border: 1px solid rgba(34, 197, 94, 0.35); color: #B8F0C9; }

        .app-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(10, 11, 14, 0.94);
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.02), 0 12px 26px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
        }
        .app-header .inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
            color: var(--text);
        }
        .brand-mark {
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 11px;
            color: var(--accent);
            background: var(--accent-soft);
            border: 1px solid rgba(225, 29, 72, 0.35);
        }
        .brand-mark svg { width: 21px; height: 21px; }
        .brand-mark img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }
        .brand-name { font-size: 16px; font-weight: 700; letter-spacing: 0.2px; line-height: 1.2; }
        .brand-name span { display: block; margin-top: 1px; font-size: 10.8px; color: var(--muted); font-weight: 500; letter-spacing: 0.4px; }

        .toggler {
            display: none;
            width: 40px;
            height: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            background: transparent;
            cursor: pointer;
            color: var(--text);
            place-items: center;
        }
        .toggler svg { width: 19px; height: 19px; }

        .nav-menu { display: flex; align-items: center; gap: 3px; flex-wrap: wrap; }
        .top-link {
            color: var(--muted);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 550;
            line-height: 1;
            padding: 9px 13px;
            border-radius: 9px;
            position: relative;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .top-link:hover { color: var(--text); background: rgba(255, 255, 255, 0.05); }
        .top-link.active { color: #fff; background: rgba(225, 29, 72, 0.10); box-shadow: inset 0 0 0 1px rgba(225, 29, 72, 0.32); }
        .top-link.active::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 1px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--accent);
            transform: translateX(-50%);
        }

        .js-dropdown { position: relative; }
        .js-dropdown .menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 210px;
            padding: 7px 0 8px;
            background: #0D0F12;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.42);
            list-style: none;
            display: none;
            z-index: 70;
            margin-top: 6px;
        }
        .js-dropdown.open .menu { display: block; }
        .js-dropdown .menu a {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 9px;
            padding: 9px 13px;
            border-radius: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 550;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .js-dropdown .menu a::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0.7;
        }
        .js-dropdown .menu a:hover { color: #fff; background: rgba(225, 29, 72, 0.10); }

        .logout-form { margin: 0; }
        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.2px;
            color: #fff;
            background: rgba(225, 29, 72, 0.10);
            border: 1px solid rgba(225, 29, 72, 0.4);
            border-radius: 9px;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;
        }
        .logout-btn svg { width: 15px; height: 15px; }
        .logout-btn:hover { background: var(--accent); border-color: var(--accent); box-shadow: 0 6px 14px rgba(225, 29, 72, 0.3); transform: translateY(-1px); }
        .logout-btn:active { transform: scale(0.97); }

        .dash-head { display: flex; flex-direction: column; gap: 20px; }
        .dash-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #E8EAEE;
            border: 1px solid rgba(245, 245, 245, 0.14);
            background: rgba(8, 9, 11, 0.35);
            padding: 6px 13px;
            border-radius: 999px;
            width: fit-content;
        }
        .dash-eyebrow::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.25); }
        .dash-title { font-size: clamp(24px, 3.2vw, 32px); font-weight: 800; letter-spacing: -0.02em; color: var(--text); margin: 0; }
        .dash-sub { font-size: 14px; color: var(--muted); margin: 2px 0 0; }

        .filter-panel {
            background: linear-gradient(180deg, #0F1114 0%, #0D0F12 100%);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
        }
        .filter-field label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 7px; font-weight: 600; letter-spacing: 0.2px; }
        .filter-select {
            appearance: none;
            -webkit-appearance: none;
            background: var(--input);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 11px 15px;
            border-radius: 11px;
            font-size: 14px;
            font-weight: 550;
            outline: none;
            cursor: pointer;
            min-width: 158px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .filter-select:focus, .filter-select:focus-visible { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.18); outline: none; }
        .filter-select option { background: #0F1114; color: var(--text); }
        .filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 26px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.3px;
            color: #fff;
            background: var(--accent);
            border: none;
            border-radius: 11px;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(225, 29, 72, 0.24);
            transition: background 0.18s ease, box-shadow 0.22s ease, transform 0.1s ease, filter 0.2s ease;
        }
        .filter-btn svg { width: 15px; height: 15px; }
        .filter-btn:hover { background: var(--accent-hover); filter: brightness(1.06); box-shadow: 0 12px 24px rgba(225, 29, 72, 0.34), 0 0 0 1px rgba(225, 29, 72, 0.9); transform: translateY(-1px); }
        .filter-btn:active { transform: scale(0.98); }
        .filter-btn:focus-visible { outline: 3px solid rgba(255, 255, 255, 0.28); outline-offset: 2px; }

        .kpi-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            margin-top: 30px;
        }
        .kpi {
            position: relative;
            background: linear-gradient(180deg, #11141A 0%, #0E1013 55%, #151017 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 22px 18px;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.02);
            display: flex;
            flex-direction: column;
            gap: 14px;
            transition: transform 0.18s ease, border-color 0.2s ease;
        }
        .kpi::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: radial-gradient(85% 60% at 88% 6%, rgba(225, 29, 72, 0.06), transparent 60%);
        }
        .kpi::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            opacity: 0.04;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }
        .kpi > * { position: relative; z-index: 1; }
        .kpi:hover { transform: translateY(-3px); border-color: rgba(225, 29, 72, 0.28); }
        .kpi-icon {
            width: 36px;
            height: 36px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 10px;
            color: var(--accent);
            background: var(--accent-soft);
            border: 1px solid rgba(225, 29, 72, 0.32);
        }
        .kpi-icon svg { width: 17px; height: 17px; }
        .kpi-label { font-size: 12.5px; color: var(--muted); font-weight: 600; letter-spacing: 0.2px; }
        .kpi-value { font-size: clamp(22px, 3vw, 34px); font-weight: 800; letter-spacing: -0.01em; color: var(--text); line-height: 1.05; }
        .kpi-unit { font-size: 13px; color: var(--muted); font-weight: 600; }

        .card { background: #101216; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; color: var(--text); }
        .card-title { color: var(--text); }
        .card-text { color: var(--muted); }
        .form-control, .form-select { background: var(--input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.16); }
        .form-select option { background: #0F1114; color: var(--text); }
        .form-label { color: var(--muted); font-weight: 600; font-size: 13px; }
        table.table {
            --bs-table-bg: transparent;
            --bs-table-color: #FFFFFF;
            --bs-table-color-state: #FFFFFF;
            --bs-table-color-type: #FFFFFF;
            --bs-table-striped-color: #FFFFFF;
            --bs-table-hover-color: #FFFFFF;
            --bs-table-active-color: #FFFFFF;
            --bs-table-border-color: rgba(255, 255, 255, 0.08);
            color: #FFFFFF;
            border-color: rgba(255, 255, 255, 0.08);
        }
        .table > :not(caption) > * > * { border-color: rgba(255, 255, 255, 0.08); }
        .table thead th { color: #FFFFFF; font-weight: 600; }
        .table-striped > tbody > tr:nth-of-type(odd) { --bs-table-accent-bg: rgba(255, 255, 255, 0.03); background-color: rgba(255, 255, 255, 0.03); }
        .table tbody td, .table tbody th { color: #FFFFFF; }
        .btn-primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); color: #fff; }
        .btn-outline-primary { color: var(--accent); border-color: rgba(225, 29, 72, 0.5); background: transparent; }
        .btn-outline-primary:hover { background: rgba(225, 29, 72, 0.12); color: #fff; border-color: var(--accent); }
        .btn-outline-light { color: #fff; border-color: rgba(255, 255, 255, 0.22); background: transparent; }
        .btn-outline-light:hover { background: rgba(255, 255, 255, 0.08); color: #fff; }
        .btn-danger { background: var(--error); border-color: var(--error); }
        .text-muted { color: var(--muted); }
        .alert-success { background: rgba(34, 197, 94, 0.10); border: 1px solid rgba(34, 197, 94, 0.35); color: #B8F0C9; }
        .alert-danger { background: rgba(248, 113, 113, 0.10); border: 1px solid rgba(248, 113, 113, 0.35); color: #F5B8B8; }
        .dropdown-menu { background: #0D0F12; border: 1px solid rgba(255, 255, 255, 0.08); }
        .dropdown-item { color: var(--muted); }
        .dropdown-item:hover { background: rgba(225, 29, 72, 0.12); color: #fff; }
        .dropdown-item.active { background: rgba(225, 29, 72, 0.14); color: #fff; }

        @media (max-width: 991.98px) {
            .toggler { display: grid; }
            .nav-menu { position: static; display: none; width: 100%; flex-direction: column; align-items: stretch; gap: 4px; padding: 10px 0; }
            .nav-menu.show { display: flex; }
            .nav-menu .top-link { display: flex; width: 100%; padding: 11px 13px; border-radius: 9px; }
            .js-dropdown { width: 100%; }
            .js-dropdown .menu { position: static; }
            .logout-form { width: 100%; margin-top: 2px; }
            .logout-btn { width: 100%; justify-content: center; padding: 11px; }
        }

        /* ---- Expanded global dark overrides (consistent readable contrast) ---- */
        .card-header { background: rgba(255, 255, 255, 0.02); border-bottom: 1px solid rgba(255, 255, 255, 0.08); color: var(--text); }
        .card-footer { background: rgba(255, 255, 255, 0.02); border-top: 1px solid rgba(255, 255, 255, 0.08); }

        .btn-secondary { background: #262B34; border-color: rgba(255, 255, 255, 0.12); color: var(--text); }
        .btn-secondary:hover { background: #303743; border-color: rgba(255, 255, 255, 0.18); color: var(--text); }
        .btn-outline-secondary { color: #C9CED6; border-color: rgba(255, 255, 255, 0.22); background: transparent; }
        .btn-outline-secondary:hover { color: var(--text); background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.32); }
        .btn-outline-danger { color: var(--error); border-color: rgba(248, 113, 113, 0.5); background: transparent; }
        .btn-outline-danger:hover { color: #fff; background: rgba(248, 113, 113, 0.16); border-color: var(--error); }
        .btn-outline-success { color: var(--success); border-color: rgba(34, 197, 94, 0.5); background: transparent; }
        .btn-outline-success:hover { color: #fff; background: rgba(34, 197, 94, 0.16); border-color: var(--success); }

        .form-control::placeholder, .form-select::placeholder { color: #6B7280; }
        .form-control:disabled, .form-select:disabled { background: #12141A; color: #6B7280; border-color: rgba(255, 255, 255, 0.06); }

        .invalid-feedback { color: #F5B8B8; }

        .badge { font-weight: 600; }
        .badge.bg-info { background: #22D3EE; color: #062D3A; }
        .badge.bg-secondary { background: #3E4450; color: #E9ECEF; }
        .badge.bg-success { background: #22C55E; color: #062B10; }
        .badge.bg-danger { background: var(--error); color: #fff; }
        .badge.bg-warning { background: #F5B524; color: #3A2B00; }

        .pagination {
            --bs-pagination-color: #C9CED6;
            --bs-pagination-bg: #12141A;
            --bs-pagination-border-color: rgba(255, 255, 255, 0.12);
            --bs-pagination-hover-color: var(--text);
            --bs-pagination-hover-bg: #1C2026;
            --bs-pagination-hover-border-color: rgba(255, 255, 255, 0.2);
            --bs-pagination-focus-color: var(--text);
            --bs-pagination-focus-bg: #1C2026;
            --bs-pagination-active-color: #fff;
            --bs-pagination-active-bg: var(--accent);
            --bs-pagination-active-border-color: var(--accent);
            --bs-pagination-disabled-color: #6B7280;
            --bs-pagination-disabled-bg: #0E1013;
            --bs-pagination-disabled-border-color: rgba(255, 255, 255, 0.06);
        }
        .pagination .page-link { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body>
<div class="bg-grain" aria-hidden="true"></div>
    <div class="bg-grid" aria-hidden="true"></div>

    <header class="app-header">
        <div class="inner">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">
                    <img src="{{ asset('images/logohijab.png') }}" alt="Logo Vendor Hijab Bandung">
                </span>
                <span class="brand-name">Keuangan Hijab<span>Vendor Hijab Bandung</span></span>
            </a>

            <button class="toggler" type="button" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 7 20 7 M4 12 20 12 M4 17 20 17" />
                </svg>
            </button>

            <nav id="navMenu" class="nav-menu">
                <a class="top-link" data-top href="{{ route('dashboard') }}">Dashboard</a>
                <a class="top-link" data-top href="{{ route('products.index') }}">Produk</a>
                <a class="top-link" data-top href="{{ route('customers.index') }}">Pelanggan</a>
                <a class="top-link" data-top href="{{ route('orders.index') }}">Log Order</a>
                <a class="top-link" data-top href="{{ route('expenses.index') }}">Pengeluaran</a>

                <div class="js-dropdown">
                    <a class="top-link" data-top="reports" href="#" aria-haspopup="true" aria-expanded="false">
                        Laporan
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 5 8 5 M6 3 6 7" /></svg>
                    </a>
                    <ul class="menu">
                        <li><a href="{{ route('reports.mer-roi') }}">MER &amp; ROI</a></li>
                        <li><a href="{{ route('reports.hpp-profit') }}">HPP &amp; Profit</a></li>
                    </ul>
                </div>

                <form action="{{ route('logout') }}" method="post" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="app-main">
        @if (session('success'))
            <div class="flash flash-success alert" role="status">
                {{ session('success') }}
                <button type="button" class="flash-x" data-bs-dismiss="alert" aria-label="Close">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
        (function () {
            var path = window.location.pathname;
            var menu = document.getElementById('navMenu');
            var toggler = document.querySelector('.toggler');
            var group = document.querySelector('.js-dropdown');
            var dropBtn = group ? group.querySelector('.top-link[data-top="reports"]') : null;

            function closeMobile() {
                if (menu && window.innerWidth <= 991) menu.classList.remove('show');
                if (toggler) toggler.setAttribute('aria-expanded', 'false');
            }

            var links = Array.prototype.slice.call(document.querySelectorAll('#navMenu .top-link[data-top]'));
            links.forEach(function (a) {
                var key = a.getAttribute('data-top');
                if (key === 'reports') {
                    if (path.indexOf('/reports') === 0) a.classList.add('active');
                    return;
                }
                try {
                    var u = new URL(a.href);
                    if (u.pathname === path || (u.pathname !== '/' && path.indexOf(u.pathname) === 0)) a.classList.add('active');
                } catch (e) {}
            });

            if (toggler) {
                toggler.addEventListener('click', function () {
                    var open = menu.classList.toggle('show');
                    toggler.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            }

            links.forEach(function (a) {
                a.addEventListener('click', closeMobile);
            });

            if (dropBtn) {
                dropBtn.addEventListener('click', function (e) {
                    if (window.innerWidth > 991) {
                        e.preventDefault();
                        var open = group.classList.toggle('open');
                        dropBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    }
                });
                if (group) {
                    var sub = Array.prototype.slice.call(group.querySelectorAll('.menu a'));
                    sub.forEach(function (a) { a.addEventListener('click', closeMobile); });
                }
                document.addEventListener('click', function (e) {
                    if (group && !group.contains(e.target)) {
                        group.classList.remove('open');
                        if (dropBtn) dropBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        })();
    </script>
</body>
</html>