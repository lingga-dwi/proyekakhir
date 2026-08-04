<?php

namespace Tests\Unit;

use App\Models\Katalog;
use App\Models\Konsultasi;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class ModelBehaviorTest extends TestCase
{
    public function test_admin_role_is_classified_correctly(): void
    {
        $admin = new User(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isDesigner());
        $this->assertFalse($admin->isPelanggan());
    }

    public function test_designer_role_is_classified_correctly(): void
    {
        $designer = new User(['role' => 'designer']);

        $this->assertTrue($designer->isDesigner());
        $this->assertFalse($designer->isAdmin());
        $this->assertFalse($designer->isPelanggan());
    }

    public function test_customer_role_is_classified_correctly(): void
    {
        $customer = new User(['role' => 'pelanggan']);

        $this->assertTrue($customer->isPelanggan());
        $this->assertFalse($customer->isAdmin());
        $this->assertFalse($customer->isDesigner());
    }

    public function test_complete_catalog_can_be_published(): void
    {
        $catalog = $this->completeCatalog();

        $this->assertTrue($catalog->isCompleteForPublication());
    }

    public function test_catalog_without_category_cannot_be_published(): void
    {
        $catalog = $this->completeCatalog(['category_id' => null]);

        $this->assertFalse($catalog->isCompleteForPublication());
    }

    public function test_catalog_without_main_image_cannot_be_published(): void
    {
        $catalog = $this->completeCatalog(['gambar_utama' => null]);

        $this->assertFalse($catalog->isCompleteForPublication());
    }

    public function test_catalog_without_description_cannot_be_published(): void
    {
        $catalog = $this->completeCatalog(['deskripsi' => null]);

        $this->assertFalse($catalog->isCompleteForPublication());
    }

    public function test_consultation_type_is_mapped_to_readable_label(): void
    {
        $consultation = new Konsultasi(['jenis_konsultasi' => 'free_consultation']);

        $this->assertSame('Desain Interior Baru', $consultation->getJenisKonsultasiLabel());
    }

    public function test_budget_range_is_mapped_to_readable_label(): void
    {
        $consultation = new Konsultasi(['budget_range' => '25m_50m']);

        $this->assertSame('Rp 25 - 50 Juta', $consultation->getBudgetRangeLabel());
    }

    public function test_timeline_is_mapped_to_readable_label(): void
    {
        $consultation = new Konsultasi(['timeline' => '1_month']);

        $this->assertSame('1 Bulan', $consultation->getTimelineLabel());
    }

    public function test_room_type_is_mapped_to_readable_label(): void
    {
        $consultation = new Konsultasi(['jenis_ruangan' => 'kitchen']);

        $this->assertSame('Ruko', $consultation->getJenisRuanganLabel());
    }

    private function completeCatalog(array $overrides = []): Katalog
    {
        return new Katalog(array_merge([
            'category_id' => 1,
            'gambar_utama' => 'catalog/example.webp',
            'deskripsi' => 'Desain interior lengkap.',
        ], $overrides));
    }
}
