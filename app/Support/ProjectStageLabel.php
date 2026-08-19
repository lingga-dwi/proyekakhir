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
            return 'Pengerjaan';
        }

        return match ($pemesanan->workflow_stage) {
            'konsultasi' => 'Konsultasi',
            'draft_design', 'revision_requested' => 'Menunggu Desain Awal & Draft RAB',
            'awaiting_draft_approval' => 'Menunggu Persetujuan',
            'awaiting_dp' => 'Menunggu Pembayaran DP',
            'dp_verification' => 'Menunggu Verifikasi Pembayaran',
            'survey_pending' => 'Menunggu Jadwal Survei',
            'survey_scheduled' => 'Survei Terjadwal',
            'final_design' => 'Desain Detail / 3D',
            'awaiting_final_approval' => 'Menunggu Persetujuan Final',
            default => 'Proses Proyek',
        };
    }
}
