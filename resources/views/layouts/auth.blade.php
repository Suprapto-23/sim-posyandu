<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PosyanduCare')</title>

    <link rel="icon" type="image/webp" href="{{ asset('img/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.webp') }}">

    <link rel="preload" as="image" href="{{ asset('img/logo.webp') }}" type="image/webp" fetchpriority="high">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    
    <style>
        :root {
            --green-900: #064e3b; --green-800: #065f46; --green-700: #047857;
            --green-600: #059669; --green-500: #10b981; --amber-500: #f59e0b;
            --slate-900: #0f172a; --slate-500: #64748b;
        }

        * { box-sizing: border-box; }
        
        /* Set background bawaan menjadi warna Dashboard untuk mencegah blank putih murni */
        html { width: 100%; min-height: 100%; background-color: #f8fafc !important; }
        
        body {
            margin: 0; min-height: 100svh;
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--slate-900);
            background: #f8fafc; /* Warna solid base */
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            position: relative;
        }

        /* Background Gradient yang bisa di-fade-out */
        .auth-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 10% 10%, rgba(16,185,129,0.15), transparent 40%),
                        radial-gradient(circle at 90% 90%, rgba(245,158,11,0.12), transparent 40%),
                        linear-gradient(135deg, #eefcf6 0%, #ffffff 50%, #fff7ed 100%);
            z-index: 1;
            transition: opacity 0.6s ease;
        }
        body.is-splitting .auth-bg { opacity: 0; }

        /* ===== BRIDGE LOADER (Muncul dari belakang saat layar terbelah) ===== */
        .bridge-loader {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%) scale(0.85);
            z-index: 2; opacity: 0;
            display: flex; flex-direction: column; align-items: center; gap: 16px;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            transition-delay: 0.1s;
            pointer-events: none;
        }
        .bridge-loader i { font-size: 42px; color: var(--green-600); }
        .bridge-loader p { color: var(--slate-600); font-weight: 700; font-size: 16px; margin: 0; letter-spacing: 0.5px;}
        
        body.is-splitting .bridge-loader {
            opacity: 1; transform: translate(-50%, -50%) scale(1);
        }

        /* ===== MAIN KONTEN ===== */
        .auth-main {
            position: relative; z-index: 10; min-height: 100svh;
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }

        /* ===== SWEETALERT PREMIUM ===== */
        .swal2-backdrop-show { background: rgba(15, 23, 42, 0.4) !important; backdrop-filter: blur(8px) !important; }
        .custom-swal-popup { border-radius: 24px !important; padding: 2rem !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.8) !important; width: 24em !important; }
        .custom-swal-title { color: var(--slate-900) !important; font-size: 1.3rem !important; font-weight: 800 !important; }
        .custom-swal-html { color: var(--slate-500) !important; font-size: 0.95rem !important; font-weight: 500 !important; }
        .custom-swal-confirm-btn { border-radius: 12px !important; background: var(--green-600) !important; font-weight: 700 !important; padding: 12px 24px !important; width: 100% !important; margin-top: 1rem !important; }
        .custom-swal-confirm-btn:hover { background: var(--green-700) !important; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-bg"></div>

    <!-- Loading yang tersembunyi di belakang layar -->
    <div class="bridge-loader">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
        <p>Menyiapkan Dasbor...</p>
    </div>

    <main class="auth-main">
        @yield('content')
    </main>

    <script>
        window.showAuthAlert = function (title, message, icon = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title, html: message, icon: icon, confirmButtonText: 'Mengerti',
                    customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-html', confirmButton: 'custom-swal-confirm-btn' },
                    buttonsStyling: false
                });
            } else { alert(title + '\n' + message); }
        };

        // Reset state jika user menekan tombol 'Back' di browser
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                document.body.classList.remove('is-splitting');
                const btn = document.getElementById('submitBtn');
                if(btn) {
                    btn.disabled = false; btn.style.opacity = '1';
                    btn.innerHTML = '<span id="submitTxt">Masuk</span><i class="fa-solid fa-arrow-right-to-bracket" id="submitIco"></i>';
                }
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Menangkap Session Logout / Error
            @if(session('success'))
                window.showAuthAlert('Sampai Jumpa!', @json(session('success')), 'success');
            @endif
            @if(session('error'))
                window.showAuthAlert('Gagal', @json(session('error')), 'error');
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>