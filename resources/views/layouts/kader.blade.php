<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Kader Workspace') — KaderCare</title>
    
    {{-- Meta Performa & Theme --}}
    <meta name="theme-color" content="#f8fafc">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjU2M2ViIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTE5IDE0YzEuNDkgLTEuNDYgMyAtMy4yMSA0IC01LjVjMS4yNSAtMi41IC0uNzYgLTQgLTMuNSAtNGMtMS44IDAgLTMgMSAtNCAyYy0xIC0xIC0yLjIgLTIgLTQgLTJjLTIuNzQgMCAtNC43NSAxLjUgLTMuNSA0YzEgMi4yOSAyLjUxIDQuMDQgNCA1LjVsNSA1WloiLz48L3N2Zz4=">
    
    {{-- Font Core: Plus Jakarta Sans (Body) & Poppins (Heading) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- Icons & Engine --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        :root { 
            --font-sans: 'Plus Jakarta Sans', sans-serif; 
            --font-poppins: 'Poppins', sans-serif;
            --nexus-blue: #0891b2; /* Cyan 600 style dari gambar */
        }

        /* 1. Reset & Base (Performance 100%) */
        body { 
            background: #f8fafc; 
            font-family: var(--font-sans) !important; 
            color: #1e293b; 
            -webkit-font-smoothing: antialiased;
            overscroll-behavior-y: none;
        }
        h1, h2, h3, .font-poppins { font-family: var(--font-poppins) !important; }

        /* 2. Ultra Smooth Animations (GPU Accelerated & Staggered) */
        .layer-gpu { transform: translateZ(0); backface-visibility: hidden; will-change: transform, opacity; }
        
        /* Entrance Choreography */
        .stagger-sidebar { animation: fadeSlideRight 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .stagger-header  { animation: fadeSlideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; animation-delay: 0.1s; }
        .stagger-content { animation: fadeSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; animation-delay: 0.2s; }

        @keyframes fadeSlideRight { from { opacity: 0; transform: translate3d(-30px, 0, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
        @keyframes fadeSlideDown  { from { opacity: 0; transform: translate3d(0, -20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
        @keyframes fadeSlideUp    { from { opacity: 0; transform: translate3d(0, 30px, 0) scale(0.98); } to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); } }

        /* 3. Nexus Glass Components */
        .nexus-glass-header {
            background: rgba(248, 250, 252, 0.75); backdrop-filter: blur(24px) saturate(150%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 30px rgba(15, 23, 42, 0.03);
        }

        /* 4. PERFECT NEXUS SWEETALERT (Sesuai Gambar 2) */
        .swal2-popup.nexus-swal {
            border-radius: 32px !important;
            padding: 2.5rem 2rem !important;
            font-family: var(--font-sans) !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15) !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px) !important;
        }
        .swal2-icon { margin-top: 0 !important; border-width: 3px !important; }
        .swal2-title { 
            font-family: var(--font-poppins) !important; font-weight: 800 !important; 
            color: #1e293b !important; font-size: 1.4rem !important; margin-top: 1rem !important;
        }
        .swal2-html-container { 
            color: #64748b !important; font-weight: 500 !important; font-size: 0.95rem !important; 
            margin-top: 0.5rem !important; line-height: 1.6 !important;
        }
        .swal2-actions { margin-top: 2rem !important; gap: 12px !important; }
        
        .btn-nexus-confirm { 
            background: linear-gradient(135deg, #06b6d4, #0891b2) !important; /* Cyan Gradient */
            color: white !important; border-radius: 16px !important; font-weight: 800 !important; 
            text-transform: uppercase !important; letter-spacing: 0.05em !important; 
            padding: 14px 32px !important; box-shadow: 0 10px 20px -5px rgba(8, 145, 178, 0.4) !important;
            transition: all 0.3s ease !important; border: none !important;
        }
        .btn-nexus-confirm:hover { transform: translateY(-2px) !important; box-shadow: 0 15px 25px -5px rgba(8, 145, 178, 0.5) !important; }
        
        .btn-nexus-cancel { 
            background: #f1f5f9 !important; color: #64748b !important; 
            border-radius: 16px !important; font-weight: 800 !important; 
            text-transform: uppercase !important; letter-spacing: 0.05em !important; 
            padding: 14px 32px !important; transition: all 0.3s ease !important; border: none !important;
        }
        .btn-nexus-cancel:hover { background: #e2e8f0 !important; color: #334155 !important; }

        /* 5. SPA Progress Bar */
        @keyframes loadProgress { 0% { transform: translateX(-100%); } 100% { transform: translateX(250%); } }
        .nav-progress { animation: loadProgress 1.2s infinite cubic-bezier(0.4, 0, 0.2, 1); }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
{{-- Engine Ikon FontAwesome (Menghidupkan kembali ikon di halaman lama) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Engine Ikon Phosphor (Untuk UI Nexus Premium terbaru) --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body class="flex min-h-screen overflow-hidden" 
      x-data="{ sidebarOpen: false, notifOpen: false, profileOpen: false, isNavigating: false }"
      @spa-start.window="isNavigating = true"
      @spa-stop.window="isNavigating = false">

    {{-- TOP PROGRESS BAR (SPA FEEL) --}}
    <div x-show="isNavigating" class="fixed top-0 left-0 w-full h-1 z-[9999] bg-cyan-50 overflow-hidden" x-cloak>
        <div class="h-full bg-cyan-500 w-1/3 rounded-r-full shadow-[0_0_10px_rgba(6,182,212,0.8)] nav-progress"></div>
    </div>

    {{-- SIDEBAR CONTAINER (STAGGER 1) --}}
    <div class="fixed inset-y-0 left-0 z-[50] w-[290px] transform transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] xl:translate-x-0 layer-gpu shadow-2xl xl:shadow-none stagger-sidebar"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        @include('partials.sidebar.kader')
    </div>

    {{-- MAIN VIEWPORT --}}
    <div class="flex-1 flex flex-col min-w-0 h-screen relative xl:ml-[290px] transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]">
        
        {{-- HEADER (STAGGER 2) --}}
        <header class="h-[80px] nexus-glass-header sticky top-0 z-[35] flex items-center justify-between px-6 sm:px-10 shrink-0 layer-gpu stagger-header">
            
            <div class="flex items-center gap-5">
                {{-- Hamburger (Mobile Only) --}}
                <button @click="sidebarOpen = !sidebarOpen" class="w-[44px] h-[44px] flex items-center justify-center bg-white border border-slate-200 text-slate-500 rounded-2xl shadow-sm xl:hidden active:scale-90 transition-all">
                    <i class="ph ph-list text-2xl"></i>
                </button>

                {{-- Status Pills (Clean Aesthetic) --}}
                <div class="hidden md:flex items-center gap-2.5 px-4 py-2 bg-cyan-50/50 rounded-2xl border border-cyan-100/50 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                    </span>
                    <span class="text-[10px] font-black text-cyan-700 uppercase tracking-[0.2em]">Kader Workspace Aktif</span>
                </div>
            </div>

            {{-- Actions (Notif & Profile) --}}
            <div class="flex items-center gap-4">
                
                {{-- Notifikasi Dropdown --}}
                <div class="relative">
                    <button @click="notifOpen = !notifOpen; profileOpen = false" class="w-[48px] h-[48px] flex items-center justify-center bg-white border border-slate-100 rounded-[18px] shadow-sm hover:border-cyan-200 hover:-translate-y-0.5 transition-all group outline-none">
                        <i class="ph ph-bell text-[22px] text-slate-500 group-hover:text-cyan-600 transition-colors"></i>
                        @php $unread = class_exists('\App\Models\Notifikasi') ? \App\Models\Notifikasi::where('user_id', Auth::id())->where('is_read', false)->count() : 0; @endphp
                        @if($unread > 0)
                        <span class="absolute top-2.5 right-2.5 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500 border-2 border-white"></span>
                        </span>
                        @endif
                    </button>
                    
                    <div x-show="notifOpen" @click.outside="notifOpen = false" x-cloak
                         x-transition:enter="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute top-full right-0 mt-4 w-[340px] bg-white rounded-[32px] shadow-2xl border border-slate-100 overflow-hidden z-[60] layer-gpu">
                        <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                            <h4 class="text-[14px] font-black text-slate-800 font-poppins">Pemberitahuan</h4>
                            <span class="text-[10px] font-bold text-cyan-600 bg-cyan-50 px-2.5 py-1 rounded-xl border border-cyan-100">{{ $unread }} Pesan</span>
                        </div>
                        <div class="max-h-[350px] overflow-y-auto custom-scrollbar p-3 space-y-1" id="notifList">
                            <p class="text-center py-10 text-slate-400 text-xs font-medium italic">Sinkronisasi pesan...</p>
                        </div>
                    </div>
                </div>

                {{-- User Profile Dropdown --}}
                <div class="relative">
                    <button @click="profileOpen = !profileOpen; notifOpen = false" class="flex items-center gap-3 p-1.5 bg-white border border-slate-100 rounded-[20px] shadow-sm hover:border-cyan-200 hover:-translate-y-0.5 transition-all outline-none group">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Kader') }}&background=0891b2&color=fff&bold=true" 
                             class="w-10 h-10 rounded-[14px] shadow-inner group-hover:scale-105 transition-transform" alt="Avatar">
                        <div class="hidden sm:block text-left mr-2">
                            <p class="text-[12px] font-black text-slate-800 leading-none truncate max-w-[120px] font-poppins">{{ Auth::user()->name ?? 'Kader' }}</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Petugas Kader</p>
                        </div>
                        <i class="ph ph-caret-down text-[12px] text-slate-300 hidden sm:block transition-transform duration-300" :class="profileOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="profileOpen" @click.outside="profileOpen = false" x-cloak
                         x-transition:enter="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute top-full right-0 mt-4 w-[260px] bg-white rounded-[32px] shadow-2xl border border-slate-100 p-3 z-[60] layer-gpu">
                        <a href="{{ route('kader.profile.index') ?? '#' }}" class="flex items-center gap-4 p-4 rounded-[22px] text-slate-600 hover:bg-cyan-50 hover:text-cyan-600 transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center group-hover:bg-white shadow-sm transition-all"><i class="ph ph-user-circle text-xl"></i></div>
                            <span class="text-xs font-black uppercase tracking-wider">Profil Akun</span>
                        </a>
                        <div class="h-px bg-slate-50 my-2 mx-4"></div>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" @click="window.dispatchEvent(new CustomEvent('spa-start'))" class="w-full flex items-center gap-4 p-4 rounded-[22px] text-rose-500 hover:bg-rose-500 hover:text-white transition-all group shadow-sm hover:shadow-rose-200">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center group-hover:bg-white transition-all"><i class="ph ph-power text-xl"></i></div>
                                <span class="text-xs font-black uppercase tracking-wider">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- CONTENT AREA (STAGGER 3 - ACCELERATED) --}}
        <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-8 lg:p-12 scroll-smooth">
            <div class="max-w-[1450px] mx-auto layer-gpu stagger-content">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- CORE ENGINE SCRIPTS --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // =========================================================================
            // NEXUS V3 GLOBAL ALERT (Matching Image 2 Aesthetics)
            // =========================================================================
            window.nexusAlert = (title, text, type = 'success', confirmText = 'MENGERTI') => {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: type,
                    showConfirmButton: true,
                    confirmButtonText: confirmText,
                    customClass: { 
                        popup: 'nexus-swal', 
                        confirmButton: 'btn-nexus-confirm',
                        cancelButton: 'btn-nexus-cancel'
                    },
                    buttonsStyling: false,
                    showClass: { popup: 'anim-reveal' },
                    hideClass: { popup: 'swal2-hide' }
                });
            };

            // Catch Flash Messages from Laravel
            @if(session('success')) setTimeout(() => window.nexusAlert('Berhasil!', "{{ session('success') }}", 'success', 'LANJUTKAN'), 400); @endif
            @if(session('error')) setTimeout(() => window.nexusAlert('Perhatian!', "{{ session('error') }}", 'error', 'TUTUP'), 400); @endif

            // Background Notif Sync
            function syncNotif() {
                @if(Route::has('kader.notifikasi.fetch'))
                fetch("{{ route('kader.notifikasi.fetch') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                    .then(r => r.json())
                    .then(data => {
                        const list = document.getElementById('notifList');
                        if(data.html && list) list.innerHTML = data.html;
                    }).catch(() => console.warn('Sync paused.'));
                @endif
            }
            syncNotif();
            setInterval(syncNotif, 45000);
        });

        // Interceptor for SPA Loader
        document.querySelectorAll('a.spa-route').forEach(link => {
            link.addEventListener('click', function(e) {
                if(this.target !== '_blank' && !e.ctrlKey) {
                    window.dispatchEvent(new CustomEvent('spa-start'));
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>