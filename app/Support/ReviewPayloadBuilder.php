<?php

namespace App\Support;

use App\Models\Pemesanan;
use Illuminate\Support\Facades\Storage;

/**
 * Builds the JSON payload consumed by the customer's "Tinjau Penawaran"
 * review modal (resources/views/customer/activities.blade.php), so both
 * the initial page load (CustomerActivityController) and in-place AJAX
 * transitions (PemesananController::decideDocument) return the same shape.
 */
class ReviewPayloadBuilder
{
    public static function build(Pemesanan $pemesanan): array
    {
        $reference = 'PRY-'.str_pad((string) $pemesanan->id, 4, '0', STR_PAD_LEFT);
        $title = $pemesanan->katalog?->nama_desain
            ?? ucfirst(str_replace('_', ' ', (string) $pemesanan->jenis_proyek));
        $building = trim((string) $pemesanan->jenis_bangunan);
        $building = in_array(mb_strtolower($building), ['', '-', 'belum_ditentukan', 'belum ditentukan'], true)
            ? null
            : ucfirst(str_replace('_', ' ', $building));
        $area = (float) $pemesanan->luas_area > 0
            ? number_format((float) $pemesanan->luas_area, 2, ',', '.').' m²'
            : null;

        $mode = match ($pemesanan->workflow_stage) {
            'awaiting_draft_approval' => 'draft',
            'awaiting_final_approval' => 'final',
            'awaiting_dp' => 'payment',
            default => 'status',
        };

        $requirementNote = null;
        $decisionUrl = null;
        $budgetLabel = null;

        $formatDoc = fn ($document, string $stage) => [
            'stage' => $stage,
            'type' => $document->document_type,
            'name' => $document->original_name,
            'size' => Storage::disk('local')->exists($document->path)
                ? Storage::disk('local')->size($document->path)
                : null,
            'downloadUrl' => route('pemesanan.document.download', [$pemesanan->id, $document->id]),
        ];

        // Draft/final documents are only visible to the customer once admin
        // has validated and sent them onward — a designer's in-progress
        // upload (still at draft_design/revision_requested/final_design,
        // or awaiting admin validation) must not be downloadable early.
        $stagesPastDraftValidation = [
            'awaiting_draft_approval', 'awaiting_dp', 'dp_verification',
            'survey_scheduled', 'final_design', 'awaiting_admin_validation_final',
            'awaiting_final_approval', 'approved',
        ];
        $stagesPastFinalValidation = ['awaiting_final_approval', 'approved'];

        $draftDocuments = in_array($pemesanan->workflow_stage, $stagesPastDraftValidation, true)
            ? $pemesanan->documents
                ->where('stage', 'draft')
                ->where('submission_round', (int) $pemesanan->draft_round)
                ->whereIn('document_type', ['design', 'rab'])
                ->groupBy('document_type')
                ->map(fn ($group) => $group->sortByDesc('version')->first())
                ->map(fn ($document) => $formatDoc($document, 'draft'))
                ->values()->all()
            : [];

        $finalDocuments = in_array($pemesanan->workflow_stage, $stagesPastFinalValidation, true)
            ? $pemesanan->documents
                ->where('stage', 'final')
                ->where('submission_round', (int) $pemesanan->final_round)
                ->whereIn('document_type', ['design', 'rab'])
                ->groupBy('document_type')
                ->map(fn ($group) => $group->sortByDesc('version')->first())
                ->map(fn ($document) => $formatDoc($document, 'final'))
                ->values()->all()
            : [];

        $documents = [...$draftDocuments, ...$finalDocuments];

        $requirementNote = $pemesanan->deskripsi_keinginan_desain;
        $budgetLabel = match ($pemesanan->konsultasi?->budget_range) {
            'under_10m' => 'Di bawah Rp 10 Juta',
            '10m_25m' => 'Rp 10 - 25 Juta',
            '25m_50m' => 'Rp 25 - 50 Juta',
            '50m_100m' => 'Rp 50 - 100 Juta',
            'above_100m' => 'Di atas Rp 100 Juta',
            default => null,
        };

        if (in_array($mode, ['draft', 'final'], true)) {
            $decisionUrl = route('pemesanan.document.decision', $pemesanan);
        }

        return [
            'id' => $pemesanan->id,
            'reference' => $reference,
            'stage' => $mode,
            'stageLabel' => ProjectStageLabel::forPemesanan($pemesanan),
            'progressNote' => $pemesanan->catatan_progres,
            'title' => $title ?: 'Pesanan desain',
            'building' => $building,
            'area' => $area,
            'budgetLabel' => $budgetLabel,
            'requirementNote' => $requirementNote,
            'documents' => $documents,
            'decisionUrl' => $decisionUrl,
            'totalHarga' => (float) $pemesanan->total_harga,
            'invoices' => $pemesanan->invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'name' => $invoice->name,
                'amount' => (float) $invoice->amount,
                'status' => $invoice->status,
                'dueDate' => $invoice->due_date?->translatedFormat('d M Y'),
                'note' => $invoice->note,
                'uploadUrl' => $invoice->status !== 'paid'
                    ? route('pemesanan.invoice-evidence.upload', [$pemesanan, $invoice])
                    : null,
            ])->values()->all(),
            'bankDisplayName' => config('company.bank.display_name'),
            'bankAccountNumber' => config('company.bank.account_number'),
            'bankAccountHolder' => config('company.bank.account_holder'),
        ];
    }
}
