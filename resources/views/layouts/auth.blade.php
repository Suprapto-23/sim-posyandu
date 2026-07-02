<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal layanan kesehatan terpadu PosyanduCare.">
    
    <link rel="preload" as="image" href="{{ asset('img/logo.webp') }}" type="image/webp" fetchpriority="high">
    <title>@yield('title', 'PosyanduCare')</title>
    <link rel="icon" type="image/webp" href="{{ asset('img/logo.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-regular-400.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"></noscript>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.11.0/dist/sweetalert2.all.min.js" defer></script>
    
    <style>
        :root {
            --green-900: #064e3b; --green-700: #047857; --green-600: #059669; --green-500: #10b981;
            --slate-900: #0f172a; --slate-700: #334155; --slate-600: #475569; --slate-500: #64748b;
        }

        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100svh; margin: 0; background-color: #f8fafc !important; }
        
        body {
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
            color: var(--slate-900);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            position: relative;
        }

        /* 1. EFEK BLUR BACKGROUND INSTAN */
        .auth-bg {
            position: absolute; inset: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 10% 10%, rgba(16,185,129,0.15), transparent 40%),
                        radial-gradient(circle at 90% 90%, rgba(245,158,11,0.12), transparent 40%),
                        linear-gradient(135deg, #eefcf6 0%, #ffffff 50%, #fff7ed 100%);
            z-index: 1;
            transition: opacity 0.1s ease-out, filter 0.1s ease-out;
            will-change: opacity, filter;
        }
        
        body.is-splitting .auth-bg { 
            opacity: 0.1; 
            filter: blur(12px); 
        }

        /* 2. FORM TRANISI KELUAR (Lebih halus dan singkat) */
        .auth-main { 
            position: relative; z-index: 10; min-height: 100svh; 
            display: flex; align-items: center; justify-content: center; padding: 24px; 
            transition: opacity 0.08s ease-out, transform 0.1s ease-out;
            will-change: opacity, transform;
        }
        body.is-splitting .auth-main { 
            opacity: 0; 
            transform: scale(0.98) translateY(-15px); 
        }

        /* 3. WADAH LOADING (Center Screen) */
        .bridge-loader {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -40%) scale(0.95);
            z-index: 20; opacity: 0;
            display: flex; flex-direction: column; align-items: center; gap: 14px;
            transition: opacity 0.12s ease-out, transform 0.12s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
            will-change: transform, opacity;
        }
        body.is-splitting .bridge-loader { 
            opacity: 1; 
            transform: translate(-50%, -50%) scale(1); 
        }

        /* 4. PURE CSS PREMIUM SPINNER (Aman untuk GPU) */
        .fluent-spinner {
            width: 46px; height: 46px; border-radius: 50%;
            border: 3.5px solid rgba(16, 185, 129, 0.12); 
            border-top-color: var(--green-600); 
            border-right-color: rgba(16, 185, 129, 0.6); 
            animation: fluentSpin 0.55s linear infinite;
        }
        
        @keyframes fluentSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .bridge-loader p { 
            color: var(--slate-900); font-weight: 800; font-size: 15px; margin: 0; letter-spacing: -0.3px;
        }

        /* 5. SWEETALERT: Desain Premium */
        .swal2-backdrop-show { background: rgba(15, 23, 42, 0.4) !important; backdrop-filter: blur(8px) !important; }
        .custom-swal-popup { border-radius: 24px !important; padding: 2rem !important; background: rgba(255, 255, 255, 0.98) !important; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1) !important; width: 24em !important; }
        .custom-swal-title { color: var(--slate-900) !important; font-size: 1.25rem !important; font-weight: 800 !important; margin-top: 10px !important;}
        .custom-swal-html { color: var(--slate-600) !important; font-size: 0.9rem !important; font-weight: 500 !important; margin-top: 0.25rem !important; }
        .custom-swal-confirm-btn { border-radius: 12px !important; background: var(--slate-900) !important; color: #fff !important; font-weight: 700 !important; padding: 12px 24px !important; width: 100% !important; margin-top: 1.5rem !important; transition: transform 0.1s ease-out !important; will-change: transform; }
        .custom-swal-confirm-btn:active { transform: scale(0.96) !important; }
        .swal2-icon.swal2-success { border-color: var(--green-500) !important; color: var(--green-500) !important; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-bg"></div>

    <div class="bridge-loader">
        <div class="fluent-spinner"></div>
        <p>Memuat Dasbor...</p>
    </div>

    <main class="auth-main">
        @yield('content')
    </main>

    <script>
        window.showAuthAlert = function (title, message, icon = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title, html: message, icon: icon, confirmButtonText: 'Tutup',
                    customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-html', confirmButton: 'custom-swal-confirm-btn' },
                    buttonsStyling: false, timer: 3000, timerProgressBar: true
                });
            }
        };

        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { document.body.classList.remove('is-splitting'); }
        });

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                window.showAuthAlert('Berhasil', @json(session('success')), 'success');
            @endif
            @if(session('error'))
                window.showAuthAlert('Gagal', @json(session('error')), 'error');
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>