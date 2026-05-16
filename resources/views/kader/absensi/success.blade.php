@extends('layouts.kader')
@section('title', 'Presensi Berhasil')
@section('page-name', 'Status Sistem')

@push('styles')
<style>
    /* ====================================================================
       NEXUS 144FPS ENGINE (SUCCESS STATE)
       ==================================================================== */
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; will-change: transform, opacity; }
    @keyframes slideUpFade { from { opacity: 0; transform: translate3d(0, 40px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }

    /* Desain Kartu Kaca (Hardware Accelerated) */
    .nexus-success-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 30px 60px -15px rgba(16, 185, 129, 0.15), inset 0 1px 0 rgba(255, 255, 255, 1);
        border-radius: 40px;
        transform: translate3d(0,0,0);
    }

    /* ====================================================================
       ANIMASI IKON SUKSES NATIVE CSS (ZERO LAG)
       ==================================================================== */
    .nexus-check-wrapper {
        position: relative; width: 140px; height: 140px; margin: 0 auto 1.5rem auto;
        display: flex; align-items: center; justify-content: center;
    }
    .nexus-check-bg-1 {
        position: absolute; inset: 0; background: rgba(16, 185, 129, 0.15); border-radius: 50%;
        animation: pulseRing 3s cubic-bezier(0.2, 0.8, 0.2, 1) infinite;
        will-change: transform, opacity;
    }
    .nexus-check-bg-2 {
        position: absolute; inset: 18px; background: rgba(16, 185, 129, 0.25); border-radius: 50%;
        animation: pulseRing 2.5s cubic-bezier(0.2, 0.8, 0.2, 1) infinite reverse;
        will-change: transform, opacity;
    }
    .nexus-check-core {
        position: relative; z-index: 10; width: 80px; height: 80px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.6);
        transform: scale(0); animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards 0.2s;
        will-change: transform;
    }
    .nexus-check-icon {
        color: white; font-size: 2.2rem; opacity: 0; transform: translateY(10px);
        animation: slideUpFade 0.4s ease forwards 0.5s;
    }

    @keyframes pulseRing {
        0% { transform: scale(0.85); opacity: 0.4; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(0.85); opacity: 0.4; }
    }
    @keyframes popIn {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }

    /* Efek Gradient pada Teks */
    .gradient-text {
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Tombol Utama (Aksi Lanjut) */
    .btn-nexus-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), inset 0 2px 0 rgba(255, 255, 255, 0.2);
        transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1); transform: translate3d(0,0,0);
        will-change: transform, box-shadow;
    }
    .btn-nexus-primary:hover {
        transform: translate3d(0, -3px, 0); box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.5), inset 0 2px 0 rgba(255, 255, 255, 0.2);
    }

    /* Tombol Sekunder */
    .btn-nexus-secondary {
        background: #ffffff; color: #475569; border: 2px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1); transform: translate3d(0,0,0);
    }
    .btn-nexus-secondary:hover {
        border-color: #cbd5e1; background: #f8fafc; color: #0f172a;
        transform: translate3d(0, -2px, 0); box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
<div class="relative max-w-4xl mx-auto animate-slide-up pb-10 flex flex-col items-center justify-center min-h-[75vh] z-10">
    
    {{-- AURA BACKGROUND BEBAS LAG (GPU Layer) --}}
    <div class="fixed w-[400px] h-[400px] bg-emerald-400/10 rounded-full blur-[100px] top-0 left-1/4 transform -translate-x-1/2 -translate-y-1/4 pointer-events-none -z-10 layer-gpu"></div>
    <div class="fixed w-[300px] h-[300px] bg-indigo-400/10 rounded-full blur-[100px] bottom-0 right-1/4 transform translate-x-1/4 translate-y-1/4 pointer-events-none -z-10 layer-gpu"></div>

    {{-- KARTU UTAMA GLASSMORPHISM --}}
    <div class="nexus-success-card p-10 md:p-16 w-full text-center relative overflow-hidden layer-gpu">
        
        {{-- ANIMASI IKON SUKSES MURNI CSS (Pengganti Piala) --}}
        <div class="nexus-check-wrapper">
            <div class="nexus-check-bg-1"></div>
            <div class="nexus-check-bg-2"></div>
            <div class="nexus-check-core">
                <i class="fas fa-check nexus-check-icon"></i>
            </div>
        </div>

        <div class="relative z-10">
            {{-- Lencana Status --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 text-[10px] font-black uppercase tracking-widest mb-4 shadow-sm">
                <i class="fas fa-shield-check"></i> Sinkronisasi Sistem Berhasil
            </div>

            <h1 class="text-3xl md:text-[38px] font-black gradient-text tracking-tight font-poppins mb-4">
                Presensi Selesai Disimpan!
            </h1>
            
            <p class="text-slate-500 font-medium text-[14px] leading-relaxed max-w-lg mx-auto mb-10">
                Luar biasa! Data kehadiran sesi Posyandu hari ini telah terkunci dan tersinkronisasi secara otomatis ke dalam antrian <span class="font-bold text-indigo-600">KaderCare</span>.
            </p>

            {{-- TOMBOL AKSI --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('kader.absensi.index') }}" class="btn-nexus-secondary w-full sm:w-auto px-8 py-4 text-[12px] font-black uppercase tracking-widest rounded-[16px] flex items-center justify-center gap-2">
                    <i class="fas fa-redo-alt text-slate-400"></i> Absen Kategori Lain
                </a>
                
                <a href="{{ route('kader.absensi.riwayat') }}" class="btn-nexus-primary w-full sm:w-auto px-10 py-4 text-white text-[12px] font-black uppercase tracking-widest rounded-[16px] flex items-center justify-center gap-2">
                    <i class="fas fa-list-ul"></i> Lihat Arsip Kehadiran
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- Library Canvas Confetti untuk Efek Kembang Api (Ini dipertahankan karena sangat ringan) --}}
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Optimasi Confetti via requestAnimationFrame
        requestAnimationFrame(() => {
            var duration = 2.5 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 100 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                var particleCount = 50 * (timeLeft / duration);
                
                // Konfeti menyembur dari dua sisi (kiri dan kanan)
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } 
                }));
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } 
                }));
            }, 250);
        });
    });
</script>
@endpush
@endsection