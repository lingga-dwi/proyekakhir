<?php

namespace App\Http\Controllers;

use App\Support\ReviewPayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activities = $user->pemesanans()
            ->with([
                'katalog', 'konsultasi', 'documents', 'dpInvoice',
                'invoices' => fn ($query) => $query->orderBy('created_at'),
                'statusTrackings' => fn ($query) => $query->orderByDesc('created_at'),
                'documentDecisions' => fn ($query) => $query->orderByDesc('created_at'),
            ])
            ->get()
            ->map(function ($pemesanan) {
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
                $meta = implode(' · ', array_filter([$building, $area]));
                $budgetLabel = match ($pemesanan->konsultasi?->budget_range) {
                    'under_10m' => 'Di bawah Rp 10 Juta',
                    '10m_25m' => 'Rp 10 - 25 Juta',
                    '25m_50m' => 'Rp 25 - 50 Juta',
                    '50m_100m' => 'Rp 50 - 100 Juta',
                    'above_100m' => 'Di atas Rp 100 Juta',
                    default => null,
                };

                $reviewPayload = ReviewPayloadBuilder::build($pemesanan);

                return (object) [
                    'type' => 'pemesanan',
                    'type_label' => 'Pesanan proyek',
                    'reference' => $reference,
                    'title' => $title ?: 'Pesanan desain',
                    'meta' => $meta ?: 'Detail proyek tersedia',
                    'building' => $building,
                    'area' => $area,
                    'budgetLabel' => $budgetLabel,
                    'requirementNote' => $pemesanan->deskripsi_keinginan_desain,
                    'normalized_status' => match ($pemesanan->status_pemesanan) {
                        'pending' => 'pending',
                        'dikonfirmasi' => 'confirmed',
                        'sedang_dikerjakan' => 'in_progress',
                        'selesai' => 'completed',
                        'dibatalkan' => 'cancelled',
                        default => 'pending',
                    },
                    'progress' => (int) $pemesanan->progress,
                    'stage_label' => \App\Support\ProjectStageLabel::forPemesanan($pemesanan),
                    'stage_class' => match (true) {
                        $pemesanan->status_pemesanan === 'dibatalkan' => 'bg-red-100 text-red-800',
                        $pemesanan->status_pemesanan === 'selesai' => 'bg-green-100 text-green-800',
                        $pemesanan->workflow_stage === 'approved' => 'bg-purple-100 text-purple-800',
                        $pemesanan->workflow_stage === 'konsultasi' => 'bg-violet-100 text-violet-700',
                        $pemesanan->workflow_stage === 'awaiting_admin_validation' => 'bg-orange-100 text-orange-800',
                        default => 'bg-blue-100 text-blue-800',
                    },
                    'actor' => \App\Support\ProjectStageLabel::actorFor($pemesanan),
                    'created_at' => $pemesanan->created_at,
                    'target_mulai' => $pemesanan->target_mulai?->translatedFormat('d M Y'),
                    'target_selesai' => $pemesanan->target_selesai?->translatedFormat('d M Y'),
                    'detail_url' => route('pemesanan.show', $pemesanan),
                    'review' => $reviewPayload,
                    'history' => $pemesanan->statusTrackings
                        ->map(fn ($tracking) => [
                            'note' => $tracking->catatan,
                            'date' => $tracking->created_at->translatedFormat('d M Y, H:i'),
                            'timestamp' => $tracking->created_at->toIso8601String(),
                        ])
                        ->concat($pemesanan->documentDecisions->map(fn ($decision) => [
                            'note' => ($decision->stage === 'draft' ? 'Desain awal' : 'Desain final').' · '.($decision->decision === 'approved' ? 'Disetujui' : 'Minta revisi').($decision->feedback ? ': '.$decision->feedback : ''),
                            'date' => $decision->created_at->translatedFormat('d M Y, H:i'),
                            'timestamp' => $decision->created_at->toIso8601String(),
                        ]))
                        ->sortByDesc('timestamp')
                        ->values()
                        ->all(),
                    'searchable' => mb_strtolower(implode(' ', [
                        $reference,
                        $title,
                        $building,
                        $pemesanan->jenis_proyek,
                        $pemesanan->deskripsi_keinginan_desain,
                    ])),
                ];
            })
            ->concat($user->konsultasis()
                ->whereNull('pemesanan_id')
                ->get()
                ->map(function ($konsultasi) {
                    $reference = 'KON-'.str_pad((string) $konsultasi->id, 4, '0', STR_PAD_LEFT);
                    $title = $konsultasi->getJenisKonsultasiLabel();
                    $room = $konsultasi->getJenisRuanganLabel();
                    $summary = trim((string) $konsultasi->deskripsi_kebutuhan);
                    if (in_array(mb_strtolower($summary), ['', '-', 'gaada', 'tidak ada', 'n/a'], true)) {
                        $summary = null;
                    }

                    return (object) [
                        'type' => 'konsultasi',
                        'type_label' => 'Konsultasi',
                        'reference' => $reference,
                        'title' => $title,
                        'meta' => implode(' · ', array_filter([$room, $summary ? Str::limit($summary, 70) : null])),
                        'building' => $room,
                        'area' => null,
                        'budgetLabel' => null,
                        'requirementNote' => $summary,
                        'normalized_status' => match ($konsultasi->status) {
                            'pending' => 'pending',
                            'confirmed' => 'confirmed',
                            'completed' => 'completed',
                            'cancelled' => 'cancelled',
                            default => 'pending',
                        },
                        'progress' => null,
                        'stage_label' => \App\Support\ProjectStageLabel::forKonsultasi($konsultasi),
                        'stage_class' => match ($konsultasi->status) {
                            'pending' => 'bg-violet-100 text-violet-700',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            default => 'bg-slate-100 text-slate-700',
                        },
                        'actor' => match ($konsultasi->status) {
                            'pending' => 'Admin',
                            'confirmed' => 'Desainer',
                            default => null,
                        },
                        'created_at' => $konsultasi->created_at,
                        'target_mulai' => null,
                        'target_selesai' => null,
                        'detail_url' => route('konsultasi.show', $konsultasi),
                        'review' => null,
                        'history' => collect([
                            [
                                'note' => 'Permintaan konsultasi diajukan.',
                                'date' => $konsultasi->created_at->translatedFormat('d M Y, H:i'),
                                'timestamp' => $konsultasi->created_at->toIso8601String(),
                            ],
                        ])
                            ->when($konsultasi->accepted_at, fn ($rows) => $rows->push([
                                'note' => $konsultasi->status === 'cancelled' ? 'Konsultasi ditolak admin.' : 'Konsultasi diterima dan desainer ditugaskan.',
                                'date' => $konsultasi->accepted_at->translatedFormat('d M Y, H:i'),
                                'timestamp' => $konsultasi->accepted_at->toIso8601String(),
                            ]))
                            ->values()
                            ->all(),
                        'searchable' => mb_strtolower(implode(' ', [
                            $reference,
                            $title,
                            $room,
                            $summary,
                        ])),
                    ];
                }))
            ->values();

        $allowedStatuses = ['all', 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $allowedTypes = ['all', 'konsultasi', 'pemesanan'];
        $allowedSorts = ['latest', 'oldest'];

        $status = in_array($request->query('status'), $allowedStatuses, true)
            ? $request->query('status')
            : 'all';
        $type = in_array($request->query('type'), $allowedTypes, true)
            ? $request->query('type')
            : 'all';
        if ($request->query('tab') === 'konsultasi' && ! $request->has('type')) {
            $type = 'konsultasi';
        }
        $sort = in_array($request->query('sort'), $allowedSorts, true)
            ? $request->query('sort')
            : 'latest';
        $search = trim((string) $request->query('q'));

        $statusCounts = collect($allowedStatuses)
            ->mapWithKeys(fn ($key) => [
                $key => $key === 'all'
                    ? $activities->count()
                    : $activities->where('normalized_status', $key)->count(),
            ]);

        if ($status !== 'all') {
            $activities = $activities->where('normalized_status', $status);
        }
        if ($type !== 'all') {
            $activities = $activities->where('type', $type);
        }
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $activities = $activities->filter(fn ($activity) => str_contains($activity->searchable, $needle));
        }

        $activities = ($sort === 'oldest'
            ? $activities->sortBy('created_at')
            : $activities->sortByDesc('created_at'))
            ->values();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $activities = new LengthAwarePaginator(
            $activities->forPage($currentPage, $perPage)->values(),
            $activities->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $filters = compact('status', 'type', 'sort', 'search');

        return view('customer.activities', compact('activities', 'filters', 'statusCounts'));
    }

    public function heartbeat(Request $request)
    {
        $user = $request->user();

        $latestPemesanan = $user->pemesanans()->max('updated_at');
        $latestKonsultasi = $user->konsultasis()->whereNull('pemesanan_id')->max('updated_at');
        $counts = $user->pemesanans()->count().':'.$user->konsultasis()->whereNull('pemesanan_id')->count();

        return response()->json(['signal' => $counts.':'.$latestPemesanan.':'.$latestKonsultasi]);
    }
}
