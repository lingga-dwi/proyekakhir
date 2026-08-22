<div id="projectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="project-modal-title">
    <div id="projectModalPanel" class="relative max-h-[94vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2">
            <div>
                <h2 id="project-modal-title" class="text-lg font-semibold text-slate-950"><span id="projectModalTitleText">Tinjau Penawaran</span> <span id="projectModalReferenceTitle" class="text-amber-700"></span></h2>
                <p id="projectModalSubtitle" class="mt-0.5 text-xs text-slate-500">Kelola tahap proses, penugasan desainer, serta desain &amp; RAB.</p>
            </div>
            <button type="button" onclick="closeProjectModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        {{-- Hidden form: submits Tahap/Desainer updates. Inputs live in the grid below and reference it via form="projectForm" to avoid nesting forms inside the upload/delete forms. --}}
        <form id="projectForm" method="POST">
            @csrf
            @method('PUT')
        </form>

        <div id="projectModalValidationView" class="mt-3 hidden">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr_1.4fr]">
                <section>
                    <h4 class="font-semibold text-slate-950">Dokumen dari Desainer</h4>
                    <div id="projectModalValidationDocuments" class="mt-3 space-y-2"></div>
                    <div id="projectModalValidationInfoBanner" class="mt-3 flex items-start gap-2 rounded-xl bg-blue-50 p-2.5 text-xs leading-5 text-blue-800">
                        <i class="fas fa-circle-info mt-0.5" aria-hidden="true"></i>
                        <span>Pastikan desain dan RAB sudah sesuai sebelum membuat penawaran.</span>
                    </div>
                </section>

                <section>
                    <h4 class="font-semibold text-slate-950">Penawaran</h4>

                    <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between px-3 py-2.5 text-sm">
                            <span class="text-slate-500">Total Sudah Ditagihkan</span>
                            <span id="projectModalValidationBilled" class="font-semibold text-slate-800">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between px-3 py-2.5 text-sm">
                            <span class="text-slate-500">Total Sudah Dibayar</span>
                            <span id="projectModalValidationPaid" class="font-semibold text-slate-800">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between px-3 py-2.5 text-sm">
                            <span class="text-slate-500">Sisa Pembayaran</span>
                            <span id="projectModalValidationRemaining" class="font-semibold text-slate-800">Rp 0</span>
                        </div>
                    </div>

                    <button type="button" onclick="openInvoiceModal('validation')" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-orange-300 px-4 py-2.5 text-sm font-semibold text-orange-600 transition hover:bg-orange-50">
                        <i class="fas fa-plus" aria-hidden="true"></i> Buat Tagihan
                    </button>
                    <p class="mt-1.5 text-xs text-slate-400">Buat tagihan baru secara fleksibel sesuai progres proyek dan kesepakatan.</p>
                </section>

                <section>
                    <h4 class="font-semibold text-slate-950">Riwayat Tagihan</h4>
                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full min-w-125 text-left text-xs">
                            <thead class="bg-slate-50 uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 font-medium">Nama Tagihan</th>
                                    <th class="px-3 py-2 font-medium">Nominal</th>
                                    <th class="px-3 py-2 font-medium">Status</th>
                                    <th class="px-3 py-2 font-medium">Bukti Pembayaran</th>
                                    <th class="px-3 py-2 font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="projectModalValidationInvoiceList" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </section>
            </div>

            <textarea id="projectModalRevisionFeedback" rows="2" maxlength="2000" class="mt-4 hidden w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Jelaskan apa yang perlu diperbaiki desainer..."></textarea>

            <div id="projectModalValidationFooter" class="mt-4 hidden flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                <button type="button" id="projectModalRevisionBtn" onclick="requestProjectValidationRevision()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    <i class="fas fa-comment-dots" aria-hidden="true"></i> Minta Revisi
                </button>
                <button type="button" id="projectModalValidateBtn" onclick="confirmValidateProjectDraft()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i> Validasi &amp; Kirim
                </button>
            </div>
        </div>

        <div id="projectModalStandardView" class="mt-3">
            <section class="rounded-2xl border border-slate-200 p-4">
                <form id="projectCompleteConsultationForm" method="POST" action="" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-2.5">
                    @csrf
                    <p class="text-sm font-semibold text-slate-900">Selesaikan Konsultasi</p>
                    <p class="mt-0.5 text-xs text-slate-600">Ringkas hasil konsultasi. Setelah disimpan, tahap otomatis pindah ke Menunggu Desain Awal &amp; Draft RAB.</p>
                    <textarea id="projectConsultationResult" name="consultation_result" rows="2" maxlength="5000" required class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: kebutuhan ruang, ukuran, preferensi, dan keputusan konsultasi."></textarea>
                    <button type="submit" class="mt-1.5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-check" aria-hidden="true"></i> Selesaikan Konsultasi
                    </button>
                </form>

                <div class="mt-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                    <button type="button" id="projectModalHistoryToggle" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800">
                        <i class="fas fa-clock-rotate-left text-[10px]" aria-hidden="true"></i> Riwayat Pengiriman
                    </button>
                </div>
                <div id="projectModalHistoryPanel" class="mt-2 hidden max-h-32 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3"></div>
            </section>
        </div>
    </div>
</div>

<div id="invoiceModal" class="fixed inset-0 z-70 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="invoice-modal-title">
    <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <h2 id="invoice-modal-title" class="text-lg font-semibold text-slate-950">Form Tagihan Baru</h2>
            <button type="button" onclick="closeInvoiceModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        <div class="mt-4 space-y-4">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Nama Tagihan</span>
                <input type="text" id="invoiceModalName" maxlength="255" placeholder="Termin 2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Nominal</span>
                <div class="mt-1.5 flex items-center rounded-xl border border-slate-300 focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500">
                    <span class="pl-3 text-sm text-slate-500">Rp</span>
                    <input type="text" inputmode="numeric" id="invoiceModalAmount" oninput="formatRupiahInput(this)" placeholder="0" class="w-full border-0 bg-transparent py-2.5 pl-1 pr-3 text-sm focus:ring-0">
                </div>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Jatuh Tempo</span>
                <input type="date" id="invoiceModalDueDate" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Metode Pembayaran</span>
                <select disabled class="mt-1.5 w-full appearance-none rounded-xl border-slate-300 bg-slate-50 text-sm text-slate-600">
                    <option>Transfer Bank</option>
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Bank Tujuan</span>
                <select disabled id="invoiceModalBankSelect" class="mt-1.5 w-full appearance-none rounded-xl border-slate-300 bg-slate-50 text-sm text-slate-600"></select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Nomor Rekening</span>
                <input type="text" readonly id="invoiceModalAccountNumber" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50 text-sm text-slate-700">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Atas Nama</span>
                <input type="text" readonly id="invoiceModalAccountHolder" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50 text-sm text-slate-700">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Keterangan (opsional)</span>
                <textarea id="invoiceModalNote" rows="3" maxlength="2000" placeholder="Contoh: Detail pembayaran, catatan tambahan untuk pelanggan, dll." class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
            </label>

            <div class="flex items-start gap-2 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                <i class="fas fa-circle-info mt-0.5 text-slate-400" aria-hidden="true"></i>
                <span>Tagihan akan ditambahkan ke riwayat tagihan dan dapat dikirim ke pelanggan setelah penawaran divalidasi.</span>
            </div>

            <p id="invoiceModalError" class="hidden text-xs text-red-600"></p>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <button type="button" onclick="closeInvoiceModal()" class="rounded-xl border border-orange-200 px-4 py-2.5 text-sm font-semibold text-orange-600 transition hover:bg-orange-50">Batal</button>
            <button type="button" id="invoiceModalSubmitBtn" onclick="submitInvoiceModal()" class="rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600">Tambahkan Tagihan</button>
        </div>
    </div>
</div>

<div id="orderReviewModal" class="fixed inset-0 z-70 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="order-review-modal-title">
    <form id="orderReviewCsrfForm">@csrf</form>
    <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <h2 id="order-review-modal-title" class="text-lg font-semibold text-slate-950">Tinjau Pemesanan <span id="orderReviewReference" class="text-amber-700"></span></h2>
            <button type="button" onclick="closeOrderReviewModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        <div class="mt-4 space-y-4">
            <div>
                <span class="text-sm font-medium text-slate-700">Tahap Saat Ini</span>
                <div class="mt-1.5 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                    <span id="orderReviewStageLabel" class="text-sm font-semibold text-slate-800"></span>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Tahap berubah otomatis berdasarkan aktivitas pelanggan, desainer, dan admin.</p>
            </div>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Penanggung Jawab</span>
                <select id="orderReviewDesigner" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Belum ditetapkan</option>
                    @foreach($designers as $designer)<option value="{{ $designer->id }}">{{ $designer->nama }}</option>@endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Target Selesai</span>
                <input type="date" id="orderReviewTargetSelesai" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                <p class="mt-1 text-[11px] text-slate-400">Dipakai untuk kartu "Deadline ≤ 7 Hari" di dashboard.</p>
            </label>

            <input type="hidden" id="orderReviewStatus">

            <div id="orderReviewFinalizeField" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                <span class="text-sm font-medium text-slate-700">Status Produksi</span>
                <p class="mt-1 text-[11px] text-slate-400">Desain final sudah disetujui pelanggan dan pengerjaan sedang berjalan. Tandai selesai setelah produksi rampung.</p>
                <button type="button" id="orderReviewCompleteBtn" onclick="setOrderReviewStatus('selesai')" class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300">
                    <i class="fas fa-circle-check" aria-hidden="true"></i> Selesaikan Proyek
                </button>
            </div>

            <p id="orderReviewError" class="hidden text-xs text-red-600"></p>
        </div>

        <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <button type="button" onclick="confirmDeleteOrderReview()" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                <i class="fas fa-trash-can" aria-hidden="true"></i> Hapus
            </button>
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeOrderReviewModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Tutup</button>
                <button type="button" id="orderReviewSaveBtn" onclick="saveOrderReview()" class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
