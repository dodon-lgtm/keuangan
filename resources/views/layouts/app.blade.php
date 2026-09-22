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
            --muted: #D6DBE2;
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

        /* ---- Notifikasi flash (partials/flash.blade.php) ---- */
        .flash {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 0 0 20px;
            padding: 13px 46px 13px 14px;
            border-radius: 14px;
            font-size: 13.5px;
            line-height: 1.45;
            overflow: hidden;
            animation: flash-in 0.24s ease both;
        }
        .flash.is-leaving { animation: flash-out 0.18s ease both; }
        .flash-icon {
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            width: 26px;
            height: 26px;
            margin-top: 1px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
        }
        .flash-icon svg { width: 15px; height: 15px; }
        .flash-content { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .flash-title { font-size: 13.5px; font-weight: 700; letter-spacing: 0.2px; }
        .flash-message { opacity: 0.92; overflow-wrap: anywhere; }
        .flash-close {
            position: absolute;
            top: 9px;
            right: 9px;
            display: grid;
            place-items: center;
            width: 26px;
            height: 26px;
            padding: 0;
            border: 1px solid transparent;
            border-radius: 9px;
            background: transparent;
            color: inherit;
            opacity: 0.6;
            cursor: pointer;
            transition: opacity 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }
        .flash-close:hover, .flash-close:focus-visible {
            opacity: 1;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.18);
        }
        .flash-close svg { width: 13px; height: 13px; }
        .flash-progress {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background: currentColor;
            opacity: 0.4;
            transform-origin: left center;
            animation: flash-progress var(--flash-duration, 4500ms) linear both;
        }
        .flash.is-paused .flash-progress { animation-play-state: paused; }
        .flash-success { background: rgba(34, 197, 94, 0.10); border: 1px solid rgba(34, 197, 94, 0.35); color: #B8F0C9; }
        .flash-error { background: rgba(248, 113, 113, 0.10); border: 1px solid rgba(248, 113, 113, 0.35); color: #F5B8B8; }
        .flash-warning { background: rgba(245, 158, 11, 0.10); border: 1px solid rgba(245, 158, 11, 0.35); color: #F6D9A5; }
        .flash-info { background: rgba(56, 189, 248, 0.10); border: 1px solid rgba(56, 189, 248, 0.35); color: #B4E2F6; }
        @keyframes flash-in {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes flash-out {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-8px); }
        }
        @keyframes flash-progress {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }

        /* ---- Modal konfirmasi (pengganti window.confirm bawaan browser) ---- */
        .confirm-modal .modal-dialog { max-width: 400px; }
        .confirm-modal .modal-content {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: var(--radius);
            box-shadow: 0 26px 60px rgba(0, 0, 0, 0.55);
            color: var(--text);
        }
        .confirm-modal .modal-body { padding: 24px 22px 20px; text-align: center; }
        .confirm-icon {
            display: grid;
            place-items: center;
            width: 46px;
            height: 46px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: rgba(248, 113, 113, 0.14);
            border: 1px solid rgba(248, 113, 113, 0.35);
            color: #F5B8B8;
        }
        .confirm-icon svg { width: 22px; height: 22px; }
        .confirm-title { margin: 0 0 6px; font-size: 16px; font-weight: 700; }
        .confirm-message { margin: 0; color: var(--muted); font-size: 13.5px; line-height: 1.5; }
        .confirm-actions { display: flex; gap: 10px; margin-top: 20px; }
        .confirm-actions .btn { flex: 1; }
        .confirm-actions .btn-cancel {
            flex: 1;
            padding: 8px 14px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 10px;
            background: transparent;
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }
        .confirm-actions .btn-cancel:hover { color: var(--text); background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.26); }
        .confirm-actions .btn-danger { background: #DC2626; border-color: #DC2626; color: #fff; font-weight: 600; }
        .confirm-actions .btn-danger:hover { background: #B91C1C; border-color: #B91C1C; }

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
        /* Hairline merah halus di bawah header â€” identitas brand */
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

        /* ---- Settings (gear): modal Bootstrap di tengah layar ---- */
        .settings-btn {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: transparent;
            color: var(--text);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.1s ease;
        }
        .settings-btn:hover { background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.28); }
        .settings-btn:active { transform: scale(0.95); }
        .settings-btn svg { width: 18px; height: 18px; }
        .settings-btn:focus-visible { outline: 2px solid rgba(225, 29, 72, 0.6); outline-offset: 2px; }

        /* Backdrop lebih gelap + blur */
        .modal-backdrop.show { opacity: 1; background: rgba(8, 9, 11, 0.72); backdrop-filter: blur(3px); }

        .settings-modal .modal-content {
            background: #101216;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 18px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.02);
            color: var(--text);
            overflow: hidden;
        }
        .settings-modal .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .settings-modal .modal-head h5 { margin: 0; font-size: 16px; font-weight: 700; letter-spacing: 0.2px; }
        .settings-modal .modal-close {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }
        .settings-modal .modal-close:hover { color: var(--text); background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.25); }
        .settings-modal .modal-close svg { width: 15px; height: 15px; }
        .settings-modal .modal-body { padding: 10px 12px 14px; }
        .settings-item {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: var(--text);
            text-decoration: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.2px;
            cursor: pointer;
            text-align: left;
            transition: background 0.15s ease;
        }
        .settings-item:hover { background: rgba(255, 255, 255, 0.05); }
        .settings-item > svg:first-child { width: 17px; height: 17px; flex: 0 0 auto; color: var(--accent); }
        .settings-item .settings-chevron { margin-left: auto; color: var(--muted); display: inline-flex; }
        .settings-item .settings-chevron svg { width: 14px; height: 14px; }
        .settings-divider { height: 1px; background: rgba(255, 255, 255, 0.07); margin: 8px 8px; }
        .theme-row .theme-switch { margin-left: auto; }
        .theme-row .theme-state { margin-left: 8px; font-size: 12px; font-weight: 600; color: var(--muted); min-width: 52px; text-align: right; }

        /* Form ganti password di dalam modal */
        .settings-form { padding: 4px 4px 2px; }
        .settings-form .form-label { color: var(--muted); }
        .settings-form .field-error { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 12.5px; color: #F5B8B8; }
        .settings-form .form-actions { display: flex; gap: 10px; margin-top: 18px; }
        .settings-form .form-actions .btn { flex: 1; }
        .settings-form .btn-cancel {
            flex: 0 0 auto;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: transparent;
            color: var(--muted);
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .settings-form .btn-cancel:hover { color: var(--text); background: rgba(255, 255, 255, 0.06); }

        /* Tombol mata (intip password) di dalam modal */
        .pw-wrap { position: relative; }
        .pw-wrap .form-control { padding-right: 44px; }
        .pw-eye {
            position: absolute;
            right: 4px;
            top: 4px;
            bottom: 4px;
            width: 34px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .pw-eye:hover { color: var(--text); background: rgba(255, 255, 255, 0.06); }
        .pw-eye svg { width: 17px; height: 17px; }
        .pw-eye .eye-open { display: block; }
        .pw-eye .eye-slash { display: none; }
        .pw-eye.showing .eye-open { display: none; }
        .pw-eye.showing .eye-slash { display: block; }
        .pw-eye.showing { color: var(--accent); }

        /* Indikator kecocokan password (real-time) */
        .pw-match {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--muted);
        }
        .pw-match[hidden] { display: none; }
        .pw-match::before {
            content: '';
            width: 14px;
            height: 14px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.16);
            transition: background 0.15s ease;
        }
        .pw-match.ok { color: var(--success); }
        .pw-match.ok::before { background: var(--success); }
        .pw-match.bad { color: #F5B8B8; }
        .pw-match.bad::before { background: var(--error); }

        /* Hint + pesan error dinamis di dalam form ganti password */
        .settings-form .form-hint { margin-top: 6px; font-size: 12px; color: var(--muted); }
        .settings-form .field-error[hidden] { display: none; }
        .settings-form .form-control.is-invalid {
            border-color: rgba(248, 113, 113, 0.65);
            box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.12);
        }

        /* Toast global: pesan sukses/error modal tanpa reload halaman */
        .app-toast {
            position: fixed;
            left: 50%;
            bottom: 26px;
            z-index: 1090;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: min(92vw, 460px);
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text);
            background: #101216;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45);
            animation: app-toast-in 0.22s ease both;
        }
        .app-toast[hidden] { display: none; }
        .app-toast-icon { width: 10px; height: 10px; flex: 0 0 auto; border-radius: 50%; background: var(--muted); }
        .app-toast.ok { border-color: rgba(34, 197, 94, 0.45); }
        .app-toast.ok .app-toast-icon { background: var(--success); }
        .app-toast.bad { border-color: rgba(248, 113, 113, 0.45); }
        .app-toast.bad .app-toast-icon { background: var(--error); }
        .app-toast-text { flex: 1; line-height: 1.35; }
        .app-toast-close {
            flex: 0 0 auto;
            padding: 0 2px;
            border: 0;
            background: transparent;
            color: var(--muted);
            font-family: inherit;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
        }
        .app-toast-close:hover { color: var(--text); }
        @keyframes app-toast-in {
            from { opacity: 0; transform: translate(-50%, 12px); }
            to { opacity: 1; transform: translate(-50%, 0); }
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
        .card-title { color: #FFFFFF; }
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

        .form-control::placeholder, .form-select::placeholder { color: #9AA1AB; }
        .form-control:disabled, .form-select:disabled { background: #12141A; color: #9AA1AB; border-color: rgba(255, 255, 255, 0.06); }

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
            --bs-pagination-disabled-color: #9AA1AB;
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
        .filter-input::placeholder { color: #9AA1AB; }
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
        .ss-search::placeholder { color: #9AA1AB; }
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
        .ss-empty { padding: 9px 11px; font-size: 12.5px; color: #9AA1AB; }

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
            color: #9AA1AB;
            padding: 6px 12px;
            min-width: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* ---- Consistent section rhythm (table â†’ content) ---- */
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
            .flash { font-size: 13px; padding: 11px 42px 11px 12px; }
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
                <button type="button" class="settings-btn" data-bs-toggle="modal" data-bs-target="#settingsModal" aria-label="Pengaturan" title="Pengaturan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
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
        @include('partials.flash')

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
                var labels = { colors: '#C3CAD4', fontSize: '12px', fontFamily: "'Inter', sans-serif", fontWeight: 500 };
                var defaults = {
                    chart: { type: 'bar', background: 'transparent', foreColor: '#C3CAD4', fontFamily: "'Inter', sans-serif", toolbar: { show: false } },
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
    <!-- ===== Modal Pengaturan (centered) ===== -->
    <div class="modal fade settings-modal" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-head">
                    <h5 id="settingsModalLabel">Pengaturan</h5>
                    <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Tutup">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Menu utama -->
                    <div id="settingsHome">
                        <button type="button" class="settings-item" id="gotoPasswordBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Ganti Password
                            <span class="settings-chevron" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
                        </button>
                        <div class="settings-divider"></div>
                        <div class="settings-item theme-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                            <span>Mode Gelap</span>
                            <span class="theme-state" id="themeState">Aktif</span>
                            <button type="button" class="theme-switch" role="switch" aria-checked="false" aria-label="Mode Gelap / Terang">
                                <span class="ts-track" aria-hidden="true">
                                    <span class="ts-thumb">
                                        <svg class="ts-icon ts-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                                        <svg class="ts-icon ts-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Form ganti password (tersembunyi sampai menu diklik) -->
                    {{-- novalidate: validasi ditangani JS (inline) + server sebagai sumber kebenaran. --}}
                    <form id="passwordForm" class="settings-form d-none" action="{{ route('settings.password') }}" method="post" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="mb-3 pw-field">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <div class="pw-wrap">
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       autocomplete="current-password" spellcheck="false" autocapitalize="off"
                                       aria-describedby="currentPasswordError">
                                <button type="button" class="pw-eye" data-eye-for="current_password" aria-label="Lihat password" title="Lihat / sembunyikan password">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-slash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div class="field-error" id="currentPasswordError" role="alert" @unless ($errors->has('current_password')) hidden @endunless>{{ $errors->first('current_password') }}</div>
                        </div>
                        <div class="mb-3 pw-field">
                            <label for="password" class="form-label">Password Baru</label>
                            <div class="pw-wrap">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password" spellcheck="false" autocapitalize="off"
                                       minlength="8" aria-describedby="passwordError passwordHint">
                                <button type="button" class="pw-eye" data-eye-for="password" aria-label="Lihat password" title="Lihat / sembunyikan password">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-slash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div class="field-error" id="passwordError" role="alert" @unless ($errors->has('password')) hidden @endunless>{{ $errors->first('password') }}</div>
                            <div class="form-hint" id="passwordHint">Minimal 8 karakter. Sebaiknya kombinasi huruf dan angka.</div>
                        </div>
                        <div class="mb-3 pw-field">
                            <label for="password_confirmation" class="form-label">Ulangi Password Baru</label>
                            <div class="pw-wrap">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" autocomplete="new-password"
                                       spellcheck="false" autocapitalize="off"
                                       aria-describedby="passwordConfirmationError pwMatch">
                                <button type="button" class="pw-eye" data-eye-for="password_confirmation" aria-label="Lihat password" title="Lihat / sembunyikan password">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-slash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div class="field-error" id="passwordConfirmationError" role="alert" hidden></div>
                            <div class="pw-match" id="pwMatch" aria-live="polite" hidden></div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" id="backToSettingsBtn">Kembali</button>
                            <button type="submit" class="btn btn-primary" id="passwordSubmitBtn">Simpan Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Toast global: dipakai modal pengaturan untuk pesan sukses/error tanpa reload -->
    <div class="app-toast" id="appToast" role="status" aria-live="assertive" aria-atomic="true" hidden>
        <span class="app-toast-icon" aria-hidden="true"></span>
        <span class="app-toast-text" id="appToastText"></span>
        <button type="button" class="app-toast-close" id="appToastClose" aria-label="Tutup notifikasi">&times;</button>
    </div>

    <!-- Modal konfirmasi hapus (pengganti window.confirm bawaan browser).
         Form dengan atribut data-confirm akan dicegat oleh skrip di bawah. -->
    <div class="modal fade confirm-modal" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <span class="confirm-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18" />
                            <path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6" />
                            <path d="M6 6l1 13.5A1.5 1.5 0 0 0 8.5 21h7a1.5 1.5 0 0 0 1.5-1.5L18 6" />
                            <path d="M10 10.5v6M14 10.5v6" />
                        </svg>
                    </span>
                    <h5 class="confirm-title" id="confirmModalLabel">Hapus data ini?</h5>
                    <p class="confirm-message" id="confirmModalText">Tindakan ini tidak bisa dibatalkan.</p>
                    <div class="confirm-actions">
                        <button type="button" class="btn-cancel" data-confirm-cancel>Batal</button>
                        <button type="button" class="btn btn-danger" data-confirm-ok>Ya, hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script>
        /* Notifikasi flash: tombol tutup + auto-dismiss dengan progress bar.
           Timer dijeda saat kursor/fokus berada di atas notifikasi agar pesan
           sempat dibaca, lalu lanjut lagi setelah keluar. */
        (function () {
            var flashes = document.querySelectorAll('[data-flash]');
            if (!flashes.length) return;

            function dismiss(el) {
                if (el.dataset.closing === '1') return;
                el.dataset.closing = '1';
                el.classList.add('is-leaving');
                window.setTimeout(function () {
                    if (el.parentNode) el.parentNode.removeChild(el);
                }, 200);
            }

            Array.prototype.forEach.call(flashes, function (el) {
                var delay = parseInt(el.getAttribute('data-auto-dismiss') || '0', 10);
                var timer = null;

                function start() {
                    if (!delay) return;
                    el.classList.remove('is-paused');
                    timer = window.setTimeout(function () { dismiss(el); }, delay);
                }

                function pause() {
                    el.classList.add('is-paused');
                    if (timer) {
                        window.clearTimeout(timer);
                        timer = null;
                    }
                }

                function resume() {
                    if (el.dataset.closing === '1' || timer) return;
                    start();
                }

                var closer = el.querySelector('[data-flash-close]');
                if (closer) {
                    closer.addEventListener('click', function () {
                        pause();
                        dismiss(el);
                    });
                }

                el.addEventListener('mouseenter', pause);
                el.addEventListener('mouseleave', resume);
                el.addEventListener('focusin', pause);
                el.addEventListener('focusout', resume);
                start();
            });
        })();

        /* Konfirmasi hapus: form dengan atribut data-confirm dicegat lalu
           ditampilkan lewat modal bertema. Tanpa atribut → submit normal. */
        (function () {
            var modalEl = document.getElementById('confirmModal');
            if (!modalEl || !window.bootstrap) return;

            var titleEl = document.getElementById('confirmModalLabel');
            var textEl = document.getElementById('confirmModalText');
            var okBtn = modalEl.querySelector('[data-confirm-ok]');
            var cancelBtn = modalEl.querySelector('[data-confirm-cancel]');
            var pendingForm = null;
            var instance = null;

            function open(form) {
                pendingForm = form;
                var title = form.getAttribute('data-confirm-title') || 'Hapus data ini?';
                var message = form.getAttribute('data-confirm') || 'Tindakan ini tidak bisa dibatalkan.';
                var okLabel = form.getAttribute('data-confirm-label') || 'Ya, hapus';

                if (titleEl) titleEl.textContent = title;
                if (textEl) textEl.textContent = message;
                if (okBtn) okBtn.textContent = okLabel;

                instance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                instance.show();
            }

            /* Fase capture supaya form dicegat sebelum handler lain berjalan. */
            document.addEventListener('submit', function (event) {
                var form = event.target;

                if (!form || !form.getAttribute || !form.getAttribute('data-confirm')) return;

                /* Submit yang dipicu tombol konfirmasi (form.submit()) tidak
                   memicu event ini, penanda di bawah hanya jaring pengaman. */
                if (form.dataset.confirmed === '1') {
                    delete form.dataset.confirmed;
                    return;
                }

                event.preventDefault();
                open(form);
            }, true);

            if (okBtn) {
                okBtn.addEventListener('click', function () {
                    var form = pendingForm;
                    pendingForm = null;
                    if (instance) instance.hide();
                    if (!form) return;

                    form.dataset.confirmed = '1';
                    form.submit();
                    window.setTimeout(function () { delete form.dataset.confirmed; }, 0);
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () {
                    pendingForm = null;
                    if (instance) instance.hide();
                });
            }

            /* Tombol "Batal" langsung difokuskan: hindari Enter menghapus data. */
            modalEl.addEventListener('shown.bs.modal', function () {
                if (cancelBtn) cancelBtn.focus();
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                pendingForm = null;
            });
        })();
    </script>


    <script>
        /* Modal "Pengaturan": navigasi menu <-> form ganti password, validasi inline
           (wajib diisi / minimal 8 karakter / konfirmasi harus cocok), submit via
           fetch() supaya modal TIDAK tertutup sebelum error atau sukses tampil,
           lalu toast + tutup modal otomatis saat berhasil.
           Bila fetch tidak tersedia, form tetap ter-submit normal ke server. */
        (function () {
            var modalEl = document.getElementById('settingsModal');
            var home = document.getElementById('settingsHome');
            var form = document.getElementById('passwordForm');
            var gotoBtn = document.getElementById('gotoPasswordBtn');
            var backBtn = document.getElementById('backToSettingsBtn');
            if (!modalEl || !home || !form) return;

            var pwCurrent = document.getElementById('current_password');
            var pwNew = document.getElementById('password');
            var pwConfirm = document.getElementById('password_confirmation');
            var pwMatch = document.getElementById('pwMatch');
            var submitBtn = document.getElementById('passwordSubmitBtn');
            var submitLabel = submitBtn ? submitBtn.textContent : 'Simpan Password';
            var minLength = parseInt((pwNew && pwNew.getAttribute('minlength')) || '', 10) || 8;
            var inputs = {
                current_password: pwCurrent,
                password: pwNew,
                password_confirmation: pwConfirm
            };
            var errorBoxes = {
                current_password: document.getElementById('currentPasswordError'),
                password: document.getElementById('passwordError'),
                password_confirmation: document.getElementById('passwordConfirmationError')
            };
            var toastEl = document.getElementById('appToast');
            var toastText = document.getElementById('appToastText');
            var toastClose = document.getElementById('appToastClose');
            var toastTimer = null;
            var closeTimer = null;

            function showForm() { home.classList.add('d-none'); form.classList.remove('d-none'); }
            function showHome() { form.classList.add('d-none'); home.classList.remove('d-none'); }

            function whenReady(fn) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fn, { once: true });
                } else {
                    fn();
                }
            }

            /* Bootstrap baru aman memanggil .show() setelah DOM siap, sehingga
               backdrop tidak "nyangkut" saat modal dibuka otomatis. */
            function openModal() {
                whenReady(function () {
                    if (window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                });
            }

            gotoBtn.addEventListener('click', showForm);
            backBtn.addEventListener('click', showHome);

            /* ---------- Toast pesan sukses / error ---------- */
            function showToast(type, message) {
                if (!toastEl || !toastText) return;
                toastText.textContent = message;
                toastEl.classList.remove('ok', 'bad');
                toastEl.classList.add(type === 'success' ? 'ok' : 'bad');
                toastEl.hidden = false;
                if (toastTimer) clearTimeout(toastTimer);
                toastTimer = setTimeout(hideToast, 5000);
            }

            function hideToast() {
                if (toastTimer) { clearTimeout(toastTimer); toastTimer = null; }
                if (toastEl) toastEl.hidden = true;
            }

            if (toastClose) toastClose.addEventListener('click', hideToast);

            /* session()->regenerate() di server merotasi CSRF token, jadi semua
               token di halaman (form ganti password & form Keluar) ikut disegarkan
               agar submit berikutnya tidak terkena 419. Attribute `value` juga
               diperbarui supaya tetap benar setelah form.reset(). */
            function syncCsrfToken(token) {
                if (!token) return;
                document.querySelectorAll('input[name="_token"]').forEach(function (input) {
                    input.value = token;
                    input.setAttribute('value', token);
                });
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
            }

            /* ---------- Mata: intip / sembunyikan password ---------- */
            document.querySelectorAll('.pw-eye').forEach(function (eye) {
                eye.addEventListener('click', function () {
                    var input = document.getElementById(eye.getAttribute('data-eye-for'));
                    if (!input) return;
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    eye.classList.toggle('showing', show);
                    eye.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Lihat password');
                });
            });

            /* ---------- Pesan error inline per field ---------- */
            function clearFieldError(name) {
                var input = inputs[name];
                var box = errorBoxes[name];
                if (input) {
                    input.classList.remove('is-invalid');
                    input.removeAttribute('aria-invalid');
                }
                if (box) {
                    box.textContent = '';
                    box.hidden = true;
                }
            }

            function clearFieldErrors() {
                Object.keys(inputs).forEach(function (name) { clearFieldError(name); });
            }

            function setFieldError(name, message) {
                var input = inputs[name];
                var box = errorBoxes[name];
                if (input) {
                    input.classList.add('is-invalid');
                    input.setAttribute('aria-invalid', 'true');
                }
                if (box) {
                    box.textContent = message;
                    box.hidden = false;
                }
            }

            function applyServerErrors(errors) {
                clearFieldErrors();
                Object.keys(errors || {}).forEach(function (key) {
                    if (!inputs[key]) return;
                    var message = errors[key];
                    setFieldError(key, Array.isArray(message) ? message[0] : String(message));
                });
            }

            /* ---------- Indikator kecocokan password (real-time) ---------- */
            function checkMatch() {
                if (!pwNew || !pwConfirm || !pwMatch) return;

                var baru = pwNew.value;
                var ulangi = pwConfirm.value;
                var text = '';
                var state = '';

                if (baru.length > 0 && baru.length < minLength) {
                    text = 'Password baru minimal ' + minLength + ' karakter';
                    state = 'bad';
                } else if (baru.length > 0 && ulangi.length > 0) {
                    state = baru === ulangi ? 'ok' : 'bad';
                    text = state === 'ok' ? 'Password cocok' : 'Password belum cocok';
                }

                pwMatch.classList.remove('ok', 'bad');
                if (!text) {
                    pwMatch.textContent = '';
                    pwMatch.hidden = true;
                    return;
                }

                pwMatch.textContent = text;
                pwMatch.classList.add(state);
                pwMatch.hidden = false;
            }

            /* ---------- Validasi sisi klien (server tetap sumber kebenaran) ---------- */
            function validate() {
                clearFieldErrors();

                var firstInvalid = null;
                function fail(name, message) {
                    setFieldError(name, message);
                    if (!firstInvalid && inputs[name]) firstInvalid = inputs[name];
                }

                if (!pwCurrent || pwCurrent.value === '') {
                    fail('current_password', 'Password saat ini wajib diisi.');
                }
                if (!pwNew || pwNew.value === '') {
                    fail('password', 'Password baru wajib diisi.');
                } else if (pwNew.value.length < minLength) {
                    fail('password', 'Password baru minimal ' + minLength + ' karakter.');
                }
                if (!pwConfirm || pwConfirm.value === '') {
                    fail('password_confirmation', 'Ulangi password baru wajib diisi.');
                } else if (pwNew && pwNew.value !== '' && pwConfirm.value !== pwNew.value) {
                    fail('password_confirmation', 'Password belum cocok.');
                }

                checkMatch();

                if (firstInvalid) firstInvalid.focus();
                return ! firstInvalid;
            }

            /* Error lama dibersihkan begitu pengguna mengetik ulang. */
            [pwCurrent, pwNew, pwConfirm].forEach(function (input) {
                if (!input) return;
                input.addEventListener('input', function () {
                    clearFieldError(input.name);
                    /* Panjang minimum & kecocokan adalah sifat bersama kedua field baru. */
                    if (input.name !== 'current_password') {
                        clearFieldError('password');
                        clearFieldError('password_confirmation');
                    }
                    checkMatch();
                });
            });
            checkMatch();

            /* ---------- Submit (AJAX, tanpa reload halaman) ---------- */
            function setLoading(state) {
                if (!submitBtn) return;
                submitBtn.disabled = state;
                submitBtn.textContent = state ? 'Menyimpan...' : submitLabel;
            }

            function closeModalSoon() {
                if (!window.bootstrap) return;
                var instance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                if (closeTimer) clearTimeout(closeTimer);
                closeTimer = setTimeout(function () {
                    closeTimer = null;
                    instance.hide();
                }, 1300);
            }

            function resetFields() {
                form.reset();
                clearFieldErrors();
                checkMatch();
            }

            form.addEventListener('submit', function (event) {
                /* Tanpa fetch: biarkan submit normal (redirect + flash) berjalan. */
                if (!window.fetch || !window.FormData) return;

                event.preventDefault();
                hideToast();

                if (! validate()) return;

                var tokenInput = form.querySelector('input[name="_token"]');
                setLoading(true);

                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': tokenInput ? tokenInput.value : ''
                    },
                    body: new FormData(form)
                }).then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (data) {
                        return { status: response.status, ok: response.ok, data: data };
                    });
                }).then(function (result) {
                    setLoading(false);

                    if (result.ok) {
                        /* Sukses: kosongkan form, sinkronkan token baru, tampilkan toast,
                           lalu tutup modal otomatis. */
                        resetFields();
                        syncCsrfToken(result.data.csrf_token);
                        showToast('success', result.data.message || 'Password berhasil diperbarui.');
                        showHome();
                        closeModalSoon();
                        return;
                    }

                    if (result.status === 422) {
                        /* Gagal validasi: modal tetap terbuka, error per field, form dikosongkan. */
                        applyServerErrors(result.data.errors);
                        [pwCurrent, pwNew, pwConfirm].forEach(function (input) {
                            if (input) input.value = '';
                        });
                        checkMatch();
                        showToast('error', result.data.message || 'Password belum bisa diperbarui.');
                        var firstInvalid = form.querySelector('.form-control.is-invalid') || pwCurrent;
                        if (firstInvalid) firstInvalid.focus();
                        return;
                    }

                    if (result.status === 419) {
                        showToast('error', 'Sesi berakhir. Muat ulang halaman lalu coba lagi.');
                        return;
                    }

                    showToast('error', result.data.message || 'Terjadi kesalahan. Password belum diperbarui.');
                }).catch(function () {
                    setLoading(false);
                    showToast('error', 'Koneksi bermasalah. Periksa jaringan lalu coba lagi.');
                });
            });

            /* State modal direset saat ditutup agar tidak ada isi/error tertinggal. */
            modalEl.addEventListener('hidden.bs.modal', function () {
                if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
                resetFields();
                setLoading(false);
                showHome();
            });


            /* Fallback tanpa JS di atas ATAU setelah redirect server (validasi gagal):
               tampilkan form ganti password lalu buka modal otomatis. */
            @if ($errors->has('current_password') || $errors->has('password'))
                showForm();
                openModal();
            @endif
        })();
    </script>
    @stack('scripts')
</body>
</html>