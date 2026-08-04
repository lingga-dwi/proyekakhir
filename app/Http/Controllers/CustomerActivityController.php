<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activities = $user->pemesanans()
            ->with('katalog')
            ->latest()
            ->get()
            ->map(fn ($pemesanan) => (object) [
                'type' => 'pemesanan',
                'record' => $pemesanan,
                'created_at' => $pemesanan->created_at,
            ])
            ->concat($user->konsultasis()
            ->whereNull('pemesanan_id')
            ->latest()
            ->get()
            ->map(fn ($konsultasi) => (object) [
                'type' => 'konsultasi',
                'record' => $konsultasi,
                'created_at' => $konsultasi->created_at,
            ]))
            ->sortByDesc('created_at')
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

        return view('customer.activities', compact('activities'));
    }
}
