@extends('layouts.main')

@section('title', 'Buat Permintaan Konsultasi')

@section('content')
<div class="min-h-screen bg-[#fff9f0] py-8 sm:py-12">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <form action="{{ route('konsultasi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-7">
            @csrf

            <section class="rounded-xl bg-white p-5 shadow-sm sm:p-8">
                <div class="mb-7 flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-slate-800">Informasi Pelanggan</h1>
                        <p class="mt-1 text-sm text-slate-400">Masukan Data anda.</p>
                    </div>
                    <span class="text-sm text-slate-400">Step 1 of 3</span>
                </div>

                <div class="grid gap-x-7 gap-y-5 md:grid-cols-2">
                    @include('konsultasi.partials.request-input', ['name' => 'nama', 'label' => 'Nama', 'value' => old('nama', auth()->user()->nama), 'required' => true, 'placeholder' => 'Nama'])
                    @include('konsultasi.partials.request-input', ['name' => 'no_telp', 'label' => 'No. Whatsapp', 'value' => old('no_telp', auth()->user()->no_telp), 'required' => true, 'placeholder' => 'No. HP', 'type' => 'tel'])
                    @include('konsultasi.partials.request-input', ['name' => 'alamat', 'label' => 'Alamat', 'value' => old('alamat', auth()->user()->alamat), 'required' => true, 'placeholder' => 'Alamat'])
                    @include('konsultasi.partials.request-input', ['name' => 'email', 'label' => 'Email (opsional)', 'value' => old('email', auth()->user()->email), 'placeholder' => 'Email', 'type' => 'email'])
                </div>
            </section>

            <section class="rounded-xl bg-white p-5 shadow-sm sm:p-8">
                <div class="mb-7 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Detail Proyek</h2>
                        <p class="mt-1 text-sm text-slate-400">Masukkan informasi Proyek</p>
                    </div>
                    <span class="text-sm text-slate-400">Step 2 of 3</span>
                </div>

                <div class="grid gap-x-7 gap-y-5 md:grid-cols-2">
                    <div>
                        <label for="jenis_konsultasi" class="mb-2 block text-sm font-semibold text-slate-800">Jenis Proyek</label>
                        <select id="jenis_konsultasi" name="jenis_konsultasi" required class="request-control">
                            <option value="">Pilih Jenis Proyek</option>
                            <option value="free_consultation" @selected(old('jenis_konsultasi') === 'free_consultation')>Desain Interior Baru</option>
                            <option value="virtual_design" @selected(old('jenis_konsultasi') === 'virtual_design')>Renovasi Interior</option>
                            <option value="in_home_visit" @selected(old('jenis_konsultasi') === 'in_home_visit')>Custom Furniture</option>
                        </select>
                        @error('jenis_konsultasi') <p class="request-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jenis_ruangan" class="mb-2 block text-sm font-semibold text-slate-800">Jenis Bangunan</label>
                        <select id="jenis_ruangan" name="jenis_ruangan" required class="request-control">
                            <option value="">Pilih Jenis Bangunan</option>
                            <option value="living_room" @selected(old('jenis_ruangan') === 'living_room')>Rumah Tinggal</option>
                            <option value="bedroom" @selected(old('jenis_ruangan') === 'bedroom')>Apartemen</option>
                            <option value="kitchen" @selected(old('jenis_ruangan') === 'kitchen')>Ruko</option>
                            <option value="bathroom" @selected(old('jenis_ruangan') === 'bathroom')>Kantor</option>
                            <option value="office" @selected(old('jenis_ruangan') === 'office')>Kafe / Restoran</option>
                            <option value="whole_house" @selected(old('jenis_ruangan') === 'whole_house')>Lainnya</option>
                        </select>
                        @error('jenis_ruangan') <p class="request-error">{{ $message }}</p> @enderror
                    </div>
                    @include('konsultasi.partials.request-input', ['name' => 'luas_ruangan', 'label' => 'Luas Area (m2)', 'value' => old('luas_ruangan'), 'required' => true, 'placeholder' => 'Contoh: 20', 'type' => 'number', 'min' => '1', 'step' => '0.01'])
                    <div>
                        <label for="budget_range" class="mb-2 block text-sm font-semibold text-slate-800">Anggaran</label>
                        <select id="budget_range" name="budget_range" required class="request-control">
                            <option value="">Pilih Anggaran</option>
                            <option value="under_10m" @selected(old('budget_range') === 'under_10m')>Di bawah Rp10 Juta</option>
                            <option value="10m_25m" @selected(old('budget_range') === '10m_25m')>Rp10 – 25 Juta</option>
                            <option value="25m_50m" @selected(old('budget_range') === '25m_50m')>Rp25 – 50 Juta</option>
                            <option value="50m_100m" @selected(old('budget_range') === '50m_100m')>Rp50 – 100 Juta</option>
                            <option value="above_100m" @selected(old('budget_range') === 'above_100m')>Di atas Rp100 Juta</option>
                        </select>
                        @error('budget_range') <p class="request-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-xl bg-white p-5 shadow-sm sm:p-8">
                <div class="mb-7 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Catatan Konsultasi</h2>
                        <p class="mt-1 text-sm text-slate-400">Ceritakan kebutuhan anda disini jika diperlukan</p>
                    </div>
                    <span class="text-sm text-slate-400">Step 3 of 3</span>
                </div>
                <textarea id="deskripsi_kebutuhan" name="deskripsi_kebutuhan" rows="7" class="request-control resize-y" placeholder="Ceritakan detail keinginan Anda untuk desain ruangan...">{{ old('deskripsi_kebutuhan') }}</textarea>
                @error('deskripsi_kebutuhan') <p class="request-error">{{ $message }}</p> @enderror

                <label for="attachments" class="mt-5 block text-sm font-semibold text-slate-800">Denah, foto ruang, atau referensi (opsional)</label>
                <input id="attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.pdf" class="mt-2 block w-full rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-slate-700">
                <p class="mt-2 text-xs text-slate-500">Maksimal 5 file, masing-masing 5 MB. JPG, PNG, WEBP, atau PDF.</p>
                @error('attachments') <p class="request-error">{{ $message }}</p> @enderror

                <button type="submit" class="mt-4 rounded-lg bg-[#edb925] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#d9a810] focus:outline-none focus:ring-4 focus:ring-amber-200">
                    Kirim Permintaan Pemesanan
                </button>
            </section>
        </form>
    </div>
</div>

@push('styles')
<style>
    .request-control { width: 100%; border: 1px solid transparent; border-radius: .6rem; background: #ffedc9; padding: .85rem 1rem; color: #334155; outline: none; }
    .request-control::placeholder { color: #94a3b8; }
    .request-control:focus { border-color: #edb925; box-shadow: 0 0 0 3px rgb(237 185 37 / .18); }
    .request-error { margin-top: .4rem; font-size: .875rem; color: #dc2626; }
</style>
@endpush
@endsection
