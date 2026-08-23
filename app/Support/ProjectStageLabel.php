<?php

namespace App\Support;

use App\Models\Konsultasi;
use App\Models\Pemesanan;

/**
 * Single source of truth for the customer-facing stage name shown to
 * Pelanggan, Desainer, and Admin alike, so the same underlying state
 * always reads the same everywhere.
 */
class ProjectStageLabel
{
    /**
     * Stage label for a consultation that has not yet become a project.
     * Returns null once the consultation is completed/cancelled — from
     * that point the Pemesanan record is the source of truth.
     */
    public static function forKonsultasi(Konsultasi $konsultasi): ?string
    {
        return match ($konsultasi->status) {
            Konsultasi::STATUS_PENDING => 'Menunggu Konfirmasi',
            Konsultasi::STATUS_CONFIRMED => 'Konsultasi',
            default => null,
        };
    }

    public static function forPemesanan(Pemesanan $pemesanan): string
    {
        if ($pemesanan->status_pemesanan === Pemesanan::STATUS_CANCELLED) {
            return 'Dibatalkan';
        }

        if ($pemesanan->status_pemesanan === Pemesanan::STATUS_COMPLETED) {
            return 'Selesai';
        }

        if ($pemesanan->workflow_stage === 'approved') {
            return 'Pengerjaan Proyek';
        }

        return match ($pemesanan->workflow_stage) {
            'konsultasi' => 'Konsultasi',
            'draft_design', 'revision_requested' => 'Menunggu Desain Awal & Draft RAB',
            'awaiting_admin_validation' => 'Menunggu Validasi Admin',
            'awaiting_draft_approval' => 'Menunggu Persetujuan',
            'awaiting_dp' => 'Menunggu Pembayaran DP',
            'dp_verification' => 'Menunggu Verifikasi Pembayaran',
            'survey_scheduled' => 'Survei',
            'final_design' => 'Desain Detail / 3D',
            'awaiting_admin_validation_final' => 'Menunggu Validasi Admin',
            'awaiting_final_approval' => 'Menunggu Persetujuan Final',
            default => 'Proses Proyek',
        };
    }

    /**
     * Which actor needs to act next for the project's current stage, shown
     * to admin as "Oleh: ..." under the status badge so they know whose
     * court the ball is in without opening the project.
     */
    public static function actorFor(Pemesanan $pemesanan): ?string
    {
        if (in_array($pemesanan->status_pemesanan, [Pemesanan::STATUS_CANCELLED, Pemesanan::STATUS_COMPLETED], true)) {
            return null;
        }

        return match ($pemesanan->workflow_stage) {
            'konsultasi', 'draft_design', 'revision_requested', 'survey_scheduled', 'final_design' => 'Desainer',
            'awaiting_admin_validation', 'dp_verification', 'awaiting_admin_validation_final' => 'Admin',
            'awaiting_draft_approval', 'awaiting_dp', 'awaiting_final_approval' => 'Pelanggan',
            'approved' => 'Tim Lapangan',
            default => null,
        };
    }
}
