<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activities = $user->pemesanans()
            ->with('katalog')
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

                return (object) [
                    'type' => 'pemesanan',
                    'type_label' => 'Pesanan proyek',
                    'reference' => $reference,
                    'title' => $title ?: 'Pesanan desain',
                    'meta' => $meta ?: 'Detail proyek tersedia',
                    'normalized_status' => match ($pemesanan->status_pemesanan) {
                        'pending' => 'pending',
                        'dikonfirmasi' => 'confirmed',
                        'sedang_dikerjakan' => 'in_progress',
                        'selesai' => 'completed',
                        'dibatalkan' => 'cancelled',
                        default => 'pending',
                    },
                    'progress' => (int) $pemesanan->progress,
                    'created_at' => $pemesanan->created_at,
                    'detail_url' => route('pemesanan.show', $pemesanan),
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
                    'normalized_status' => match ($konsultasi->status) {
                        'pending' => 'pending',
                        'confirmed' => 'confirmed',
                        'completed' => 'completed',
                        'cancelled' => 'cancelled',
                        default => 'pending',
                    },
                    'progress' => null,
                    'created_at' => $konsultasi->created_at,
                    'detail_url' => route('konsultasi.show', $konsultasi),
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
}
