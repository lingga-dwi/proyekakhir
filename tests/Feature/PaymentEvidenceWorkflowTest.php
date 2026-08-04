<?php

namespace Tests\Feature;

use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentEvidenceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_upload_evidence_and_admin_can_verify_it_without_payment_tables(): void
    {
        Storage::fake('payment_evidence');
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);
        $pemesanan = $this->makeOrder($customer);

        $this->actingAs($customer)
            ->post(route('pemesanan.payment-evidence.upload', $pemesanan), [
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti-bayar.jpg'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('status_tracking', [
            'id_pemesanan' => $pemesanan->id,
            'actor_id' => $customer->id,
            'status' => 'Menunggu Verifikasi Pembayaran',
        ]);
        $this->assertNotEmpty(Storage::disk('payment_evidence')->allFiles('payment-proofs/order-'.$pemesanan->id));

        $this->actingAs($admin)
            ->post(route('admin.pemesanan.payment-evidence.verify', $pemesanan))
            ->assertRedirect();

        $this->assertDatabaseHas('status_tracking', [
            'id_pemesanan' => $pemesanan->id,
            'actor_id' => $admin->id,
            'status' => 'Pembayaran Diverifikasi',
        ]);
    }

    public function test_other_customer_cannot_upload_or_download_payment_evidence(): void
    {
        Storage::fake('payment_evidence');
        $owner = User::factory()->create(['role' => 'pelanggan']);
        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);
        $pemesanan = $this->makeOrder($owner);

        $this->actingAs($otherCustomer)
            ->post(route('pemesanan.payment-evidence.upload', $pemesanan), [
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertForbidden();

        $this->actingAs($otherCustomer)
            ->get(route('pemesanan.payment-evidence.download', $pemesanan))
            ->assertForbidden();
    }

    private function makeOrder(User $customer): Pemesanan
    {
        return Pemesanan::create([
            'id_user' => $customer->id,
            'tanggal_pesan' => now()->toDateString(),
            'sumber_masuk' => 'website',
            'status_pemesanan' => Pemesanan::STATUS_PENDING,
            'progress' => 0,
            'total_harga' => 0,
            'jenis_proyek' => 'renovasi',
            'jenis_bangunan' => 'rumah_tinggal',
            'luas_area' => 50,
            'deskripsi_keinginan_desain' => 'Pembaruan interior rumah.',
        ]);
    }
}
