@extends('layouts.bidan')

@section('title', 'Log Vaksinasi Baru')
@section('page-name', 'Log Imunisasi')

@push('styles')
<style>
    /* ANIMASI MASUK HALUS */
    .fade-in-up { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    /* TIPOGRAFI & INPUT PREMIUM */
    .med-input { 
        width: 100%; background: #ffffff; border: 2px solid #f1f5f9; border-radius: 16px; 
        padding: 16px 20px; color: #0f172a; font-weight: 600; font-size: 14px; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); outline: none; appearance: none;
        box-shadow: 0 2px 6px rgba(15,23,42,0.02);
    }
    
    /* FIX OVERLAP MUTLAK: Kunci padding kiri untuk kolom search */
    .search-input-kia { padding-left: 56px !important; }

    .med-input:focus { 
        border-color: #0ea5e9; 
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15), 0 2px 6px rgba(15,23,42,0.02); 
    }
    .med-input::placeholder { color: #94a3b8; font-weight: 500; }
    
    .med-label { 
        display: block; font-size: 11px; font-weight: 800; color: #64748b; 
        text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 10px; margin-left: 4px; 
        font-family: 'Poppins', sans-serif;
    }

    /* KARTU KATEGORI BERDIMENSI */
    .cat-label { display: block; cursor: pointer; height: 100%; perspective: 1000px; }
    .cat-box { 
        height: 100%; display: flex; align-items: center; justify-content: center; gap: 16px; padding: 22px; 
        border: 2px solid #f1f5f9; border-radius: 20px; background: #ffffff; 
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }
    .cat-label:hover .cat-box { border-color: #cbd5e1; transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0,0,0,0.05); }
    
    .cat-radio:checked + .cat-box { 
        border-color: #0ea5e9; background: linear-gradient(145deg, #ffffff, #f0f9ff); 
        box-shadow: 0 15px 35px -10px rgba(14, 165, 233, 0.25); transform: translateY(-4px); 
    }
    .cat-radio:checked + .cat-box .icon-circle { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; transform: scale(1.15); box-shadow: 0 6px 15px rgba(14, 165, 233, 0.3); border-color: transparent; }
    .cat-radio:checked + .cat-box .cat-text { color: #0369a1; }

    /* LENCANA LANGKAH (SQUIRCLE) */
    .step-badge {
        width: 34px; height: 34px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 900; font-family: 'Poppins', sans-serif;
        background: #0ea5e9; color: white;
        box-shadow: 0 6px 15px rgba(14, 165, 233, 0.35); transition: all 0.5s ease;
    }
    .step-badge.inactive { background: #f1f5f9; color: #94a3b8; box-shadow: none; }

    /* SEARCH ITEM PREMIUM */
    .search-item { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 14px; cursor: pointer; border: 1px solid transparent; }
    .search-item:hover { background: #f0f9ff; border-color: #bae6fd; padding-left: 1.5rem; }

    /* EFEK LOCK KACA (GLASSMORPHISM) */
    .locked-section { filter: grayscale(1); opacity: 0.3; pointer-events: none; transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
    .unlocked-section { filter: grayscale(0); opacity: 1; pointer-events: auto; }

    /* SWEETALERT NEXUS ULTIMATE */
    .swal2-popup.nexus-swal {
        border-radius: 32px !important; padding: 3rem 2rem !important;
        background: rgba(255, 255, 255, 0.95) !important; backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255,255,255,0.8) !important; box-shadow: 0 30px 60px -12px rgba(15, 23, 42, 0.15) !important;
    }
    .swal2-title { font-family: 'Poppins', sans-serif !important; font-weight: 900 !important; color: #0f172a !important; font-size: 24px !important; }
    .swal2-html-container { font-weight: 500 !important; color: #64748b !important; font-size: 14px !important; margin-top: 0.5em !important; }
    .swal2-confirm.nexus-confirm {
        background: linear-gradient(135deg, #0ea5e9, #0284c7) !important; border-radius: 16px !important; font-weight: 800 !important; text-transform: uppercase !important; letter-spacing: 0.05em !important;
        padding: 16px 36px !important; font-size: 13px !important; box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.4) !important; transition: all 0.3s ease !important;
    }
    .swal2-confirm.nexus-confirm:hover { transform: translateY(-2px) !important; box-shadow: 0 15px 35px -5px rgba(14, 165, 233, 0.5) !important; }

    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')

{{-- Inisialisasi Alpine dengan $masterData dari Controller --}}
<div x-data="imunisasiWizard(@js($masterData))" class="max-w-[1050px] mx-auto fade-in-up pb-24 relative">

    {{-- NAVIGASI ATAS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 px-2">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 rounded-[20px] bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center text-2xl shadow-[0_10px_25px_rgba(6,182,212,0.35)]">
                <i class="fas fa-shield-virus"></i>
            </div>
            <div>
                <h1 class="text-[26px] font-black text-slate-800 tracking-tight font-poppins leading-none">Log Injeksi KIA</h1>
                <p class="text-[13px] font-semibold text-slate-500 mt-1.5">Layanan Imunisasi Balita </p>
            </div>
        </div>
        <a href="{{ route('bidan.imunisasi.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white border border-slate-200 text-slate-600 font-bold text-[11.5px] uppercase tracking-widest rounded-[16px] hover:bg-slate-50 hover:text-cyan-600 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-slate-400"></i> Kembali
        </a>
    </div>

    {{-- KONTANER UTAMA --}}
    <form id="formImunisasi" action="{{ route('bidan.imunisasi.store') }}" method="POST" class="bg-white rounded-[36px] border border-slate-100 shadow-[0_25px_70px_-15px_rgba(0,0,0,0.06)] overflow-hidden flex flex-col relative z-10">
        @csrf
        
        {{-- Input Tersembunyi --}}
        <input type="hidden" name="pasien_id" x-model="pasienId">

        {{-- HEADER FORM --}}
        <div class="px-8 md:px-12 py-8 border-b border-slate-100 bg-slate-50/50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-[18px] font-black text-slate-800 font-poppins tracking-tight">Pencatatan Rekam Medis Vaksinasi</h2>
                    <p class="text-[12.5px] font-medium text-slate-500 mt-1">Sistem menyinkronkan data secara otomatis ke riwayat EMR warga.</p>
                </div>
                <div class="inline-flex px-5 py-3 bg-white rounded-2xl border border-slate-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-cyan-50 text-cyan-600 flex items-center justify-center text-[11px]"><i class="fas fa-user-nurse"></i></div>
                    <span class="text-[12px] font-black text-slate-700 tracking-wide">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>

        {{-- 1. SASARAN VAKSIN --}}
        <div class="p-8 md:p-12 border-b border-slate-100 bg-white">
            <div class="flex items-center gap-4 mb-8">
                <div class="step-badge">1</div>
                <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-widest font-poppins">Kluster Sasaran</h3>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pl-0 md:pl-[50px]">
                <label class="cat-label">
                    <input type="radio" name="kategori" value="balita" x-model="kategori" @change="resetPencarian()" class="sr-only cat-radio">
                    <div class="cat-box">
                        <div class="icon-circle w-14 h-14 rounded-[16px] bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center text-2xl transition-all duration-300 shrink-0">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div class="text-left flex-1">
                            <span class="cat-text font-black text-slate-800 text-[17px] transition-colors duration-300 block font-poppins tracking-tight">Bayi & Balita</span>
                            <span class="text-[10.5px] text-slate-400 font-bold uppercase tracking-widest mt-1 block">Imunisasi Dasar / Lanjutan</span>
                        </div>
                    </div>
                </label>

               
            </div>
        </div>

        {{-- 2. PENCARIAN OFFLINE SUPER CEPAT --}}
        <div class="p-8 md:p-12 border-b border-slate-100 bg-slate-50/50">
            <div class="flex flex-col md:flex-row md:items-start gap-8 lg:gap-12">
                
                <div class="w-full md:w-1/3 shrink-0">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="step-badge transition-colors duration-500" :class="pasienId ? 'bg-emerald-500 shadow-emerald-200' : (kategori ? 'bg-cyan-500' : 'inactive')">2</div>
                        <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-widest font-poppins">Identitas Penerima</h3>
                    </div>
                    <p class="text-[12px] text-slate-500 font-medium leading-relaxed pl-0 md:pl-[50px]">
                        Ketik <strong class="text-cyan-600">Nama atau NIK</strong> warga. Pencarian otomatis dibatasi pada kelompok sasaran yang dipilih.
                    </p>
                </div>
                
                <div class="w-full relative pl-0 md:pl-2" @click.away="dropOpen = false">
                    <div class="relative w-full flex items-center">
                        <div class="absolute left-5 flex items-center pointer-events-none z-10">
                            <i class="fas fa-search text-[16px] transition-all duration-300" :class="query.length > 0 ? 'text-cyan-500 scale-110' : 'text-slate-400'"></i>
                        </div>
                        
                        <input type="text" x-model="query" @focus="dropOpen = true" @input="dropOpen = true"
                               class="med-input search-input-kia w-full bg-white" 
                               :placeholder="kategori ? 'Ketik pencarian ' + kategori.replace('_', ' ') + '...' : 'Pilih sasaran (Langkah 1) dahulu...'">
                        
                        <button type="button" x-show="pasienId" @click="resetPencarian()" class="absolute right-5 flex items-center text-rose-400 hover:text-rose-600 hover:rotate-90 transition-all z-10" x-cloak>
                            <i class="fas fa-times-circle text-[22px]"></i>
                        </button>
                    </div>

                    {{-- Dropdown Hasil --}}
                    <div x-show="dropOpen" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-cloak class="absolute left-0 md:left-2 w-full md:w-[calc(100%-0.5rem)] mt-3 bg-white rounded-[24px] shadow-[0_25px_60px_rgba(15,23,42,0.15)] border border-slate-100 max-h-[320px] overflow-y-auto p-3 z-50 custom-scrollbar">
                        
                        <div x-show="!kategori" class="p-8 text-center bg-slate-50/80 rounded-[16px] border border-slate-100">
                            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-slate-100"><i class="fas fa-hand-pointer text-cyan-400 text-2xl"></i></div>
                            <p class="text-slate-500 text-[12px] font-black uppercase tracking-widest font-poppins">Pilih Kluster Sasaran Dahulu</p>
                        </div>

                        <template x-if="kategori">
                            <div class="space-y-1.5">
                                <template x-for="p in results()" :key="p.id">
                                    <div @click="pilihPasien(p)" class="search-item p-4 bg-white border border-slate-50 flex items-center justify-between gap-4 group hover:shadow-[0_4px_10px_rgba(0,0,0,0.02)]">
                                        <div class="flex items-center gap-5">
                                            <div class="w-11 h-11 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-black text-[14px] group-hover:bg-cyan-100 group-hover:text-cyan-600 transition-colors" x-text="p.nama.charAt(0)"></div>
                                            <div>
                                                <p class="font-black text-slate-800 text-[15px] font-poppins tracking-tight" x-text="p.nama"></p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1" x-text="'NIK: ' + (p.nik ?? '-')"></p>
                                            </div>
                                        </div>
                                        <i class="fas fa-arrow-right text-[14px] text-slate-200 group-hover:text-cyan-500 group-hover:translate-x-2 transition-all"></i>
                                    </div>
                                </template>
                                <div x-show="results().length === 0 && query.length > 0" class="p-10 text-center text-slate-400 font-bold">
                                    <i class="fas fa-search-minus text-3xl mb-3 block text-slate-200"></i>
                                    Tidak ada data yang cocok.
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. RINCIAN INJEKSI MEDIS --}}
        <div class="p-8 md:p-12 transition-all duration-500 bg-white relative" :class="pasienId ? 'unlocked-section' : 'locked-section'">
            {{-- Overlay Pengunci --}}
            <div x-show="!pasienId" class="absolute inset-0 z-20 bg-white/40 backdrop-blur-[3px] flex flex-col items-center justify-center" x-transition.opacity>
                <div class="px-8 py-5 bg-white rounded-[24px] shadow-[0_15px_40px_rgba(0,0,0,0.08)] border border-slate-100 flex items-center gap-5">
                    <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-xl"><i class="fas fa-lock"></i></div>
                    <div>
                        <p class="text-[15px] font-black text-slate-800 font-poppins">Form Medis Terkunci</p>
                        <p class="text-[11px] font-semibold text-slate-500 mt-0.5">Selesaikan Langkah 2 untuk membuka rincian form.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mb-10">
                <div class="step-badge">3</div>
                <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-widest font-poppins">Rincian Injeksi Medis</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pl-0 md:pl-[50px] relative z-10">
                
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="med-label">Program Vaksinasi <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="jenis_imunisasi" class="med-input cursor-pointer pr-10" required>
                            <option value="">-- Tentukan Program --</option>
                            <option value="Dasar Lengkap (0-11 Bulan)" x-show="kategori === 'balita'">Dasar Lengkap (0-11 Bulan)</option>
                            <option value="Lanjutan Baduta (18-24 Bulan)" x-show="kategori === 'balita'">Lanjutan Baduta (18-24 Bulan)</option>
                            <option value="Imunisasi Anak Sekolah (BIAS)" x-show="kategori === 'balita'">Imunisasi Anak Sekolah (BIAS)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[12px]"></i>
                    </div>
                </div>

                <div class="md:col-span-2 lg:col-span-1">
                    <label class="med-label">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_imunisasi" value="{{ date('Y-m-d') }}" class="med-input cursor-pointer text-slate-700" required>
                </div>
                
                {{-- Penambahan Field Dosis dan Vaksin Bersebelahan --}}
                <div class="md:col-span-1">
                    <label class="med-label">Jenis / Nama Vaksin <span class="text-rose-500">*</span></label>
                    <input list="vaksin-kia-list" name="vaksin" required placeholder="Contoh: BCG / DPT / TT" class="med-input">
                    <datalist id="vaksin-kia-list">
                        <option value="Hepatitis B (HB-0)">
                        <option value="BCG">
                        <option value="Polio 1 (OPV)">
                        <option value="DPT-HB-Hib 1 (Pentabio)">
                        <option value="PCV 1">
                        <option value="Campak-Rubella (MR) 1">
                        <option value="TT (Tetanus Toxoid)">
                    </datalist>
                </div>

                <div class="md:col-span-1">
                    <label class="med-label">Dosis Vaksinasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="dosis" required placeholder="Contoh: 0.5 ml / 2 Tetes" class="med-input">
                </div>

                <div class="md:col-span-2">
                    <label class="med-label">Observasi Klinis (KIPI)</label>
                    <textarea name="keterangan" rows="3" class="med-input resize-none bg-slate-50/50" placeholder="Catat keluhan pasca penyuntikan di sini. Kosongkan form ini jika tidak ada reaksi abnormal (demam/bengkak) pada pasien..."></textarea>
                </div>
            </div>
        </div>

        {{-- FOOTER SUBMIT --}}
        <div class="px-8 md:px-12 py-8 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6 shrink-0 relative z-0">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-cyan-500"></span>
                </span>
                <div>
                    <p class="text-[12px] font-black text-slate-700 uppercase tracking-widest leading-none mb-1">Koneksi Aman</p>
                    <p class="text-[10.5px] font-semibold text-slate-400">Data dienkripsi ke sistem EMR.</p>
                </div>
            </div>
            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-12 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-black text-[13px] uppercase tracking-widest rounded-[16px] hover:shadow-[0_15px_30px_rgba(6,182,212,0.4)] transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                <i class="fas fa-save text-[16px]"></i> SIMPAN KE REGISTER
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imunisasiWizard', (masterData) => ({
            master: masterData, 
            kategori: '', 
            query: '', 
            pasienId: '', 
            dropOpen: false,
            
            resetPencarian() { 
                this.pasienId = ''; 
                this.query = ''; 
                this.dropOpen = false; 
            },
            
            pilihPasien(p) { 
                this.pasienId = p.id; 
                this.query = p.nama; 
                this.dropOpen = false; 
            },
            
            results() {
                if(!this.kategori || !this.master[this.kategori]) return [];
                const q = this.query.toLowerCase();
                return this.master[this.kategori].filter(p => p.nama.toLowerCase().includes(q) || (p.nik && p.nik.includes(q))).slice(0, 10);
            }
        }));
    });

    document.getElementById('formImunisasi').addEventListener('submit', function(e) {
        if(!document.querySelector('input[name="pasien_id"]').value) {
            e.preventDefault();
            Swal.fire({ 
                icon: 'warning', 
                title: 'Data Belum Lengkap!', 
                text: 'Silakan cari dan pilih Identitas Warga pada Langkah 2 sebelum menyimpan rekam medis.', 
                confirmButtonText: 'MENGERTI',
                customClass: { 
                    popup: 'nexus-swal',
                    confirmButton: 'nexus-confirm'
                } 
            });
            return;
        }
        
        // Animasi Loading Submit
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin text-[16px]"></i> MENYIMPAN...';
        btn.classList.add('opacity-70', 'cursor-wait', 'scale-95');
    });
</script>
@endpush
@endsection