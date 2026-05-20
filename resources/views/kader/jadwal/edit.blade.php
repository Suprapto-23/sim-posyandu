@extends('layouts.kader')
@section('title', 'Edit Jadwal')
@section('page-name', 'Koreksi Agenda')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .form-label { display: block; font-size: 0.70rem; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; text-align: left;}
    .form-input { width: 100%; background-color: #f8fafc; border: 2px solid #e2e8f0; color: #0f172a; font-size: 0.875rem; border-radius: 1rem; padding: 1rem 1.25rem; outline: none; transition: all 0.3s ease; font-weight: 700; box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.02); }
    .form-input:focus { background-color: #ffffff; border-color: #f59e0b; box-shadow: 0 4px 20px -3px rgba(245, 158, 11, 0.15); transform: translateY(-2px); }
    .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8); }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto animate-slide-up relative z-10 pb-10">
    <div class="absolute top-0 right-0 w-96 h-96 bg-amber-400/20 rounded-full blur-3xl pointer-events-none z-0"></div>

    <div class="mb-6 flex items-center gap-3 relative z-10">
        <a href="{{ route('kader.jadwal.index') }}" class="loader-trigger w-12 h-12 rounded-[16px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-amber-50 hover:text-amber-600 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    {{-- WARNING HEADER --}}
    <div class="bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 rounded-[32px] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_15px_40px_-10px_rgba(245,158,11,0.4)] flex flex-col md:flex-row items-center gap-8 z-10">
        <div class="absolute inset-0 opacity-[0.15] pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="w-20 h-20 rounded-[24px] bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center text-4xl shrink-0 shadow-lg transform rotate-3 hover:rotate-0 transition-transform">
            <i class="fas fa-edit"></i>
        </div>
        <div class="text-center md:text-left relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/20 border border-white/30 text-white text-[10px] font-black px-4 py-1.5 rounded-full mb-3 uppercase tracking-widest backdrop-blur-sm">
                <i class="fas fa-exclamation-triangle text-amber-200"></i> Mode Pembaruan Agenda
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight font-poppins mb-2">Edit: {{ Str::limit($jadwal->judul, 25) }}</h1>
            <p class="text-amber-50 font-medium text-[13px] md:text-[14px]">Perbarui informasi, waktu, lokasi, atau tandai acara ini sebagai "Selesai" / "Batal".</p>
        </div>
    </div>

    <form action="{{ route('kader.jadwal.update', $jadwal->id) }}" method="POST" id="formJadwal" class="relative z-10">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 mb-8">
            
            {{-- KOLOM KIRI: INFO ACARA --}}
            <div class="xl:col-span-7 flex flex-col gap-8">
                <div class="glass-panel rounded-[32px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 md:p-10 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-bl-full pointer-events-none"></div>
                    <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5">
                        <span class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black shadow-md">1</span>
                        <h3 class="text-xl font-black text-slate-800 font-poppins">Informasi Kegiatan</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="form-label">Nama / Judul Acara <span class="text-rose-500">*</span></label>
                            <input type="text" name="judul" value="{{ old('judul', $jadwal->judul) }}" required class="form-input focus:ring-4 focus:ring-amber-50">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label text-indigo-600">Target Sasaran <span class="text-rose-500">*</span></label>
                                <select name="target_peserta" required class="form-input bg-indigo-50 border-indigo-100 text-indigo-800 cursor-pointer">
                                    <option value="semua" {{ $jadwal->target_peserta == 'semua' ? 'selected' : '' }}>Semua Warga</option>
                                    <option value="balita" {{ $jadwal->target_peserta == 'balita' ? 'selected' : '' }}>Balita & Anak</option>
                                    <option value="remaja" {{ $jadwal->target_peserta == 'remaja' ? 'selected' : '' }}>Remaja</option>
                                    <option value="lansia" {{ $jadwal->target_peserta == 'lansia' ? 'selected' : '' }}>Lansia</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label text-rose-500">Status Acara <span class="text-rose-500">*</span></label>
                                <select name="status" required class="form-input bg-rose-50 border-rose-100 text-rose-800 cursor-pointer focus:ring-4 focus:ring-rose-100 focus:bg-white">
                                    <option value="aktif" {{ $jadwal->status == 'aktif' ? 'selected' : '' }}>Aktif / Berjalan</option>
                                    <option value="selesai" {{ $jadwal->status == 'selesai' ? 'selected' : '' }}>Sudah Selesai</option>
                                    <option value="dibatalkan" {{ $jadwal->status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Kategori Layanan <span class="text-rose-500">*</span></label>
                            <select name="kategori" required class="form-input cursor-pointer focus:ring-4 focus:ring-amber-50">
                                <option value="kesehatan_ibu_anak" {{ $jadwal->kategori == 'kesehatan_ibu_anak' ? 'selected' : '' }}>Kesehatan Ibu & Anak</option>
                                <option value="imunisasi" {{ $jadwal->kategori == 'imunisasi' ? 'selected' : '' }}>Imunisasi / Vaksin</option>
                                <option value="pemeriksaan_lansia" {{ $jadwal->kategori == 'pemeriksaan_lansia' ? 'selected' : '' }}>Pemeriksaan Lansia</option>
                                <option value="penyuluhan" {{ $jadwal->kategori == 'penyuluhan' ? 'selected' : '' }}>Penyuluhan Gizi</option>
                                <option value="lainnya" {{ $jadwal->kategori == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea name="deskripsi" rows="3" class="form-input resize-none focus:ring-4 focus:ring-amber-50">{{ old('deskripsi', $jadwal->deskripsi) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: WAKTU & TEMPAT --}}
            <div class="xl:col-span-5 flex flex-col gap-8">
                <div class="bg-slate-800 rounded-[32px] shadow-[0_20px_50px_rgb(0,0,0,0.15)] p-8 md:p-10 relative overflow-hidden flex flex-col h-full text-white">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-sky-500/20 rounded-bl-full pointer-events-none blur-xl"></div>
                    
                    <div class="flex items-center gap-4 mb-8 border-b border-slate-700 pb-5 relative z-10">
                        <span class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center font-black shadow-md">2</span>
                        <h3 class="text-xl font-black text-white font-poppins">Waktu & Lokasi</h3>
                    </div>

                    <div class="space-y-6 relative z-10">
                        <div>
                            <label class="form-label text-slate-300">Tanggal Pelaksanaan <span class="text-rose-400">*</span></label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', $jadwal->tanggal) }}" required class="form-input bg-slate-700 border-slate-600 text-white cursor-pointer focus:border-amber-400 focus:bg-slate-600">
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="form-label text-slate-300">Mulai (WIB) <span class="text-rose-400">*</span></label>
                                <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', $jadwal->waktu_mulai) }}" required class="form-input bg-slate-700 border-slate-600 text-white cursor-pointer focus:border-amber-400 focus:bg-slate-600 text-center">
                            </div>
                            <div>
                                <label class="form-label text-slate-300">Selesai (WIB) <span class="text-rose-400">*</span></label>
                                <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai', $jadwal->waktu_selesai) }}" required class="form-input bg-slate-700 border-slate-600 text-white cursor-pointer focus:border-amber-400 focus:bg-slate-600 text-center">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-700">
                            <label class="form-label text-sky-400"><i class="fas fa-map-marker-alt mr-1"></i> Lokasi Posyandu <span class="text-rose-400">*</span></label>
                            <input type="text" name="lokasi" value="{{ old('lokasi', $jadwal->lokasi) }}" required class="form-input bg-sky-900/50 border-sky-800 text-white focus:border-amber-400 focus:bg-slate-700">
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="p-8 border-t border-slate-100 bg-white/80 backdrop-blur-xl rounded-[32px] shadow-lg flex flex-col sm:flex-row items-center justify-end gap-4">
            <a href="{{ route('kader.jadwal.index') }}" class="loader-trigger w-full sm:w-auto px-8 py-3.5 bg-slate-100 border border-slate-200 text-slate-600 font-extrabold text-[13px] rounded-xl hover:bg-slate-200 transition-colors text-center uppercase tracking-widest">Batal</a>
            <button type="submit" id="btnSubmit" class="btn-press w-full sm:w-auto px-10 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black text-[13px] rounded-xl hover:from-amber-600 hover:to-orange-600 shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                <i class="fas fa-save text-lg"></i> Update Jadwal
            </button>
        </div>
        
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formJadwal').addEventListener('submit', async function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Memperbarui...', html: 'Menyimpan perubahan ke database.',
            allowOutsideClick: false, showConfirmButton: false,
            willOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await fetch(this.action, {
                method: 'POST', body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const result = await res.json();
            if (res.ok && result.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 1500, showConfirmButton: false })
                .then(() => { window.location.href = result.redirect; });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error Koneksi', text: 'Gagal terhubung ke server.' });
        }
    });
</script>
@endpush
@endsection