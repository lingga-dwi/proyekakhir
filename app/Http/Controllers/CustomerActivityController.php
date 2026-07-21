<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerActivityController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = in_array($request->query('tab'), ['pesanan', 'konsultasi'], true)
            ? $request->query('tab')
            : 'pesanan';

        $user = $request->user();
        $pemesanans = $user->pemesanans()
            ->with(['katalog', 'rfq.katalog'])
            ->latest()
            ->paginate(10, ['*'], 'pesanan_page')
            ->withQueryString();
        $konsultasis = $user->konsultasis()
            ->latest()
            ->paginate(10, ['*'], 'konsultasi_page')
            ->withQueryString();

        return view('customer.activities', compact(
            'activeTab',
            'pemesanans',
            'konsultasis',
        ));
    }
}
