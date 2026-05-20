@extends('layouts.kader')
@section('title', 'Buat Jadwal Baru')
@section('page-name', 'Rancang Agenda')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .form-label { display: block; font-size: 0.70rem; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; text-align: left;}
    .form-input { width: 100%; background-color: #f8fafc; border: 2px solid #e2e8f0; color: #0f172a; font-size: 0.875rem; border-radius: 1rem; padding: 1rem 1.25rem; outline: none; transition: all 0.3s ease; font-weight: 700; box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.02); }
    .form-input:focus { background-color: #ffffff; border-color: #8b5cf6; box-shadow: 0 4px 20px -3px rgba(139, 92, 246, 0.15); transform: translateY(-2px); }
    .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8); }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto animate-slide-up relative z-10 pb-10">
    <div class="absolute top-0 right-0 w-96 h-96 bg-violet-400/20 rounded-full blur-3xl pointer-events-none z-0"></div>

    <div class="mb-6 flex items-center gap-3 relative z-10">
        <a href="{{ route('kader.jadwal.index') }}" class="loader-trigger w-12 h-12 rounded-[16px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-violet-50 hover:text-violet-600 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    <div class="text-center mb-10 relative z-10">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-[24px] bg-gradient-to-br from-violet-100 to-indigo-100 text-violet-600 mb-5 shadow-sm border border-violet-200 transform rotate-3 hover:rotate-0 transition-transform">
            <i class="fas fa-calendar-plus text-4xl"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight font-poppins">Rancang Agenda Posyandu</h1>
        <p class="text-slate-500 mt-2 font-medium text-[13px] max-w-lg mx-auto">Isi detail acara dengan lengkap. Setelah jadwal disimpan, Anda dapat menyiarkannya (Broadcast) ke HP warga terkait.</p>
    </div>

    <form action="{{ route('kader.jadwal.store') }}" method="POST" id="formJadwal" class="relative z-10">
        @csrf
        
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 mb-8">
            
            {{-- KOLOM KIRI: INFO ACARA --}}
            <div class="xl:col-span-7 flex flex-col gap-8">
                <div class="glass-panel rounded-[32px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 md:p-10 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-violet-500/10 rounded-bl-full pointer-events-none"></div>
                    <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5">
                        <span class="w-10 h-10 rounded-xl bg-violet-600 text-white flex items-center justify-center font-black shadow-md">1</span>
                        <h3 class="text-xl font-black text-slate-800 font-poppins">Informasi Kegiatan</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="form-label">Nama / Judul Acara <span class="text-rose-500">*</span></label>
                            <input type="text" name="judul" required placeholder="Contoh: Gebyar Posyandu Balita RW 01" class="form-input focus:ring-4 focus:ring-violet-50">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Kategori Layanan <span class="text-rose-500">*</span></label>
                                <select name="kategori" required class="form-input cursor-pointer focus:ring-4 focus:ring-violet-50">
                                    <option value="kesehatan_ibu_anak">Kesehatan Ibu & Anak</option>
                                    <option value="imunisasi">Imunisasi / Vaksin</option>
                                    <option value="pemeriksaan_lansia">Pemeriksaan Lansia</option>
                                    <option value="penyuluhan">Penyuluhan Gizi</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label text-indigo-600">Target Sasaran Warga <span class="text-rose-500">*</span></label>
                                <select name="target_peserta" required class="form-input bg-indigo-50 border-indigo-100 text-indigo-800 cursor-pointer focus:ring-4 focus:ring-indigo-100 focus:bg-white">
                                    <option value="semua">Semua Warga (Umum)</option>
                                    <option value="balita">Balita & Anak</option>
                                    <option value="remaja">Remaja</option>
                                    <option value="lansia">Lansia</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Catatan / Persyaratan (Opsional)</label>
                            <textarea name="deskripsi" rows="3" placeholder="Contoh: Harap membawa buku KIA dan fotokopi KK..." class="form-input resize-none focus:ring-4 focus:ring-violet-50"></textarea>
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
                            <input type="date" name="tanggal" required class="form-input bg-slate-700 border-slate-600 text-white cursor-pointer focus:border-sky-400 focus:bg-slate-600" min="{{ date('Y-m-d') }}">
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="form-label text-slate-300">Mulai (WIB) <span class="text-rose-400">*</span></label>
                                <input type="time" name="waktu_mulai" required value="08:00" class="form-input bg-slate-700 border-slate-600 text-white cursor-pointer focus:border-sky-400 focus:bg-slate-600 text-center">
                            </div>
                            <div>
                                <label class="form-label text-slate-300">Selesai (WIB) <span class="text-rose-400">*</span></label>
                                <input type="time" name="waktu_selesai" required value="12:00" class="form-input bg-slate-700 border-slate-600 text-white cursor-pointer focus:border-sky-400 focus:bg-slate-600 text-center">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-700">
                            <label class="form-label text-sky-400"><i class="fas fa-map-marker-alt mr-1"></i> Lokasi Posyandu <span class="text-rose-400">*</span></label>
                            <input type="text" name="lokasi" required placeholder="Balai Desa / Pos RW..." class="form-input bg-sky-900/50 border-sky-800 text-white focus:border-sky-400 focus:bg-slate-700 placeholder:text-slate-500">
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        {{-- ACTION BUTTONS --}}
        <div class="p-8 border-t border-slate-100 bg-white/80 backdrop-blur-xl rounded-[32px] shadow-lg flex flex-col sm:flex-row items-center justify-end gap-4">
            <a href="{{ route('kader.jadwal.index') }}" class="loader-trigger w-full sm:w-auto px-8 py-3.5 bg-slate-100 border border-slate-200 text-slate-600 font-extrabold text-[13px] rounded-xl hover:bg-slate-200 transition-colors text-center uppercase tracking-widest">
                Batal
            </a>
            <button type="submit" id="btnSubmit" class="btn-press w-full sm:w-auto px-10 py-3.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-black text-[13px] rounded-xl hover:from-violet-500 hover:to-indigo-500 shadow-[0_8px_20px_rgba(139,92,246,0.3)] transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                <i class="fas fa-save text-lg"></i> Simpan Agenda
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
            title: 'Memproses...', html: 'Menyimpan jadwal ke database.',
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
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Cek kembali isian Anda.' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error Koneksi', text: 'Gagal terhubung ke server.' });
        }
    });
</script>
@endpush
@endsection