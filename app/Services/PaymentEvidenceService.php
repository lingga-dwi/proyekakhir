<?php

namespace App\Services;

use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentEvidenceService
{
    private const STATUS_PENDING = 'Menunggu Verifikasi Pembayaran';

    private const STATUS_VERIFIED = 'Pembayaran Diverifikasi';

    public function upload(Pemesanan $pemesanan, UploadedFile $file, User $actor): void
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'file');
        $path = $file->storeAs(
            'payment-proofs/order-'.$pemesanan->id,
            now()->format('YmdHis').'-'.Str::uuid().'.'.$extension,
            'payment_evidence'
        );

        $previousStatus = $this->summary($pemesanan)['tracking']?->status;

        $pemesanan->statusTrackings()->create([
            'actor_id' => $actor->id,
            'previous_status' => $previousStatus,
            'status' => self::STATUS_PENDING,
            'progress' => $pemesanan->progress,
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Bukti pembayaran diunggah. File: '.$path,
        ]);
    }

    public function verify(Pemesanan $pemesanan, User $actor): bool
    {
        $summary = $this->summary($pemesanan);

        if ($summary['state'] !== 'pending' || ! $summary['proof_path']) {
            return false;
        }

        $pemesanan->statusTrackings()->create([
            'actor_id' => $actor->id,
            'previous_status' => self::STATUS_PENDING,
            'status' => self::STATUS_VERIFIED,
            'progress' => $pemesanan->progress,
            'tanggal_update' => now()->toDateString(),
            'catatan' => 'Bukti pembayaran diverifikasi oleh admin.',
        ]);

        return true;
    }

    /**
     * @return array{state: string, label: string, proof_path: ?string, tracking: mixed}
     */
    public function summary(Pemesanan $pemesanan): array
    {
        $trackings = $pemesanan->statusTrackings()
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_VERIFIED])
            ->latest('id')
            ->get();

        $latest = $trackings->first();
        $proofPath = $trackings
            ->map(fn ($tracking) => $this->proofPathFromNote($tracking->catatan))
            ->first(fn ($path) => $path && Storage::disk('payment_evidence')->exists($path));

        if (! $latest || ! $proofPath) {
            return ['state' => 'none', 'label' => 'Belum ada bukti pembayaran', 'proof_path' => null, 'tracking' => null];
        }

        if ($latest->status === self::STATUS_VERIFIED) {
            return ['state' => 'verified', 'label' => 'Pembayaran diverifikasi', 'proof_path' => $proofPath, 'tracking' => $latest];
        }

        return ['state' => 'pending', 'label' => self::STATUS_PENDING, 'proof_path' => $proofPath, 'tracking' => $latest];
    }

    private function proofPathFromNote(?string $note): ?string
    {
        if (! $note || ! preg_match('/payment-proofs\/order-\d+\/[A-Za-z0-9._-]+/', $note, $matches)) {
            return null;
        }

        return $matches[0];
    }
}
