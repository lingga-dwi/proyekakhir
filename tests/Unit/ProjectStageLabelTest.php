<?php

namespace Tests\Unit;

use App\Models\Konsultasi;
use App\Models\Pemesanan;
use App\Support\ProjectStageLabel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProjectStageLabelTest extends TestCase
{
    public function test_konsultasi_stage_labels(): void
    {
        $this->assertSame('Menunggu Konfirmasi', ProjectStageLabel::forKonsultasi(new Konsultasi(['status' => Konsultasi::STATUS_PENDING])));
        $this->assertSame('Konsultasi', ProjectStageLabel::forKonsultasi(new Konsultasi(['status' => Konsultasi::STATUS_CONFIRMED])));
        $this->assertNull(ProjectStageLabel::forKonsultasi(new Konsultasi(['status' => Konsultasi::STATUS_COMPLETED])));
        $this->assertNull(ProjectStageLabel::forKonsultasi(new Konsultasi(['status' => Konsultasi::STATUS_CANCELLED])));
    }

    #[DataProvider('pemesananStageProvider')]
    public function test_pemesanan_stage_labels(string $statusPemesanan, ?string $workflowStage, string $expected): void
    {
        $pemesanan = new Pemesanan([
            'status_pemesanan' => $statusPemesanan,
            'workflow_stage' => $workflowStage,
        ]);

        $this->assertSame($expected, ProjectStageLabel::forPemesanan($pemesanan));
    }

    public static function pemesananStageProvider(): array
    {
        return [
            'konsultasi' => [Pemesanan::STATUS_CONFIRMED, 'konsultasi', 'Konsultasi'],
            'draft design' => [Pemesanan::STATUS_CONFIRMED, 'draft_design', 'Menunggu Desain Awal & Draft RAB'],
            'revision requested' => [Pemesanan::STATUS_CONFIRMED, 'revision_requested', 'Menunggu Desain Awal & Draft RAB'],
            'awaiting draft approval' => [Pemesanan::STATUS_CONFIRMED, 'awaiting_draft_approval', 'Menunggu Persetujuan'],
            'awaiting dp' => [Pemesanan::STATUS_CONFIRMED, 'awaiting_dp', 'Menunggu Pembayaran DP'],
            'dp verification' => [Pemesanan::STATUS_CONFIRMED, 'dp_verification', 'Menunggu Verifikasi Pembayaran'],
            'survey scheduled' => [Pemesanan::STATUS_CONFIRMED, 'survey_scheduled', 'Survei'],
            'final design' => [Pemesanan::STATUS_CONFIRMED, 'final_design', 'Desain Detail / 3D'],
            'awaiting final approval' => [Pemesanan::STATUS_CONFIRMED, 'awaiting_final_approval', 'Menunggu Persetujuan Final'],
            'approved but still confirmed status = pengerjaan' => [Pemesanan::STATUS_CONFIRMED, 'approved', 'Pengerjaan'],
            'approved and in progress status = pengerjaan' => [Pemesanan::STATUS_IN_PROGRESS, 'approved', 'Pengerjaan'],
            'completed overrides workflow stage' => [Pemesanan::STATUS_COMPLETED, 'approved', 'Selesai'],
            'cancelled overrides workflow stage' => [Pemesanan::STATUS_CANCELLED, 'draft_design', 'Dibatalkan'],
        ];
    }

    #[DataProvider('actorForProvider')]
    public function test_actor_for(string $statusPemesanan, ?string $workflowStage, ?string $expected): void
    {
        $pemesanan = new Pemesanan([
            'status_pemesanan' => $statusPemesanan,
            'workflow_stage' => $workflowStage,
        ]);

        $this->assertSame($expected, ProjectStageLabel::actorFor($pemesanan));
    }

    public static function actorForProvider(): array
    {
        return [
            'konsultasi' => [Pemesanan::STATUS_CONFIRMED, 'konsultasi', 'Desainer'],
            'draft design' => [Pemesanan::STATUS_CONFIRMED, 'draft_design', 'Desainer'],
            'revision requested' => [Pemesanan::STATUS_CONFIRMED, 'revision_requested', 'Desainer'],
            'survey scheduled' => [Pemesanan::STATUS_CONFIRMED, 'survey_scheduled', 'Desainer'],
            'final design' => [Pemesanan::STATUS_CONFIRMED, 'final_design', 'Desainer'],
            'approved / pengerjaan' => [Pemesanan::STATUS_IN_PROGRESS, 'approved', 'Desainer'],
            'awaiting admin validation' => [Pemesanan::STATUS_CONFIRMED, 'awaiting_admin_validation', 'Admin'],
            'dp verification' => [Pemesanan::STATUS_CONFIRMED, 'dp_verification', 'Admin'],
            'awaiting draft approval' => [Pemesanan::STATUS_CONFIRMED, 'awaiting_draft_approval', 'Pelanggan'],
            'awaiting dp' => [Pemesanan::STATUS_CONFIRMED, 'awaiting_dp', 'Pelanggan'],
            'awaiting final approval' => [Pemesanan::STATUS_CONFIRMED, 'awaiting_final_approval', 'Pelanggan'],
            'completed has no actor' => [Pemesanan::STATUS_COMPLETED, 'approved', null],
            'cancelled has no actor' => [Pemesanan::STATUS_CANCELLED, 'draft_design', null],
        ];
    }
}
