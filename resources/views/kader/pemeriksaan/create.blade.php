@extends('layouts.kader')

@section('title', 'Input Pengukuran Fisik')
@section('page-name', 'Input Pengukuran Fisik')

@php
    $kategori_awal = request('kategori', 'balita');
    $pasien_id_awal = request('pasien_id');
    $pasien_nama_awal = request('pasien_nama');
    $returnUrl = request('return_url', route('kader.pemeriksaan.index'));

    if (! in_array($kategori_awal, ['balita', 'remaja', 'lansia'], true)) {
        $kategori_awal = 'balita';
    }

    $kategoriMenus = [
        'balita' => ['label' => 'Balita', 'desc' => 'Tumbuh kembang anak', 'icon' => 'fa-child-reaching', 'bg' => 'bg-gradient-to-br from-emerald-500 to-teal-500'],
        'remaja' => ['label' => 'Remaja', 'desc' => 'Skrining kesehatan', 'icon' => 'fa-user-graduate', 'bg' => 'bg-gradient-to-br from-teal-500 to-cyan-500'],
        'lansia' => ['label' => 'Lansia', 'desc' => 'Pemantauan lansia', 'icon' => 'fa-person-cane', 'bg' => 'bg-gradient-to-br from-sky-500 to-blue-500'],
    ];

    $today = now('Asia/Jakarta')->toDateString();
    
    // State recovery
    $oldKategori = old('kategori_pasien', $kategori_awal);
    $oldPasienId = old('pasien_id', $pasien_id_awal);
    $oldPasienNama = old('pasien_nama_hidden', $pasien_nama_awal);
    $oldReturnUrl = old('return_url', $returnUrl);
    
    // Kunci sasaran jika ada ID dari Absensi
    $isLocked = !empty($oldPasienId) && !empty($oldPasienNama);
    
    // Edukasi: acuan nilai yang lazim per kelompok sasaran (gambaran umum; penilaian klinis tetap oleh Bidan)
    $labelKategori = [
        'balita' => 'Balita (0–5 tahun)',
        'remaja' => 'Remaja (10–18 tahun)',
        'lansia' => 'Lansia (≥ 60 tahun)',
    ];
    $panduanNilai = [
        'berat_badan' => [
            'judul' => 'Berat badan',
            'balita' => 'Rata-rata ±3,25 kg saat lahir, ±9–10 kg usia 1 tahun, dan ±18 kg usia 5 tahun.',
            'remaja' => 'Mengikuti umur dan jenis kelamin; yang dinilai adalah IMT/U (normal −2 s.d. +1 SD). Kisaran lazim ±25–100 kg.',
            'lansia' => 'Kisaran lazim ±35–90 kg; IMT normal ±18,5–24,9 kg/m².',
        ],
        'tinggi_badan' => [
            'judul' => 'Tinggi/panjang badan',
            'balita' => 'Panjang lahir ±48–52 cm, usia 1 tahun ±75 cm, usia 5 tahun ±105–110 cm. Anak di bawah 2 tahun diukur berbaring.',
            'remaja' => 'Kisaran lazim ±130–185 cm.',
            'lansia' => 'Kisaran lazim ±135–180 cm.',
        ],
        'lingkar_kepala' => [
            'judul' => 'Lingkar kepala',
            'balita' => 'Lahir ±32–37 cm, usia 1 tahun ±44–47 cm, dan ±50 cm menjelang usia 5 tahun. Pertambahan melambat setelah usia 2 tahun.',
        ],
        'lingkar_lengan' => [
            'judul' => 'Lingkar lengan atas (LiLA)',
            'balita' => 'Usia 6–59 bulan: ≥ 12,5 cm normal; 11,5–12,4 cm gizi kurang; < 11,5 cm gizi buruk.',
            'remaja' => 'Umumnya ±20–30 cm. Remaja putri dengan LiLA < 23,5 cm berisiko KEK (kurang energi kronis).',
        ],
        'lingkar_perut' => [
            'judul' => 'Lingkar perut',
            'remaja' => 'Umumnya ±50–90 cm. Sebagai pegangan, ≥ 80 cm (putri) atau ≥ 90 cm (putra) dicatat sebagai risiko obesitas sentral untuk ditinjau Bidan.',
            'lansia' => 'Umumnya ±65–110 cm. Obesitas sentral bila > 90 cm (pria) atau > 80 cm (wanita).',
        ],
        'tekanan_darah' => [
            'judul' => 'Tensi',
            'remaja' => 'Normal < 120/80 mmHg (usia ≥ 13 tahun); ≥ 140/90 mmHg tergolong tinggi. Tulis sistolik/diastolik, contoh 110/70.',
            'lansia' => 'Normal < 120/80 mmHg; 120–139/80–89 perlu diwaspadai; ≥ 140/90 mmHg tergolong hipertensi. Contoh penulisan 130/80.',
        ],
        'hemoglobin' => [
            'judul' => 'Hemoglobin',
            'remaja' => 'Usia 12–14 tahun ≥ 12 g/dL. Usia ≥ 15 tahun: putri ≥ 12 g/dL, putra ≥ 13 g/dL. Di bawah batas itu tergolong anemia.',
        ],
        'gula_darah' => [
            'judul' => 'Gula darah',
            'remaja' => 'Gula darah sewaktu umumnya < 200 mg/dL.',
            'lansia' => 'Gula darah sewaktu umumnya < 200 mg/dL; ≥ 200 mg/dL perlu dirujuk ke Bidan.',
        ],
        'kolesterol' => [
            'judul' => 'Kolesterol',
            'remaja' => 'Kolesterol total umumnya < 200 mg/dL.',
            'lansia' => 'Kolesterol total umumnya < 200 mg/dL.',
        ],
        'asam_urat' => [
            'judul' => 'Asam urat',
            'remaja' => 'Wanita ±2,4–6,0 mg/dL; pria ±3,4–7,6 mg/dL (acuan dapat berbeda antar alat).',
            'lansia' => 'Wanita ±2,4–6,0 mg/dL; pria ±3,4–7,6 mg/dL (acuan dapat berbeda antar alat).',
        ],
    ];
    $kunciPanduan = collect($errors->keys())->filter(fn ($k) => isset($panduanNilai[$k]))->values();

    // Helper untuk render awal UI
    $isBalita = $oldKategori === 'balita';
    $isRemaja = $oldKategori === 'remaja';
    $isLansia = $oldKategori === 'lansia';
@endphp

@push('styles')
<style>
    /* Palet disamakan dengan halaman index pengukuran fisik */
    body {
        background-color: #f1f5f9;
        background-image:
            radial-gradient(at 0% 0%, #d1fae5 0, transparent 50%),
            radial-gradient(at 100% 0%, #cffafe 0, transparent 50%),
            radial-gradient(at 100% 100%, #d1fae5 0, transparent 50%);
        background-attachment: fixed;
    }
    .widget-card { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    
    input[type="number"]::-webkit-inner-spin-button, input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type="number"] { -moz-appearance: textfield; }

    .input-soft { width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1.2rem; padding: 12px 16px; font-size: 13px; font-weight: 700; outline: none; transition: all .25s ease;}
    .input-soft:focus { background: #ffffff; border-color: #14b8a6; box-shadow: 0 0 0 4px rgba(20, 184, 166, .12); }
    .locked-input { background: rgba(16, 185, 129, 0.05) !important; color: #047857 !important; border-color: rgba(16, 185, 129, 0.3) !important; cursor: default !important; pointer-events: none;}
    
    /* Hero: gradien, bingkai, dan bayangan sama dengan .pk-hero di index */
    .pc-hero {
        position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 1rem;
        padding: clamp(1.5rem, 3vw, 2.5rem); border-radius: 2rem; color: #fff;
        background: linear-gradient(110deg, #14b8a6, #2dd4bf 55%, #34d399);
        border: 3px solid rgba(255, 255, 255, .5); box-shadow: 0 20px 40px -15px rgba(20, 184, 166, .45);
    }
    .pc-hero::before { content: ""; position: absolute; inset: 0; pointer-events: none; background: radial-gradient(rgba(255, 255, 255, .12) 1.5px, transparent 1.5px) 0 0 / 20px 20px; }
    .pc-hero > * { position: relative; }
    .pc-back { background: rgba(0, 0, 0, .12); border: 1px solid rgba(255, 255, 255, .35); }
    .pc-back:hover { background: rgba(0, 0, 0, .2); }
    .pc-btn-primary { background: linear-gradient(135deg, #14b8a6, #10b981); }
    .pc-btn-primary:hover { filter: brightness(1.05); }

    /* Peringatan: tone rose + kotak edukasi tone teal (token warna sama dengan index) */
    .pc-alert { display: flex; gap: .75rem; padding: 1rem 1.25rem; border-radius: 1.5rem; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; outline: none; }
    .pc-alert > i { font-size: 1.25rem; margin-top: .125rem; }
    .pc-alert strong { display: block; font-weight: 800; font-size: 14px; }
    .pc-alert p, .pc-alert li { font-size: 12px; font-weight: 600; line-height: 1.5; }
    .pc-alert ul { list-style: disc; padding-left: 1.1rem; margin-top: .5rem; }
    .pc-edu { margin-top: .875rem; padding: .875rem 1rem; border-radius: 1rem; background: #f0fdfa; border: 1px solid #99f6e4; color: #0f766e; }
    .pc-alert .pc-edu-title { font-weight: 800; }
    .pc-edu-field { margin-top: .625rem; }
    .pc-edu-field > b { display: block; color: #115e59; font-size: 12px; font-weight: 800; }
    .pc-edu-field p { margin-top: .125rem; padding: .125rem .5rem; border-radius: .5rem; font-weight: 500; }
    .pc-edu-field p.is-active { background: #ccfbf1; font-weight: 700; }
    .pc-edu-note { margin-top: .75rem; font-size: 11px !important; color: #475569; font-weight: 500 !important; }

    .input-soft.input-error { border-color: #f43f5e; background: #fff1f2; }
    .input-soft.input-error:focus { border-color: #f43f5e; box-shadow: 0 0 0 4px rgba(244, 63, 94, .15); }

    .form-label { display: block; margin-bottom: 0.5rem; font-size: 10px; font-weight: 900; text-transform: uppercase; color: #64748b; }
    .req-star { color: #f43f5e; margin-left: 2px; }
    
    .pc-modal-backdrop { position: fixed !important; inset: 0 !important; z-index: 9999999 !important; display: none; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.45) !important; padding: 1rem; opacity: 0; transition: opacity 0.3s ease; }
    .pc-modal-backdrop.is-open { display: flex !important; opacity: 1; }
    .pc-modal-card { width: 100%; max-width: 440px; background: white; border-radius: 2.5rem; padding: 2.5rem 2rem; transform: scale(0.9) translateY(20px); opacity: 0; transition: all 0.4s ease; }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>
@endpush

@section('content')
<div class="px-4 py-8 max-w-[1000px] mx-auto space-y-6 animate-pop-in">

    <section class="pc-hero">
        <div class="relative z-10 w-full flex flex-col items-center gap-3">
            <div class="inline-flex items-center gap-2 mb-2">
                <a href="{{ $oldReturnUrl }}" data-no-spa="1" class="btn-pill pc-back text-white px-5 py-2 text-[10px] font-black uppercase tracking-widest flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">Catat Pengukuran Fisik</h1>
            <p class="text-white/90 text-sm font-medium max-w-xl mx-auto leading-relaxed">
                Silakan masukkan data hasil pengukuran fisik untuk sasaran terpilih. Data akan langsung tersimpan ke antrean validasi Bidan.
            </p>
        </div>
    </section>

    @if(session('error') || $errors->any())
        <div id="formAlert" role="alert" tabindex="-1" class="pc-alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Peringatan: data belum tersimpan</strong>

                @if(session('error'))
                    <p style="margin-top: .25rem;">{{ session('error') }}</p>
                @endif

                @if($errors->any())
                    <p style="margin-top: .25rem;">Periksa isian berikut, lalu simpan kembali:</p>
                    <ul>
                        @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>

                    @if($kunciPanduan->isNotEmpty())
                        <div class="pc-edu">
                            <p class="pc-edu-title"><i class="fa-solid fa-lightbulb"></i> Acuan nilai yang lazim</p>
                            @foreach($kunciPanduan as $key)
                                <div class="pc-edu-field">
                                    <b>{{ $panduanNilai[$key]['judul'] }}</b>
                                    @foreach($labelKategori as $kat => $labelKat)
                                        @if(isset($panduanNilai[$key][$kat]))
                                            <p class="{{ $oldKategori === $kat ? 'is-active' : '' }}"><b>{{ $labelKat }}:</b> {{ $panduanNilai[$key][$kat] }}</p>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                            <p class="pc-edu-note">Kategori yang sedang Anda isi ditandai latar lebih gelap. Jika angka Anda jauh dari acuan, cek ulang alat ukur, satuan, dan kategori sasaran. Acuan ini gambaran umum (Kemenkes RI, Permenkes No. 2 Tahun 2020, WHO); penilaian status gizi dan kesehatan tetap oleh Bidan.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif

    <form id="measurementForm" method="POST" action="{{ route('kader.pemeriksaan.store') }}" data-api-url="{{ route('kader.pemeriksaan.api') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="return_url" value="{{ $oldReturnUrl }}">
        <input type="hidden" name="pasien_nama_hidden" value="{{ $oldPasienNama }}">
        <input type="hidden" name="pasien_nama_pilihan" id="pasienNamaPilihan" value="{{ old('pasien_nama_pilihan') }}">

        {{-- IDENTITAS SASARAN --}}
        <section class="widget-card p-6 md:p-8 space-y-6">
            <h5 class="font-black text-slate-800 text-sm uppercase mb-4 border-b border-slate-100 pb-4"><i class="fas fa-users text-emerald-500 mr-2"></i> Identitas Sasaran</h5>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($kategoriMenus as $key => $item)
                    <label class="cursor-pointer relative block {{ ($isLocked && $oldKategori !== $key) ? 'opacity-40 grayscale pointer-events-none' : '' }} {{ ($isLocked && $oldKategori === $key) ? 'pointer-events-none' : '' }}">
                        <input type="radio" name="kategori_pasien" value="{{ $key }}" class="peer sr-only" data-category-radio @checked($oldKategori === $key)>
                        <div class="h-full rounded-2xl border-2 border-slate-100 bg-slate-50/50 p-4 peer-checked:border-emerald-400 peer-checked:bg-white peer-checked:shadow-md flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white {{ $item['bg'] }}"><i class="fa-solid {{ $item['icon'] }}"></i></div>
                            <div><p class="text-sm font-black text-slate-700 peer-checked:text-emerald-700">{{ $item['label'] }}</p></div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="form-label">Nama Sasaran *</label>
                    <div class="relative" id="liveSearchWrapper">
                        <input type="hidden" name="pasien_id" id="pasienIdHidden" value="{{ $oldPasienId }}">
                        <div class="relative">
                            <input type="text" id="pasienSearchInput" value="{{ $oldPasienNama ?: old('pasien_nama_pilihan') }}" class="input-soft {{ $isLocked ? 'locked-input pr-12' : 'pr-12' }}" placeholder="Ketik nama sasaran..." autocomplete="off" @readonly($isLocked)>
                            <i class="fas {{ $isLocked ? 'fa-check text-emerald-500' : 'fa-search text-slate-400' }} absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" id="inputIcon"></i>
                        </div>
                        <ul id="pasienList" class="absolute z-50 w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl max-h-60 overflow-y-auto hidden"></ul>
                    </div>
                    <p id="pasienHelp" class="text-[10px] font-bold text-slate-400 mt-1 pl-1">
                        {!! $isLocked ? '<span class="text-emerald-500"><i class="fa-solid fa-link"></i> Terkoneksi otomatis dengan data presensi.</span>' : 'Ketik nama sasaran untuk mencari.' !!}
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Tanggal Pengukuran *</label>
                    <input type="date" name="tanggal_periksa" value="{{ old('tanggal_periksa', $today) }}" max="{{ $today }}" class="input-soft cursor-pointer">
                </div>
            </div>
        </section>

        {{-- PENGUKURAN FISIK --}}
        <section class="widget-card p-6 md:p-8 space-y-6">
            <h5 class="font-black text-slate-800 text-sm uppercase mb-4 border-b border-slate-100 pb-4"><i class="fas fa-stethoscope text-teal-500 mr-2"></i> Pengukuran Fisik Antropometri</h5>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                
                <div class="relative">
                    <label class="form-label">Berat Badan <span class="req-star">*</span></label>
                    <input type="number" step="0.1" name="berat_badan" value="{{ old('berat_badan') }}" class="input-soft pr-10" placeholder="Cth: 12.5" required autofocus data-imt-source>
                    <span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400 pointer-events-none">Kg</span>
                </div>
                
                <div class="relative">
                    <label class="form-label">Tinggi / Panjang <span class="req-star">*</span></label>
                    <input type="number" step="0.1" name="tinggi_badan" value="{{ old('tinggi_badan') }}" class="input-soft pr-10" placeholder="Cth: 85.0" required data-imt-source>
                    <span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                </div>

                <div data-field-group="remaja lansia" class="relative {{ ($isRemaja || $isLansia) ? 'block' : 'hidden' }}">
                    <label class="form-label text-teal-600">IMT Estimasi</label>
                    <div id="imtDisplay" class="input-soft bg-teal-50/50 border-teal-100 text-teal-700 min-h-[46px] flex items-center">-</div>
                    <span class="absolute right-4 bottom-3 text-[10px] font-black text-teal-600/50 pointer-events-none">kg/m²</span>
                </div>

                <div data-field-group="balita" class="relative {{ $isBalita ? 'block' : 'hidden' }}">
                    <label class="form-label">Lingkar Kepala <span class="req-star">*</span></label>
                    <input type="number" step="0.1" name="lingkar_kepala" value="{{ old('lingkar_kepala') }}" class="input-soft pr-10" placeholder="Wajib Balita" {{ $isBalita ? 'required' : 'disabled' }}>
                    <span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                </div>
                
                <div data-field-group="balita remaja" class="relative {{ ($isBalita || $isRemaja) ? 'block' : 'hidden' }}">
                    <label class="form-label">Lingkar Lengan (LILA) <span class="req-star">*</span></label>
                    <input type="number" step="0.1" name="lingkar_lengan" value="{{ old('lingkar_lengan') }}" class="input-soft pr-10" placeholder="LiLA" {{ ($isBalita || $isRemaja) ? 'required' : 'disabled' }}>
                    <span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                </div>
                
                <div data-field-group="remaja lansia" class="relative {{ ($isRemaja || $isLansia) ? 'block' : 'hidden' }}">
                    <label class="form-label">Lingkar Perut <span class="req-star">*</span></label>
                    <input type="number" step="0.1" name="lingkar_perut" value="{{ old('lingkar_perut') }}" class="input-soft pr-10" placeholder="Wajib" {{ ($isRemaja || $isLansia) ? 'required' : 'disabled' }}>
                    <span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                </div>
                
                <div data-field-group="remaja lansia" class="{{ ($isRemaja || $isLansia) ? 'block' : 'hidden' }}">
                    <label class="form-label">Tensi <span class="req-star">*</span></label>
                    <input type="text" name="tekanan_darah" value="{{ old('tekanan_darah') }}" placeholder="Cth: 120/80" class="input-soft" {{ ($isRemaja || $isLansia) ? 'required' : 'disabled' }}>
                </div>
                
                <div data-field-group="lansia" class="md:col-span-3 {{ $isLansia ? 'block' : 'hidden' }}">
                    <label class="form-label">Kemandirian <span class="req-star">*</span></label>
                    <select name="tingkat_kemandirian" class="input-soft cursor-pointer" {{ $isLansia ? 'required' : 'disabled' }}>
                        <option value="">Pilih Status</option>
                        <option value="mandiri" @selected(old('tingkat_kemandirian') === 'mandiri')>Mandiri</option>
                        <option value="bantuan_sebagian" @selected(old('tingkat_kemandirian') === 'bantuan_sebagian')>Bantuan Sebagian</option>
                        <option value="bantuan_penuh" @selected(old('tingkat_kemandirian') === 'bantuan_penuh')>Bantuan Penuh</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- SKRINING LANJUTAN --}}
        <section class="widget-card p-6 md:p-8 space-y-6">
            <h5 class="font-black text-slate-800 text-sm uppercase mb-4 border-b border-slate-100 pb-4"><i class="fas fa-notes-medical text-cyan-500 mr-2"></i> Skrining Lanjutan & Catatan</h5>
            
            <div data-field-group="remaja lansia" class="{{ ($isRemaja || $isLansia) ? 'block' : 'hidden' }} mb-6 pb-6 border-b border-slate-100">
                <p class="text-[10px] font-black uppercase text-slate-400 mb-4">Pemeriksaan Penunjang PTM (Opsional)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="relative"><label class="form-label">Gula Darah</label><input type="number" step="0.1" name="gula_darah" value="{{ old('gula_darah') }}" class="input-soft pr-14" {{ ($isRemaja || $isLansia) ? '' : 'disabled' }}><span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400">mg/dL</span></div>
                    <div class="relative"><label class="form-label">Kolesterol</label><input type="number" name="kolesterol" value="{{ old('kolesterol') }}" class="input-soft pr-14" {{ ($isRemaja || $isLansia) ? '' : 'disabled' }}><span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400">mg/dL</span></div>
                    <div class="relative"><label class="form-label">Asam Urat</label><input type="number" step="0.1" name="asam_urat" value="{{ old('asam_urat') }}" class="input-soft pr-14" {{ ($isRemaja || $isLansia) ? '' : 'disabled' }}><span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400">mg/dL</span></div>
                    <div data-field-group="remaja" class="relative {{ $isRemaja ? 'block' : 'hidden' }}"><label class="form-label">Hemoglobin</label><input type="number" step="0.1" name="hemoglobin" value="{{ old('hemoglobin') }}" class="input-soft pr-12" {{ $isRemaja ? '' : 'disabled' }}><span class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400">g/dL</span></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="form-label">Riwayat Keluhan</label><textarea name="keluhan" rows="3" class="input-soft resize-none" placeholder="Contoh: Batuk pilek sejak 2 hari...">{{ old('keluhan') }}</textarea></div>
                <div><label class="form-label">Catatan Tambahan Kader</label><textarea name="catatan_kader" rows="3" class="input-soft resize-none" placeholder="Catat temuan spesifik...">{{ old('catatan_kader') }}</textarea></div>
            </div>
        </section>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
            <a href="{{ $oldReturnUrl }}" data-no-spa="1" class="btn-pill w-full sm:w-auto px-8 py-3.5 font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 text-sm text-center">
                <i class="fas fa-times mr-1"></i> Batal / Kembali
            </a>
            <button type="button" id="openSubmitModalBtn" class="btn-pill pc-btn-primary w-full sm:w-auto px-10 py-3.5 font-bold text-white shadow-md flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Simpan Pengukuran Fisik
            </button>
        </div>
    </form>
</div>

{{-- MODAL KONFIRMASI --}}
<div id="pcSubmitModal" class="pc-modal-backdrop">
    <div class="pc-modal-card text-center">
        <h3 class="text-2xl font-black text-slate-800 mb-2 mt-4">Simpan Data?</h3>
        <p class="text-sm font-medium text-slate-500 mb-8 px-4">Data akan disimpan dan Anda akan dialihkan kembali.</p>
        <div class="flex gap-3">
            <button type="button" id="pcCancelSubmit" class="btn-pill flex-1 border border-slate-200 bg-white text-slate-700 py-3.5 text-sm font-bold shadow-sm">Batal</button>
            <button type="button" id="pcConfirmSubmit" class="btn-pill flex-1 bg-teal-500 text-white py-3.5 text-sm font-bold shadow-md">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// SPA SAFE SCRIPT USING GLOBAL EVENT DELEGATION
(function () {
    'use strict';

    // 1. DELEGASI EVENT GLOBAL (Mencegah Tombol Macet saat Pindah Halaman via SPA)
    if (!window.pcCreateFisikDelegated) {
        document.addEventListener('click', function(e) {
            
            // --- A. BUKA MODAL SIMPAN ---
            const submitBtn = e.target.closest('#openSubmitModalBtn');
            if (submitBtn) {
                e.preventDefault();
                const form = document.getElementById('measurementForm');
                const hiddenInput = document.getElementById('pasienIdHidden');
                
                if(!hiddenInput || !hiddenInput.value) {
                    alert('Pilih sasaran dari daftar pencarian terlebih dahulu.');
                    return;
                }
                
                if (form && !form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                const submitModal = document.getElementById('pcSubmitModal');
                if(submitModal) {
                    submitModal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }
                return;
            }

            // --- B. BATAL/TUTUP MODAL ---
            const cancelSubmit = e.target.closest('#pcCancelSubmit');
            if (cancelSubmit) {
                e.preventDefault();
                const submitModal = document.getElementById('pcSubmitModal');
                if(submitModal) {
                    submitModal.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
                return;
            }

            // --- C. KONFIRMASI SIMPAN ---
            const confirmSubmit = e.target.closest('#pcConfirmSubmit');
            if (confirmSubmit) {
                e.preventDefault();
                const form = document.getElementById('measurementForm');
                if (form) {
                    confirmSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                    confirmSubmit.disabled = true;
                    
                    const btn = document.getElementById('openSubmitModalBtn');
                    if (btn) btn.disabled = true;
                    
                    form.submit();
                }
                return;
            }

            // --- D. TUTUP MODAL JIKA KLIK BACKDROP ---
            if (e.target.id === 'pcSubmitModal') {
                e.target.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            
            // --- E. TUTUP DROPDOWN PENCARIAN JIKA KLIK DI LUAR ---
            const wrapper = document.getElementById('liveSearchWrapper');
            const list = document.getElementById('pasienList');
            if(wrapper && list && !wrapper.contains(e.target)) {
                list.classList.add('hidden');
                const hInput = document.getElementById('pasienIdHidden');
                const sInput = document.getElementById('pasienSearchInput');
                if(hInput && !hInput.value && sInput) sInput.value = '';
            }
        });

        document.addEventListener('keydown', e => { 
            if (e.key === 'Escape') { 
                const submitModal = document.getElementById('pcSubmitModal');
                if(submitModal && submitModal.classList.contains('is-open')) {
                    submitModal.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            } 
        });

        window.pcCreateFisikDelegated = true;
    }

    // 2. INISIASI LOGIKA HALAMAN (Live Search & IMT Kalkulator)
    function initCreateFisik() {
        const form = document.getElementById('measurementForm');
        if (!form) return;

        // Cegah modal bertumpuk jika navigasi SPA maju-mundur
        const submitModal = document.getElementById('pcSubmitModal');
        if (submitModal && submitModal.parentElement !== document.body) {
            document.querySelectorAll('body > #pcSubmitModal').forEach(el => el.remove());
            document.body.appendChild(submitModal);
        }

        const isLocked = {{ $isLocked ? 'true' : 'false' }};
        const searchInput = document.getElementById('pasienSearchInput');
        const hiddenInput = document.getElementById('pasienIdHidden');
        const list = document.getElementById('pasienList');
        const inputIcon = document.getElementById('inputIcon');
        const pasienHelp = document.getElementById('pasienHelp');
        const namaPilihan = document.getElementById('pasienNamaPilihan');
        const errorFields = @json($errors->keys());
        let allPatients = [];

        // Tandai isian yang ditolak server dan bawa pengguna ke pesan kesalahannya
        function markInvalidFields() {
            errorFields.forEach(name => {
                const field = name === 'pasien_id' ? searchInput : form.querySelector('[name="' + name + '"]');
                if (!field) return;
                field.classList.add('input-error');
                field.addEventListener('input', () => field.classList.remove('input-error'), { once: true });
            });

            const alertBox = document.getElementById('formAlert');
            if (alertBox) {
                setTimeout(() => alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' }), 200);
            }
        }

        function getSelectedCategory() {
            const checked = form.querySelector('[data-category-radio]:checked');
            return checked ? checked.value : 'balita';
        }

        function updateVisibleFields() {
            const selected = getSelectedCategory();
            form.querySelectorAll('[data-field-group]').forEach(function (group) {
                const values = String(group.dataset.fieldGroup || '').split(' ');
                const isVisible = values.includes(selected);
                
                if (isVisible) {
                    group.classList.remove('hidden');
                    group.querySelectorAll('input, select, textarea').forEach(el => {
                        el.disabled = false;
                        if(group.querySelector('.req-star')) el.required = true;
                    });
                } else {
                    group.classList.add('hidden');
                    group.querySelectorAll('input, select, textarea').forEach(el => {
                        el.disabled = true;
                        el.required = false;
                    });
                }
            });
        }

        function calculateImt() {
            const summaryImt = form.querySelector('#imtDisplay');
            if (!summaryImt) return;
            
            const category = getSelectedCategory();
            const bb = parseFloat(form.querySelector('[name="berat_badan"]')?.value || 0);
            const tb = parseFloat(form.querySelector('[name="tinggi_badan"]')?.value || 0);

            if (category === 'balita' || !bb || !tb) {
                summaryImt.textContent = '-'; 
                return;
            }
            const imt = bb / ((tb / 100) * (tb / 100));
            summaryImt.textContent = isFinite(imt) ? imt.toFixed(2) : '-';
        }

        form.querySelectorAll('[data-imt-source]').forEach(input => input.addEventListener('input', calculateImt));

        form.querySelectorAll('[data-category-radio]').forEach(r => r.addEventListener('change', () => { 
            if(!isLocked) {
                updateVisibleFields(); 
                hiddenInput.value = '';
                loadPasien();
            }
        }));

        function loadPasien() {
            if (isLocked) return; 

            const category = getSelectedCategory();
            const apiUrl = form.dataset.apiUrl;

            allPatients = [];
            if (!hiddenInput.value) searchInput.value = '';
            searchInput.disabled = true;
            inputIcon.className = 'fas fa-spinner fa-spin absolute right-4 top-1/2 -translate-y-1/2 text-teal-500 pointer-events-none';
            searchInput.placeholder = 'Memuat data sasaran...';

            fetch(apiUrl + '?kategori=' + encodeURIComponent(category), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(payload => {
                allPatients = Array.isArray(payload.data) ? payload.data : [];
                searchInput.disabled = false;
                searchInput.placeholder = 'Ketik nama sasaran...';
                inputIcon.className = 'fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none';
                
                if(allPatients.length > 0) {
                    pasienHelp.innerHTML = `Pencarian siap. ${allPatients.length} sasaran tersedia.`;
                } else {
                    pasienHelp.innerHTML = 'Belum ada data sasaran terdaftar di kategori ini.';
                    searchInput.disabled = true;
                    searchInput.placeholder = 'Data kosong';
                }
            })
            .catch(() => {
                searchInput.placeholder = 'Gagal memuat data';
                inputIcon.className = 'fas fa-wifi absolute right-4 top-1/2 -translate-y-1/2 text-rose-400 pointer-events-none';
                pasienHelp.innerHTML = 'Gagal terhubung ke server API.';
            });
        }

        function renderDropdown(data) {
            list.innerHTML = '';
            if(data.length === 0) {
                list.innerHTML = '<li class="p-3 text-sm font-bold text-slate-400 text-center">Pencarian tidak ditemukan</li>';
                return;
            }
            data.forEach(item => {
                const li = document.createElement('li');
                li.className = 'px-4 py-3 hover:bg-emerald-50 cursor-pointer rounded-xl transition-colors flex flex-col group';
                li.innerHTML = `<span class="text-sm font-black text-slate-700 group-hover:text-emerald-700">${item.nama}</span>
                                <span class="text-[10px] font-bold text-slate-400 mt-0.5">NIK: ${item.nik || '-'}</span>`;
                li.addEventListener('click', () => {
                    hiddenInput.value = item.id;
                    searchInput.value = item.nama;
                    if (namaPilihan) namaPilihan.value = item.nama;
                    list.classList.add('hidden');
                });
                list.appendChild(li);
            });
        }

        if(searchInput) {
            searchInput.addEventListener('input', (e) => {
                if(isLocked) return;
                const keyword = e.target.value.toLowerCase();
                const filtered = allPatients.filter(p => p.nama.toLowerCase().includes(keyword) || (p.nik && String(p.nik).includes(keyword)));
                renderDropdown(filtered);
                list.classList.remove('hidden');
                hiddenInput.value = '';
                if (namaPilihan) namaPilihan.value = '';
            });

            searchInput.addEventListener('focus', () => {
                if(isLocked) return; 
                if(allPatients.length > 0 && !searchInput.disabled) { renderDropdown(allPatients); list.classList.remove('hidden'); }
            });
        }

        updateVisibleFields();
        calculateImt();
        markInvalidFields();
        if(!isLocked) loadPasien();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCreateFisik);
    } else {
        initCreateFisik();
    }

    document.addEventListener('spa:loaded', initCreateFisik);
})();
</script>
@endpush