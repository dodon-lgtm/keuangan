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
            background: rgba(9, 10, 13, 0.88);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.03), 0 16px 32px rgba(0, 0, 0, 0.32);
            backdrop-filter: blur(14px) saturate(1.15);
            -webkit-backdrop-filter: blur(14px) saturate(1.15);
        }
        /* Hairline merah halus di bawah header — identitas brand */
        .app-header::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(225, 29, 72, 0.35) 18%, rgba(225, 29, 72, 0.10) 55%, transparent);
            pointer-events: none;
        }
        .app-header .inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
            min-height: 64px;
            display: flex;
            align-items: center;
            gap: 18px;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            order: 2;
            margin-left: 8px;
            padding-left: 16px;
            border-left: 1px solid rgba(255, 255, 255, 0.08);
            flex: 0 0 auto;
        }
        /* Nav sebagai floating pill dock di sisi kanan */
        .nav-menu {
            order: 1;
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
            padding: 5px;
            background: rgba(255, 255, 255, 0.035);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 999px;
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
        .brand-name { font-size: 16.5px; font-weight: 800; letter-spacing: 0.2px; line-height: 1.2; }
        .brand-name span {
            display: block;
            margin-top: 2px;
            font-size: 9.5px;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .toggler {
            display: none;
            width: 40px;
            height: 40px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 999px;
            background: transparent;
            cursor: pointer;
            color: var(--text);
            place-items: center;
            transition: background 0.18s ease, border-color 0.18s ease;
        }
        .toggler:hover { background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.28); }
        .toggler svg { width: 19px; height: 19px; }

        .top-link {
            color: var(--muted);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1;
            padding: 9px 15px;
            border-radius: 999px;
            position: relative;
            white-space: nowrap;
            transition: color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
        }
        .top-link:hover { color: var(--text); background: rgba(255, 255, 255, 0.06); }
        .top-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(225, 29, 72, 0.24), rgba(225, 29, 72, 0.10));
            box-shadow: inset 0 0 0 1px rgba(225, 29, 72, 0.38), 0 4px 12px rgba(225, 29, 72, 0.15);
        }
        .top-link.active::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 3px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--accent);
            transform: translateX(-50%);
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.18);
        }
        .top-link:focus-visible { outline: 2px solid rgba(225, 29, 72, 0.6); outline-offset: 2px; }

        .js-dropdown { position: relative; }
        .js-dropdown .menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translate(-50%, 6px);
            min-width: 230px;
            padding: 8px;
            background: rgba(13, 15, 18, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 14px;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.50), 0 0 0 1px rgba(255, 255, 255, 0.02);
            list-style: none;
            display: none;
            z-index: 70;
            margin-top: 10px;
            backdrop-filter: blur(12px);
        }
        .js-dropdown.open .menu { display: block; animation: menuIn 0.18s ease both; }
        @keyframes menuIn {
            from { opacity: 0; transform: translate(-50%, -4px); }
            to { opacity: 1; transform: translate(-50%, 6px); }
        }
        .js-dropdown .menu a {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 550;
            transition: color 0.15s ease, background 0.15s ease, transform 0.15s ease;
        }
        .js-dropdown .menu a::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0.7;
            transition: opacity 0.15s ease, box-shadow 0.15s ease;
        }
        .js-dropdown .menu a:hover {
            color: #fff;
            background: rgba(225, 29, 72, 0.12);
            transform: translateX(2px);
        }
        .js-dropdown .menu a:hover::before { opacity: 1; box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.18); }

        .logout-form { margin: 0; }
        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            font-size: 12.5px;
            font-weight: 600;
            letter-spacing: 0.4px;
            color: #fff;
            background: rgba(225, 29, 72, 0.10);
            border: 1px solid rgba(225, 29, 72, 0.38);
            border-radius: 999px;
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
            margin: 14px 0 30px;
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
            margin-top: 6px;
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
        /* Baris total (tfoot): terbaca di kedua tema */
        .table tr.total-row > td,
        .table tr.total-row > th {
            background: rgba(225, 29, 72, 0.12);
            color: var(--text);
            border-top: 1px solid rgba(225, 29, 72, 0.40);
        }
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
            .app-header .inner { gap: 12px; padding: 0 16px; min-height: 58px; }
            .header-actions { margin-left: auto; padding-left: 0; border-left: none; }
            /* Panel menu slide-down */
            .nav-menu {
                order: 3;
                position: static;
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 3px;
                margin: 2px 0 12px;
                padding: 8px;
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.07);
                border-radius: 16px;
            }
            .nav-menu.show { display: flex; animation: menuPanel 0.22s ease both; }
            .nav-menu .top-link { display: flex; width: 100%; padding: 12px 14px; border-radius: 11px; font-size: 14px; }
            .nav-menu .top-link.active::after { display: none; }
            .js-dropdown { width: 100%; }
            .js-dropdown .menu {
                position: static;
                transform: none;
                animation: none;
                margin-top: 4px;
                background: rgba(255, 255, 255, 0.03);
                box-shadow: none;
            }
            .logout-form { width: 100%; margin-top: 6px; padding-top: 8px; border-top: 1px solid rgba(255, 255, 255, 0.07); }
            .logout-btn { width: 100%; justify-content: center; padding: 12px; }
        }
        @keyframes menuPanel {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: none; }
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

        /* ---- Complete filter (typing search + typeable selects + reset) ---- */
        .filter-input {
            width: 100%;
            background: var(--input);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 11px 13px;
            border-radius: 11px;
            font-size: 14px;
            outline: none;
            min-width: 168px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .filter-input:focus, .filter-input:focus-visible { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.18); outline: none; }
        .filter-input::placeholder { color: #6B7280; }
        .filter-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 600;
            color: #C9CED6;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 11px;
            text-decoration: none;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }
        .filter-reset:hover { color: var(--text); background: rgba(255, 255, 255, 0.09); border-color: rgba(255, 255, 255, 0.25); }
        .filter-reset svg { width: 14px; height: 14px; }
        .filter-active {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.2px;
            color: #E8EAEE;
            background: var(--accent-soft);
            border: 1px solid rgba(225, 29, 72, 0.35);
            border-radius: 999px;
            padding: 5px 12px;
            width: fit-content;
        }
        .filter-active::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.22); }
        .filter-panel .filter-field { display: flex; flex-direction: column; gap: 8px; min-width: 150px; }

        /* ---- Typeable searchable select (.ss) ---- */
        .ss { position: relative; min-width: 158px; }
        .ss-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
            background: var(--input);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 11px 13px;
            border-radius: 11px;
            font-size: 14px;
            font-weight: 550;
            cursor: pointer;
            text-align: left;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .ss-btn:hover { border-color: rgba(255, 255, 255, 0.22); }
        .ss-btn.open, .ss-btn:focus-visible { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.18); }
        .ss-btn svg { width: 13px; height: 13px; opacity: 0.65; flex: 0 0 auto; }
        .ss-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1 1 auto; }
        .ss-menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: max-content;
            max-width: 330px;
            min-width: 100%;
            background: #0F1114;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.48);
            padding: 8px;
            display: none;
            z-index: 120;
        }
        .ss-menu.open { display: block; }
        .ss-search {
            width: 100%;
            background: #12141A;
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: var(--text);
            padding: 8px 10px;
            border-radius: 9px;
            font-size: 13px;
            outline: none;
            box-sizing: border-box;
            margin-bottom: 7px;
        }
        .ss-search:focus { border-color: var(--accent); }
        .ss-search::placeholder { color: #6B7280; }
        .ss-list { list-style: none; margin: 0; padding: 0; max-height: 212px; overflow-y: auto; }
        .ss-list li {
            padding: 8px 11px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--muted);
            font-size: 13px;
            font-weight: 550;
            transition: background 0.12s ease, color 0.12s ease;
        }
        .ss-list li:hover, .ss-list li.hover { background: rgba(225, 29, 72, 0.10); color: #fff; }
        .ss-list li.selected { background: rgba(225, 29, 72, 0.16); color: #fff; box-shadow: inset 0 0 0 1px rgba(225, 29, 72, 0.4); }
        .ss-empty { padding: 9px 11px; font-size: 12.5px; color: #6B7280; }

        /* ---- Analysis charts ---- */
        .chart-section { margin-top: 40px; padding-top: 6px; }
        .chart-eyebrow {
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
        .chart-eyebrow::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.25); }
        .chart-heading { margin: 12px 0 2px; font-size: clamp(19px, 2.4vw, 24px); font-weight: 800; letter-spacing: -0.02em; color: var(--text); }
        .chart-sub { font-size: 13.5px; color: var(--muted); margin: 0; }
        .chart-grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); margin-top: 18px; }
        .chart-card {
            position: relative;
            background: linear-gradient(180deg, #11141A 0%, #0E1013 55%, #141019 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 20px 18px;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.02);
        }
        .chart-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: radial-gradient(85% 60% at 88% 6%, rgba(225, 29, 72, 0.055), transparent 60%);
        }
        .chart-card > * { position: relative; z-index: 1; }
        .chart-head { display: flex; flex-direction: column; gap: 6px; }
        .chart-title { font-size: 15px; font-weight: 700; color: var(--text); margin: 0; }
        .chart-desc { font-size: 12.5px; color: var(--muted); margin: 0; }
        .chart-canvas { min-height: 300px; margin-top: 14px; }
        .chart-empty {
            padding: 28px;
            text-align: center;
            color: var(--muted);
            font-size: 13.5px;
            border: 1px dashed rgba(255, 255, 255, 0.16);
            border-radius: 12px;
            margin-top: 14px;
        }
        .chart-note { font-size: 12px; color: var(--muted); margin-top: 14px; }

        @media (max-width: 900px) {
            .chart-grid { grid-template-columns: 1fr; }
            .filter-panel { flex-direction: column; align-items: stretch; }
        }

        /* ---- Pagination: force dark theme (fixes white leak from Tailwind view) ---- */
        .pagination-wrap { display: flex; justify-content: center; margin-top: 22px; }
        .pagination-wrap nav { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px; }
        .pagination-wrap nav > div:first-child { display: none; }
        .pagination-wrap nav > div { display: contents; }
        .pagination-wrap nav a,
        .pagination-wrap nav span,
        .pagination-wrap nav p { color: #C9CED6; font-size: 13px; }
        .pagination-wrap nav p { margin: 0; color: var(--muted); }
        .pagination-wrap nav a {
            background: #12141A;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 6px 12px;
            min-width: 34px;
            text-align: center;
            text-decoration: none;
            font-weight: 550;
            font-variant-numeric: tabular-nums;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }
        .pagination-wrap nav a:hover { background: #1C2026; border-color: rgba(255, 255, 255, 0.28); color: var(--text); }
        .pagination-wrap nav a[rel="prev"], .pagination-wrap nav a[rel="next"] { padding: 6px 10px; }
        .pagination-wrap nav a svg { width: 16px; height: 16px; }
        .pagination-wrap nav span[aria-current="page"],
        .pagination-wrap nav span[aria-current="page"] span {
            background: var(--accent);
            border: 1px solid var(--accent);
            border-radius: 10px;
            color: #fff;
            padding: 6px 12px;
            min-width: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .pagination-wrap nav span[aria-disabled="true"],
        .pagination-wrap nav span[aria-disabled="true"] span {
            background: #0E1013;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            color: #6B7280;
            padding: 6px 12px;
            min-width: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* ---- Consistent section rhythm (table → content) ---- */
        .table { margin-bottom: 14px; }
        .chart-section + .chart-section { margin-top: 40px; }

        /* ---- Mobile refinement ---- */
        @media (max-width: 767.98px) {
            .app-main { padding: 18px 12px 40px; }
            /* Tabel lebar: geser horizontal, tidak merusak layout */
            .table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table td, .table th { white-space: nowrap; }
            .btn-sm { padding: 7px 13px; }
            .dash-head { gap: 14px; }
            .kpi { padding: 18px 14px; }
            .chart-card { padding: 16px 14px; }
        }
        @media (max-width: 575.98px) {
            .filter-panel { padding: 14px; gap: 11px; }
            .filter-field { min-width: 0; }
            .filter-select, .filter-input, .ss { min-width: 0; width: 100%; }
            .filter-btn, .filter-reset { width: 100%; }
            .flash { font-size: 13px; padding: 11px 13px; }
            .pagination-wrap nav a,
            .pagination-wrap nav span[aria-current="page"],
            .pagination-wrap nav span[aria-disabled="true"] { min-width: 36px; padding: 8px 12px; }
        }
    </style>
    <script>
        /* Terapkan tema sebelum render agar tidak ada flash (default: dark) */
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t !== 'light') t = 'dark';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
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

            <div class="header-actions">
                <button type="button" class="theme-switch" role="switch" aria-checked="false" aria-label="Ganti tema terang/gelap" title="Mode Terang / Gelap">
                    <span class="ts-track" aria-hidden="true">
                        <span class="ts-thumb">
                            <svg class="ts-icon ts-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                            <svg class="ts-icon ts-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                        </span>
                    </span>
                </button>
                <button class="toggler" type="button" aria-controls="navMenu" aria-expanded="false" aria-label="Open/sluit navigatie">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M4 7 20 7 M4 12 20 12 M4 17 20 17" />
                    </svg>
                </button>
            </div>

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
                        Keluar
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="app-main">
        @if (session('success'))
            <div class="flash flash-success alert" role="status">
                {{ session('success') }}
                <button type="button" class="flash-x" data-bs-dismiss="alert" aria-label="Sluiten">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/theme.js') }}"></script>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts@4/dist/apexcharts.min.js"></script>
    <script>
        /* Shared chart theme + formatters, matching the dark rose template. */
        window.KeuanganChart = {
            colors: ['#E11D48', '#22C55E', '#F5B524', '#22D3EE', '#8B5CF6', '#F47171', '#38BDF8', '#F97316', '#A3E635', '#EC4899'],
            rupiah: function (val) {
                var neg = val < 0 ? '-' : '';
                var str = String(Math.round(Math.abs(val)));
                return 'Rp ' + neg + str.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },
            pct: function (val) {
                var num = Number(val);

                // Nilai kosong/tidak valid tetap tampil rapi sebagai 0.00%.
                if (!isFinite(num)) { num = 0; }

                // Konsisten dengan number_format($x, 2) di sisi Blade.
                return num.toFixed(2) + '%';
            },
            base: function (extra) {
                var labels = { colors: '#9AA1AB', fontSize: '12px', fontFamily: "'Inter', sans-serif", fontWeight: 500 };
                var defaults = {
                    chart: { type: 'bar', background: 'transparent', foreColor: '#9AA1AB', fontFamily: "'Inter', sans-serif", toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    grid: { padding: { left: 10, right: 10 }, strokeDashArray: 4, borderColor: 'rgba(255,255,255,0.08)', colors: ['rgba(255,255,255,0.06)'] },
                    tooltip: { theme: 'dark', style: { fontSize: '12.5px', fontFamily: "'Inter', sans-serif" } },
                    legend: { show: false, position: 'bottom', labels: { colors: '#C9CED6' }, markers: { size: 4 } },
                    stroke: { width: 2, curve: 'smooth' },
                    xaxis: { labels: labels, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: Object.assign({}, labels, { formatter: window.KeuanganChart.rupiah }) },
                    colors: window.KeuanganChart.colors.slice()
                };

                extra = extra || {};

                if (extra.chart) { defaults.chart = Object.assign(defaults.chart, extra.chart); delete extra.chart; }
                if (extra.legend) { defaults.legend = Object.assign(defaults.legend, extra.legend); delete extra.legend; }

                return Object.assign(defaults, extra);
            }
        };
    </script>
    <script id="keuangan-ss-upgrade">
        /* Turn filter selects into typeable / searchable dropdowns (progressive enhancement). */
        (function () {
            function chevron() {
                var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.setAttribute('viewBox', '0 0 12 12');
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', 'currentColor');
                svg.setAttribute('stroke-width', '1.6');
                var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', 'M4 5 8 5 M6 3 6 7');
                svg.appendChild(path);
                return svg;
            }

            function currentLabel(select) {
                var options = Array.prototype.slice.call(select.querySelectorAll('option'));
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value === select.value) return options[i].text;
                }
                var first = select.querySelector('option');
                return first ? first.text : '';
            }

            function openMenu(ui) {
                var q = (ui.search.value || '').toLowerCase();
                ui.list.innerHTML = '';
                var first = null;

                Array.prototype.slice.call(ui.select.querySelectorAll('option')).forEach(function (opt) {
                    var text = opt.text;
                    if (q && text.toLowerCase().indexOf(q) === -1) return;
                    var li = document.createElement('li');
                    li.textContent = text;
                    li.dataset.value = opt.value;
                    if (opt.value === ui.select.value) li.className = 'selected';
                    if (!first) first = li;
                    ui.list.appendChild(li);
                });

                if (!ui.list.querySelector('li')) {
                    var empty = document.createElement('li');
                    empty.className = 'ss-empty';
                    empty.textContent = 'Belum ada hasil yang sesuai.';
                    ui.list.appendChild(empty);
                }

                ui.btn.classList.add('open');
                ui.menu.classList.add('open');
                ui.btn.setAttribute('aria-expanded', 'true');
                ui.search.focus();
                ui.search.select();
            }

            function closeMenu(ui) {
                ui.btn.classList.remove('open');
                ui.menu.classList.remove('open');
                ui.btn.setAttribute('aria-expanded', 'false');
            }

            function enhance(select) {
                if (select.dataset.ssEnabled) return;
                select.dataset.ssEnabled = '1';

                var wrap = document.createElement('div');
                wrap.className = 'ss';

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ss-btn';
                btn.setAttribute('aria-haspopup', 'true');
                btn.setAttribute('aria-expanded', 'false');
                var label = document.createElement('span');
                label.className = 'ss-label';
                label.textContent = currentLabel(select);
                btn.appendChild(label);
                btn.appendChild(chevron());

                var menu = document.createElement('div');
                menu.className = 'ss-menu';
                var search = document.createElement('input');
                search.type = 'text';
                search.className = 'ss-search';
                search.placeholder = 'Tipe untuk mencari...';
                search.setAttribute('aria-label', 'Opties mencari: ' + (select.id || select.name));
                var list = document.createElement('ul');
                list.className = 'ss-list';
                menu.appendChild(search);
                menu.appendChild(list);

                wrap.appendChild(btn);
                wrap.appendChild(menu);

                var ui = { select: select, wrap: wrap, btn: btn, label: label, menu: menu, search: search, list: list };
                var parent = select.parentNode;

                select.style.display = 'none';
                parent.insertBefore(wrap, select);
                parent.removeChild(select);
                wrap.appendChild(select);

                btn.addEventListener('click', function () {
                    if (menu.classList.contains('open')) { closeMenu(ui); return; }
                    Array.prototype.slice.call(document.querySelectorAll('.ss-menu.open')).forEach(function (m) {
                        m.classList.remove('open');
                        var b = m.parentNode.querySelector('.ss-btn');
                        if (b) { b.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); }
                    });
                    search.value = '';
                    openMenu(ui);
                });

                search.addEventListener('input', function () { openMenu(ui); });

                search.addEventListener('keydown', function (e) {
                    if (e.key !== 'Enter') return;
                    var first = list.querySelector('li:not(.ss-empty)');
                    if (first) first.click();
                    e.preventDefault();
                });

                list.addEventListener('click', function (e) {
                    var li = e.target.closest('li');
                    if (!li || !li.dataset || !li.dataset.value) return;
                    select.value = li.dataset.value;
                    label.textContent = li.textContent;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    closeMenu(ui);
                });

                select.addEventListener('change', function () {
                    label.textContent = currentLabel(select);
                });
            }

            Array.prototype.slice.call(document.querySelectorAll('.filter-panel select.filter-select[data-searchable]')).forEach(enhance);

            document.addEventListener('click', function (e) {
                Array.prototype.slice.call(document.querySelectorAll('.ss-menu.open')).forEach(function (menu) {
                    var wrap = menu.closest('.ss');
                    if (!wrap || !wrap.contains(e.target)) {
                        menu.classList.remove('open');
                        var b = wrap.querySelector('.ss-btn');
                        if (b) { b.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); }
                    }
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>