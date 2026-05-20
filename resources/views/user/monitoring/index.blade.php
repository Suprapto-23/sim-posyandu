@extends('layouts.user')

@section('title', 'Pemantauan Kesehatan')

@section('content')
<div class="max-w-5xl mx-auto pb-24 px-4 md:px-8 font-poppins">
    
    {{-- 1. HEADER & LIVE SEARCH --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10 md:mb-12 animate-slide-up">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-teal-50 text-teal-600 text-[10px] font-black uppercase tracking-[0.15em] mb-4 border border-teal-100 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                </span>
                Kesehatan Terpadu
            </div>
            <h1 class="text-2xl md:text-4xl font-black text-slate-800 tracking-tight leading-tight mb-2">Pemantauan Keluarga</h1>
            <p class="text-[13px] md:text-sm font-medium text-slate-500 leading-relaxed">
                Ringkasan kondisi medis seluruh anggota keluarga Anda yang terekam secara resmi oleh Bidan Posyandu.
            </p>
        </div>

        @if($hasData)
        {{-- Live Search Input --}}
        <div class="w-full lg:w-80 relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300 group-focus-within:text-teal-500 transition-colors">
                <i class="fas fa-search"></i>
            </div>
            <input type="text" id="liveSearchPasien" placeholder="Ketik nama anggota keluarga..." 
                class="w-full bg-white border border-slate-200 text-slate-700 text-[13px] font-medium rounded-2xl focus:ring-4 focus:ring-teal-50 focus:border-teal-400 block pl-11 pr-4 py-3.5 transition-all shadow-[0_8px_20px_rgba(0,0,0,0.02)] hover:border-slate-300 outline-none" autocomplete="off">
            <div id="searchIndicator" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-teal-500 opacity-0 transition-opacity">
                <i class="fas fa-spinner fa-spin text-sm"></i>
            </div>
        </div>
        @endif
    </div>

    {{-- 2. DAFTAR PANEL (Di-render dari Components) --}}
    @if($hasData)
    <div class="space-y-6 md:space-y-8 animate-slide-up-1" id="pasienContainer">
        
        {{-- Loop Panel Balita --}}
        @foreach($balitas as $anak)
            <div class="pasien-card transition-all duration-300 transform" data-nama="{{ strtolower($anak->nama_lengkap) }}">
                {{-- SESUAIKAN PATH INI JIKA ERROR: Jika folder components ada di dalam user/, gunakan 'user.components.panel_balita' --}}
                @include('user.components.panel_balita', ['data' => $anak])
            </div>
        @endforeach

        

        {{-- Loop Panel Remaja --}}
        @foreach($remajas as $remaja)
            <div class="pasien-card transition-all duration-300 transform" data-nama="{{ strtolower($remaja->nama_lengkap) }}">
                @include('user.components.panel_remaja', ['data' => $remaja])
            </div>
        @endforeach

        {{-- Loop Panel Lansia --}}
        @foreach($lansias as $lansia)
            <div class="pasien-card transition-all duration-300 transform" data-nama="{{ strtolower($lansia->nama_lengkap) }}">
                @include('user.components.panel_lansia', ['data' => $lansia])
            </div>
        @endforeach

        {{-- Pesan pencarian tidak ditemukan (Disembunyikan via JS) --}}
        <div id="emptySearch" class="hidden py-16 md:py-24 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200">
            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fas fa-search-minus"></i>
            </div>
            <h3 class="text-lg font-black text-slate-700">Tidak Ditemukan</h3>
            <p class="text-[13px] font-medium text-slate-500 mt-1">Nama anggota keluarga tidak cocok dengan pencarian Anda.</p>
        </div>

    </div>
    @else
    {{-- 3. GLOBAL EMPTY STATE (Jika tidak ada data sama sekali) --}}
    <div class="py-20 animate-slide-up-1 text-center bg-white rounded-[3rem] border border-slate-100 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.03)] flex flex-col items-center">
        <div class="relative w-24 h-24 mb-6">
            <div class="absolute inset-0 bg-teal-100 rounded-[1rem] rotate-12 opacity-50"></div>
            <div class="absolute inset-0 bg-white border border-slate-100 rounded-[1.2rem] flex items-center justify-center text-slate-300 shadow-sm">
                <i class="fas fa-users-slash text-4xl"></i>
            </div>
        </div>
        <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">Belum Ada Rekam Medis</h3>
        <p class="text-[13px] font-medium text-slate-500 mt-2 max-w-sm mx-auto leading-relaxed">
            Data pemantauan akan muncul otomatis setelah Anda atau keluarga melakukan pemeriksaan di Posyandu.
        </p>
        <a href="{{ route('user.jadwal.index') }}" class="mt-8 px-6 py-3.5 bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-black uppercase tracking-[0.15em] rounded-xl transition-all shadow-lg shadow-teal-100 active:scale-95">
            Cek Jadwal Posyandu
        </a>
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-slide-up-1 { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; }
    @keyframes slideUpFade { 
        from { opacity: 0; transform: translateY(30px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('liveSearchPasien');
        const cards = document.querySelectorAll('.pasien-card');
        const emptyState = document.getElementById('emptySearch');
        const searchIndicator = document.getElementById('searchIndicator');

        if(searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                let hasVisibleCards = false;

                // Animasi UX loading instan
                searchIndicator.style.opacity = '1';
                
                // Gunakan setTimeout kecil agar UI tidak freeze saat mengetik
                setTimeout(() => {
                    cards.forEach(card => {
                        const nama = card.getAttribute('data-nama');
                        if(nama.includes(term)) {
                            card.style.display = 'block';
                            // Animasi masuk
                            setTimeout(() => { card.style.opacity = '1'; card.style.transform = 'scale(1)'; }, 10);
                            hasVisibleCards = true;
                        } else {
                            // Animasi keluar
                            card.style.opacity = '0'; 
                            card.style.transform = 'scale(0.95)';
                            setTimeout(() => { card.style.display = 'none'; }, 300); // Sesuaikan dengan durasi transition-all
                        }
                    });

                    // Toggle empty state
                    if(emptyState) {
                        if(hasVisibleCards || term === '') {
                            emptyState.style.display = 'none';
                        } else {
                            setTimeout(() => { emptyState.style.display = 'block'; }, 300);
                        }
                    }

                    searchIndicator.style.opacity = '0';
                }, 50); // Delay 50ms untuk feel yang lebih organik
            });
        }
    });
</script>
@endpush