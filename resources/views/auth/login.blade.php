<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Vendor Hijab Bandung</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            --success: #22C55E;
            --error: #F87171;
            --radius: 20px;
            --font: 'Inter', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        * {
            box-sizing: border-box;
        }
        html,
        body {
            height: 100%;
        }
        body {
            margin: 0;
            overflow: hidden;
            color: var(--text);
            background: var(--bg);
            font-family: var(--font);
            -webkit-font-smoothing: antialiased;
        }
        .app {
            display: grid;
            grid-template-columns: 55fr 45fr;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
        }

        /* ================= LEFT: VISUAL HERO ================= */
        .visual {
            position: relative;
            display: flex;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            background: var(--panel);
        }
        .slides {
            position: absolute;
            inset: 0;
            z-index: 0;
            background: #11131a;
            animation: fadeIn 1s ease both;
        }
        .slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transform: scale(1.04);
            transition: opacity 800ms ease, transform 6000ms ease;
            will-change: opacity;
        }
        .slide.active {
            opacity: 1;
            transform: scale(1);
        }
        .slide-dots {
            position: absolute;
            left: clamp(24px, 4vw, 56px);
            bottom: clamp(18px, 3vw, 34px);
            z-index: 5;
            display: flex;
            gap: 8px;
        }
        .slide-dot {
            width: 26px;
            height: 4px;
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.28);
            cursor: pointer;
            transition: background 200ms ease, width 200ms ease;
        }
        .slide-dot:hover {
            background: rgba(255, 255, 255, 0.45);
        }
        .slide-dot.active {
            background: var(--accent);
            width: 34px;
        }
        .visual-content {
            transition: opacity 350ms ease;
        }
        .visual-content.is-fading {
            opacity: 0.55;
        }
        .visual-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(180deg, rgba(8, 9, 11, 0.74) 0%, rgba(8, 9, 11, 0.30) 40%, rgba(8, 9, 11, 0.12) 62%, rgba(8, 9, 11, 0.86) 100%),
                linear-gradient(90deg, rgba(8, 9, 11, 0.62) 0%, rgba(8, 9, 11, 0.04) 46%, rgba(8, 9, 11, 0.10) 100%);
        }
        .visual-glow {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background: radial-gradient(55% 42% at 76% 26%, rgba(225, 29, 72, 0.12), transparent 70%);
        }
        .visual-grain {
            position: absolute;
            inset: 0;
            z-index: 3;
            pointer-events: none;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }
        .visual-content {
            position: relative;
            z-index: 4;
            width: 100%;
            display: flex;
            flex-direction: column;
            padding: clamp(24px, 4vw, 56px);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-mark {
            width: 40px;
            height: 40px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 11px;
            color: var(--accent);
            background: rgba(225, 29, 72, 0.12);
            border: 1px solid rgba(225, 29, 72, 0.35);
        }
        .brand-mark svg {
            width: 20px;
            height: 20px;
        }
        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }
        .brand-name {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.2px;
            line-height: 1.2;
        }
        .brand-name span {
            display: block;
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--muted);
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .visual-body {
            margin-top: auto;
            max-width: 430px;
        }
        .visual-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #E8EAEE;
            border: 1px solid rgba(245, 245, 245, 0.14);
            background: rgba(8, 9, 11, 0.35);
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 22px;
        }
        .visual-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.25);
        }
        .visual-headline {
            margin: 0 0 18px;
            font-size: clamp(30px, 3.5vw, 48px);
            line-height: 1.12;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .visual-headline em {
            font-style: normal;
            color: var(--accent);
        }
        .visual-desc {
            margin: 0 0 28px;
            font-size: 15px;
            line-height: 1.65;
            color: var(--muted);
        }
        .feature-badges {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .feature-badges li {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            font-size: 12.5px;
            font-weight: 500;
            color: #D9DDE2;
            background: rgba(13, 15, 18, 0.55);
            border: 1px solid var(--border);
            border-radius: 999px;
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
        }
        .feature-badges li svg {
            width: 14px;
            height: 14px;
            color: var(--accent);
        }

        /* ================= RIGHT: FORM PANEL ================= */
        .form-panel {
            position: relative;
            display: grid;
            place-items: center;
            padding: clamp(24px, 5vw, 72px);
            min-height: 100vh;
            min-height: 100dvh;
            background: var(--bg);
            overflow: hidden;
        }
        .form-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(42% 30% at 8% 0%, rgba(225, 29, 72, 0.07), transparent 70%);
        }

        /* ================= RIGHT: BACKGROUND DECORATION =================
           Subtle "texture & atmosphere" behind the login panel (Dark Luxury SaaS).
           Everything sits behind the content (z-index: 0) and has pointer-events: none,
           so the working login form always stays the interactive focus.
           Vanilla CSS, no libraries. When in doubt, kept more subtle. */
        .panel-deco {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .login-card,
        .panel-foot {
            z-index: 1; /* above the decoration */
        }
        .login-card {
            position: relative;
        }

        /* Layer 1 : red radial glows + soft radial lights (top/mid/right-bottom).
           Strong enough to feel the depth, soft enough to never look "red". */
        .deco-glow {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(62% 50% at 82% 14%, rgba(225, 29, 72, 0.14), transparent 60%),
                radial-gradient(20% 16% at 96% 48%, rgba(255, 255, 255, 0.05), transparent 66%),
                radial-gradient(34% 26% at 64% 46%, rgba(225, 29, 72, 0.05), transparent 70%),
                radial-gradient(46% 36% at 84% 86%, rgba(225, 29, 72, 0.09), transparent 64%);
        }

        /* Layer 2 : subtle grid, barely-there texture, faded toward the edges */
        .deco-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 56px 56px;
            -webkit-mask-image: radial-gradient(74% 62% at 52% 44%, #000 0%, #000 66%, transparent 88%);
            mask-image: radial-gradient(74% 62% at 52% 44%, #000 0%, #000 66%, transparent 88%);
        }

        /* Layer 3 : abstract orbits for depth (partly outside the viewport) */
        .deco-ring {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.075);
            border-radius: 50%;
        }
        .deco-ring-1 {
            width: 560px;
            height: 560px;
            top: -170px;
            left: -250px;
            box-shadow: 0 0 0 60px rgba(225, 29, 72, 0.03), 0 0 0 120px rgba(225, 29, 72, 0.018);
        }
        .deco-ring-2 {
            width: 460px;
            height: 460px;
            bottom: -130px;
            right: -190px;
        }
        .deco-ring-3 {
            width: 320px;
            height: 320px;
            top: -110px;
            right: -130px;
            border-color: rgba(225, 29, 72, 0.14);
        }

        /* Layer 4 : small dot pattern, bottom-right corner only, very faint */
        .deco-dots {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 380px;
            height: 380px;
            opacity: 0.10;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.6) 0.55px, transparent 1.05px);
            background-size: 13px 13px;
            -webkit-mask-image: radial-gradient(circle at 100% 100%, #000 0%, transparent 84%);
            mask-image: radial-gradient(circle at 100% 100%, #000 0%, transparent 84%);
        }
        .login-card {
            width: 100%;
            max-width: 460px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(150deg, #15171B 0%, #0E1013 55%, #151017 100%);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-top-color: rgba(255, 255, 255, 0.12);
            border-right-color: rgba(255, 255, 255, 0.05);
            border-bottom-color: rgba(225, 29, 72, 0.12);
            border-left-color: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.45),
                0 14px 34px rgba(0, 0, 0, 0.30),
                0 0 0 1px rgba(255, 255, 255, 0.02),
                0 16px 46px rgba(225, 29, 72, 0.05);
            padding: clamp(28px, 4vw, 44px);
            animation: cardIn 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        /* Subtle ambient glow + blurred red shapes INSIDE the card (behind the content) */
        .login-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(80% 60% at 88% 6%, rgba(225, 29, 72, 0.07), transparent 60%),
                radial-gradient(130% 85% at 50% -12%, rgba(225, 29, 72, 0.05), transparent 58%);
        }

        /* Ultra-thin grain/texture inside the card (matches the left panel noise) */
        .login-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            z-index: 0;
            pointer-events: none;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* Keep every card element above the inner decoration */
        .login-card > * {
            position: relative;
            z-index: 1;
        }
        .card-head {
            position: relative;
            text-align: center;
            margin-bottom: 30px;
        }
        /* Subtle curved red accent line under the headline */
        .card-head::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translate(-50%, 16px);
            width: 46px;
            height: 2px;
            border-radius: 999px;
            pointer-events: none;
            background: linear-gradient(90deg, rgba(225, 29, 72, 0.04), rgba(225, 29, 72, 0.38), rgba(225, 29, 72, 0.04));
        }
        .card-mark {
            width: 52px;
            height: 52px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            color: var(--accent);
            background: rgba(225, 29, 72, 0.10);
            border: 1px solid rgba(225, 29, 72, 0.32);
        }
        .card-mark svg {
            width: 26px;
            height: 26px;
        }
        .card-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }
        .card-title {
            margin: 0 0 8px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .card-sub {
            margin: 0;
            font-size: 13.5px;
            color: var(--muted);
        }

        /* --- global error --- */
        .login-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: rgba(248, 113, 113, 0.09);
            border: 1px solid rgba(248, 113, 113, 0.4);
            border-radius: 11px;
            padding: 12px 14px;
            font-size: 13px;
            color: #F5B8B8;
            margin-bottom: 22px;
            animation: fadeIn 0.3s ease both;
        }
        .login-alert svg {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            margin-top: 2px;
        }

        /* --- fields --- */
        .field-group {
            margin-bottom: 18px;
        }
        .field-label {
            display: block;
            margin-bottom: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--muted);
        }
        .input-wrap,
        .password-wrap {
            position: relative;
        }
        .input-wrap .icon,
        .password-wrap .icon {
            position: absolute;
            left: 14px;
            top: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            color: #6B7280;
            pointer-events: none;
        }
        .input-wrap .icon svg,
        .password-wrap .icon svg {
            width: 16px;
            height: 16px;
        }
        .input-field {
            width: 100%;
            padding: 12px 14px 12px 42px;
            font-size: 14px;
            caret-color: var(--accent);
            color: var(--text);
            background: var(--input);
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }
        .input-field::placeholder {
            color: #555D69;
        }
        .input-field:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.18);
        }
        .input-field.is-error {
            border-color: rgba(248, 113, 113, 0.65);
            box-shadow: 0 0 0 1px rgba(248, 113, 113, 0.28);
            background: #191014;
        }
        .field-error {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 7px;
            font-size: 12.5px;
            color: #F5B8B8;
            animation: fadeIn 0.25s ease both;
        }
        .field-error svg {
            width: 13px;
            height: 13px;
            flex: 0 0 auto;
        }
        .password-wrap .input-field {
            padding-right: 44px;
        }
        .eye-toggle {
            position: absolute;
            right: 3px;
            top: 0;
            bottom: 0;
            width: 38px;
            display: grid;
            place-items: center;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #6B7280;
            padding: 0;
            transition: color 0.15s ease;
        }
        .eye-toggle svg {
            width: 17px;
            height: 17px;
        }
        .eye-toggle:hover {
            color: #C9CED6;
        }
        .eye-slash {
            display: none;
        }

        /* --- options row --- */
        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 2px 0 22px;
        }
        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            font-size: 13px;
            color: var(--muted);
            user-select: none;
        }
        .remember-label input[type="checkbox"] {
            appearance: none;
            width: 16px;
            height: 16px;
            margin: 0;
            background: var(--input);
            border: 1px solid var(--border);
            border-radius: 5px;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .remember-label input[type="checkbox"]:checked {
            background: var(--accent);
            border-color: var(--accent);
        }
        .remember-label input[type="checkbox"]:checked::after {
            content: '';
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 2px;
        }
        .forgot-link {
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .forgot-link:hover {
            color: var(--accent);
        }

        /* --- submit --- */
        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            padding: 13px;
            font-size: 14.5px;
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
        .submit-btn svg {
            width: 16px;
            height: 16px;
        }
        .submit-btn:hover {
            background: var(--accent-hover);
            filter: brightness(1.06);
            box-shadow: 0 12px 26px rgba(225, 29, 72, 0.36), 0 0 0 1px rgba(225, 29, 72, 0.9);
            transform: translateY(-1px);
        }
        .submit-btn:active {
            transform: scale(0.98);
        }
        .submit-btn:focus-visible {
            outline: 3px solid rgba(255, 255, 255, 0.28);
            outline-offset: 2px;
        }
        .panel-foot {
            position: absolute;
            bottom: 16px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            color: #6B7280;
        }

        /* --- animations --- */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: none;
            }
        }

        /* --- responsive --- */
        @media (max-width: 1024px) {
            .app {
                grid-template-columns: 42fr 58fr;
            }
            .visual-content {
                padding: clamp(18px, 3vw, 36px);
            }
            .feature-badges li {
                padding: 7px 11px;
                font-size: 12px;
            }
            .panel-deco {
                opacity: 0.6; /* tablet: decoration extra dimmed */
            }
        }
        /* ===== MOBILE (<=768px): HERO slideshow di ATAS, login form di BAWAH =====
           Layout vertikal, bukan desktop yang diperkecil.
           Hero/slideshow TETAP tampil (gambar tidak disembunyikan). */
        @media (max-width: 768px) {
            /* Aktifkan scroll vertikal di mobile (desktop tetap overflow: hidden) */
            html,
            body {
                height: auto;
            }
            body {
                overflow-x: hidden;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Grid 2 kolom -> 1 kolom vertikal */
            .app {
                grid-template-columns: 1fr;
                overflow: visible;
            }

            /* Mobile: decoration stays but stays subtle */
            .panel-deco {
                opacity: 0.5;
                position: absolute;
                inset: 0;
            }

            /* --- HERO: TETAP tampil, tidak disembunyikan --- */
            .visual {
                width: 100%;
                height: 38vh;
                min-height: 280px;
                max-height: 360px;
            }

            /* Gambar tetap penuh memenuhi panel.
               Slide berbasis div: background-size: cover + background-position: center
               adalah padanan dari object-fit: cover untuk gambar biasa. */
            .slide {
                background-size: cover;
                background-position: center;
            }

            /* --- Teks hero diringkas agar pas di atas foto --- */
            .visual-content {
                padding: 16px 18px 0;
            }
            .brand-mark {
                width: 32px;
                height: 32px;
                border-radius: 9px;
            }
            .brand-name {
                font-size: 15px;
            }
            .brand-name span {
                margin-top: 1px;
                font-size: 10.5px;
            }
            .visual-body {
                max-width: 100%;
                padding-bottom: 20px;
            }
            .visual-eyebrow {
                margin-bottom: 10px;
                padding: 5px 10px;
                font-size: 10.5px;
            }
            .visual-headline {
                font-size: clamp(28px, 9vw, 33px);
                line-height: 1.14;
                margin-bottom: 10px;
            }
            .visual-desc {
                font-size: 13.5px;
                line-height: 1.55;
                margin-bottom: 0;
            }

            /* Pills cukup panjang disembunyikan pada mobile.
               Foto & headline utama tetap tampil. */
            .feature-badges {
                display: none;
            }

            /* Indikator slide tetap tampil */
            .slide-dots {
                left: 18px;
                bottom: 12px;
            }

            /* --- LOGIN PANEL: mengalir di bawah hero --- */
            .form-panel {
                min-height: auto;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px 16px;
                padding-bottom: max(20px, env(safe-area-inset-bottom));
            }
            .login-card {
                width: 100%;
                max-width: 460px;
                padding: 24px 20px;
                box-shadow: 0 20px 44px rgba(0, 0, 0, 0.5);
            }
            .card-head {
                margin-bottom: 22px;
            }
            .card-mark {
                width: 44px;
                height: 44px;
                margin-bottom: 14px;
            }
            .card-title {
                font-size: 20px;
            }
            /* 16px mencegah iOS auto-zoom saat input difokuskan */
            .input-field {
                font-size: 16px;
            }
            .panel-foot {
                position: static;
                margin-top: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="app">

        <!-- ===== LEFT: VISUAL HERO ===== -->
        <aside class="visual">
            <div class="slides" aria-hidden="true">
                <div class="slide active" style="background-image:url('{{ asset('images/hijab.jpg') }}')"></div>
                <div class="slide" style="background-image:url('{{ asset('images/hijab3.jpg') }}')"></div>
                <div class="slide" style="background-image:url('{{ asset('images/Gemini_Generated_Image_m7ee54m7ee54m7ee.jpg') }}')"></div>
            </div>
            <div class="visual-overlay"></div>
            <div class="visual-glow"></div>
            <div class="visual-grain"></div>
            <div class="visual-content" id="visualContent">
                <div class="brand">
                    <div class="brand-mark">
                        <img src="{{ asset('images/logohijab.png') }}" alt="Logo Vendor Hijab Bandung">
                    </div>
                    <div class="brand-name">Vendor Hijab Bandung
                        <span>Business Management Platform</span>
                    </div>
                </div>

                <div class="visual-body">
                    <div class="visual-eyebrow">Premium Fashion Business</div>
                    <h1 class="visual-headline">Kelola Bisnis Hijab<br>Lebih <em>Terarah.</em></h1>
                    <p class="visual-desc">Pantau riset, produk, iklan, penjualan, dan profit bisnis dalam satu platform.</p>
                    <ul class="feature-badges">
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            Riset Bisnis
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                            </svg>
                            Kelola Produk
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 11 18-5v12L3 14v-3z"/>
                                <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
                            </svg>
                            Optimasi Iklan
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                            Pantau Profit
                        </li>
                    </ul>
                </div>
            </div>
            <div class="slide-dots" role="tablist" aria-label="Slide showcase">
                <button type="button" class="slide-dot active" data-slide="0" aria-label="Slide 1"></button>
                <button type="button" class="slide-dot" data-slide="1" aria-label="Slide 2"></button>
                <button type="button" class="slide-dot" data-slide="2" aria-label="Slide 3"></button>
            </div>
        </aside>

        <!-- ===== RIGHT: FORM ===== -->
        <main class="form-panel">
            <!-- ===== BACKGROUND DECORATION (kanan) — subtiel, achter de card ===== -->
            <div class="panel-deco" aria-hidden="true">
                <div class="deco-glow"></div>
                <div class="deco-grid"></div>
                <div class="deco-ring deco-ring-1"></div>
                <div class="deco-ring deco-ring-2"></div>
                <div class="deco-ring deco-ring-3"></div>
                <div class="deco-dots"></div>
            </div>
            <div class="login-card">
                <div class="card-head">
                    <div class="card-mark">
                        <img src="{{ asset('images/logohijab.png') }}" alt="Logo Vendor Hijab Bandung">
                    </div>
                    <h2 class="card-title">Selamat Datang Kembali</h2>
                    <p class="card-sub">Masuk untuk melanjutkan ke akun Anda.</p>
                </div>

                @error('login')
                    <div class="login-alert" role="alert">
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round">
                            <circle cx="8" cy="8" r="6" />
                            <path d="M6.4 5.8 9.6 10.2 M9.6 5.8 6.4 10.2" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <form action="{{ route('login') }}" method="post" novalidate>
                    @csrf

                    <div class="field-group">
                        <label class="field-label" for="login">Email / Username</label>
                        <div class="input-wrap">
                            <span class="icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="m22 7-10 6L2 7"/>
                                </svg>
                            </span>
                            <input class="input-field {{ $errors->has('login') ? 'is-error' : '' }}" type="text"
                                   id="login" name="login" value="{{ old('login') }}"
                                   placeholder="Masukkan email atau username"
                                   autocomplete="username" required>
                        </div>
                        @error('login')
                            <div class="field-error">
                                <svg viewBox="0 0 13 13" fill="none" stroke="currentColor" stroke-width="1.3">
                                    <circle cx="6.5" cy="6.5" r="5" />
                                    <path d="M5.2 5.2 7.8 7.8 M7.8 5.2 5.2 7.8" />
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="password">Password</label>
                        <div class="password-wrap">
                            <span class="icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input class="input-field {{ $errors->has('password') ? 'is-error' : '' }}" type="password"
                                   id="password" name="password"
                                   placeholder="Masukkan password"
                                   autocomplete="current-password" required>
                            <button type="button" class="eye-toggle" id="eyeToggle" aria-label="Toon / verberg password" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-slash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-error">
                                <svg viewBox="0 0 13 13" fill="none" stroke="currentColor" stroke-width="1.3">
                                    <circle cx="6.5" cy="6.5" r="5" />
                                    <path d="M5.2 5.2 7.8 7.8 M7.8 5.2 5.2 7.8" />
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="options-row">
                        <label class="remember-label">
                            <input type="checkbox" name="remember" id="remember" value="1"
                                   {{ old('remember') ? 'checked' : '' }}>
                            Ingat saya
                        </label>
                        <a class="forgot-link" href="#">Lupa password?</a>
                    </div>

                    <button type="submit" class="submit-btn">
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8 12 8 M12 8 14.4 8 M13 2.2 13 13.8" />
                        </svg>
                        Masuk
                    </button>
                </form>
            </div>

            <div class="panel-foot">&copy; 2026 Vendor Hijab Bandung</div>
        </main>
    </div>
    <script>
        (function () {
            /* ===== Slider (vanilla JS) ===== */
            var slides = document.querySelectorAll('.slide');
            var dots = document.querySelectorAll('.slide-dot');
            var visualContent = document.getElementById('visualContent');
            var current = 0;
            var timer = null;
            var INTERVAL = 5000;

            function goTo(index) {
                slides[current].classList.remove('active');
                dots[current].classList.remove('active');
                current = (index + slides.length) % slides.length;
                slides[current].classList.add('active');
                dots[current].classList.add('active');

                if (visualContent) {
                    visualContent.classList.add('is-fading');
                    setTimeout(function () {
                        visualContent.classList.remove('is-fading');
                    }, 350);
                }
            }

            function startAutoplay() {
                if (timer) {
                    clearInterval(timer);
                }
                timer = setInterval(function () {
                    goTo(current + 1);
                }, INTERVAL);
            }

            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    goTo(parseInt(dot.getAttribute('data-slide'), 10));
                    startAutoplay(); /* autoplay tetap berjalan setelah klik */
                });
            });

            if (slides.length > 1) {
                startAutoplay();
            }

            /* ===== Show/Hide password ===== */
            var eyeToggle = document.getElementById('eyeToggle');
            var passwordField = document.getElementById('password');
            var eyeOpen = document.querySelector('.eye-open');
            var eyeSlash = document.querySelector('.eye-slash');
            if (!eyeToggle || !passwordField) {
                return;
            }
            eyeToggle.addEventListener('click', function () {
                var reveal = passwordField.type === 'password';
                passwordField.type = reveal ? 'text' : 'password';
                eyeToggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
                eyeOpen.style.display = reveal ? 'none' : 'block';
                eyeSlash.style.display = reveal ? 'block' : 'none';
            });
        })();
    </script>
</body>
</html>
