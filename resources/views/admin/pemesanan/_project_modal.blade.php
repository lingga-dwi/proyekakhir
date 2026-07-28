<div id="projectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="project-modal-title">
    <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 id="project-modal-title" class="text-lg font-semibold text-slate-950">Kelola Pesanan</h2>
                <p class="mt-1 text-xs text-slate-500">Perbarui status dan informasi pelaksanaan proyek.</p>
            </div>
            <button type="button" onclick="closeProjectModal()" class="flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>
        <form id="projectForm" method="POST" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Status</span>
                <select id="projectStatus" name="status_pemesanan" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                    <option value="pending">Pesanan baru</option>
                    <option value="dikonfirmasi">Dikonfirmasi</option>
                    <option value="sedang_dikerjakan">Sedang dikerjakan</option>
                    <option value="selesai">Selesai</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Progres (%)</span>
                <input id="projectProgress" name="progress" type="number" min="0" max="100" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Target selesai</span>
                <input id="projectTarget" name="target_selesai" type="date" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Desainer</span>
                <select id="projectDesigner" name="designer_id" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Belum ditetapkan</option>
                    @foreach($designers as $designer)<option value="{{ $designer->id }}">{{ $designer->nama }}</option>@endforeach
                </select>
            </label>
            <label class="block sm:col-span-2">
                <span class="text-sm font-medium text-slate-700">Budget</span>
                <input id="projectBudget" name="total_harga" type="number" min="0" step="1000" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
            </label>
            <label class="block sm:col-span-2">
                <span class="text-sm font-medium text-slate-700">Catatan progres</span>
                <textarea id="projectNote" name="catatan_progres" rows="4" maxlength="2000" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: Desain awal sedang ditinjau pelanggan"></textarea>
            </label>
            <div class="flex justify-end gap-3 sm:col-span-2">
                <button type="button" onclick="closeProjectModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
