@php
    $customerOptions = $customers->map(fn ($customer) => [
        'id' => (string) $customer->id,
        'name' => $customer->nama,
        'email' => $customer->email,
        'phone' => $customer->no_telp,
    ])->values();
@endphp

<div id="orderModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
    <div class="max-h-[92vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="order-modal-title" class="text-lg font-semibold text-slate-950">Tambah Pesanan</h2>
                <p class="mt-1 text-sm text-slate-500">Catat pesanan yang diterima di luar website.</p>
            </div>
            <button type="button" onclick="closeOrderModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        <form method="POST" action="{{ route('admin.pemesanan.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            @if($errors->manualOrder->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 sm:col-span-2" role="alert">
                    <p class="font-semibold">Data pesanan belum lengkap.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->manualOrder->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div
                class="relative sm:col-span-2"
                x-data="customerPicker(@js($customerOptions), @js(old('customer_mode', 'existing')), @js((string) old('id_user', '')))"
                @click.outside="open = false"
            >
                <label for="customer-search" class="text-sm font-medium text-slate-700">Pelanggan</label>
                <div class="relative mt-2">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                    <input
                        id="customer-search"
                        type="search"
                        x-model="query"
                        @focus="open = true"
                        @input="clearSelection(); open = true"
                        @keydown.escape="open = false"
                        autocomplete="off"
                        placeholder="Cari nama, email, atau nomor telepon"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-10 text-sm focus:border-amber-500 focus:ring-amber-500"
                        role="combobox"
                        :aria-expanded="open"
                        aria-controls="customer-results"
                    >
                    <button x-show="query" type="button" @click="reset()" class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100" aria-label="Kosongkan pelanggan">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <input type="hidden" name="customer_mode" :value="mode">
                <input type="hidden" name="id_user" :value="selectedId">

                <div id="customer-results" x-cloak x-show="open" x-transition class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                    <div class="max-h-52 overflow-y-auto p-1.5">
                        <template x-for="customer in filteredCustomers" :key="customer.id">
                            <button type="button" @click="selectCustomer(customer)" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left hover:bg-slate-50">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600" x-text="customer.name.charAt(0).toUpperCase()"></span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-slate-900" x-text="customer.name"></span>
                                    <span class="block truncate text-xs text-slate-500" x-text="customer.email || customer.phone"></span>
                                </span>
                            </button>
                        </template>
                        <p x-show="filteredCustomers.length === 0" class="px-3 py-3 text-center text-sm text-slate-500">Pelanggan tidak ditemukan.</p>
                    </div>
                    <div class="border-t border-slate-100 p-1.5">
                        <button type="button" @click="selectNewCustomer()" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left font-semibold text-amber-700 hover:bg-amber-50">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-100"><i class="fas fa-plus text-sm"></i></span>
                            <span class="text-sm">Tambah pelanggan baru</span>
                        </button>
                    </div>
                </div>

                <div x-cloak x-show="mode === 'new'" x-transition class="mt-4 grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Nama</span>
                        <input name="nama" value="{{ old('nama') }}" type="text" maxlength="255" :required="mode === 'new'" class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Nomor WhatsApp</span>
                        <input name="no_telp" value="{{ old('no_telp') }}" type="tel" maxlength="20" :required="mode === 'new'" class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Email</span>
                        <input name="email" value="{{ old('email') }}" type="email" maxlength="255" :required="mode === 'new'" class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">
                    </label>
                </div>
            </div>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Asal pesanan</span>
                <select name="sumber_masuk" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">
                    <option value="kantor" @selected(old('sumber_masuk', 'kantor') === 'kantor')>Datang ke kantor</option>
                    <option value="whatsapp" @selected(old('sumber_masuk') === 'whatsapp')>WhatsApp</option>
                    <option value="telepon" @selected(old('sumber_masuk') === 'telepon')>Telepon</option>
                    <option value="instagram" @selected(old('sumber_masuk') === 'instagram')>Instagram</option>
                    <option value="website" @selected(old('sumber_masuk') === 'website')>Website</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Tanggal masuk</span>
                <input name="tanggal_pesan" value="{{ old('tanggal_pesan', now()->toDateString()) }}" type="date" max="{{ now()->toDateString() }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Jenis proyek</span>
                <input name="jenis_proyek" value="{{ old('jenis_proyek') }}" type="text" list="project-types" maxlength="255" required placeholder="Pilih atau tulis jenis proyek" class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">
                <datalist id="project-types">
                    <option value="Kitchen Set"><option value="Kamar Tidur"><option value="Ruang Tamu"><option value="Ruang Kerja"><option value="Interior Hunian"><option value="Interior Komersial">
                </datalist>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Jenis bangunan <span class="font-normal text-slate-400">(opsional)</span></span>
                <input name="jenis_bangunan" value="{{ old('jenis_bangunan') }}" type="text" maxlength="255" placeholder="Contoh: Rumah tinggal" class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">
            </label>
            <label class="block sm:col-span-2">
                <span class="text-sm font-medium text-slate-700">Catatan kebutuhan <span class="font-normal text-slate-400">(opsional)</span></span>
                <textarea name="deskripsi_keinginan_desain" rows="2" maxlength="2000" placeholder="Ringkas kebutuhan atau hasil pembicaraan awal" class="mt-2 w-full rounded-xl border border-slate-300 bg-white focus:border-amber-500 focus:ring-amber-500">{{ old('deskripsi_keinginan_desain') }}</textarea>
            </label>
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 sm:col-span-2">
                <button type="button" onclick="closeOrderModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan Pesanan</button>
            </div>
        </form>
    </div>
</div>
