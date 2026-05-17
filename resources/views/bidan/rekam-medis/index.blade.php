@extends('layouts.bidan')

@section('title', 'Pusat Rekam Medis (EMR)')
@section('page-name', 'Dashboard EMR')

@push('styles')
<style>
    /* ANIMASI MASUK HALUS */
    .fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* NEXUS HORIZONTAL DIRECTORY CARD */
    .dir-card {
        background: #ffffff; border-radius: 24px; border: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative; overflow: hidden;
    }
    .dir-card:hover {
        transform: translateX(6px); border-color: #bae6fd;
        box-shadow: 0 15px 35px -10px rgba(6, 182, 212, 0.15);
        background: linear-gradient(to right, #ffffff, #f0f9ff);
    }

    /* IKON KONTANER */
    .icon-box { transition: all 0.4s ease; }
    .dir-card:hover .icon-box { transform: scale(1.08) rotate(-3deg); }

    /* TOMBOL NAVIGASI */
    .nav-btn { transition: all 0.3s ease; }
    .dir-card:hover .nav-btn { background-color: #0ea5e9; color: #ffffff; border-color: #0ea5e9; box-shadow: 0 6px 15px rgba(14, 165, 233, 0.25); transform: translateX(3px); }
</style>
@endpush

@section('content')

{{-- =================================================================
     LOADER SISTEM NEXUS (Diperbarui agar kebal z-index bentrok)
     ================================================================= --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center transition-all duration-300 opacity-0 pointer-events-none">
    <div class="relative w-16 h-16 flex items-center justify-center mb-5">
        <div class="absolute inset-0 border-4 border-cyan-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-cyan-600 rounded-full border-t-transparent animate-spin"></div>
        <i class="fas fa-folder-open text-cyan-600 text-xl animate-pulse"></i>
    </div>
    <div class="bg-white px-5 py-2.5 rounded-full shadow-lg border border-slate-100 flex items-center gap-3">
        <div class="w-2 h-2 rounded-full bg-cyan-500 animate-ping"></div>
        <p class="text-[10.5px] font-black text-cyan-700 uppercase tracking-[0.2em] font-poppins" id="loaderText">MEMBUKA DIREKTORI...</p>
    </div>
</div>

<div class="max-w-[1000px] mx-auto fade-in-up pb-16">

    {{-- =================================================================
         1. COMPACT HERO HEADER (Sleek & Professional)
         ================================================================= --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 bg-white p-8 rounded-[32px] border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-cyan-50 rounded-full blur-3xl opacity-60 -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        
        <div class="flex items-center gap-6 relative z-10">
            <div class="w-16 h-16 rounded-[20px] bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center text-2xl shadow-[0_8px_20px_rgba(6,182,212,0.3)] shrink-0 border border-cyan-400">
                <i class="fas fa-server"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3 mb-1.5">
                    <h1 class="text-[22px] md:text-[24px] font-black text-slate-800 tracking-tight font-poppins leading-none">Direktori EMR</h1>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-md border border-emerald-100 flex items-center gap-1.5 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Sinkron
                    </span>
                </div>
                <p class="text-[13px] font-medium text-slate-500 leading-relaxed">Pilih direktori kluster pasien untuk melakukan peninjauan rekam data klinis warga.</p>
            </div>
        </div>
    </div>

    {{-- =================================================================
         2. DIRECTORY LIST (Presisi Horizontal Flexbox)
         ================================================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
        
        {{-- KARTU 1: BAYI & BALITA --}}
        <a href="{{ route('bidan.pasien.balita') }}" class="smooth-route block group">
            <div class="dir-card p-6 flex items-center gap-5">
                <div class="icon-box w-14 h-14 rounded-[16px] bg-sky-50 text-sky-500 border border-sky-100 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-baby"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-[16px] font-black text-slate-800 font-poppins truncate mb-1">Bayi & Balita</h3>
                    <p class="text-[12px] font-medium text-slate-500 truncate">Pertumbuhan, Stunting & Imunisasi</p>
                </div>
                <div class="nav-btn w-10 h-10 rounded-[14px] bg-slate-50 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </a>

        {{-- KARTU 2: IBU HAMIL --}}
        <a href="{{ route('bidan.pasien.ibu_hamil') }}" class="smooth-route block group">
            <div class="dir-card p-6 flex items-center gap-5">
                <div class="icon-box w-14 h-14 rounded-[16px] bg-pink-50 text-pink-500 border border-pink-100 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-female"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-[16px] font-black text-slate-800 font-poppins truncate mb-1">Ibu Hamil</h3>
                    <p class="text-[12px] font-medium text-slate-500 truncate">Manajemen ANC & Deteksi Risiko</p>
                </div>
                <div class="nav-btn w-10 h-10 rounded-[14px] bg-slate-50 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </a>

        {{-- KARTU 3: REMAJA --}}
        <a href="{{ route('bidan.pasien.remaja') }}" class="smooth-route block group">
            <div class="dir-card p-6 flex items-center gap-5">
                <div class="icon-box w-14 h-14 rounded-[16px] bg-indigo-50 text-indigo-500 border border-indigo-100 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-[16px] font-black text-slate-800 font-poppins truncate mb-1">Remaja</h3>
                    <p class="text-[12px] font-medium text-slate-500 truncate">Pemeriksaan Anemia & Edukasi</p>
                </div>
                <div class="nav-btn w-10 h-10 rounded-[14px] bg-slate-50 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </a>

        {{-- KARTU 4: LANSIA --}}
        <a href="{{ route('bidan.pasien.lansia') }}" class="smooth-route block group">
            <div class="dir-card p-6 flex items-center gap-5">
                <div class="icon-box w-14 h-14 rounded-[16px] bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-wheelchair"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-[16px] font-black text-slate-800 font-poppins truncate mb-1">Geriatri (Lansia)</h3>
                    <p class="text-[12px] font-medium text-slate-500 truncate">Pemantauan Hipertensi & PTM</p>
                </div>
                <div class="nav-btn w-10 h-10 rounded-[14px] bg-slate-50 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </a>

    </div>

    {{-- 3. INFORMASI SISTEM MINI --}}
    <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-3 text-[11px] font-bold text-slate-400 px-4 text-center">
        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0"><i class="fas fa-lock text-slate-300"></i></div>
        Terenkripsi & Terintegrasi dengan Sistem Keamanan Standar EMR Posyandu
    </div>

</div>
@endsection

@push('scripts')
<script>
    const showLoader = () => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            loader.style.display = 'flex';
            // Paksa browser membaca DOM ulang agar animasi transisi berjalan
            void loader.offsetWidth; 
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }
    };
    
    // Aktifkan loader saat mengklik kartu direktori
    document.querySelectorAll('.smooth-route').forEach(link => {
        link.addEventListener('click', function(e) {
            // Abaikan jika user menekan Ctrl+Click (membuka tab baru)
            if(this.target !== '_blank' && !e.ctrlKey && !e.metaKey) {
                showLoader();
            }
        });
    });

    // Sembunyikan loader jika pengguna menekan tombol "Back" di browser
    window.addEventListener('pageshow', (event) => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0');
            // Menunggu transisi opacity selesai sebelum mematikan interaksi
            setTimeout(() => {
                loader.classList.add('pointer-events-none');
                loader.style.display = 'none';
            }, 300);
        }
    });
</script>
@endpush