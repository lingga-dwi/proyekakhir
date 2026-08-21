<div id="reviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="review-modal-title">
    <form id="reviewCsrfForm">@csrf</form>
    <div class="max-h-[95vh] w-full max-w-6xl overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
            <div>
                <h2 id="review-modal-title" class="text-lg font-semibold text-slate-950">Tinjau Penawaran <span id="reviewModalReference" class="text-amber-700"></span></h2>
                <p id="reviewModalSubtitle" class="mt-0.5 text-xs text-slate-500"></p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        <div id="reviewModalEmptyState" class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-6 text-center">
            <i class="fas fa-file-circle-question text-2xl text-slate-300" aria-hidden="true"></i>
            <p class="mt-2 text-sm font-semibold text-slate-700">Belum ada penawaran</p>
            <p class="mt-1 text-xs text-slate-500">Permintaan konsultasi ini belum diproses menjadi proyek dengan penawaran. Anda akan menerima notifikasi begitu penawaran tersedia.</p>
        </div>

        <div id="reviewModalContent" class="mt-4 grid gap-6 lg:grid-cols-3">
            <section>
                <h3 class="font-semibold text-slate-950">Dokumen dari Desainer</h3>
                <div id="reviewModalDocuments" class="mt-3 space-y-2 [&>p.uppercase:not(:first-child)]:pt-2"></div>

                <div id="reviewModalStatusBox" class="mt-3 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Tahap Saat Ini</p>
                    <p id="reviewModalStageLabel" class="mt-1 text-sm font-semibold text-slate-900"></p>
                    <p id="reviewModalProgressNote" class="mt-1 text-xs leading-5 text-slate-600"></p>
                </div>

                <div id="reviewModalDocumentsNotice" class="mt-3 flex items-start gap-2 rounded-xl bg-blue-50 p-2.5 text-xs leading-5 text-blue-800">
                    <i class="fas fa-circle-info mt-0.5" aria-hidden="true"></i>
                    <span id="reviewModalDocumentsNoticeText">Pastikan desain dan RAB sudah sesuai dengan kebutuhan Anda sebelum menyetujui penawaran.</span>
                </div>
            </section>

            <section>
                <h3 class="font-semibold text-slate-950">Penawaran</h3>
                <p class="mt-3 text-sm font-medium text-slate-700">Nilai Penawaran</p>
                <p id="reviewModalTotalHarga" class="text-2xl font-bold text-slate-950">Rp 0</p>
                <p class="mt-1 text-xs text-slate-400">Total nilai proyek berdasarkan desain dan RAB.</p>

                <div class="mt-4 rounded-xl border border-slate-200 p-3">
                    <p class="text-sm font-semibold text-slate-900">Ringkasan Pesanan</p>
                    <dl class="mt-2 space-y-1.5 text-sm">
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Referensi</dt><dd id="reviewModalReferenceValue" class="font-semibold text-slate-800"></dd></div>
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Jenis Proyek</dt><dd id="reviewModalTitle" class="font-semibold text-slate-800"></dd></div>
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Jenis Bangunan</dt><dd id="reviewModalBuilding" class="font-semibold text-slate-800"></dd></div>
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Luas Area</dt><dd id="reviewModalArea" class="font-semibold text-slate-800"></dd></div>
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Kisaran Anggaran</dt><dd id="reviewModalBudget" class="font-semibold text-slate-800"></dd></div>
                    </dl>
                </div>
            </section>

            <section>
                <h3 class="font-semibold text-slate-950">Tagihan yang Dibuat Admin</h3>
                <div id="reviewModalInvoiceList" class="mt-3 space-y-3"></div>
            </section>
        </div>

        <input type="file" id="reviewModalInvoiceUploadInput" accept=".jpg,.jpeg,.png,.webp,.pdf" class="hidden">

        <div id="reviewModalRevisionPanel" class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 p-4">
            <label class="block text-sm font-semibold text-slate-800" for="reviewModalFeedback">Catatan revisi</label>
            <textarea id="reviewModalFeedback" rows="3" maxlength="2000" required class="mt-2 w-full rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Jelaskan bagian yang perlu diperbaiki..."></textarea>
        </div>

        <div id="reviewModalApprovalNotice" class="mt-4 flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-900">
            <i class="fas fa-triangle-exclamation mt-0.5" aria-hidden="true"></i>
            <span>Dengan menyetujui penawaran ini, Anda menyetujui nilai penawaran serta ketentuan yang berlaku.</span>
        </div>

        <div id="reviewModalApprovalFooter" class="mt-4 flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
            <div class="flex flex-wrap justify-end gap-3">
                <button type="button" id="reviewModalCloseBtn" onclick="closeReviewModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Tutup</button>
                <button type="button" id="reviewModalRevisionBtn" onclick="showRevisionPanel()" class="rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-amber-800 hover:bg-amber-50">Minta Revisi</button>
                <button type="button" id="reviewModalCancelRevisionBtn" onclick="hideRevisionPanel()" class="hidden rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="button" id="reviewModalSubmitRevisionBtn" onclick="submitReviewDecision('revision_requested')" class="hidden rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">Kirim Permintaan Revisi</button>
                <button type="button" id="reviewModalApproveBtn" onclick="confirmApproveReviewDecision()" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                    <i class="fas fa-check" aria-hidden="true"></i> Setujui
                </button>
            </div>
        </div>

        <div id="reviewModalStatusFooter" class="mt-4 hidden items-center justify-end gap-3 border-t border-slate-100 pt-4">
            <button type="button" onclick="closeReviewModal()" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Tutup</button>
        </div>
    </div>
</div>
