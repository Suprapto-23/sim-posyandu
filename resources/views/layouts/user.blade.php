<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Portal Warga') — PosyanduCare</title>
    
    {{-- Typography: Jakarta Sans & Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Plus Jakarta Sans', 'sans-serif'], 
                        poppins: ['Poppins', 'sans-serif'] 
                    },
                    colors: {
                        teal: { 50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4', 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a', 950: '#042f2e' },
                    }
                }
            }
        }
    </script>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Glassmorphism Header */
        .glass-nav { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px); 
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        
        /* 🔥 Floating Dock Bawah (Spasi Diperbaiki) 🔥 */
        .glass-bottom-nav { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 1); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1), 0 4px 10px rgba(0, 0, 0, 0.05); 
        }

        /* Entry Animation Halus */
        .app-loaded { animation: smoothEntry 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes smoothEntry {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Jarak scroll diperbaiki agar tidak terlalu jauh */
        @media (max-width: 768px) {
            .mobile-padding-bottom { padding-bottom: 95px !important; } 
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans antialiased overflow-hidden flex h-screen">

    {{-- Sidebar Overlay --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-500" onclick="toggleSidebar()"></div>

    {{-- Pemanggilan Sidebar (Ingat untuk hapus rute Konseling di file partial sidebar juga ya!) --}}
    @include('partials.sidebar.user')

    {{-- MAIN WRAPPER --}}
    <div class="flex-1 flex flex-col min-w-0 relative h-screen app-loaded">
        
        {{-- TOPBAR --}}
        <header class="h-[72px] md:h-20 glass-nav flex items-center justify-between px-5 md:px-10 z-30 shrink-0 relative">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="md:hidden w-10 h-10 flex items-center justify-center rounded-[14px] bg-slate-50 border border-slate-100 text-slate-600 active:scale-95 transition-transform duration-300">
                    <i class="fas fa-bars-staggered text-[15px]"></i>
                </button>
                <div class="truncate mt-0.5">
                    <h2 class="text-[17px] md:text-xl font-black text-slate-800 font-poppins tracking-tight leading-none">@yield('page_title', 'Beranda')</h2>
                    <p class="text-[9.5px] font-bold text-teal-600 uppercase tracking-[0.15em] hidden sm:block mt-1">Portal Warga Aktif</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Area Notifikasi --}}
                <div class="relative" id="notifArea">
                    <button onclick="toggleNotif()" id="notifBtn" class="w-10 h-10 md:w-11 md:h-11 flex items-center justify-center rounded-[14px] bg-white border border-slate-200 text-slate-500 hover:text-teal-600 transition-colors relative shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                        <i class="fas fa-bell text-[15px] md:text-lg"></i>
                        <span id="notifBadge" class="hidden absolute top-2 right-2.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
                        <span id="notifBadgePulse" class="hidden absolute top-2 right-2.5 w-2.5 h-2.5 bg-rose-500 rounded-full animate-ping opacity-75"></span>
                    </button>

                    {{-- Dropdown Notifikasi --}}
                    <div id="notifDropdown" class="absolute right-0 mt-4 w-[300px] sm:w-[380px] bg-white/95 backdrop-blur-xl rounded-[24px] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] border border-slate-100 opacity-0 invisible translate-y-4 transition-all duration-500 z-50 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between bg-slate-50/80">
                            <h3 class="font-black text-slate-800 text-[14px] font-poppins">Pemberitahuan</h3>
                            <span id="notifStatus" class="text-[9px] font-black bg-slate-100 text-slate-500 px-2.5 py-1 rounded-md uppercase tracking-widest">Sinkronisasi</span>
                        </div>
                        <div id="notifList" class="max-h-[350px] overflow-y-auto no-scrollbar bg-white/50">
                            <div class="py-12 text-center flex flex-col items-center">
                                <i class="fas fa-circle-notch fa-spin text-teal-500 text-2xl mb-3"></i>
                                <p class="text-[11px] font-semibold text-slate-400">Memuat data...</p>
                            </div>
                        </div>
                        <div class="p-4 border-t border-slate-50 bg-slate-50/80 text-center">
                            <a href="{{ route('user.notifikasi.index') }}" class="text-[10px] font-black text-teal-600 uppercase tracking-[0.15em] hover:text-teal-800 transition-colors">Lihat Semua Notifikasi</a>
                        </div>
                    </div>
                </div>

                {{-- Desktop Profile --}}
                <div class="hidden md:flex items-center gap-3 pl-4 border-l border-slate-200">
                    <div class="text-right">
                        <p class="text-[12px] font-black text-slate-800 leading-none">{{ Auth::user()->name }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-[14px] bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-100">
                        <i class="fas fa-user text-[15px]"></i>
                    </div>
                </div>
            </div>
        </header>

        {{-- CONTENT AREA --}}
        <main class="flex-1 overflow-y-auto p-4 md:p-8 mobile-padding-bottom no-scrollbar bg-transparent relative z-10">
            @yield('content')
        </main>

        {{-- 🔥 DOCK BAWAH (Fitur Diupdate, Spasi Pas) 🔥 --}}
        {{-- Jarak bottom diturunkan ke 4 (16px), cukup untuk terlihat melayang --}}
        <nav class="md:hidden fixed bottom-4 left-4 right-4 z-[60] glass-bottom-nav px-3 py-2 rounded-[28px] flex items-center justify-between h-[70px]">
            
            <a href="{{ route('user.dashboard') }}" class="flex flex-col items-center gap-1 w-[20%] transition-colors duration-300 {{ Request::is('user/dashboard*') ? 'text-teal-600' : 'text-slate-400' }}">
                <div class="{{ Request::is('user/dashboard*') ? 'bg-teal-50/80 px-3 py-1 rounded-full' : '' }} transition-colors">
                    <i class="fas fa-home text-[17px]"></i>
                </div>
                <span class="text-[8.5px] font-bold uppercase tracking-wide">Home</span>
            </a>
            
            <a href="{{ route('user.jadwal.index') }}" class="flex flex-col items-center gap-1 w-[20%] transition-colors duration-300 {{ Request::is('user/jadwal*') ? 'text-teal-600' : 'text-slate-400' }}">
                <div class="{{ Request::is('user/jadwal*') ? 'bg-teal-50/80 px-3 py-1 rounded-full' : '' }} transition-colors">
                    <i class="far fa-calendar-alt text-[17px]"></i>
                </div>
                <span class="text-[8.5px] font-bold uppercase tracking-wide">Jadwal</span>
            </a>

            {{-- TOMBOL UTAMA BARU: PANTAU DATA (Menggantikan Konseling) --}}
            <div class="relative -top-5 flex flex-col items-center w-[20%] z-50">
                <a href="{{ route('user.monitoring.index') }}" class="w-[54px] h-[54px] bg-gradient-to-tr from-teal-600 to-emerald-400 rounded-full flex items-center justify-center text-white shadow-[0_8px_20px_rgba(13,148,136,0.35)] border-[3px] border-white active:scale-95 transition-transform duration-300">
                    <i class="fas fa-chart-line text-[20px]"></i>
                </a>
                <span class="text-[8.5px] font-bold uppercase tracking-wide text-teal-700 mt-1 absolute -bottom-4">Pantau</span>
            </div>

            <a href="{{ route('user.riwayat.index') }}" class="flex flex-col items-center gap-1 w-[20%] transition-colors duration-300 {{ Request::is('user/riwayat*') ? 'text-teal-600' : 'text-slate-400' }}">
                <div class="{{ Request::is('user/riwayat*') ? 'bg-teal-50/80 px-3 py-1 rounded-full' : '' }} transition-colors">
                    <i class="fas fa-history text-[17px]"></i>
                </div>
                <span class="text-[8.5px] font-bold uppercase tracking-wide">Riwayat</span>
            </a>

            <a href="{{ route('user.profile.edit') }}" class="flex flex-col items-center gap-1 w-[20%] transition-colors duration-300 {{ Request::is('user/profile*') ? 'text-teal-600' : 'text-slate-400' }}">
                <div class="{{ Request::is('user/profile*') ? 'bg-teal-50/80 px-3 py-1 rounded-full' : '' }} transition-colors">
                    <i class="far fa-user-circle text-[18px]"></i>
                </div>
                <span class="text-[8.5px] font-bold uppercase tracking-wide">Profil</span>
            </a>
            
        </nav>
    </div>

    <script>
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isClosed = sb.classList.contains('-translate-x-full');

            if(isClosed) {
                sb.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            } else {
                sb.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 500);
            }
        }

        function toggleNotif() {
            const dd = document.getElementById('notifDropdown');
            dd.classList.toggle('opacity-0');
            dd.classList.toggle('invisible');
            dd.classList.toggle('translate-y-4');
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#notifArea')) {
                document.getElementById('notifDropdown').classList.add('opacity-0', 'invisible', 'translate-y-4');
            }
        });

        window.NexusToast = (title, body, iconHtml = '') => {
            Swal.fire({
                html: `
                    <div class="flex items-center gap-4 text-left p-1">
                        <div class="w-12 h-12 rounded-[16px] bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100 shadow-sm">
                            ${iconHtml || '<i class="fas fa-bell text-[16px] animate-bounce"></i>'}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-black text-slate-800 font-poppins leading-tight truncate">${title}</p>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5 line-clamp-2 leading-relaxed">${body}</p>
                        </div>
                    </div>
                `,
                position: 'top-end', 
                showConfirmButton: false, 
                timer: 4500, 
                timerProgressBar: true,
                showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' },
                customClass: { 
                    popup: 'rounded-[24px] border border-slate-100/50 shadow-[0_20px_50px_-10px_rgba(0,0,0,0.12)] !w-auto min-w-[320px] max-w-[90vw] mt-4 mr-4 md:mt-6 md:mr-6 !bg-white/90 !backdrop-blur-xl' 
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let currentCount = -1;
            const badge = document.getElementById('notifBadge');
            const pulse = document.getElementById('notifBadgePulse');
            const list = document.getElementById('notifList');
            const status = document.getElementById('notifStatus');

            function syncNotif() {
                fetch("{{ route('user.notifikasi.fetch') ?? '#' }}", {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if(data.unreadCount > 0) {
                        badge.classList.remove('hidden');
                        pulse.classList.remove('hidden');
                        status.innerText = data.unreadCount + ' BARU';
                        status.className = "text-[9px] font-black bg-rose-50 text-rose-600 px-2.5 py-1 rounded-md uppercase tracking-widest";
                    } else {
                        badge.classList.add('hidden');
                        pulse.classList.add('hidden');
                        status.innerText = 'TERBACA';
                        status.className = "text-[9px] font-black bg-slate-100 text-slate-500 px-2.5 py-1 rounded-md uppercase tracking-widest";
                    }

                    if(data.html) list.innerHTML = data.html;
                    
                    if(currentCount !== -1 && data.unreadCount > currentCount) {
                        window.NexusToast(data.latest_title || 'Notifikasi Baru', data.latest_body || 'Ada pembaruan informasi.');
                    }
                    currentCount = data.unreadCount;
                }).catch(e => console.log('Sync polling paused.'));
            }
            
            syncNotif();
            setInterval(syncNotif, 20000);

            @if(session('success'))
                window.NexusToast('Berhasil', "{{ session('success') }}", '<i class="fas fa-check-circle text-[16px] text-teal-600"></i>');
            @endif
            @if(session('error'))
                window.NexusToast('Perhatian', "{{ session('error') }}", '<i class="fas fa-exclamation-triangle text-[16px] text-rose-600"></i>');
            @endif
        });
    </script>
    
    @stack('scripts')
</body>
</html>