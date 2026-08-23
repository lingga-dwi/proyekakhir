<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Models\ProjectDocument;
use App\Models\ProjectInvoice;
use App\Models\User;
use App\Services\AccountActivationService;
use App\Services\AdminWorkItemService;
use App\Services\DaikuNotificationService;
use App\Services\ManualOrderService;
use App\Services\PaymentEvidenceService;
use App\Services\ProjectWorkflowService;
use App\Support\ReviewPayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    public function __construct(
        private readonly ProjectWorkflowService $workflow,
        private readonly AdminWorkItemService $workItems,
        private readonly ManualOrderService $manualOrders,
        private readonly AccountActivationService $activation,
        private readonly PaymentEvidenceService $paymentEvidence,
        private readonly DaikuNotificationService $notifications,
    ) {}

    public function create(Request $request)
    {
        return redirect()->route('konsultasi.create')
            ->with('success', 'Mulai dari formulir konsultasi agar kebutuhan, jadwal, desain, dan RAB dapat ditangani dalam satu alur.');
    }

    public function store(Request $request)
    {
        return redirect()->route('konsultasi.create')
            ->with('error', 'Pesanan langsung tidak digunakan lagi. Kirim permintaan melalui formulir konsultasi agar proses desain dapat ditelusuri dari awal.');
    }

    public function show(Request $request, $id)
    {
        $pemesanan = Pemesanan::with(['user', 'katalog', 'documents.uploader', 'documentDecisions.customer', 'dpInvoice', 'invoices', 'statusTrackings' => fn ($query) => $query->oldest()])->findOrFail($id);
        $this->authorizeOrderAccess($request, $pemesanan);

        return view('pemesanan.show', compact('pemesanan'));
    }

    public function uploadDocument(Request $request, Pemesanan $pemesanan)
    {
        abort_unless(
            $request->user()->isAdmin() || $pemesanan->designer_id === $request->user()->id,
            403
        );

        $data = $request->validate([
            'document_type' => ['required', Rule::in(['design', 'rab'])],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $isDraft = in_array($pemesanan->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested'], true);
        $isFinal = in_array($pemesanan->workflow_stage, ['survey_scheduled', 'final_design'], true);
        abort_unless($isDraft || $isFinal, 422, 'Dokumen belum dapat diunggah pada tahap ini.');

        $stage = $isDraft ? 'draft' : 'final';
        $round = $isDraft ? (int) $pemesanan->draft_round : (int) $pemesanan->final_round;
        $nextVersion = (int) $pemesanan->documents()
            ->where('stage', $stage)
            ->where('document_type', $data['document_type'])
            ->max('version') + 1;
        $file = $data['document'];
        $path = $file->store('project-documents/'.$pemesanan->id.'/'.$stage, 'local');

        $pemesanan->documents()->create([
            'uploaded_by' => $request->user()->id,
            'stage' => $stage,
            'document_type' => $data['document_type'],
            'submission_round' => $round,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'version' => $nextVersion,
        ]);

        $message = 'Dokumen berhasil disimpan.';

        if ($request->wantsJson()) {
            return $this->documentJsonResponse($pemesanan->fresh(), $data['document_type'], $message, actor: $request->user());
        }

        return back()->with('success', $message);
    }

    public function sendDocuments(Request $request, Pemesanan $pemesanan)
    {
        abort_unless(
            $request->user()->isAdmin() || $pemesanan->designer_id === $request->user()->id,
            403
        );

        $isDraft = in_array($pemesanan->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested'], true);
        $isFinal = in_array($pemesanan->workflow_stage, ['survey_scheduled', 'final_design'], true);
        abort_unless($isDraft || $isFinal, 422, 'Dokumen belum dapat dikirim pada tahap ini.');

        $stage = $isDraft ? 'draft' : 'final';
        $round = $isDraft ? (int) $pemesanan->draft_round : (int) $pemesanan->final_round;
        $submittedTypes = $pemesanan->documents()
            ->where('stage', $stage)
            ->where('submission_round', $round)
            ->whereIn('document_type', ['design', 'rab'])
            ->distinct()
            ->pluck('document_type');

        if ($submittedTypes->count() < 2) {
            $missingLabel = $submittedTypes->contains('design') ? 'RAB' : 'desain';
            $message = 'Unggah '.$missingLabel.' terlebih dahulu sebelum mengirim ke pelanggan.';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['document' => $message]);
        }

        $pemesanan->update([
            'workflow_stage' => $isDraft ? 'awaiting_admin_validation' : 'awaiting_admin_validation_final',
            'progress' => $isDraft ? 15 : 45,
            'catatan_progres' => $isDraft ? 'Desain awal/RAB dikirim dan menunggu validasi admin.' : 'Desain dan RAB final dikirim dan menunggu validasi admin.',
        ]);
        $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

        $this->notifications->admins(
            $isDraft ? 'Desain awal & RAB menunggu validasi' : 'Desain & RAB final menunggu validasi',
            'Dokumen proyek DI-'.$pemesanan->id.' telah dikirim desainer dan menunggu validasi Anda.',
            route('admin.pemesanan.index', ['search' => 'DI-'.$pemesanan->id], false),
            'Validasi dokumen'
        );

        $message = 'Dokumen berhasil dikirim dan menunggu validasi admin.';

        if ($request->wantsJson()) {
            return $this->documentJsonResponse($pemesanan->fresh(), $submittedTypes->first(), $message, actor: $request->user());
        }

        return back()->with('success', $message);
    }

    public function validateDraft(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($pemesanan->workflow_stage === 'awaiting_admin_validation', 422, 'Proyek tidak sedang menunggu validasi admin.');

        $data = $request->validate([
            'total_harga' => ['required', 'numeric', 'min:0.01'],
        ]);

        $pemesanan->update([
            'total_harga' => $data['total_harga'],
            'workflow_stage' => 'awaiting_draft_approval',
            'progress' => 20,
            'catatan_progres' => 'Desain awal/RAB divalidasi admin dan dikirim untuk ditinjau pelanggan.',
        ]);
        $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

        $this->notifications->send(
            $pemesanan->user,
            'Desain awal dan RAB tersedia',
            'Dokumen proyek DI-'.$pemesanan->id.' telah divalidasi dan menunggu keputusan Anda.',
            route('pemesanan.show', $pemesanan, false),
            'Tinjau dokumen'
        );

        $message = 'Desain divalidasi dan berhasil dikirim ke pelanggan.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function requestValidationRevision(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($pemesanan->workflow_stage === 'awaiting_admin_validation', 422, 'Proyek tidak sedang menunggu validasi admin.');

        $data = $request->validate([
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $pemesanan->update([
            'workflow_stage' => 'revision_requested',
            'draft_round' => $pemesanan->draft_round + 1,
            'catatan_progres' => 'Admin meminta revisi: '.($data['feedback'] ?: 'Tidak ada catatan tambahan.'),
        ]);
        $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

        if ($pemesanan->designer) {
            $this->notifications->send(
                $pemesanan->designer,
                'Admin meminta revisi',
                'Admin meminta revisi proyek DI-'.$pemesanan->id.'. '.($data['feedback'] ?: 'Tidak ada catatan tambahan.'),
                route('pemesanan.show', $pemesanan, false),
                'Lihat catatan revisi'
            );
        }

        $message = 'Permintaan revisi dikirim ke desainer.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function validateFinal(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($pemesanan->workflow_stage === 'awaiting_admin_validation_final', 422, 'Proyek tidak sedang menunggu validasi admin untuk desain final.');

        $pemesanan->update([
            'workflow_stage' => 'awaiting_final_approval',
            'progress' => 45,
            'catatan_progres' => 'Desain dan RAB final divalidasi admin dan dikirim untuk persetujuan pelanggan.',
        ]);
        $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

        $this->notifications->send(
            $pemesanan->user,
            'Desain final dan RAB tersedia',
            'Dokumen proyek DI-'.$pemesanan->id.' telah divalidasi dan menunggu keputusan Anda.',
            route('pemesanan.show', $pemesanan, false),
            'Tinjau dokumen'
        );

        $message = 'Desain final divalidasi dan berhasil dikirim ke pelanggan.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function requestFinalValidationRevision(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($pemesanan->workflow_stage === 'awaiting_admin_validation_final', 422, 'Proyek tidak sedang menunggu validasi admin untuk desain final.');

        $data = $request->validate([
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $pemesanan->update([
            'workflow_stage' => 'final_design',
            'final_round' => $pemesanan->final_round + 1,
            'catatan_progres' => 'Admin meminta revisi desain final: '.($data['feedback'] ?: 'Tidak ada catatan tambahan.'),
        ]);
        $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

        if ($pemesanan->designer) {
            $this->notifications->send(
                $pemesanan->designer,
                'Admin meminta revisi',
                'Admin meminta revisi desain final proyek DI-'.$pemesanan->id.'. '.($data['feedback'] ?: 'Tidak ada catatan tambahan.'),
                route('pemesanan.show', $pemesanan, false),
                'Lihat catatan revisi'
            );
        }

        $message = 'Permintaan revisi dikirim ke desainer.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function downloadDocument(Request $request, Pemesanan $pemesanan, ProjectDocument $document)
    {
        abort_unless($document->pemesanan_id === $pemesanan->id, 404);
        $this->authorizeOrderAccess($request, $pemesanan);

        if ($pemesanan->id_user === $request->user()->id) {
            $stagesPastDraftValidation = [
                'awaiting_draft_approval', 'awaiting_dp', 'dp_verification',
                'survey_scheduled', 'final_design', 'awaiting_admin_validation_final',
                'awaiting_final_approval', 'approved',
            ];
            $stagesPastFinalValidation = ['awaiting_final_approval', 'approved'];

            $isVisibleToCustomer = $document->stage === 'draft'
                ? in_array($pemesanan->workflow_stage, $stagesPastDraftValidation, true)
                : in_array($pemesanan->workflow_stage, $stagesPastFinalValidation, true);

            abort_unless($isVisibleToCustomer, 403, 'Dokumen ini belum divalidasi dan dikirim oleh admin.');
        }

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function deleteDocument(Request $request, Pemesanan $pemesanan, ProjectDocument $document)
    {
        abort_unless(
            $request->user()->isAdmin() || $pemesanan->designer_id === $request->user()->id,
            403
        );
        abort_unless($document->pemesanan_id === $pemesanan->id, 404);

        $deletableStages = ['konsultasi', 'draft_design', 'revision_requested', 'final_design', 'awaiting_draft_approval', 'awaiting_final_approval'];
        abort_unless(in_array($pemesanan->workflow_stage, $deletableStages, true), 422, 'Dokumen tidak dapat dihapus pada tahap ini.');

        $documentType = $document->document_type;
        $stage = $document->stage;
        $round = $document->submission_round;
        $wasAwaitingDecision = in_array($pemesanan->workflow_stage, ['awaiting_draft_approval', 'awaiting_final_approval'], true);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        if ($wasAwaitingDecision) {
            $remainingTypes = $pemesanan->documents()
                ->where('stage', $stage)
                ->where('submission_round', $round)
                ->whereIn('document_type', ['design', 'rab'])
                ->distinct()
                ->pluck('document_type');

            if ($remainingTypes->count() < 2) {
                $pemesanan->update([
                    'workflow_stage' => $stage === 'draft' ? 'draft_design' : 'final_design',
                ]);
                $this->recordStatusTracking(
                    $pemesanan,
                    $request->user(),
                    'Dokumen '.($documentType === 'design' ? 'desain' : 'RAB').' dihapus. Menunggu kelengkapan dokumen kembali dikirim.',
                    $pemesanan->status_pemesanan
                );
            }
        }

        $message = 'Dokumen berhasil dihapus.';

        if ($request->wantsJson()) {
            return $this->documentJsonResponse($pemesanan->fresh(), $documentType, $message, deleted: true, actor: $request->user());
        }

        return back()->with('success', $message);
    }

    private function documentJsonResponse(Pemesanan $pemesanan, string $documentType, string $message, bool $deleted = false, ?User $actor = null)
    {
        $isKonsultasi = $pemesanan->workflow_stage === 'konsultasi';
        $isDraft = in_array($pemesanan->workflow_stage, ['draft_design', 'revision_requested'], true);
        $isFinal = in_array($pemesanan->workflow_stage, ['survey_scheduled', 'final_design'], true);
        $isAwaitingDecision = in_array($pemesanan->workflow_stage, ['awaiting_admin_validation', 'awaiting_draft_approval', 'awaiting_admin_validation_final', 'awaiting_final_approval'], true);
        $deleteRouteName = $actor?->isAdmin() ? 'admin.pemesanan.document.delete' : 'designer.proyek.document.delete';

        $stage = $isKonsultasi || $isDraft || in_array($pemesanan->workflow_stage, ['awaiting_admin_validation', 'awaiting_draft_approval'], true) ? 'draft' : 'final';
        $round = $stage === 'draft' ? (int) $pemesanan->draft_round : (int) $pemesanan->final_round;

        $document = null;
        if (! $deleted) {
            $latest = $pemesanan->documents()
                ->where('stage', $stage)
                ->where('submission_round', $round)
                ->where('document_type', $documentType)
                ->orderByDesc('version')
                ->first();

            if ($latest) {
                $document = [
                    'id' => $latest->id,
                    'name' => $latest->original_name,
                    'size' => Storage::disk('local')->exists($latest->path) ? Storage::disk('local')->size($latest->path) : null,
                    'downloadUrl' => route('pemesanan.document.download', [$pemesanan->id, $latest->id]),
                    'deleteUrl' => route($deleteRouteName, [$pemesanan->id, $latest->id]),
                ];
            }
        }

        $submittedCount = $pemesanan->documents()
            ->where('stage', $stage)
            ->where('submission_round', $round)
            ->whereIn('document_type', ['design', 'rab'])
            ->distinct('document_type')
            ->count('document_type');
        $sendRouteName = $actor?->isAdmin() ? 'admin.pemesanan.document.send' : 'designer.proyek.document.send';

        return response()->json([
            'success' => true,
            'message' => $message,
            'documentType' => $documentType,
            'document' => $document,
            'canManageDocuments' => $isKonsultasi || $isDraft || $isFinal,
            'isAwaitingDecision' => $isAwaitingDecision,
            'canSend' => ($isKonsultasi || $isDraft || $isFinal) && $submittedCount >= 2,
            'totalHarga' => (float) $pemesanan->total_harga,
            'sendUrl' => route($sendRouteName, $pemesanan->id),
        ]);
    }

    public function decideDocument(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->id_user === $request->user()->id, 403);

        $data = $request->validate([
            'stage' => ['required', Rule::in(['draft', 'final'])],
            'decision' => ['required', Rule::in(['approved', 'revision_requested'])],
            'feedback' => ['nullable', 'required_if:decision,revision_requested', 'string', 'max:2000'],
        ]);

        $expectedStage = $data['stage'] === 'draft' ? 'awaiting_draft_approval' : 'awaiting_final_approval';
        $round = $data['stage'] === 'draft' ? (int) $pemesanan->draft_round : (int) $pemesanan->final_round;

        if ($pemesanan->workflow_stage !== $expectedStage) {
            // A customer's already-open modal can go stale if the stage
            // moved on from under them — most commonly because they
            // uploaded DP payment evidence before ever clicking Setujui,
            // which auto-records the draft approval and jumps straight to
            // dp_verification. Treat a repeat "approved" click as a no-op
            // success instead of a confusing error in that specific case.
            $alreadyApproved = $data['decision'] === 'approved'
                && $pemesanan->documentDecisions()
                    ->where('stage', $data['stage'])
                    ->where('submission_round', $round)
                    ->where('decision', 'approved')
                    ->exists();

            if ($alreadyApproved) {
                $message = $data['stage'] === 'draft' ? 'Desain awal sudah disetujui sebelumnya.' : 'Desain final sudah disetujui sebelumnya.';

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'review' => ReviewPayloadBuilder::build($pemesanan->fresh(['documents', 'konsultasi', 'invoices'])),
                    ]);
                }

                return back()->with('success', $message);
            }

            abort(422, 'Tidak ada dokumen yang menunggu keputusan pada tahap ini.');
        }

        if ($data['stage'] === 'draft' && $data['decision'] === 'approved' && (float) $pemesanan->total_harga <= 0) {
            $message = 'Admin perlu menetapkan nilai proyek sebelum invoice DP dapat dibuat.';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['decision' => $message]);
        }

        $pemesanan->documentDecisions()->create([
            'decided_by' => $request->user()->id,
            'stage' => $data['stage'],
            'submission_round' => $round,
            'decision' => $data['decision'],
            'feedback' => $data['feedback'] ?? null,
        ]);

        if ($data['decision'] === 'revision_requested') {
            $changes = [
                'workflow_stage' => $data['stage'] === 'draft' ? 'revision_requested' : 'final_design',
                'catatan_progres' => 'Pelanggan meminta revisi: '.($data['feedback'] ?: 'Tidak ada catatan tambahan.'),
            ];
            $changes[$data['stage'] === 'draft' ? 'draft_round' : 'final_round'] = $round + 1;
            $pemesanan->update($changes);
            $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

            if ($pemesanan->designer) {
                $this->notifications->send(
                    $pemesanan->designer,
                    'Pelanggan meminta revisi',
                    'Pelanggan meminta revisi proyek DI-'.$pemesanan->id.'. '.($data['feedback'] ?: 'Tidak ada catatan tambahan.'),
                    route('pemesanan.show', $pemesanan, false),
                    'Lihat catatan revisi'
                );
            }

            $message = 'Permintaan revisi telah dikirim kepada desainer.';

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        }

        if ($data['stage'] === 'draft') {
            $pemesanan->update([
                'workflow_stage' => 'awaiting_dp',
                'progress' => 25,
                'catatan_progres' => 'Desain awal disetujui. Menunggu pembayaran sesuai tagihan yang diterbitkan admin.',
            ]);
            $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

            $this->notifications->send(
                $pemesanan->user,
                'Desain awal disetujui',
                'Desain awal proyek DI-'.$pemesanan->id.' telah disetujui. Silakan lakukan pembayaran sesuai tagihan yang tersedia.',
                route('pemesanan.show', $pemesanan, false),
                'Lihat tagihan'
            );

            $message = 'Desain awal disetujui.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'review' => ReviewPayloadBuilder::build($pemesanan->fresh(['documents', 'konsultasi', 'invoices'])),
                ]);
            }

            return back()->with('success', $message);
        }

        $previousStatus = $pemesanan->status_pemesanan;
        $pemesanan->update([
            'workflow_stage' => 'approved',
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
            'progress' => max(50, (int) $pemesanan->progress),
            'catatan_progres' => 'Desain dan RAB final disetujui pelanggan. Pengerjaan dapat dimulai.',
        ]);
        $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $previousStatus);

        if ($pemesanan->designer) {
            $this->notifications->send(
                $pemesanan->designer,
                'Desain final disetujui',
                'Pelanggan menyetujui desain dan RAB final proyek DI-'.$pemesanan->id.'. Pengerjaan dapat dimulai.',
                route('pemesanan.show', $pemesanan, false),
                'Buka proyek'
            );
        }
        $this->notifications->admins(
            'Desain final disetujui pelanggan',
            'Proyek DI-'.$pemesanan->id.' telah disetujui dan masuk tahap pengerjaan.',
            route('admin.pemesanan.index', ['search' => 'DI-'.$pemesanan->id], false),
            'Kelola proyek'
        );

        $message = 'Desain final disetujui. Proyek masuk ke tahap pengerjaan.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function uploadDpEvidence(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->id_user === $request->user()->id, 403);
        $invoice = $pemesanan->dpInvoice;
        abort_unless($invoice && $invoice->status !== 'paid' && in_array($pemesanan->workflow_stage, ['awaiting_dp', 'dp_verification'], true), 422, 'Tidak ada invoice DP yang menunggu pembayaran.');

        $data = $request->validate(['bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']]);
        $path = $data['bukti_pembayaran']->store('dp-proofs/order-'.$pemesanan->id, 'payment_evidence');

        $invoice->update(['proof_path' => $path, 'status' => 'submitted']);
        $pemesanan->update(['workflow_stage' => 'dp_verification', 'catatan_progres' => 'Bukti pembayaran DP telah diunggah dan menunggu verifikasi admin.']);
        $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

        $this->notifications->admins(
            'Bukti pembayaran DP masuk',
            'Pelanggan mengunggah bukti DP untuk proyek DI-'.$pemesanan->id.'.',
            route('admin.pemesanan.index', ['search' => 'DI-'.$pemesanan->id], false),
            'Verifikasi pembayaran',
            'payment',
            [
                'Referensi' => 'DI-'.$pemesanan->id,
                'Nama Pelanggan' => $pemesanan->user?->nama,
                'Tagihan' => $invoice->name,
                'Nomor Invoice' => $invoice->number,
                'Nominal' => 'Rp '.number_format((float) $invoice->amount, 0, ',', '.'),
            ]
        );

        $message = 'Bukti pembayaran DP berhasil dikirim.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function uploadInvoiceEvidence(Request $request, Pemesanan $pemesanan, ProjectInvoice $invoice)
    {
        abort_unless($pemesanan->id_user === $request->user()->id, 403);
        abort_unless($invoice->pemesanan_id === $pemesanan->id, 404);
        abort_unless($invoice->status !== 'paid', 422, 'Tagihan ini sudah lunas.');

        $data = $request->validate(['bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']]);
        $path = $data['bukti_pembayaran']->store('invoice-proofs/order-'.$pemesanan->id, 'payment_evidence');

        $invoice->update(['proof_path' => $path, 'status' => 'submitted']);

        $stagesPastDpVerification = ['survey_scheduled', 'final_design', 'awaiting_admin_validation_final', 'awaiting_final_approval', 'approved'];
        $isDpInvoice = $pemesanan->dpInvoice?->id === $invoice->id;
        if ($isDpInvoice && ! in_array($pemesanan->workflow_stage, $stagesPastDpVerification, true)) {
            if ($pemesanan->workflow_stage === 'awaiting_draft_approval') {
                $pemesanan->documentDecisions()->create([
                    'decided_by' => $request->user()->id,
                    'stage' => 'draft',
                    'submission_round' => (int) $pemesanan->draft_round,
                    'decision' => 'approved',
                ]);
            }

            $pemesanan->update(['workflow_stage' => 'dp_verification', 'catatan_progres' => 'Bukti pembayaran DP telah diunggah dan menunggu verifikasi admin.']);
            $this->recordStatusTracking($pemesanan, $request->user(), $pemesanan->catatan_progres, $pemesanan->status_pemesanan);
        } else {
            $this->recordStatusTracking(
                $pemesanan,
                $request->user(),
                'Bukti pembayaran tagihan "'.$invoice->name.'" diunggah dan menunggu verifikasi admin.',
                $pemesanan->status_pemesanan
            );
        }

        $this->notifications->admins(
            'Bukti pembayaran masuk',
            'Pelanggan mengunggah bukti tagihan "'.$invoice->name.'" untuk proyek DI-'.$pemesanan->id.'.',
            route('admin.pemesanan.index', ['search' => 'DI-'.$pemesanan->id], false),
            'Verifikasi pembayaran',
            'payment',
            [
                'Referensi' => 'DI-'.$pemesanan->id,
                'Nama Pelanggan' => $pemesanan->user?->nama,
                'Tagihan' => $invoice->name,
                'Nomor Invoice' => $invoice->number,
                'Nominal' => 'Rp '.number_format((float) $invoice->amount, 0, ',', '.'),
            ]
        );

        $message = 'Bukti pembayaran berhasil dikirim.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Move the project into the survey stage once its DP invoice is
     * confirmed paid via the invoice table's "Konfirmasi Pembayaran" button.
     */
    private function advanceToSurveyAfterDpVerified(Pemesanan $pemesanan, User $actor): void
    {
        if (! in_array($pemesanan->workflow_stage, ['awaiting_dp', 'dp_verification'], true)) {
            return;
        }

        $pemesanan->update(['workflow_stage' => 'survey_scheduled', 'progress' => 30, 'catatan_progres' => 'DP telah diverifikasi. Menunggu survei lokasi oleh desainer.']);
        $this->recordStatusTracking($pemesanan, $actor, $pemesanan->catatan_progres, $pemesanan->status_pemesanan);

        $this->notifications->send(
            $pemesanan->user,
            'Pembayaran DP terverifikasi',
            'Pembayaran DP proyek DI-'.$pemesanan->id.' telah diverifikasi. Tim Daiku akan menjadwalkan survei lokasi.',
            route('pemesanan.show', $pemesanan, false),
            'Lihat proyek'
        );
    }

    public function storeInvoice(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $invoice = $pemesanan->invoices()->create([
            'number' => 'INV-'.now()->format('Y').'-'.str_pad((string) ($pemesanan->invoices()->count() + 1), 4, '0', STR_PAD_LEFT).'-'.$pemesanan->id,
            'type' => 'custom',
            'name' => $data['name'],
            'amount' => $data['amount'],
            'status' => 'pending',
            'due_date' => $data['due_date'] ?? null,
            'note' => $data['note'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        if ($pemesanan->workflow_stage === 'awaiting_admin_validation') {
            $pemesanan->update(['total_harga' => (float) $pemesanan->invoices()->sum('amount')]);
        }

        $this->notifications->send(
            $pemesanan->user,
            'Tagihan baru tersedia',
            'Tagihan "'.$invoice->name.'" sebesar Rp '.number_format((float) $invoice->amount, 0, ',', '.').' telah diterbitkan untuk proyek DI-'.$pemesanan->id.'.',
            route('pemesanan.show', $pemesanan, false),
            'Lihat tagihan'
        );

        $message = 'Tagihan berhasil dibuat.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'invoice' => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'name' => $invoice->name,
                'amount' => (float) $invoice->amount,
                'status' => $invoice->status,
                'dueDate' => $invoice->due_date?->translatedFormat('d M Y'),
                'note' => $invoice->note,
            ]]);
        }

        return back()->with('success', $message);
    }

    public function markInvoicePaid(Request $request, Pemesanan $pemesanan, ProjectInvoice $invoice)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($invoice->pemesanan_id === $pemesanan->id, 404);
        abort_unless($invoice->status !== 'paid', 422, 'Tagihan ini sudah lunas.');

        $invoice->update(['status' => 'paid', 'verified_by' => $request->user()->id, 'verified_at' => now()]);

        $isDpInvoice = $pemesanan->dpInvoice?->id === $invoice->id;
        if ($isDpInvoice) {
            $this->advanceToSurveyAfterDpVerified($pemesanan, $request->user());
        }

        $message = 'Tagihan ditandai lunas.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function downloadInvoiceEvidence(Request $request, Pemesanan $pemesanan, ProjectInvoice $invoice)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($invoice->pemesanan_id === $pemesanan->id, 404);
        abort_unless($invoice->proof_path, 404);

        return Storage::disk('payment_evidence')->download($invoice->proof_path);
    }

    public function uploadPaymentEvidence(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->id_user === $request->user()->id, 403);

        $data = $request->validate([
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $this->paymentEvidence->upload($pemesanan, $data['bukti_pembayaran'], $request->user());

        return back()->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function downloadPaymentEvidence(Request $request, Pemesanan $pemesanan)
    {
        $this->authorizeOrderAccess($request, $pemesanan);
        $summary = $this->paymentEvidence->summary($pemesanan);

        abort_unless($summary['proof_path'], 404);

        return Storage::disk('payment_evidence')->download($summary['proof_path']);
    }

    public function verifyPaymentEvidence(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($this->paymentEvidence->verify($pemesanan, $request->user()), 422, 'Tidak ada bukti pembayaran yang menunggu verifikasi.');

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function index(Request $request)
    {
        $workItems = $this->workItems->paginate($request);

        $designers = User::where('role', 'designer')->orderBy('nama')->get(['id', 'nama']);
        $customers = User::where('role', 'pelanggan')->orderBy('nama')->get(['id', 'nama', 'email', 'no_telp']);

        return view('admin.pemesanan.index', compact('workItems', 'designers', 'customers'));
    }

    public function heartbeat()
    {
        return response()->json(['signal' => $this->workItems->heartbeat()]);
    }

    public function destroy(Request $request, Pemesanan $pemesanan)
    {
        $reference = 'DI-'.str_pad((string) $pemesanan->id, 3, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($pemesanan) {
            $pemesanan->konsultasi()->delete();
            $pemesanan->statusTrackings()->delete();
            $pemesanan->delete();
        });

        $message = 'Pesanan '.$reference.' berhasil dihapus.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.pemesanan.index')->with('success', $message);
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validateWithBag('manualOrder', [
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],
            'id_user' => [
                'nullable',
                'required_if:customer_mode,existing',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'pelanggan')),
            ],
            'nama' => ['nullable', 'required_if:customer_mode,new', 'string', 'max:255'],
            'email' => ['nullable', 'required_if:customer_mode,new', 'email', 'max:255', Rule::unique('users', 'email')],
            'no_telp' => ['nullable', 'required_if:customer_mode,new', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'sumber_masuk' => ['required', Rule::in(['kantor', 'whatsapp', 'telepon', 'instagram', 'website'])],
            'tanggal_pesan' => ['required', 'date', 'before_or_equal:today'],
            'jenis_proyek' => ['required', 'string', 'max:255'],
            'jenis_bangunan' => ['nullable', 'string', 'max:255'],
            'deskripsi_keinginan_desain' => ['nullable', 'string', 'max:2000'],
        ]);

        $pemesanan = $this->manualOrders->create($data, $request->user());

        $message = 'Pesanan DI-'.str_pad((string) $pemesanan->id, 3, '0', STR_PAD_LEFT).' berhasil dicatat.';

        if ($data['customer_mode'] === 'new') {
            $customer = $pemesanan->user()->firstOrFail();

            if (! $this->activation->isDeliveryConfigured()) {
                $message .= ' Akun pelanggan dibuat, tetapi email aktivasi belum dikirim karena layanan email belum dikonfigurasi.';
            } else {
                $message .= $this->activation->send($customer)
                    ? ' Tautan aktivasi akun telah dikirim ke email pelanggan.'
                    : ' Akun pelanggan dibuat, tetapi email aktivasi gagal dikirim. Periksa layanan email.';
            }
        }

        return redirect()->route('admin.pemesanan.index')
            ->with('success', $message);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(Pemesanan::STATUSES)],
        ]);

        $pemesanan = Pemesanan::findOrFail($id);
        $changed = $this->workflow->update(
            $pemesanan,
            ['status_pemesanan' => $request->status],
            $request->user(),
            'Status pesanan diperbarui oleh admin.'
        );

        return back()->with('success', $changed ? 'Status pesanan berhasil diperbarui.' : 'Tidak ada perubahan status.');
    }

    public function updateProject(Request $request, Pemesanan $pemesanan)
    {
        $data = $request->validate([
            'status_pemesanan' => ['required', Rule::in(Pemesanan::STATUSES)],
            'progress' => ['nullable', 'integer', 'between:0,100'],
            'target_mulai' => ['nullable', 'date'],
            'target_selesai' => ['nullable', 'date', 'after_or_equal:target_mulai'],
            'designer_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'designer')),
            ],
            'total_harga' => ['nullable', 'numeric', 'min:0'],
            'catatan_progres' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($data['status_pemesanan'], [Pemesanan::STATUS_IN_PROGRESS, Pemesanan::STATUS_COMPLETED], true)
            && $pemesanan->workflow_stage !== 'approved') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status_pemesanan' => 'Pelanggan harus menyetujui desain dan RAB final sebelum proyek dapat dikerjakan atau diselesaikan.',
            ]);
        }

        $changed = $this->workflow->update($pemesanan, $data, $request->user());
        $message = $changed ? 'Progres proyek berhasil diperbarui.' : 'Tidak ada perubahan proyek.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function updateAssignedProject(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->designer_id === $request->user()->id, 403);

        $data = $request->validate([
            'status_pemesanan' => [
                'required',
                Rule::in([
                    Pemesanan::STATUS_CONFIRMED,
                    Pemesanan::STATUS_IN_PROGRESS,
                    Pemesanan::STATUS_COMPLETED,
                ]),
            ],
            'progress' => ['nullable', 'integer', 'between:0,100'],
            'target_mulai' => ['nullable', 'date'],
            'target_selesai' => ['nullable', 'date', 'after_or_equal:target_mulai'],
            'catatan_progres' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($data['status_pemesanan'], [Pemesanan::STATUS_IN_PROGRESS, Pemesanan::STATUS_COMPLETED], true)
            && $pemesanan->workflow_stage !== 'approved') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status_pemesanan' => 'Desain dan RAB final harus disetujui pelanggan sebelum pengerjaan dimulai.',
            ]);
        }

        $changed = $this->workflow->update(
            $pemesanan,
            $data,
            $request->user(),
            'Progres proyek diperbarui oleh desainer.'
        );
        $message = $changed ? 'Progres proyek berhasil diperbarui.' : 'Tidak ada perubahan proyek.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Record a status_tracking entry for a workflow transition that just
     * happened via a direct $pemesanan->update([...]) call (as opposed to
     * ProjectWorkflowService, which already records its own). Without this,
     * every automatic transition in this controller — document sent,
     * validated, approved, DP uploaded/verified, etc. — leaves no trace,
     * and the timeline shown to admin/designer/customer stays empty.
     */
    private function recordStatusTracking(Pemesanan $pemesanan, ?User $actor, string $catatan, string $previousStatus): void
    {
        $pemesanan->statusTrackings()->create([
            'actor_id' => $actor?->id,
            'previous_status' => $previousStatus,
            'status' => $pemesanan->status_pemesanan,
            'progress' => $pemesanan->progress,
            'tanggal_update' => now()->toDateString(),
            'catatan' => $catatan,
        ]);
    }

    private function authorizeOrderAccess(Request $request, Pemesanan $pemesanan): void
    {
        $user = $request->user();
        $canAccess = $user->isAdmin()
            || $pemesanan->id_user === $user->id
            || ($user->isDesigner() && $pemesanan->designer_id === $user->id);

        abort_unless($canAccess, 403);
    }
}
