@php
    $p = $data->pemeriksaan_terakhir;
    $nilai = fn ($v) => filled($v) ? $v : '-';

    $tensi = $p->tekanan_darah ?? '-';
    $sistolik = (int) explode('/', (string) $tensi)[0];
    $tensiTinggi = $sistolik >= 140;

    $tanggalPeriksa = $p ? ($p->tanggal_periksa ?? $p->created_at) : null;
@endphp

<article class="wg-card h-full flex flex-col overflow-hidden">
    <div class="p-5 sm:p-6 flex-1 flex flex-col gap-5">

        {{-- Identitas --}}
        <div class="flex items-start gap-4 pb-5" style="border-bottom: 1px solid var(--line);">
            <div class="wg-icon"><i class="fas fa-person-cane"></i></div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="wg-chip">{{ $data->kategori_sop ?? 'Lansia' }}</span>
                </div>
                <h2 class="wg-title truncate">{{ $data->nama_lengkap }}</h2>
                <p class="wg-sub mt-1 truncate">
                    {{ $data->usia_tahun }} tahun
                    <span aria-hidden="true">&nbsp;|&nbsp;</span>NIK {{ $data->nik ?? '-' }}
                </p>
            </div>
        </div>

        @if($p)
            <section>
                <h3 class="wg-label mb-3">Hasil pemeriksaan terakhir</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="wg-tile {{ $tensiTinggi ? 'wg-tile--alert' : '' }}">
                        <p class="wg-tile__label"><i class="fas fa-heart-pulse"></i> Tensi</p>
                        <p class="wg-tile__value">{{ $tensi }}</p>
                        @if($tensiTinggi)
                            <p class="text-xs font-bold mt-1" style="color: #be123c;">Tinggi, tanyakan ke Bidan</p>
                        @endif
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-weight"></i> Berat</p>
                        <p class="wg-tile__value">{{ $nilai($p->berat_badan) }} <span class="wg-tile__unit">kg</span></p>
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-chart-simple"></i> Status IMT</p>
                        <p class="wg-tile__value" style="font-size: 16px;">{{ $data->status_imt ?? 'Belum dinilai' }}</p>
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-person-walking"></i> Kemandirian</p>
                        <p class="wg-tile__value" style="font-size: 16px;">{{ $p->kemandirian ?? 'Belum dicatat' }}</p>
                    </div>
                </div>
            </section>

            <section class="flex items-start gap-3 p-4" style="border-radius: var(--r-md); background: #f7fbf9; border: 1px solid var(--line);">
                <span class="wg-icon wg-icon--soft"><i class="fas fa-notes-medical"></i></span>
                <div class="min-w-0">
                    <p class="wg-label">Catatan kesehatan atau penyakit bawaan</p>
                    <p class="text-sm font-semibold mt-1" style="color: var(--ink);">{{ $data->info_penyakit ?: 'Tidak ada catatan penyakit bawaan.' }}</p>
                </div>
            </section>
        @else
            <div class="wg-empty flex-1">
                <span class="wg-icon wg-icon--soft"><i class="fas fa-stethoscope"></i></span>
                <div>
                    <h4>Belum ada pemeriksaan</h4>
                    <p>Data kesehatan lansia belum tercatat. Datang rutin ke Posyandu Lansia agar tensi dan berat badan terpantau.</p>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-auto pt-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="border-top: 1px solid var(--line);">
            <div class="min-w-0">
                <p class="wg-label">Pemeriksaan terakhir</p>
                <p class="text-sm font-bold truncate" style="color: var(--ink);">
                    {{ $tanggalPeriksa ? \Carbon\Carbon::parse($tanggalPeriksa)->translatedFormat('d F Y') : 'Belum tersedia' }}
                </p>
            </div>
            <a href="{{ route('user.riwayat.index') }}" class="wg-btn wg-btn--primary">
                Lihat rekam medis <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</article>