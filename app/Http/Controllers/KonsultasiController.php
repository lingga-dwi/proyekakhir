<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Konsultasi;

class KonsultasiController extends Controller
{
    public function index()
    {
        return view('konsultasi.index');
    }

    public function create()
    {
        return view('konsultasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'no_telp' => 'required|string',
            'jenis_konsultasi' => 'required|in:free_consultation,virtual_design,in_home_visit,chat_support',
            'jenis_ruangan' => 'required|in:living_room,bedroom,kitchen,bathroom,office,whole_house',
            'budget_range' => 'required|in:under_10m,10m_25m,25m_50m,50m_100m,above_100m',
            'timeline' => 'required|in:immediate,1_month,3_months,6_months,flexible',
            'luas_ruangan' => 'nullable|numeric|min:1',
            'gaya_preferensi' => 'nullable|string',
            'deskripsi_kebutuhan' => 'required|string',
            'tanggal_konsultasi' => 'required|date|after:today',
            'waktu_konsultasi' => 'required',
            'upload_foto.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $user = $request->user();

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('upload_foto')) {
            foreach ($request->file('upload_foto') as $file) {
                $path = $file->store('uploads/konsultasi', 'public');
                $uploadedFiles[] = $path;
            }
        }

        $konsultasi = Konsultasi::create([
            'user_id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'no_telp' => $user->no_telp,
            'jenis_konsultasi' => $request->jenis_konsultasi,
            'jenis_ruangan' => $request->jenis_ruangan,
            'budget_range' => $request->budget_range,
            'timeline' => $request->timeline,
            'luas_ruangan' => $request->luas_ruangan,
            'gaya_preferensi' => $request->gaya_preferensi,
            'deskripsi_kebutuhan' => $request->deskripsi_kebutuhan,
            'upload_foto' => $uploadedFiles,
            'tanggal_konsultasi' => $request->tanggal_konsultasi,
            'waktu_konsultasi' => $request->waktu_konsultasi,
            'status' => 'pending',
        ]);

        return redirect()->route('konsultasi.show', $konsultasi->id)
                        ->with('success', 'Konsultasi berhasil dijadwalkan! Tim kami akan menghubungi Anda segera.');
    }

    public function show($id)
    {
        $konsultasi = Konsultasi::with('user')->findOrFail($id);
        
        // Ensure user can only see their own consultation
        if (!auth()->user()->isAdmin() && $konsultasi->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('konsultasi.show', compact('konsultasi'));
    }

    public function myConsultations()
    {
        $konsultasis = auth()->user()->konsultasis()
                                  ->latest()
                                  ->paginate(10);
        
        return view('konsultasi.my-consultations', compact('konsultasis'));
    }

    // Admin methods
    public function adminIndex()
    {
        $konsultasis = Konsultasi::with('user')
                                ->latest()
                                ->paginate(15);
        
        return view('admin.konsultasi.index', compact('konsultasis'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'catatan_admin' => 'nullable|string'
        ]);

        $konsultasi = Konsultasi::findOrFail($id);
        $konsultasi->update([
            'status' => $request->status,
            'catatan_admin' => $request->catatan_admin
        ]);

        return back()->with('success', 'Status konsultasi berhasil diperbarui.');
    }
}
