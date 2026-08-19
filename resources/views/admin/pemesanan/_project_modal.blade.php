<div id="projectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="project-modal-title">
    <div class="relative max-h-[88vh] w-full max-w-[60rem] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl sm:p-7">
        <div id="projectModalToast" class="absolute left-1/2 top-4 z-10 hidden w-[min(90%,26rem)] -translate-x-1/2 items-center justify-center rounded-xl px-5 py-3 text-center text-sm font-semibold shadow-lg"></div>

        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
            <div>
                <h2 id="project-modal-title" class="text-lg font-semibold text-slate-950">Kelola Permintaan <span id="projectModalReferenceTitle" class="text-amber-700"></span></h2>
                <p class="mt-0.5 text-xs text-slate-500">Kelola tahap proses, penugasan desainer, serta desain &amp; RAB.</p>
            </div>
            <button type="button" onclick="closeProjectModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>

        {{-- Hidden form: submits Tahap/Desainer updates. Inputs live in the grid below and reference it via form="projectForm" to avoid nesting forms inside the upload/delete forms. --}}
        <form id="projectForm" method="POST">
            @csrf
            @method('PUT')
        </form>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900">1. Informasi dari Pelanggan</h3>
                <p id="projectModalReference" class="mt-2 text-xs font-bold uppercase tracking-[0.16em] text-amber-700"></p>

                <h4 class="mt-5 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fas fa-user-circle text-amber-600" aria-hidden="true"></i> Informasi Pelanggan
                </h4>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Nama</dt><dd id="projectModalCustomer" class="mt-1 font-medium text-slate-800"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">No. WhatsApp</dt><dd id="projectModalPhone" class="mt-1 text-slate-700"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Email (opsional)</dt><dd id="projectModalEmail" class="mt-1 break-words text-slate-700"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Alamat</dt><dd id="projectModalAddress" class="mt-1 text-slate-700"></dd></div>
                </dl>

                <h4 class="mt-5 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fas fa-file-alt text-amber-600" aria-hidden="true"></i> Detail Proyek
                </h4>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Jenis Proyek</dt><dd id="projectModalTitle" class="mt-1 font-medium text-slate-800"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Jenis Bangunan</dt><dd id="projectModalBuilding" class="mt-1 font-medium text-slate-800"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Luas Area</dt><dd id="projectModalArea" class="mt-1 text-slate-700"></dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Kisaran Anggaran</dt><dd id="projectModalBudgetLabel" class="mt-1 text-slate-700"></dd></div>
                </dl>
                <p class="mt-2 text-[11px] text-slate-400">Anggaran awal dari pelanggan (tidak dapat diubah).</p>

                <h4 class="mt-5 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fas fa-comment-dots text-amber-600" aria-hidden="true"></i> Catatan Konsultasi
                </h4>
                <p id="projectModalDescription" class="mt-3 whitespace-pre-line rounded-xl bg-amber-50 p-3 text-sm leading-6 text-slate-700"></p>

                <div id="projectModalAttachments" class="mt-5 hidden">
                    <h4 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <i class="fas fa-paperclip text-amber-600" aria-hidden="true"></i> Lampiran dari Pelanggan
                    </h4>
                    <div id="projectModalAttachmentList" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4"></div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-slate-900">2. Pengelolaan (Internal)</h3>
                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-800">Data dapat diubah oleh admin</span>
                </div>

                <div class="mt-3">
                    <span class="text-sm font-medium text-slate-700">Tahap Saat Ini</span>
                    <div class="mt-1.5 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                        <span id="projectModalStageLabel" class="text-sm font-semibold text-slate-800"></span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">Tahap berubah otomatis berdasarkan aktivitas pelanggan, desainer, dan admin.</p>
                </div>

                <label id="projectFinalizeField" class="mt-3 hidden">
                    <span class="text-sm font-medium text-slate-700">Selesaikan Pengerjaan</span>
                    <select id="projectStatus" name="status_pemesanan" form="projectForm" class="mt-1.5 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></select>
                    <p class="mt-1 text-[11px] text-slate-400">Desain final sudah disetujui pelanggan. Tandai progres pengerjaan di sini.</p>
                </label>

                <label class="mt-3 block">
                    <span class="text-sm font-medium text-slate-700">Desainer yang Ditugaskan</span>
                    <select id="projectDesigner" name="designer_id" form="projectForm" class="mt-1.5 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Belum ditetapkan</option>
                        @foreach($designers as $designer)<option value="{{ $designer->id }}">{{ $designer->nama }}</option>@endforeach
                    </select>
                </label>

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">Desain &amp; RAB</p>
                    <p class="mt-1 text-xs text-slate-500">Unggah desain awal dan draft RAB untuk dikirim kepada pelanggan.</p>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        @foreach(['design' => ['Design', 'Desain', '.jpg,.jpeg,.png,.webp,.pdf', 'PDF/JPG/PNG'], 'rab' => ['Rab', 'RAB', '.pdf,.xlsx,.xls', 'PDF/XLSX']] as $type => [$cap, $label, $accept, $formats])
                            <div>
                                <p class="text-xs font-semibold text-slate-600">{{ $label }} ({{ $formats }})</p>

                                <form id="project{{ $cap }}UploadForm" method="POST" enctype="multipart/form-data" class="mt-1.5">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $type }}">
                                    <label id="project{{ $cap }}Dropzone" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-white p-3 text-center transition hover:border-amber-400">
                                        <i class="fas fa-cloud-upload-alt text-xl text-slate-300" aria-hidden="true"></i>
                                        <span class="mt-1.5 text-[11px] font-medium text-slate-600">Pilih atau drag &amp; drop file {{ strtolower($label) }}</span>
                                        <span class="mt-0.5 text-[10px] text-slate-400">Maks. 10 MB</span>
                                        <input type="file" name="document" id="project{{ $cap }}Input" accept="{{ $accept }}" class="hidden">
                                    </label>
                                </form>

                                <div id="project{{ $cap }}FileRow" class="mt-2 hidden items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white p-2.5">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <i class="fas fa-file-lines text-slate-400" aria-hidden="true"></i>
                                        <div class="min-w-0">
                                            <p id="project{{ $cap }}FileName" class="truncate text-[11px] font-semibold text-slate-700"></p>
                                            <p id="project{{ $cap }}FileSize" class="text-[10px] text-slate-400"></p>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <a id="project{{ $cap }}Download" href="#" class="text-slate-400 hover:text-amber-600" aria-label="Unduh {{ $label }}"><i class="fas fa-download" aria-hidden="true"></i></a>
                                        <button type="button" id="project{{ $cap }}DeleteBtn" class="text-slate-400 hover:text-red-600" aria-label="Hapus {{ $label }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 rounded-xl bg-white p-4">
                        <label class="flex items-center gap-1.5 text-sm font-semibold text-slate-900" for="projectTotalHarga">
                            Nilai Penawaran
                            <i class="fas fa-circle-info text-xs text-slate-400" title="Nilai berdasarkan draft RAB. Menjadi dasar perhitungan DP 20% setelah disetujui pelanggan." aria-hidden="true"></i>
                        </label>
                        <div class="mt-2 flex items-center rounded-xl border border-slate-300 focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500">
                            <span class="pl-3 text-lg font-semibold text-slate-500">Rp</span>
                            <input id="projectTotalHarga" type="text" inputmode="numeric" oninput="formatRupiahInput(this)" class="w-full border-0 bg-transparent py-2 pl-1 pr-3 text-lg font-semibold text-slate-900 focus:ring-0" placeholder="0">
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-400">Nilai ini menjadi dasar perhitungan DP 20% setelah desain awal disetujui pelanggan.</p>
                    </div>

                    <div id="projectModalSendInfo" class="mt-3 hidden items-start gap-2 rounded-xl bg-blue-50 p-3 text-xs leading-5 text-blue-800">
                        <i class="fas fa-circle-info mt-0.5" aria-hidden="true"></i>
                        <span id="projectModalSendInfoText"></span>
                    </div>
                    <p id="projectModalDocStatus" class="mt-3 text-xs text-slate-500"></p>

                    <button type="button" id="projectModalSendBtn" onclick="sendProjectDocuments()" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim ke Pelanggan
                    </button>
                </div>

                <button type="button" id="projectModalHistoryToggle" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800">
                    <i class="fas fa-clock-rotate-left text-[10px]" aria-hidden="true"></i> Riwayat Pengiriman
                </button>
                <div id="projectModalHistoryPanel" class="mt-2 hidden max-h-40 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3"></div>

                <a id="projectModalDetailLink" href="#" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    Buka halaman detail proyek (pembayaran, survei, invoice) <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                </a>
            </section>
        </div>

        <div class="mt-4 flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
            <button type="button" onclick="closeProjectModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Tutup</button>
            <button type="button" id="projectModalSaveBtn" onclick="saveProjectChanges()" class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan Perubahan</button>
        </div>
    </div>
</div>
