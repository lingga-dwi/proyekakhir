<div id="reviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="review-modal-title">
    <form id="reviewCsrfForm">@csrf</form>
    <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl sm:p-7">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
            <div>
                <h2 id="review-modal-title" class="text-lg font-semibold text-slate-950">Tinjau Pesanan <span id="reviewModalReference" class="text-amber-700"></span></h2>
                <p id="reviewModalSubtitle" class="mt-0.5 text-xs text-slate-500"></p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <section>
                <h3 class="text-sm font-semibold text-slate-900">Ringkasan Pesanan</h3>
                <dl class="mt-3 space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fas fa-id-badge" aria-hidden="true"></i></span>
                        <div><dt class="text-xs text-slate-400">Referensi</dt><dd id="reviewModalReferenceValue" class="font-semibold text-slate-900"></dd></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fas fa-building" aria-hidden="true"></i></span>
                        <div><dt class="text-xs text-slate-400">Jenis Proyek</dt><dd id="reviewModalTitle" class="font-semibold text-slate-900"></dd></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fas fa-house" aria-hidden="true"></i></span>
                        <div><dt class="text-xs text-slate-400">Jenis Bangunan</dt><dd id="reviewModalBuilding" class="font-semibold text-slate-900"></dd></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fas fa-ruler-combined" aria-hidden="true"></i></span>
                        <div><dt class="text-xs text-slate-400">Luas Area</dt><dd id="reviewModalArea" class="font-semibold text-slate-900"></dd></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fas fa-coins" aria-hidden="true"></i></span>
                        <div><dt class="text-xs text-slate-400">Kisaran Anggaran</dt><dd id="reviewModalBudget" class="font-semibold text-slate-900"></dd></div>
                    </div>
                </dl>

                <div id="reviewModalNoteSection" class="mt-5 hidden">
                    <h3 class="text-sm font-semibold text-slate-900">Catatan Kebutuhan</h3>
                    <div class="mt-2 flex items-start gap-3 rounded-xl bg-slate-50 p-4">
                        <i class="fas fa-clipboard-list mt-0.5 text-slate-400" aria-hidden="true"></i>
                        <p id="reviewModalNote" class="text-sm leading-6 text-slate-700"></p>
                    </div>
                </div>
            </section>

            <section>
                <h3 class="text-sm font-semibold text-slate-900">Dokumen dari Daiku</h3>
                <div id="reviewModalDocuments" class="mt-3 grid grid-cols-2 gap-3"></div>

                <div class="mt-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <i class="fas fa-circle-info mt-0.5 text-amber-600" aria-hidden="true"></i>
                    <p id="reviewModalInfoText" class="text-sm leading-6 text-amber-900"></p>
                </div>
            </section>
        </div>

        <div id="reviewModalRevisionPanel" class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 p-4">
            <label class="block text-sm font-semibold text-slate-800" for="reviewModalFeedback">Catatan revisi</label>
            <textarea id="reviewModalFeedback" rows="3" maxlength="2000" required class="mt-2 w-full rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Jelaskan bagian yang perlu diperbaiki..."></textarea>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <p id="reviewModalToast" class="hidden rounded-lg px-3 py-1.5 text-xs font-semibold"></p>
            <div class="ml-auto flex flex-wrap justify-end gap-3">
                <button type="button" id="reviewModalCloseBtn" onclick="closeReviewModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Tutup</button>
                <button type="button" id="reviewModalRevisionBtn" onclick="showRevisionPanel()" class="rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-amber-800 hover:bg-amber-50">Minta Revisi</button>
                <button type="button" id="reviewModalCancelRevisionBtn" onclick="hideRevisionPanel()" class="hidden rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="button" id="reviewModalSubmitRevisionBtn" onclick="submitReviewDecision('revision_requested')" class="hidden rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">Kirim Permintaan Revisi</button>
                <button type="button" id="reviewModalApproveBtn" onclick="submitReviewDecision('approved')" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Setujui</button>
            </div>
        </div>
    </div>
</div>
