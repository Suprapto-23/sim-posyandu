<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PosyanduCare')</title>

    <!-- 1. PRELOAD LCP IMAGE (Sangat Bagus! Pastikan img/logo.webp sudah di-resize ke ukuran kecil) -->
    <link rel="preload" as="image" href="{{ asset('img/logo.webp') }}" type="image/webp" fetchpriority="high">

    <!-- 2. PRECONNECT UNTUK FONT & RESOURCE PIHAK KETIGA -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- 3. PEMANGGILAN FONT UTAMA (Disederhanakan) -->
    <!-- Menghapus trik media="print" karena bisa memicu pergeseran layout (CLS) saat teks muncul. -->
    <!-- Cukup andalkan display=swap dan preconnect untuk font utama Plus Jakarta Sans. -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"></noscript>

    <!-- 4. FONT AWESOME (Tetap Async karena file besar dan bukan prioritas teks utama) -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'" referrerpolicy="no-referrer">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer"></noscript>

    <!-- 5. ASSETS VITE (Wajib di sini agar di-preload oleh Laravel) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- 6. SWEETALERT2 (Defer, eksekusi paling akhir) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <style>
        /* ===== RESET & BASE ===== */
        :root {
            --green-900: #064e3b;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --amber-500: #f59e0b;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --ease: cubic-bezier(.16, 1, .3, 1);
        }

        * { box-sizing: border-box; }
        html { width: 100%; min-height: 100%; }
        body {
            margin: 0;
            min-height: 100svh;
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--slate-900);
            background: radial-gradient(circle at 12% 12%, rgba(16,185,129,.12), transparent 28%),
                        radial-gradient(circle at 88% 10%, rgba(245,158,11,.11), transparent 26%),
                        radial-gradient(circle at 50% 96%, rgba(14,165,233,.075), transparent 32%),
                        linear-gradient(135deg, #f8fffc 0%, #f8fafc 56%, #fffaf0 100%);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        body.auth-submitting { cursor: wait; }
        a { color: inherit; text-decoration: none; }
        button, input { font-family: inherit; }

        /* ===== AMBIENT BACKGROUND ===== */
        .auth-bg-soft {
            position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden;
        }
        .auth-bg-soft::before, .auth-bg-soft::after {
            content: ""; position: absolute; border-radius: 999px; pointer-events: none;
        }
        .auth-bg-soft::before {
            width: 430px; height: 430px; top: -190px; left: -180px;
            background: rgba(16,185,129,.11); filter: blur(72px);
        }
        .auth-bg-soft::after {
            width: 390px; height: 390px; right: -170px; bottom: -160px;
            background: rgba(245,158,11,.105); filter: blur(74px);
        }
        .auth-grid-soft {
            position: fixed; inset: 0; z-index: 1; pointer-events: none; opacity: .07;
            background-image: linear-gradient(rgba(15,23,42,.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(15,23,42,.04) 1px, transparent 1px);
            background-size: 72px 72px;
        }

        .auth-main {
            position: relative; z-index: 5; min-height: 100svh;
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }

        /* ===== SWEETALERT CUSTOM ===== */
        .custom-swal-popup {
            border-radius: 28px !important; padding: 2rem !important;
            font-family: "Plus Jakarta Sans", sans-serif !important;
            border: 1px solid rgba(226,232,240,.86) !important;
            box-shadow: 0 28px 70px rgba(15,23,42,.16) !important;
            background: rgba(255,255,255,.98) !important;
        }
        .custom-swal-title {
            color: #0f172a !important; font-size: 1.2rem !important; font-weight: 900 !important;
        }
        .custom-swal-html {
            color: #475569 !important; font-size: .92rem !important; font-weight: 650 !important;
        }
        .custom-swal-confirm-btn {
            border: 0 !important; border-radius: 16px !important;
            padding: .8rem 1.25rem !important;
            background: linear-gradient(135deg, #047857, #10b981) !important;
            color: #fff !important; font-weight: 900 !important;
            box-shadow: 0 10px 22px rgba(5,150,105,.28) !important;
        }
        .swal2-container {
            z-index: 99999 !important; background: rgba(15,23,42,.36) !important;
            backdrop-filter: blur(6px) !important;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            body {
                background: radial-gradient(circle at 18% 6%, rgba(16,185,129,.10), transparent 30%),
                            radial-gradient(circle at 90% 18%, rgba(245,158,11,.09), transparent 26%),
                            linear-gradient(135deg, #f8fffc 0%, #f8fafc 66%, #fffaf0 100%);
            }
            .auth-main { padding: 12px; }
            .auth-grid-soft { display: none; }
            .auth-bg-soft::before, .auth-bg-soft::after { filter: blur(46px); }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 1ms !important !important;
                transition-duration: 1ms !important !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="auth-bg-soft"></div>
    <div class="auth-grid-soft"></div>

    <main class="auth-main">
        @yield('content')
    </main>

    <script>
        (function () {
            // Tidak ada splash screen – langsung resolve
            window.PosyanduAuthTransition = {
                show: function () {},
                hide: function () {},
                play: function () { return Promise.resolve(); }
            };

            // Fungsi alert global – menggunakan SweetAlert2
            window.showAuthAlert = function (title, message, icon = 'info') {
                if (typeof Swal === 'undefined') {
                    alert(title + '\n' + message);
                    return;
                }
                Swal.fire({
                    title: title,
                    html: message,
                    icon: icon,
                    confirmButtonText: 'Mengerti',
                    timer: 4000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        htmlContainer: 'custom-swal-html',
                        confirmButton: 'custom-swal-confirm-btn'
                    },
                    buttonsStyling: false,
                    backdrop: 'rgba(15,23,42,.36)'
                });
            };

            window.addEventListener('pageshow', function () {
                document.body.classList.remove('auth-submitting');
            });
        })();
    </script>

    @stack('scripts')
    <script>
    // Mencegah klik kanan
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Mencegah shortcut inspect (F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J')) ||
            (e.ctrlKey && e.key === 'U')) {
            e.preventDefault();
            return false;
        }
    });
</script>
</body>
</html>
