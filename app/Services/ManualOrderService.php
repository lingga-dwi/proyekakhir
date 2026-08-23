<?php

namespace App\Services;

use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManualOrderService
{
    public function create(array $data, User $actor): Pemesanan
    {
        return DB::transaction(function () use ($data, $actor) {
            $customer = $data['customer_mode'] === 'new'
                ? $this->createCustomer($data)
                : User::where('role', 'pelanggan')->findOrFail($data['id_user']);

            $order = Pemesanan::create([
                'id_user' => $customer->id,
                'tanggal_pesan' => $data['tanggal_pesan'],
                'sumber_masuk' => $data['sumber_masuk'],
                'status_pemesanan' => Pemesanan::STATUS_PENDING,
                'progress' => 0,
                'total_harga' => 0,
                'jenis_proyek' => trim($data['jenis_proyek']),
                'jenis_bangunan' => filled($data['jenis_bangunan'] ?? null) ? trim($data['jenis_bangunan']) : null,
                'deskripsi_keinginan_desain' => filled($data['deskripsi_keinginan_desain'] ?? null)
                    ? trim($data['deskripsi_keinginan_desain'])
                    : null,
            ]);

            $source = match ($data['sumber_masuk']) {
                'kantor' => 'kunjungan kantor',
                'whatsapp' => 'WhatsApp',
                'telepon' => 'telepon',
                'instagram' => 'Instagram',
                default => 'website',
            };

            $order->statusTrackings()->create([
                'actor_id' => $actor->id,
                'previous_status' => null,
                'status' => Pemesanan::STATUS_PENDING,
                'progress' => 0,
                'tanggal_update' => now()->toDateString(),
                'catatan' => "Pesanan dicatat admin dari {$source}.",
            ]);

            return $order;
        });
    }

    private function createCustomer(array $data): User
    {
        return User::create([
            'nama' => trim($data['nama']),
            'email' => strtolower(trim($data['email'])),
            'no_telp' => trim($data['no_telp']),
            'alamat' => filled($data['alamat'] ?? null) ? trim($data['alamat']) : null,
            'password' => Str::random(40),
            'role' => 'pelanggan',
        ]);
    }
}
