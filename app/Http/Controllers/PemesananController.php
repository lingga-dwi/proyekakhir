<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\Katalog;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    public function create(Request $request)
    {
        $katalog_id = $request->get('katalog_id');
        $katalog = null;
        
        if ($katalog_id) {
            $katalog = Katalog::with('category')->findOrFail($katalog_id);
        }
        
        return view('pemesanan.create', compact('katalog'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
            'jenis_proyek' => 'required|string',
            'jenis_bangunan' => 'required|string',
            'luas_area' => 'required|numeric|min:1',
            'jumlah_ruangan' => 'required|integer|min:1',
            'gaya_desain_preferensi' => 'required|string',
            'warna_dominan' => 'required|string',
            'deskripsi_keinginan_desain' => 'required|string',
            'upload_denah_foto.*' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'katalog_id' => 'nullable|exists:katalog,id',
            'terms' => 'accepted',
        ]);

        $katalog = null;
        if ($request->filled('katalog_id')) {
            $katalog = Katalog::findOrFail($request->katalog_id);
        }

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('upload_denah_foto')) {
            foreach ($request->file('upload_denah_foto') as $file) {
                $path = $file->store('uploads/denah', 'public');
                $uploadedFiles[] = $path;
            }
        }


        // Create Pemesanan
        $pemesanan = Pemesanan::create([
            'id_user' => auth()->id(),
            'katalog_id' => $katalog?->id,
            'tanggal_pesan' => now()->toDateString(),
            'status_pemesanan' => 'pending',
            'total_harga' => $katalog ? $katalog->harga_estimasi : 0,
            'jenis_proyek' => $request->jenis_proyek,
            'jenis_bangunan' => $request->jenis_bangunan,
            'luas_area' => $request->luas_area,
            'jumlah_ruangan' => $request->jumlah_ruangan,
            'gaya_desain_preferensi' => $request->gaya_desain_preferensi,
            'warna_dominan' => $request->warna_dominan,
            'deskripsi_keinginan_desain' => $request->deskripsi_keinginan_desain,
            'upload_denah_foto' => $uploadedFiles,
        ]);

        return redirect()->route('pemesanan.show', $pemesanan->id)
                        ->with('success', 'Pesanan berhasil dibuat! Tim kami akan segera menghubungi Anda.');
    }

    public function show($id)
    {
        $pemesanan = Pemesanan::with(['user', 'katalog', 'rfq.katalog'])->findOrFail($id);
        
        // Ensure user can only see their own orders (unless admin)
        if (!auth()->user()->isAdmin() && $pemesanan->id_user !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('pemesanan.show', compact('pemesanan'));
    }

    public function myOrders()
    {
        $pemesanans = auth()->user()->pemesanans()
                                  ->with(['katalog', 'rfq.katalog'])
                                  ->latest()
                                  ->paginate(10);
        
        return view('pemesanan.my-orders', compact('pemesanans'));
    }

    public function index()
    {
        $pemesanans = Pemesanan::with(['user', 'katalog'])
                              ->latest()
                              ->paginate(10);
        
        return view('admin.pemesanan.index', compact('pemesanans'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,dikonfirmasi,sedang_dikerjakan,selesai,dibatalkan'
        ]);

        $pemesanan = Pemesanan::findOrFail($id);
        $pemesanan->update(['status_pemesanan' => $request->status]);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
