@extends('layouts.main')

@section('title', 'Buat Pesanan - Daiku Interior')

@section('content')
<div class="min-h-screen bg-yellow-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 mx-auto mb-4">
        </div>
        
        <!-- Multi-step Form -->
        <div class="bg-white rounded-lg shadow-lg p-8" x-data="{ step: 1, totalSteps: 4 }">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm text-gray-600">Step <span x-text="step"></span> of <span x-text="totalSteps"></span></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full transition-all duration-500" 
                         :style="`width: ${(step / totalSteps) * 100}%`"></div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('pemesanan.store') }}" enctype="multipart/form-data">
                @csrf
                @if($katalog)
                    <input type="hidden" name="katalog_id" value="{{ $katalog->id }}">
                @endif
                
                <!-- Step 1: Informasi Pelanggan -->
                <div x-show="step === 1" x-transition>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Informasi Pelanggan</h2>
                    <p class="text-gray-600 mb-6">Masukan Data anda</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                            <input type="text" 
                                   name="nama" 
                                   value="{{ old('nama', auth()->user()->nama) }}"
                                   placeholder="Nama" 
                                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('nama') border-red-500 @enderror"
                                   required>
                            @error('nama')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                            <input type="text" 
                                   name="no_hp" 
                                   value="{{ old('no_hp', auth()->user()->no_telp) }}"
                                   placeholder="No. HP" 
                                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('no_hp') border-red-500 @enderror"
                                   required>
                            @error('no_hp')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea name="alamat" 
                                      placeholder="Alamat"
                                      rows="3"
                                      class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('alamat') border-red-500 @enderror"
                                      required>{{ old('alamat', auth()->user()->alamat) }}</textarea>
                            @error('alamat')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" 
                                   name="email" 
                                   value="{{ old('email', auth()->user()->email) }}"
                                   placeholder="Email" 
                                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('email') border-red-500 @enderror"
                                   required>
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Detail Proyek -->
                <div x-show="step === 2" x-transition>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Detail Proyek</h2>
                    <p class="text-gray-600 mb-6">Masukan informasi Proyek</p>
                    
                    <div class="space-y-6">
                        <!-- Penggunaan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-circle mr-2"></i>Penggunaan
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Proyek</label>
                                    <select name="jenis_proyek" 
                                            class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('jenis_proyek') border-red-500 @enderror"
                                            required>
                                        <option value="">Pilih Jenis Proyek</option>
                                        <option value="renovasi" {{ old('jenis_proyek') == 'renovasi' ? 'selected' : '' }}>Renovasi</option>
                                        <option value="bangunan_baru" {{ old('jenis_proyek') == 'bangunan_baru' ? 'selected' : '' }}>Bangunan Baru</option>
                                        <option value="interior_saja" {{ old('jenis_proyek') == 'interior_saja' ? 'selected' : '' }}>Interior Saja</option>
                                    </select>
                                    @error('jenis_proyek')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Bangunan</label>
                                    <select name="jenis_bangunan" 
                                            class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('jenis_bangunan') border-red-500 @enderror"
                                            required>
                                        <option value="">Pilih Jenis Bangunan</option>
                                        <option value="rumah_tinggal" {{ old('jenis_bangunan') == 'rumah_tinggal' ? 'selected' : '' }}>Rumah Tinggal</option>
                                        <option value="apartemen" {{ old('jenis_bangunan') == 'apartemen' ? 'selected' : '' }}>Apartemen</option>
                                        <option value="kantor" {{ old('jenis_bangunan') == 'kantor' ? 'selected' : '' }}>Kantor</option>
                                        <option value="toko" {{ old('jenis_bangunan') == 'toko' ? 'selected' : '' }}>Toko</option>
                                    </select>
                                    @error('jenis_bangunan')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Luas Area (m2)</label>
                                <input type="number" 
                                       name="luas_area" 
                                       value="{{ old('luas_area') }}"
                                       placeholder="0" 
                                       min="1"
                                       class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('luas_area') border-red-500 @enderror"
                                       required>
                                @error('luas_area')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Ruangan</label>
                                <select name="jumlah_ruangan" 
                                        class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('jumlah_ruangan') border-red-500 @enderror"
                                        required>
                                    <option value="">Pilih Tanggal</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('jumlah_ruangan') == $i ? 'selected' : '' }}>{{ $i }} Ruangan</option>
                                    @endfor
                                    <option value="10+" {{ old('jumlah_ruangan') == '10+' ? 'selected' : '' }}>Lebih dari 10</option>
                                </select>
                                @error('jumlah_ruangan')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Preferensi Desain -->
                <div x-show="step === 3" x-transition>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Preferensi Desain</h2>
                    <p class="text-gray-600 mb-6">Gaya Desain Preferensi</p>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gaya Desain Preferensi</label>
                            <select name="gaya_desain_preferensi" 
                                    class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('gaya_desain_preferensi') border-red-500 @enderror"
                                    required>
                                <option value="">Pilih Gaya Desain</option>
                                <option value="modern" {{ old('gaya_desain_preferensi') == 'modern' ? 'selected' : '' }}>Modern</option>
                                <option value="minimalis" {{ old('gaya_desain_preferensi') == 'minimalis' ? 'selected' : '' }}>Minimalis</option>
                                <option value="klasik" {{ old('gaya_desain_preferensi') == 'klasik' ? 'selected' : '' }}>Klasik</option>
                                <option value="industrial" {{ old('gaya_desain_preferensi') == 'industrial' ? 'selected' : '' }}>Industrial</option>
                                <option value="skandinavia" {{ old('gaya_desain_preferensi') == 'skandinavia' ? 'selected' : '' }}>Skandinavia</option>
                            </select>
                            @error('gaya_desain_preferensi')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Warna Dominan</label>
                            <input type="text" 
                                   name="warna_dominan" 
                                   value="{{ old('warna_dominan') }}"
                                   placeholder="Contoh: Putih, Abu-abu, Biru" 
                                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('warna_dominan') border-red-500 @enderror"
                                   required>
                            @error('warna_dominan')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Keinginan Desain</label>
                            <textarea name="deskripsi_keinginan_desain" 
                                      placeholder="Ceritakan keinginan Anda untuk desain ruangan..."
                                      rows="4"
                                      class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('deskripsi_keinginan_desain') border-red-500 @enderror"
                                      required>{{ old('deskripsi_keinginan_desain') }}</textarea>
                            @error('deskripsi_keinginan_desain')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Denah/Foto Ruangan/Referensi Desain</label>
                            <div class="border-2 border-dashed border-yellow-300 rounded-lg p-6 text-center bg-yellow-50">
                                <input type="file" 
                                       name="upload_denah_foto[]" 
                                       multiple
                                       accept=".jpg,.jpeg,.png,.pdf"
                                       class="hidden" 
                                       id="file-upload">
                                <label for="file-upload" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-yellow-500 mb-4"></i>
                                    <p class="text-gray-600 mb-2">Klik untuk upload file atau drag & drop</p>
                                    <p class="text-sm text-gray-500">Format: JPG, PNG, PDF (Max: 5MB per file)</p>
                                </label>
                            </div>
                            @error('upload_denah_foto.*')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Konfirmasi -->
                <div x-show="step === 4" x-transition>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Confirmation</h2>
                    <p class="text-gray-600 mb-6">We are getting to the end. Just few clicks and your rental is ready!</p>
                    
                    <div class="bg-yellow-50 p-6 rounded-lg mb-6">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" 
                                   id="agreement" 
                                   class="w-4 h-4 text-yellow-500 border-gray-300 rounded focus:ring-yellow-500"
                                   required>
                            <label for="agreement" class="ml-3 text-sm text-gray-600">
                                I agree with our terms and conditions and privacy policy.
                            </label>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-8">
                    <button type="button" 
                            @click="step > 1 ? step-- : null"
                            :class="step === 1 ? 'invisible' : ''"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Previous
                    </button>
                    
                    <div x-show="step < totalSteps">
                        <button type="button" 
                                @click="step++"
                                class="px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
                            Next<i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                    
                    <div x-show="step === totalSteps">
                        <button type="submit" 
                                class="px-8 py-3 bg-yellow-500 text-white rounded-lg font-semibold hover:bg-yellow-600 transition duration-200">
                            Kirim Permintaan Pemesanan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// File upload preview
document.getElementById('file-upload').addEventListener('change', function(e) {
    const files = e.target.files;
    const label = e.target.nextElementSibling;
    
    if (files.length > 0) {
        label.innerHTML = `
            <i class="fas fa-check text-green-500 text-2xl mb-2"></i>
            <p class="text-green-600">${files.length} file(s) selected</p>
        `;
    }
});
</script>
@endpush
