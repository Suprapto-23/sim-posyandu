@php
    $p = $data->pemeriksaan_terakhir;
    $nilai = fn ($v) => filled($v) ? $v : '-';

    $hb = (float) ($p->hb ?? 0);
    $hbRendah = $hb > 0 && $hb < 12;
@endphp

<article class="wg-card h-full flex flex-col overflow-hidden">
    <div class="p-5 sm:p-6 flex-1 flex flex-col gap-5">

        {{-- Identitas --}}
        <div class="flex items-start gap-4 pb-5" style="border-bottom: 1px solid var(--line);">
            <div class="wg-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="wg-chip">{{ $data->kategori_sop ?? 'Remaja' }}</span>
                </div>
                <h2 class="wg-title truncate">{{ $data->nama_lengkap }}</h2>
                <p class="wg-sub mt-1 truncate">
                    {{ $data->usia_tahun }} tahun
                    <span aria-hidden="true">&nbsp;|&nbsp;</span>{{ $data->sekolah ?? 'Sekolah belum diisi' }}
                </p>
            </div>
        </div>

        @if($p)
            <section>
                <h3 class="wg-label mb-3">Hasil pengukuran terakhir</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-weight"></i> Berat</p>
                        <p class="wg-tile__value">{{ $nilai($p->berat_badan) }} <span class="wg-tile__unit">kg</span></p>
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-ruler-vertical"></i> Tinggi</p>
                        <p class="wg-tile__value">{{ $nilai($p->tinggi_badan) }} <span class="wg-tile__unit">cm</span></p>
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-chart-simple"></i> IMT</p>
                        <p class="wg-tile__value">{{ $nilai($p->imt) }}</p>
                    </div>
                    <div class="wg-tile {{ $hbRendah ? 'wg-tile--alert' : '' }}">
                        <p class="wg-tile__label"><i class="fas fa-droplet"></i> Hemoglobin</p>
                        <p class="wg-tile__value">{{ $nilai($p->hb) }} @if($hb > 0)<span class="wg-tile__unit">g/dL</span>@endif</p>
                        @if($hbRendah)
                            <p class="text-xs font-bold mt-1" style="color: #be123c;">Di bawah 12, tanyakan ke Bidan</p>
                        @endif
                    </div>
                </div>
            </section>
        @else
            <div class="wg-empty flex-1">
                <span class="wg-icon wg-icon--soft"><i class="fas fa-user-clock"></i></span>
                <div>
                    <h4>Belum ada data pemeriksaan</h4>
                    <p>Rekam medis remaja belum tercatat. Datang ke Posyandu Remaja untuk pengecekan rutin.</p>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-auto pt-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="border-top: 1px solid var(--line);">
            <div class="min-w-0">
                <p class="wg-label">Pembaruan terakhir</p>
                <p class="text-sm font-bold truncate" style="color: var(--ink);">
                    {{ $p ? \Carbon\Carbon::parse($p->tanggal_periksa)->translatedFormat('d F Y') : 'Belum tersedia' }}
                </p>
            </div>
            <a href="{{ route('user.remaja.show', $data->id) }}" class="wg-btn wg-btn--primary">
                Buka rekam medis remaja <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</article>