@if ($paginator->hasPages())
    <div id="tableFooter" class="border-t border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-4 w-full">
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
            Halaman <span class="text-slate-900">{{ $paginator->currentPage() }}</span> dari <span class="text-slate-900">{{ $paginator->lastPage() }}</span>
        </p>
        
        <div class="flex items-center gap-2">
            {{-- Tombol Previous (<) --}}
            @if ($paginator->onFirstPage())
                <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600">
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
            @endif

            {{-- LOGIKA PEMBATASAN ANGKA (MAKSIMAL 5 HALAMAN BERURUTAN) --}}
            @php
                $window = 2; // Menampilkan 2 angka di kiri dan 2 angka di kanan halaman aktif
                $start = max(1, $paginator->currentPage() - $window);
                $end = min($paginator->lastPage(), $paginator->currentPage() + $window);

                // Penyesuaian agar jumlah tombol yang tampil tetap konsisten 5 angka jika berada di ujung awal/akhir rentang
                if ($paginator->currentPage() <= $window) {
                    $end = min($paginator->lastPage(), 1 + ($window * 2));
                }
                if ($paginator->currentPage() > $paginator->lastPage() - $window) {
                    $start = max(1, $paginator->lastPage() - ($window * 2));
                }
            @endphp

            {{-- Render Angka Hasil Kalkulasi --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $paginator->currentPage())
                    <span class="btn-pill w-10 h-10 flex items-center justify-center bg-emerald-500 text-white font-black text-sm shadow-md pointer-events-none">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-sm shadow-sm hover:bg-emerald-50 hover:text-emerald-600">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            {{-- Tombol Next (>) --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600">
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            @else
                <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            @endif
        </div>
    </div>
@endif