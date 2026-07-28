<?php

namespace App\Services;

use App\Models\Konsultasi;
use App\Models\Pemesanan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWorkItemService
{
    public function stats(): array
    {
        return [
            'total' => Pemesanan::count(),
            'pending' => Pemesanan::where('status_pemesanan', Pemesanan::STATUS_PENDING)->count(),
            'confirmed' => Pemesanan::where('status_pemesanan', Pemesanan::STATUS_CONFIRMED)->count(),
            'active' => Pemesanan::where('status_pemesanan', Pemesanan::STATUS_IN_PROGRESS)->count(),
            'completed' => Pemesanan::where('status_pemesanan', Pemesanan::STATUS_COMPLETED)->count(),
            'consultations_pending' => Konsultasi::where('status', Konsultasi::STATUS_PENDING)->count(),
        ];
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        $consultations = DB::table('konsultasi as consultation')
            ->leftJoin('users as customer', 'customer.id', '=', 'consultation.user_id')
            ->whereNull('consultation.pemesanan_id')
            ->select([
                DB::raw("'consultation' as item_type"),
                'consultation.id',
                DB::raw('COALESCE(customer.nama, consultation.nama) as customer_name'),
                DB::raw('COALESCE(customer.email, consultation.email) as customer_email'),
                'consultation.no_telp as customer_phone',
                'consultation.jenis_ruangan as title',
                'consultation.deskripsi_kebutuhan as detail',
                'consultation.jenis_konsultasi as space',
                DB::raw("'website' as source"),
                'consultation.status',
                DB::raw('0 as progress'),
                'consultation.tanggal_konsultasi as scheduled_date',
                'consultation.waktu_konsultasi as scheduled_time',
                DB::raw('NULL as designer_id'),
                DB::raw('NULL as designer_name'),
                DB::raw('NULL as target_selesai'),
                DB::raw('0 as total_harga'),
                'consultation.catatan_admin as note',
                'consultation.created_at',
            ]);

        $orders = DB::table('pemesanan as orders')
            ->join('users as customer', 'customer.id', '=', 'orders.id_user')
            ->leftJoin('users as designer', 'designer.id', '=', 'orders.designer_id')
            ->select([
                DB::raw("'order' as item_type"),
                'orders.id',
                'customer.nama as customer_name',
                'customer.email as customer_email',
                'customer.no_telp as customer_phone',
                'orders.jenis_proyek as title',
                'orders.deskripsi_keinginan_desain as detail',
                'orders.jenis_bangunan as space',
                'orders.sumber_masuk as source',
                'orders.status_pemesanan as status',
                'orders.progress',
                'orders.tanggal_pesan as scheduled_date',
                DB::raw('NULL as scheduled_time'),
                'orders.designer_id',
                'designer.nama as designer_name',
                'orders.target_selesai',
                'orders.total_harga',
                'orders.catatan_progres as note',
                'orders.created_at',
            ]);

        $query = DB::query()->fromSub($consultations->unionAll($orders), 'work_items');
        $this->applySearch($query, trim((string) $request->string('search')));

        $stages = [
            'consultation_pending' => ['consultation', Konsultasi::STATUS_PENDING],
            'consultation_confirmed' => ['consultation', Konsultasi::STATUS_CONFIRMED],
            'consultation_completed' => ['consultation', Konsultasi::STATUS_COMPLETED],
            'consultation_cancelled' => ['consultation', Konsultasi::STATUS_CANCELLED],
            'order_pending' => ['order', Pemesanan::STATUS_PENDING],
            'order_confirmed' => ['order', Pemesanan::STATUS_CONFIRMED],
            'order_active' => ['order', Pemesanan::STATUS_IN_PROGRESS],
            'order_completed' => ['order', Pemesanan::STATUS_COMPLETED],
            'order_cancelled' => ['order', Pemesanan::STATUS_CANCELLED],
        ];

        if (isset($stages[$request->stage])) {
            [$type, $status] = $stages[$request->stage];
            $query->where('item_type', $type)->where('status', $status);
        }

        return $query
            ->orderByRaw("CASE
                WHEN item_type = 'consultation' AND status = 'pending' THEN 0
                WHEN item_type = 'consultation' AND status = 'confirmed' THEN 1
                WHEN item_type = 'order' AND status = 'pending' THEN 2
                WHEN item_type = 'order' AND status IN ('dikonfirmasi', 'sedang_dikerjakan') THEN 3
                ELSE 4 END")
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
    }

    private function applySearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        if (preg_match('/^(KS|DI)-?(\d+)$/i', $search, $reference) === 1) {
            $query
                ->where('item_type', strtoupper($reference[1]) === 'KS' ? 'consultation' : 'order')
                ->where('id', (int) $reference[2]);

            return;
        }

        $numericId = ctype_digit($search) ? (int) $search : 0;
        $query->where(function ($nested) use ($search, $numericId) {
            $nested->where('customer_name', 'like', "%{$search}%")
                ->orWhere('customer_email', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('detail', 'like', "%{$search}%");

            if ($numericId > 0) {
                $nested->orWhere('id', $numericId);
            }
        });
    }
}
