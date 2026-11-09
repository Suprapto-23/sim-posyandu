@php
    $p = $data->pemeriksaan_terakhir;
    $umur = \Carbon\Carbon::parse($data->tanggal_lahir)->diff(now());
    $umurText = $umur->y > 0 ? $umur->y . ' thn ' . $umur->m . ' bln' : $umur->m . ' bln';
    $lakiLaki = $data->jenis_kelamin == 'L';

    $nilai = fn ($v) => filled($v) ? $v : '-';

    // Warna status mengikuti isi teksnya. Jika belum ada data, tampil netral ("Belum dinilai"),
    // bukan berwarna merah atau hijau seolah-olah sudah dinilai.
    $tone = function ($text, array $buruk, array $waspada, array $baik) {
        $t = strtolower(trim((string) $text));
        if ($t === '') return 'none';
        foreach ($buruk as $w) { if (str_contains($t, $w)) return 'bad'; }
        foreach ($waspada as $w) { if (str_contains($t, $w)) return 'warn'; }
        foreach ($baik as $w) { if (str_contains($t, $w)) return 'ok'; }
        return 'none';
    };

    $bbuText = $p->status_bbu ?? $p->status_gizi ?? null;
    $statusItems = $p ? [
        ['label' => 'Berat menurut umur',  'text' => $bbuText,         'tone' => $tone($bbuText, ['sangat', 'buruk'], ['kurang', 'risiko', 'lebih'], ['baik', 'normal'])],
        ['label' => 'Tinggi menurut umur', 'text' => $p->status_tbu ?? null,  'tone' => $tone($p->status_tbu ?? null, ['sangat', 'pendek', 'stunting'], [], ['normal', 'baik', 'tinggi'])],
        ['label' => 'Berat menurut tinggi', 'text' => $p->status_bbtb ?? null, 'tone' => $tone($p->status_bbtb ?? null, ['sangat', 'buruk', 'wasting', 'obes'], ['kurus', 'kurang', 'gemuk', 'lebih', 'risiko'], ['normal', 'baik'])],
    ] : [];

    $vitaminA = $data->vitamin_a ?? true;
    $obatCacing = $data->obat_cacing ?? true;
@endphp

<article class="wg-card h-full flex flex-col overflow-hidden">
    <div class="p-5 sm:p-6 flex-1 flex flex-col gap-5">

        {{-- Identitas --}}
        <div class="flex items-start gap-4 pb-5" style="border-bottom: 1px solid var(--line);">
            <div class="wg-icon"><i class="fas fa-baby"></i></div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="wg-chip">{{ $data->kode_balita ?? 'Anak' }}</span>
                    <span class="wg-chip wg-chip--muted">{{ $umurText }}</span>
                </div>
                <h2 class="wg-title truncate">{{ $data->nama_lengkap }}</h2>
                <p class="wg-sub mt-1 truncate">
                    <i class="fas {{ $lakiLaki ? 'fa-mars' : 'fa-venus' }}" style="color: var(--g-600);"></i>
                    {{ $lakiLaki ? 'Laki-laki' : 'Perempuan' }}
                    <span aria-hidden="true">&nbsp;|&nbsp;</span>NIK {{ $data->nik ?? '-' }}
                </p>
            </div>
        </div>

        @if($p)
            {{-- Hasil pengukuran --}}
            <section>
                <h3 class="wg-label mb-3">Hasil pengukuran terakhir</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-weight"></i> Berat</p>
                        <p class="wg-tile__value">{{ $nilai($p->berat_badan) }} <span class="wg-tile__unit">kg</span></p>
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-ruler-vertical"></i> Tinggi</p>
                        <p class="wg-tile__value">{{ $nilai($p->tinggi_badan) }} <span class="wg-tile__unit">cm</span></p>
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-brain"></i> Lingkar kepala</p>
                        <p class="wg-tile__value">{{ $nilai($p->lingkar_kepala) }} <span class="wg-tile__unit">cm</span></p>
                    </div>
                    <div class="wg-tile">
                        <p class="wg-tile__label"><i class="fas fa-tape"></i> Lingkar lengan</p>
                        <p class="wg-tile__value">{{ $nilai($p->lila) }} <span class="wg-tile__unit">cm</span></p>
                    </div>
                </div>
            </section>

            {{-- Status gizi --}}
            <section>
                <h3 class="wg-label mb-3">Status gizi (standar WHO)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach($statusItems as $s)
                        <div class="wg-status wg-status--{{ $s['tone'] }}">
                            <p class="wg-status__label">{{ $s['label'] }}</p>
                            <p class="wg-status__value">{{ filled($s['text']) ? $s['text'] : 'Belum dinilai' }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Vitamin dan obat cacing --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-5" style="border-top: 1px solid var(--line);">
                <div class="flex items-center gap-3">
                    <span class="wg-icon wg-icon--soft"><i class="fas fa-capsules"></i></span>
                    <div class="min-w-0">
                        <p class="wg-label">Kapsul vitamin A</p>
                        <p class="text-sm font-bold" style="color: {{ $vitaminA ? 'var(--g-700)' : '#64748b' }};">{{ $vitaminA ? 'Sudah diberikan' : 'Belum diberikan' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="wg-icon wg-icon--soft"><i class="fas fa-pills"></i></span>
                    <div class="min-w-0">
                        <p class="wg-label">Obat cacing</p>
                        <p class="text-sm font-bold" style="color: {{ $obatCacing ? 'var(--g-700)' : '#64748b' }};">{{ $obatCacing ? 'Sudah diberikan' : 'Belum diberikan' }}</p>
                    </div>
                </div>
            </section>
        @else
            <div class="wg-empty flex-1">
                <span class="wg-icon wg-icon--soft"><i class="fas fa-clipboard-list"></i></span>
                <div>
                    <h4>Belum ada hasil pemeriksaan</h4>
                    <p>Hasil timbang dan ukur akan muncul di sini setelah anak diperiksa di Posyandu dan datanya diverifikasi Bidan.</p>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-auto pt-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="border-top: 1px solid var(--line);">
            <div class="min-w-0">
                <p class="wg-label">Pemeriksaan terakhir</p>
                <p class="text-sm font-bold truncate" style="color: var(--ink);">
                    {{ $p ? \Carbon\Carbon::parse($p->tanggal_periksa)->translatedFormat('d M Y') : 'Belum ada sesi' }}
                </p>
            </div>
            <a href="{{ route('user.balita.show', $data->id) }}" class="wg-btn wg-btn--primary">
                Buka Buku KIA digital <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</article>