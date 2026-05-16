@extends('layouts.bidan')

@section('title', 'Ruang Validasi Medis')
@section('page-name', 'Meja 5 — Validasi Klinis')

@push('styles')
<style>
    /* NEXUS ANIMATION SYSTEM */
    .fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .clinical-card { background: white; border-radius: 28px; border: 1px solid #f1f5f9; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.03); overflow: hidden; }
    
    /* PREMIUM MEDICAL INPUT */
    .med-input { 
        width: 100%; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 16px; 
        padding: 14px 18px; color: #0f172a; font-weight: 700; font-size: 13px; 
        transition: all 0.3s ease; outline: none; appearance: none;
    }
    .med-input:focus { background: #fff; border-color: #06b6d4; box-shadow: 0 0 0 4px rgba(6,182,212,0.1); }
    .med-label { display: block; font-size: 10px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }

    /* SWEETALERT CUSTOM */
    .swal2-popup.nexus-swal { border-radius: 32px !important; padding: 2rem !important; border: 1px solid #f1f5f9 !important; font-family: 'Poppins', sans-serif; }
</style>
@endpush

@section('content')
@php
    $namaPasien = $pemeriksaan->nama_pasien;
    $kategori = strtolower($pemeriksaan->kategori_pasien ?? 'umum');

    $config = match($kategori) {
        'balita', 'bayi' => ['col' => 'sky', 'ico' => 'fa-baby', 'label' => 'Anak & Balita'],
        'remaja'         => ['col' => 'violet', 'ico' => 'fa-user-graduate', 'label' => 'Usia Remaja'],
        'ibu_hamil'      => ['col' => 'pink', 'ico' => 'fa-female', 'label' => 'Ibu Hamil'],
        'lansia'         => ['col' => 'emerald', 'ico' => 'fa-user-clock', 'label' => 'Lansia'],
        default          => ['col' => 'slate', 'ico' => 'fa-user', 'label' => 'Umum'],
    };

    // Bersihkan nama petugas jika sistem
    $namaKader = $pemeriksaan->pemeriksa->name ?? 'Kader';
    if (in_array(strtolower($namaKader), ['system', 'sistem'])) { $namaKader = 'Kader Lapangan'; }
@endphp

<div class="max-w-[1200px] mx-auto space-y-6 fade-in-up pb-20">

    {{-- 1. HERO HEADER: IDENTITAS PASIEN --}}
    <div class="clinical-card p-6 md:p-8 border-l-[10px] border-l-cyan-500 flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-[0.03] text-[150px] pointer-events-none"><i class="fas {{ $config['ico'] }}"></i></div>
        
        <div class="flex items-center gap-6 relative z-10 w-full md:w-auto">
            <div class="w-[72px] h-[72px] rounded-[22px] bg-cyan-50 text-cyan-600 flex items-center justify-center text-[32px] shadow-inner border border-cyan-100 shrink-0">
                <i class="fas {{ $config['ico'] }}"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                    <span class="px-2.5 py-1 bg-cyan-100 text-cyan-700 text-[9px] font-black uppercase rounded-md border border-cyan-200">Meja 5 Bidan</span>
                    <span class="text-[10px] font-bold text-slate-400">ID: #{{ str_pad($pemeriksaan->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h1 class="text-[26px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-1.5">{{ $namaPasien }}</h1>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-{{$config['col']}}-50 text-{{$config['col']}}-600 text-[9px] font-black uppercase rounded border border-{{$config['col']}}-100">{{ $config['label'] }}</span>
                    <span class="text-[11px] font-bold text-slate-400">NIK: {{ $pemeriksaan->nik_pasien }}</span>
                </div>
            </div>
        </div>
        
        <div class="bg-slate-50 rounded-[20px] p-5 border border-slate-100 text-left md:text-right w-full md:w-auto relative z-10 shrink-0">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Data dari Kader</p>
            <p class="text-[14px] font-black text-indigo-600 font-poppins flex items-center md:justify-end gap-2">
                <i class="fas fa-id-badge text-indigo-300"></i> {{ $namaKader }}
            </p>
            <p class="text-[10px] font-medium text-slate-400 mt-1 italic"><i class="far fa-clock mr-1"></i>Dikirim {{ $pemeriksaan->created_at->diffForHumans() }}</p>
        </div>
    </div>

    <form id="formValidasi" action="{{ route('bidan.pemeriksaan.update', $pemeriksaan->id) }}" method="POST">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- KOLOM KIRI: PARAMETER FISIK (READ ONLY) --}}
            <div class="lg:col-span-4">
                <div class="clinical-card p-6 md:p-8 bg-slate-50/50 h-full border-2 border-slate-100">
                    <h3 class="text-[14px] font-black text-slate-800 mb-5 flex items-center gap-2 border-b border-slate-200/60 pb-4">
                        <i class="fas fa-ruler-combined text-cyan-500"></i> Parameter Fisik
                    </h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="med-label">Berat Badan (kg)</label>
                            <input type="text" value="{{ $pemeriksaan->berat_badan }}" disabled class="med-input bg-slate-100/50 text-slate-400 border-dashed">
                        </div>
                        <div>
                            <label class="med-label">Tinggi/Panjang Badan (cm)</label>
                            <input type="text" value="{{ $pemeriksaan->tinggi_badan }}" disabled class="med-input bg-slate-100/50 text-slate-400 border-dashed">
                        </div>
                        <div>
                            <label class="med-label">Lingkar Lengan (LiLA) (cm)</label>
                            <input type="text" value="{{ $pemeriksaan->lingkar_lengan }}" disabled class="med-input bg-slate-100/50 text-slate-400 border-dashed">
                        </div>
                        @if(in_array($kategori, ['balita', 'bayi']))
                        <div>
                            <label class="med-label">Lingkar Kepala (cm)</label>
                            <input type="text" value="{{ $pemeriksaan->lingkar_kepala }}" disabled class="med-input bg-slate-100/50 text-slate-400 border-dashed">
                        </div>
                        @endif
                    </div>

                    <div class="mt-8 p-4 bg-cyan-50 rounded-2xl border border-cyan-100">
                        <p class="text-[10px] font-bold text-cyan-700 leading-relaxed italic">
                            <i class="fas fa-info-circle mr-1"></i> Data fisik di atas bersifat permanen dari hasil pengukuran Kader.
                        </p>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: ANALISA MEDIS BIDAN --}}
            <div class="lg:col-span-8">
                <div class="clinical-card border-2 border-cyan-500/20 h-full flex flex-col">
                    
                    <div class="px-8 py-5 bg-gradient-to-r from-cyan-600 to-blue-700 text-white">
                        <h3 class="text-[15px] font-black font-poppins flex items-center gap-2">
                            <i class="fas fa-stethoscope"></i> Kesimpulan Medis & Diagnosa
                        </h3>
                    </div>
                    
                    <div class="p-8 space-y-8 flex-1">
                        
                        {{-- 1. Vital Signs --}}
                        <div>
                            <h4 class="text-[11px] font-black text-cyan-600 uppercase tracking-widest mb-4 border-b border-cyan-100 pb-2">1. Pengukuran Vital (Opsional)</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                <div>
                                    <label class="med-label">Suhu Tubuh (°C)</label>
                                    <input type="number" step="0.1" name="suhu_tubuh" value="{{ $pemeriksaan->suhu_tubuh }}" class="med-input" placeholder="36.5">
                                </div>
                                <div>
                                    <label class="med-label">Tensi Darah (mmHg)</label>
                                    <input type="text" name="tekanan_darah" value="{{ $pemeriksaan->tekanan_darah }}" class="med-input" placeholder="120/80">
                                </div>
                                <div>
                                    <label class="med-label">Status Kesehatan</label>
                                    <select name="status_gizi" class="med-input cursor-pointer">
                                        <option value="Sehat/Normal" {{ $pemeriksaan->status_gizi == 'Sehat/Normal' ? 'selected' : '' }}>Sehat / Normal</option>
                                        <option value="Perlu Pantauan" {{ $pemeriksaan->status_gizi == 'Perlu Pantauan' ? 'selected' : '' }}>Perlu Pantauan</option>
                                        <option value="Rujuk" {{ $pemeriksaan->status_gizi == 'Rujuk' ? 'selected' : '' }}>Rujuk Ke Puskesmas</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Diagnosa --}}
                        <div class="flex-1">
                            <h4 class="text-[11px] font-black text-cyan-600 uppercase tracking-widest mb-4 border-b border-cyan-100 pb-2">2. Analisa & Tindakan Medis</h4>
                            <div class="space-y-6">
                                <div>
                                    <label class="med-label">Hasil Diagnosa <span class="text-rose-500">*</span></label>
                                    <textarea name="diagnosa" rows="3" required class="med-input resize-none" placeholder="Tuliskan diagnosa klinis...">{{ $pemeriksaan->diagnosa }}</textarea>
                                </div>
                                <div>
                                    <label class="med-label">Tindakan / Saran <span class="text-rose-500">*</span></label>
                                    <textarea name="tindakan" rows="2" required class="med-input resize-none" placeholder="Berikan resep vitamin, edukasi, atau saran tindakan...">{{ $pemeriksaan->tindakan }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Aksi --}}
                        <div class="pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Siap Disahkan ke EMR</p>
                            </div>
                            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-10 py-4 bg-gradient-to-r from-cyan-600 to-blue-700 text-white font-black text-[12px] uppercase tracking-widest rounded-2xl hover:shadow-[0_15px_30px_rgba(6,182,212,0.3)] transition-all hover:-translate-y-1 active:scale-95 shadow-lg">
                                <i class="fas fa-check-circle mr-2"></i> Sahkan & Simpan Rekam Medis
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('formValidasi').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Sahkan Data Medis?',
            text: "Data diagnosa Anda akan dikunci dan disimpan secara permanen ke dalam rekam medis warga.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0891b2',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Sahkan Data',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: { popup: 'nexus-swal' }
        }).then((res) => {
            if (res.isConfirmed) {
                const btn = document.getElementById('btnSubmit');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses EMR...';
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');

                // EKSEKUSI VIA AJAX FETCH
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Kegagalan sistem saat menyimpan data.');
                    return response.json();
                })
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        
                        // LOGIKA SMART BRIDGE: Cek kategori pasien
                        const targetKategori = data.kategori.toLowerCase();
                        const isTargetImunisasi = ['balita', 'bayi', 'ibu_hamil', 'bumil'].includes(targetKategori);

                        if (isTargetImunisasi) {
                            Swal.fire({
                                title: 'Berhasil Disahkan!',
                                text: `Data rekam medis ${data.nama} telah disimpan. Apakah Anda ingin melanjutkan dengan pemberian Imunisasi sekarang?`,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonColor: '#0891b2',
                                cancelButtonColor: '#94a3b8',
                                confirmButtonText: 'Ya, Catat Imunisasi',
                                cancelButtonText: 'Kembali ke Antrian',
                                allowOutsideClick: false,
                                customClass: { popup: 'nexus-swal' }
                            }).then((choice) => {
                                if (choice.isConfirmed) {
                                    // Redirect ke Form Imunisasi dengan Prefill Data
                                    window.location.href = `{{ route('bidan.imunisasi.create') }}?pasien_id=${data.pasien_id}&type=${targetKategori}`;
                                } else {
                                    window.location.href = "{{ route('bidan.pemeriksaan.index') }}";
                                }
                            });
                        } else {
                            // Untuk Lansia/Remaja langsung kembali ke index
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Data rekam medis warga telah sah disimpan.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false,
                                customClass: { popup: 'nexus-swal' }
                            }).then(() => {
                                window.location.href = "{{ route('bidan.pemeriksaan.index', ['tab' => 'verified']) }}";
                            });
                        }
                    }
                })
                .catch(error => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-not-allowed');
                    
                    Swal.fire({
                        title: 'Terjadi Kesalahan',
                        text: error.message,
                        icon: 'error',
                        customClass: { popup: 'nexus-swal' }
                    });
                });
            }
        });
    });
</script>
@endpush