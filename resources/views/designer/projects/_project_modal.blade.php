<div id="dpModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="dp-modal-title">
    <div class="relative max-h-[94vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-lg text-amber-500"><i class="fas fa-file-signature" aria-hidden="true"></i></span>
                <div>
                    <p id="dpReference" class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700"></p>
                    <h2 id="dp-modal-title" class="text-lg font-semibold text-slate-950"><span id="dpStageTitle">Desain &amp; RAB</span> <span id="dpReferenceTitle" class="text-amber-700"></span></h2>
                    <p id="dpStageSubtitle" class="mt-0.5 text-xs text-slate-500">Kelola tahap, target, serta desain &amp; RAB proyek.</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-2">
                <span id="dpStagePill" class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-600"></span>
                <button type="button" onclick="closeDpModal()" class="flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Tutup"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div class="mt-3">
            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="p-3">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['design' => ['Design', 'Desain', '.jpg,.jpeg,.png,.webp,.pdf', 'PDF/JPG/PNG'], 'rab' => ['Rab', 'RAB', '.pdf,.xlsx,.xls', 'PDF/XLSX']] as $type => [$cap, $label, $accept, $formats])
                            <div>
                                <p class="flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                                    <span id="dp{{ $cap }}FieldLabel">{{ $label }}</span> ({{ $formats }})
                                    <i class="fas fa-circle-info text-slate-300" aria-hidden="true"></i>
                                </p>

                                <form id="dp{{ $cap }}UploadForm" method="POST" enctype="multipart/form-data" class="mt-1">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $type }}">
                                    <label id="dp{{ $cap }}Dropzone" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-amber-300 bg-amber-50/40 p-3 text-center transition hover:border-amber-400 hover:bg-amber-50">
                                        <i class="fas fa-cloud-upload-alt text-lg text-amber-400" aria-hidden="true"></i>
                                        <span class="mt-1 text-[11px] font-medium text-slate-600">Klik atau drag &amp; drop file <span id="dp{{ $cap }}DropLabel">{{ strtolower($label) }}</span></span>
                                        <span class="text-[10px] text-slate-400">Maks. 10 MB</span>
                                        <input type="file" name="document" id="dp{{ $cap }}Input" accept="{{ $accept }}" class="hidden">
                                    </label>
                                </form>
                                <p class="mt-1 text-[11px] text-slate-400">Format: {{ str_replace('/', ', ', $formats) }}</p>

                                <div id="dp{{ $cap }}FileRow" class="mt-1.5 hidden items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white p-2">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <i class="fas fa-file-lines text-slate-400" aria-hidden="true"></i>
                                        <div class="min-w-0">
                                            <p id="dp{{ $cap }}FileName" class="truncate text-[11px] font-semibold text-slate-700"></p>
                                            <p id="dp{{ $cap }}FileSize" class="text-[10px] text-slate-400"></p>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <a id="dp{{ $cap }}Download" href="#" class="text-slate-400 hover:text-amber-600" aria-label="Unduh {{ $label }}"><i class="fas fa-download" aria-hidden="true"></i></a>
                                        <button type="button" id="dp{{ $cap }}DeleteBtn" class="text-slate-400 hover:text-red-600" aria-label="Hapus {{ $label }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 flex items-center justify-end border-t border-slate-100 pt-3">
                        <button type="button" id="dpSendBtn" onclick="confirmSendDpDocuments()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
