<div id="historyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="history-modal-title">
    <div class="relative max-h-[85vh] w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 id="history-modal-title" class="text-lg font-semibold text-slate-950">Riwayat Tahapan <span id="historyModalReference" class="text-amber-700"></span></h2>
                <p class="mt-0.5 text-xs text-slate-500">Seluruh perkembangan pesanan Anda tercatat di sini.</p>
            </div>
            <button type="button" onclick="closeHistoryModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        <div id="historyModalList" class="max-h-[65vh] space-y-3 overflow-y-auto p-5 sm:p-6"></div>
    </div>
</div>
