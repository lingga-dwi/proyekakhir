<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="detail-modal-title">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <p id="detailModalType" class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700"></p>
                <h2 id="detail-modal-title" class="mt-1 text-xl font-semibold text-slate-950"></h2>
                <p id="detailModalDate" class="mt-1 text-sm text-slate-500"></p>
            </div>
            <div class="flex items-center gap-3">
                <span id="detailModalStatus" class="rounded-full px-3 py-1 text-xs font-semibold"></span>
                <button type="button" onclick="closeDetailModal()" class="flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <section class="rounded-xl bg-slate-50 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Informasi pelanggan</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Nama</dt><dd id="detailModalCustomer" class="mt-0.5 font-medium text-slate-800"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Kontak</dt><dd id="detailModalContact" class="mt-0.5 text-slate-700"></dd></div>
                </dl>
            </section>
            <section class="rounded-xl bg-slate-50 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Ringkasan kebutuhan</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Jenis / bangunan</dt><dd id="detailModalNeed" class="mt-0.5 font-medium text-slate-800"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Sumber</dt><dd id="detailModalSource" class="mt-0.5 text-slate-700"></dd></div>
                </dl>
            </section>
        </div>

        <section class="mt-5 rounded-xl border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-900">Catatan kebutuhan</h3>
            <p id="detailModalDescription" class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600"></p>
        </section>

        <section id="detailModalProject" class="mt-5 hidden rounded-xl border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-900">Informasi proyek</h3>
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-xs uppercase tracking-wide text-slate-400">Progres</dt><dd id="detailModalProgress" class="mt-0.5 font-semibold text-slate-800"></dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-slate-400">Desainer</dt><dd id="detailModalDesigner" class="mt-0.5 text-slate-700"></dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-slate-400">Target selesai</dt><dd id="detailModalTarget" class="mt-0.5 text-slate-700"></dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-slate-400">Anggaran</dt><dd id="detailModalBudget" class="mt-0.5 text-slate-700"></dd></div>
            </dl>
        </section>

        <section id="detailModalNoteSection" class="mt-5 hidden rounded-xl bg-amber-50 p-4">
            <h3 class="text-sm font-semibold text-slate-900">Catatan internal</h3>
            <p id="detailModalNote" class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700"></p>
        </section>

        <div class="mt-6 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Tutup</button>
        </div>
    </div>
</div>
