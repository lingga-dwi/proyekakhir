<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
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
use Illuminate\Http\Request;
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
        $pemesanan = Pemesanan::with(['user', 'katalog', 'documents.uploader', 'documentDecisions.customer', 'dpInvoice'])->findOrFail($id);
        $this->authorizeOrderAccess($request, $pemesanan);

        $adminView = $request->routeIs('admin.pemesanan.show');
        $designers = $request->user()->isAdmin()
            ? User::where('role', 'designer')->orderBy('nama')->get(['id', 'nama'])
            : collect();

        return view('pemesanan.show', compact('pemesanan', 'adminView', 'designers'));
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
        $isFinal = $pemesanan->workflow_stage === 'final_design';
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

        $isDraft = in_array($pemesanan->workflow_stage, ['draft_design', 'revision_requested'], true);
        $isFinal = $pemesanan->workflow_stage === 'final_design';
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

        if ($isDraft && (float) $pemesanan->total_harga <= 0) {
            $message = 'Tetapkan nilai penawaran sebelum mengirim ke pelanggan.';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['total_harga' => $message]);
        }

        $pemesanan->update([
            'workflow_stage' => $isDraft ? 'awaiting_draft_approval' : 'awaiting_final_approval',
            'progress' => $isDraft ? 20 : 45,
            'catatan_progres' => $isDraft ? 'Desain awal/RAB dikirim untuk ditinjau pelanggan.' : 'Desain dan RAB final dikirim untuk persetujuan pelanggan.',
        ]);

        $this->notifications->send(
            $pemesanan->user,
            $isDraft ? 'Desain awal dan RAB tersedia' : 'Desain final dan RAB tersedia',
            'Dokumen proyek DI-'.$pemesanan->id.' telah dikirim dan menunggu keputusan Anda.',
            route('pemesanan.show', $pemesanan, false),
            'Tinjau dokumen'
        );

        $message = 'Dokumen berhasil dikirim ke pelanggan untuk ditinjau.';

        if ($request->wantsJson()) {
            return $this->documentJsonResponse($pemesanan->fresh(), $submittedTypes->first(), $message, actor: $request->user());
        }

        return back()->with('success', $message);
    }

    public function downloadDocument(Request $request, Pemesanan $pemesanan, ProjectDocument $document)
    {
        abort_unless($document->pemesanan_id === $pemesanan->id, 404);
        $this->authorizeOrderAccess($request, $pemesanan);

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
        $isFinal = $pemesanan->workflow_stage === 'final_design';
        $isAwaitingDecision = in_array($pemesanan->workflow_stage, ['awaiting_draft_approval', 'awaiting_final_approval'], true);
        $deleteRouteName = $actor?->isAdmin() ? 'admin.pemesanan.document.delete' : 'designer.proyek.document.delete';

        $stage = $isKonsultasi || $isDraft || in_array($pemesanan->workflow_stage, ['awaiting_draft_approval'], true) ? 'draft' : 'final';
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
            'canSend' => ($isDraft || $isFinal) && $submittedCount >= 2,
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
        abort_unless($pemesanan->workflow_stage === $expectedStage, 422, 'Tidak ada dokumen yang menunggu keputusan pada tahap ini.');

        if ($data['stage'] === 'draft' && $data['decision'] === 'approved' && (float) $pemesanan->total_harga <= 0) {
            $message = 'Admin perlu menetapkan nilai proyek sebelum invoice DP dapat dibuat.';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['decision' => $message]);
        }

        $round = $data['stage'] === 'draft' ? (int) $pemesanan->draft_round : (int) $pemesanan->final_round;
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
            $invoice = ProjectInvoice::firstOrCreate(
                ['pemesanan_id' => $pemesanan->id, 'type' => 'dp_20'],
                [
                    'number' => 'DP-'.now()->format('Y').'-'.str_pad((string) $pemesanan->id, 5, '0', STR_PAD_LEFT),
                    'amount' => round((float) $pemesanan->total_harga * 0.20, 2),
                    'status' => 'pending',
                    'due_date' => now()->addDays(7)->toDateString(),
                ]
            );

            $pemesanan->update([
                'workflow_stage' => $invoice->status === 'paid' ? 'survey_pending' : 'awaiting_dp',
                'progress' => 25,
                'catatan_progres' => 'Desain awal disetujui. Invoice DP 20% '.$invoice->number.' telah dibuat.',
            ]);

            $this->notifications->send(
                $pemesanan->user,
                'Invoice DP 20% tersedia',
                'Desain awal disetujui. Invoice '.$invoice->number.' sebesar Rp '.number_format((float) $invoice->amount, 0, ',', '.').' telah dibuat.',
                route('pemesanan.show', $pemesanan, false),
                'Lihat invoice'
            );

            $message = 'Desain awal disetujui. Invoice DP 20% telah dibuat.';

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        }

        $pemesanan->update([
            'workflow_stage' => 'approved',
            'status_pemesanan' => Pemesanan::STATUS_IN_PROGRESS,
            'progress' => max(50, (int) $pemesanan->progress),
            'catatan_progres' => 'Desain dan RAB final disetujui pelanggan. Pengerjaan dapat dimulai.',
        ]);

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
            route('admin.pemesanan.show', $pemesanan, false),
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
        abort_unless($invoice && $pemesanan->workflow_stage === 'awaiting_dp', 422, 'Tidak ada invoice DP yang menunggu pembayaran.');

        $data = $request->validate(['bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']]);
        $path = $data['bukti_pembayaran']->store('dp-proofs/order-'.$pemesanan->id, 'payment_evidence');

        $invoice->update(['proof_path' => $path, 'status' => 'submitted']);
        $pemesanan->update(['workflow_stage' => 'dp_verification', 'catatan_progres' => 'Bukti pembayaran DP telah diunggah dan menunggu verifikasi admin.']);

        $this->notifications->admins(
            'Bukti pembayaran DP masuk',
            'Pelanggan mengunggah bukti DP untuk proyek DI-'.$pemesanan->id.'.',
            route('admin.pemesanan.show', $pemesanan, false),
            'Verifikasi pembayaran'
        );

        return back()->with('success', 'Bukti pembayaran DP berhasil dikirim.');
    }

    public function verifyDp(Request $request, Pemesanan $pemesanan)
    {
        $invoice = $pemesanan->dpInvoice;
        abort_unless($invoice && $invoice->status === 'submitted', 422, 'Tidak ada pembayaran DP yang menunggu verifikasi.');

        $invoice->update(['status' => 'paid', 'verified_by' => $request->user()->id, 'verified_at' => now()]);
        $pemesanan->update(['workflow_stage' => 'survey_pending', 'progress' => 30, 'catatan_progres' => 'DP telah diverifikasi. Admin dapat menugaskan desainer dan menjadwalkan survei lokasi.']);

        $this->notifications->send(
            $pemesanan->user,
            'Pembayaran DP terverifikasi',
            'Pembayaran DP proyek DI-'.$pemesanan->id.' telah diverifikasi. Tim Daiku akan menjadwalkan survei lokasi.',
            route('pemesanan.show', $pemesanan, false),
            'Lihat proyek'
        );

        return back()->with('success', 'DP berhasil diverifikasi. Proyek siap dijadwalkan untuk survei.');
    }

    public function scheduleSurvey(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->workflow_stage === 'survey_pending', 422, 'Survei hanya dapat dijadwalkan setelah DP diverifikasi.');
        $data = $request->validate([
            'designer_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'designer'))],
            'survey_scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'survey_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $pemesanan->update([
            ...$data,
            'workflow_stage' => 'survey_scheduled',
            'progress' => 35,
            'catatan_progres' => 'Survei lokasi telah dijadwalkan dan menunggu diselesaikan oleh desainer.',
        ]);

        $surveyDate = $pemesanan->fresh()->survey_scheduled_at->format('d M Y, H:i');
        $surveyMessage = 'Survei proyek DI-'.$pemesanan->id.' dijadwalkan pada '.$surveyDate.'.';
        $this->notifications->send($pemesanan->user, 'Survei lokasi dijadwalkan', $surveyMessage, route('pemesanan.show', $pemesanan, false), 'Lihat jadwal');
        if ($pemesanan->designer) {
            $this->notifications->send($pemesanan->designer, 'Survei lokasi dijadwalkan', $surveyMessage, route('pemesanan.show', $pemesanan, false), 'Lihat jadwal');
        }

        return back()->with('success', 'Desainer ditugaskan dan survei lokasi berhasil dijadwalkan.');
    }

    public function completeSurvey(Request $request, Pemesanan $pemesanan)
    {
        abort_unless($pemesanan->designer_id === $request->user()->id, 403);
        abort_unless($pemesanan->workflow_stage === 'survey_scheduled', 422, 'Survei belum dapat diselesaikan pada tahap ini.');

        $data = $request->validate([
            'survey_result' => ['required', 'string', 'max:5000'],
            'survey_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        if ($request->hasFile('survey_document')) {
            $file = $data['survey_document'];
            $path = $file->store('project-documents/'.$pemesanan->id.'/survey', 'local');
            $version = (int) $pemesanan->documents()->where('document_type', 'survey')->max('version') + 1;
            $pemesanan->documents()->create([
                'uploaded_by' => $request->user()->id,
                'stage' => 'final',
                'document_type' => 'survey',
                'submission_round' => (int) $pemesanan->final_round,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'version' => $version,
            ]);
        }

        $pemesanan->update([
            'survey_result' => $data['survey_result'],
            'survey_completed_at' => now(),
            'workflow_stage' => 'final_design',
            'progress' => 40,
            'catatan_progres' => 'Survei lokasi selesai. Desainer dapat menyusun desain 3D detail dan RAB final.',
        ]);

        $this->notifications->send(
            $pemesanan->user,
            'Survei lokasi selesai',
            'Survei proyek DI-'.$pemesanan->id.' telah diselesaikan. Desainer akan menyiapkan desain dan RAB final.',
            route('pemesanan.show', $pemesanan, false),
            'Lihat proyek'
        );
        $this->notifications->admins(
            'Survei lokasi selesai',
            'Desainer menyelesaikan survei proyek DI-'.$pemesanan->id.'.',
            route('admin.pemesanan.show', $pemesanan, false),
            'Tinjau hasil survei'
        );

        return back()->with('success', 'Hasil survei tersimpan. Tahap desain dan RAB final telah dibuka.');
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
        $stats = $this->workItems->stats();
        $workItems = $this->workItems->paginate($request);

        $designers = User::where('role', 'designer')->orderBy('nama')->get(['id', 'nama']);
        $customers = User::where('role', 'pelanggan')->orderBy('nama')->get(['id', 'nama', 'email', 'no_telp']);

        return view('admin.pemesanan.index', compact('workItems', 'stats', 'designers', 'customers'));
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
            'target_selesai' => ['nullable', 'date'],
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
            'target_selesai' => ['nullable', 'date'],
            'catatan_progres' => ['required', 'string', 'max:2000'],
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

    private function authorizeOrderAccess(Request $request, Pemesanan $pemesanan): void
    {
        $user = $request->user();
        $canAccess = $user->isAdmin()
            || $pemesanan->id_user === $user->id
            || ($user->isDesigner() && $pemesanan->designer_id === $user->id);

        abort_unless($canAccess, 403);
    }
}
