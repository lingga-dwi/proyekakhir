<?php

namespace App\Services;

use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectWorkflowService
{
    private const TRANSITIONS = [
        Pemesanan::STATUS_PENDING => [Pemesanan::STATUS_PENDING, Pemesanan::STATUS_CONFIRMED, Pemesanan::STATUS_CANCELLED],
        Pemesanan::STATUS_CONFIRMED => [Pemesanan::STATUS_CONFIRMED, Pemesanan::STATUS_IN_PROGRESS, Pemesanan::STATUS_CANCELLED],
        Pemesanan::STATUS_IN_PROGRESS => [Pemesanan::STATUS_IN_PROGRESS, Pemesanan::STATUS_COMPLETED, Pemesanan::STATUS_CANCELLED],
        Pemesanan::STATUS_COMPLETED => [Pemesanan::STATUS_COMPLETED],
        Pemesanan::STATUS_CANCELLED => [Pemesanan::STATUS_CANCELLED],
    ];

    private const PROJECT_FIELDS = [
        'status_pemesanan',
        'progress',
        'target_mulai',
        'target_selesai',
        'designer_id',
        'total_harga',
        'catatan_progres',
    ];

    public function update(
        Pemesanan $project,
        array $changes,
        ?User $actor,
        string $defaultNote = 'Progres proyek diperbarui oleh admin.'
    ): bool {
        $currentStatus = $project->status_pemesanan;
        $nextStatus = $changes['status_pemesanan'] ?? $currentStatus;

        $this->validateTransition($currentStatus, $nextStatus);

        $changes = Arr::only($changes, self::PROJECT_FIELDS);
        $changes['status_pemesanan'] = $nextStatus;
        $changes['progress'] = $this->normalizedProgress(
            $nextStatus,
            array_key_exists('progress', $changes) ? (int) $changes['progress'] : null,
            (int) $project->progress
        );

        $dirtyChanges = collect($changes)
            ->reject(fn ($value, $key) => $this->sameValue($project->{$key}, $value))
            ->all();

        if ($dirtyChanges === []) {
            return false;
        }

        DB::transaction(function () use ($project, $changes, $currentStatus, $actor, $defaultNote): void {
            $project->update($changes);
            $project->statusTrackings()->create([
                'actor_id' => $actor?->id,
                'previous_status' => $currentStatus,
                'status' => $project->status_pemesanan,
                'progress' => $project->progress,
                'tanggal_update' => now()->toDateString(),
                'catatan' => ($changes['catatan_progres'] ?? null) ?: $defaultNote,
            ]);
        });

        return true;
    }

    private function validateTransition(string $currentStatus, string $nextStatus): void
    {
        if (! in_array($nextStatus, self::TRANSITIONS[$currentStatus] ?? [], true)) {
            throw ValidationException::withMessages([
                'status_pemesanan' => "Status {$currentStatus} tidak dapat langsung diubah menjadi {$nextStatus}.",
            ]);
        }
    }

    private function normalizedProgress(string $status, ?int $requested, int $current): int
    {
        if ($status === Pemesanan::STATUS_COMPLETED) {
            return 100;
        }

        if ($status === Pemesanan::STATUS_PENDING) {
            if ($requested !== null && $requested !== 0) {
                $this->invalidProgress('Pesanan baru harus memiliki progres 0%.');
            }

            return 0;
        }

        if ($status === Pemesanan::STATUS_CONFIRMED) {
            $progress = $requested ?? max($current, 10);
            if ($progress < 10 || $progress > 24) {
                $this->invalidProgress('Tahap persiapan harus memiliki progres 10–24%.');
            }

            return $progress;
        }

        if ($status === Pemesanan::STATUS_IN_PROGRESS) {
            $progress = $requested ?? max($current, 25);
            if ($progress < 25 || $progress > 99) {
                $this->invalidProgress('Proyek aktif harus memiliki progres 25–99%.');
            }

            return $progress;
        }

        return max(0, min(100, $requested ?? $current));
    }

    private function invalidProgress(string $message): never
    {
        throw ValidationException::withMessages(['progress' => $message]);
    }

    private function sameValue(mixed $current, mixed $next): bool
    {
        if ($current instanceof \DateTimeInterface) {
            return $current->format('Y-m-d') === (string) $next;
        }

        return (string) ($current ?? '') === (string) ($next ?? '');
    }
}
