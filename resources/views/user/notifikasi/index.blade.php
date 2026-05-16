@extends('layouts.user')

@section('title', 'Kotak Masuk')
@section('page_title', 'Kotak Masuk')

@section('content')
<div class="font-jakarta pb-12 max-w-5xl mx-auto">
    
    {{-- Header Section (Streamlined Premium) --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 animate-fade-in-down">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 bg-teal-50/80 rounded-full border border-teal-100/50 mb-2 shadow-sm">
                <i class="fas fa-inbox text-teal-600 text-xs animate-bounce-slow"></i>
                <span class="text-[10px] font-bold tracking-widest uppercase text-teal-700">Pusat Informasi</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight font-poppins">
                Kotak Masuk Bidan
            </h1>
            <p class="text-[13px] font-medium text-slate-500 max-w-2xl leading-relaxed">
                Semua pesan, pengingat jadwal, dan informasi kesehatan dari Bidan serta Kader akan muncul di sini secara real-time.
            </p>
        </div>

        @if(isset($unreadCount) && $unreadCount > 0)
            <form action="{{ route('user.notifikasi.markall') }}" method="POST" id="formMarkAll" class="m-0 shrink-0">
                @csrf
                <button type="button" onclick="confirmMarkAll()" class="w-full md:w-auto px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm group">
                    <i class="fas fa-check-double text-teal-500 group-hover:scale-110 transition-transform"></i>
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    {{-- Filter Tab Lembut & Elegan --}}
    <div class="flex items-center gap-2 mb-8 overflow-x-auto pb-2 no-scrollbar border-b border-slate-100 animate-fade-in">
        <a href="{{ route('user.notifikasi.index', ['filter' => 'semua']) }}" 
           class="px-5 py-3 text-xs font-bold tracking-wide transition-all duration-300 whitespace-nowrap flex items-center gap-2 relative {{ ($filter ?? 'semua') == 'semua' ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }}">
            Semua Pesan 
            <span class="px-2 py-0.5 rounded-md text-[10px] {{ ($filter ?? 'semua') == 'semua' ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-500' }} transition-colors">{{ $allCount ?? 0 }}</span>
            @if(($filter ?? 'semua') == 'semua')
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 rounded-t-full shadow-[0_-2px_8px_rgba(20,184,166,0.5)]"></span>
            @endif
        </a>
        
        <a href="{{ route('user.notifikasi.index', ['filter' => 'belum']) }}" 
           class="px-5 py-3 text-xs font-bold tracking-wide transition-all duration-300 whitespace-nowrap flex items-center gap-2 relative {{ ($filter ?? '') == 'belum' ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }}">
            Belum Dibaca
            @if(isset($unreadCount) && $unreadCount > 0)
                <span class="px-2 py-0.5 rounded-md text-[10px] {{ ($filter ?? '') == 'belum' ? 'bg-teal-500 text-white shadow-sm shadow-teal-200 animate-pulse' : 'bg-rose-100 text-rose-600' }}">{{ $unreadCount }}</span>
            @endif
            @if(($filter ?? '') == 'belum')
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 rounded-t-full shadow-[0_-2px_8px_rgba(20,184,166,0.5)]"></span>
            @endif
        </a>

        <a href="{{ route('user.notifikasi.index', ['filter' => 'sudah']) }}" 
           class="px-5 py-3 text-xs font-bold tracking-wide transition-all duration-300 whitespace-nowrap flex items-center gap-2 relative {{ ($filter ?? '') == 'sudah' ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }}">
            Sudah Dibaca
            @if(($filter ?? '') == 'sudah')
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 rounded-t-full shadow-[0_-2px_8px_rgba(20,184,166,0.5)]"></span>
            @endif
        </a>
    </div>

    {{-- List Notifikasi (Fluid Layout with Staggered Animation) --}}
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm overflow-hidden mb-12">
        @forelse($notifikasis as $index => $notif)
            @php 
                $judulLower = strtolower($notif->judul);
                $icon = 'fas fa-envelope-open-text';
                $iconBg = 'bg-slate-50 text-slate-400 border-slate-100';
                
                if (str_contains($judulLower, 'jadwal') || str_contains($judulLower, 'agenda')) {
                    $icon = 'far fa-calendar-alt';
                } elseif (str_contains($judulLower, 'imunisasi') || str_contains($judulLower, 'vaksin')) {
                    $icon = 'fas fa-syringe';
                } elseif (str_contains($judulLower, 'pemeriksaan') || str_contains($judulLower, 'hasil')) {
                    $icon = 'fas fa-stethoscope';
                }

                if (!$notif->is_read) {
                    $iconBg = 'bg-teal-50 text-teal-600 border-teal-100';
                }
            @endphp

            {{-- Tambahkan animasi staggred dengan delay index --}}
            <div class="group flex flex-col sm:flex-row sm:items-center gap-4 p-5 sm:p-6 border-b border-slate-50 hover:bg-slate-50/80 transition-all duration-300 relative animate-slide-up-fade {{ !$notif->is_read ? 'bg-teal-50/10' : 'bg-white' }}" style="animation-delay: {{ $index * 75 }}ms; animation-fill-mode: both;">
                
                @if(!$notif->is_read)
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-teal-400 to-emerald-500"></div>
                @endif

                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-3 {{ $iconBg }} border">
                    <i class="{{ $icon }} text-lg"></i>
                </div>

                <div class="flex-1 min-w-0 w-full">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                        <div class="flex items-center gap-2">
                            <h4 class="text-[14px] font-bold text-slate-800 font-poppins tracking-tight truncate">
                                {{ $notif->judul ?? 'Informasi Posyandu' }}
                            </h4>
                            @if(!$notif->is_read)
                                <span class="flex h-2 w-2 rounded-full bg-teal-500 shadow-[0_0_8px_rgba(20,184,166,0.6)] shrink-0"></span>
                            @endif
                        </div>
                        
                        <span class="text-[11px] font-semibold text-slate-400 whitespace-nowrap shrink-0 hidden sm:block">
                            {{ $notif->created_at->translatedFormat('d M Y, H:i') }}
                        </span>
                    </div>

                    <p class="text-[13px] text-slate-500 leading-relaxed line-clamp-1 group-hover:line-clamp-none transition-all duration-500 pr-4">
                        {{ $notif->pesan }}
                    </p>

                    <span class="text-[11px] font-semibold text-slate-400 whitespace-nowrap shrink-0 sm:hidden mt-2 block">
                        {{ $notif->created_at->translatedFormat('d M, H:i') }}
                    </span>
                </div>

                <div class="flex items-center gap-3 shrink-0 mt-3 sm:mt-0 sm:ml-4">
                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest hidden md:block transition-colors group-hover:text-slate-400">
                        {{ $notif->created_at->diffForHumans() }}
                    </span>

                    @if(!$notif->is_read)
                        <form action="{{ route('user.notifikasi.read', $notif->id) }}" method="POST" class="m-0 form-mark-read">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-white border border-slate-200 text-teal-600 hover:bg-teal-500 hover:text-white hover:border-teal-500 rounded-lg text-[11px] font-bold transition-all duration-300 flex items-center gap-1.5 shadow-sm whitespace-nowrap" title="Tandai dibaca">
                                <i class="fas fa-check text-[10px]"></i> Mengerti
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-20 flex flex-col items-center justify-center bg-slate-50/30 px-4 text-center animate-fade-in">
                <div class="w-16 h-16 bg-white text-slate-300 rounded-full flex items-center justify-center text-2xl mb-4 shadow-sm border border-slate-100">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="text-slate-800 font-bold font-poppins text-[15px]">Kotak Masuk Bersih</h3>
                <p class="text-slate-500 text-[13px] mt-1 max-w-sm">
                    @if(($filter ?? 'semua') == 'belum')
                        Anda sudah membaca semua pesan. Tidak ada notifikasi baru saat ini.
                    @else
                        Belum ada pesan atau pengingat jadwal dari Bidan Posyandu.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @if($notifikasis->hasPages())
        <div class="px-2 mt-8 animate-fade-in-up">
            {{ $notifikasis->links() }}
        </div>
    @endif
</div>

{{-- Styling Animasi Khusus --}}
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    .pagination { @apply flex items-center gap-1; }
    .page-item .page-link { @apply border-none bg-white rounded-lg text-xs font-bold text-slate-500 px-3.5 py-2 hover:bg-slate-50 transition-all shadow-sm border border-slate-100; }
    .page-item.active .page-link { background-color: #0d9488 !important; border-color: #0d9488 !important; color: white !important; box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2); }

    /* Keyframes Animasi Nexus */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-slide-up-fade { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
    .animate-fade-in-down { animation: fadeInDown 0.6s ease-out forwards; }
    .animate-fade-in-up { animation: slideUpFade 0.6s ease-out forwards; }
    .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
    .animate-bounce-slow { animation: bounce 3s infinite; }
</style>

{{-- SweetAlert2 Scripts --}}
@push('scripts')
<script>
    // 1. Konfirmasi "Tandai Semua Dibaca"
    function confirmMarkAll() {
        Swal.fire({
            title: 'Tandai Semua Terbaca?',
            text: "Semua pesan belum terbaca akan dimasukkan ke riwayat.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488', // Warna Teal
            cancelButtonColor: '#94a3b8',
            confirmButtonText: '<i class="fas fa-check-double mr-1"></i> Ya, Tandai!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[24px] border border-slate-100 shadow-2xl font-poppins',
                title: 'text-[18px] font-black text-slate-800',
                htmlContainer: 'text-[13px] text-slate-500 font-medium',
                confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-xs tracking-wide',
                cancelButton: 'rounded-xl font-bold px-5 py-2.5 text-xs tracking-wide bg-slate-100 text-slate-600 hover:bg-slate-200'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formMarkAll').submit();
            }
        })
    }

    // 2. SweetAlert Toast ala Nexus (Muncul saat Controller mengirim with('success'))
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                html: `
                    <div class="flex items-center gap-4 text-left">
                        <div class="w-12 h-12 rounded-[14px] bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[13px] font-black text-slate-800 font-poppins leading-tight">Berhasil!</p>
                            <p class="text-[11.5px] font-medium text-slate-500 mt-1 line-clamp-1">{{ session('success') }}</p>
                        </div>
                    </div>
                `,
                position: 'top-end',
                showConfirmButton: false, 
                timer: 4000, 
                timerProgressBar: true,
                showClass: { popup: 'animate__animated animate__fadeInRight animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOutRight animate__faster' },
                customClass: { 
                    popup: '!mt-4 sm:!mt-6 !mr-4 sm:!mr-6 !w-auto min-w-[320px] rounded-2xl border border-slate-100 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] bg-white p-2' 
                }
            });
        @endif
    });
</script>
@endpush
@endsection