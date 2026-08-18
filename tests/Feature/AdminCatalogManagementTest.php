<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Katalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_catalog_index_uses_management_table_and_status_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category('Kamar Tidur');
        $this->catalog($category, ['nama_desain' => 'Desain Publik', 'status' => 'published']);
        $this->catalog($category, ['nama_desain' => 'Desain Draft', 'status' => 'draft']);

        $this->actingAs($admin)
            ->get(route('admin.katalog.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSee('Kelola Katalog')
            ->assertSee('Kelengkapan')
            ->assertSee('Desain Draft')
            ->assertDontSee('Desain Publik');
    }

    public function test_catalog_completeness_filter_is_separate_from_publication_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category('Kamar Tidur');
        $this->catalog($category, [
            'nama_desain' => 'Draft Lengkap',
            'status' => 'draft',
        ]);
        $this->catalog($category, [
            'nama_desain' => 'Draft Belum Lengkap',
            'gambar_utama' => null,
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.katalog.index', [
                'status' => 'draft',
                'completeness' => 'incomplete',
            ]))
            ->assertOk()
            ->assertSee('Draft Belum Lengkap')
            ->assertDontSee('Draft Lengkap');
    }

    public function test_admin_can_apply_bulk_catalog_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $oldCategory = $this->category('Kamar Tidur');
        $newCategory = $this->category('Dapur');
        $first = $this->catalog($oldCategory);
        $second = $this->catalog($oldCategory, ['nama_desain' => 'Desain Kedua']);

        $this->actingAs($admin)->post(route('admin.katalog.bulk-action'), [
            'ids' => [$first->id, $second->id],
            'action' => 'publish',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('katalog', ['id' => $first->id, 'status' => 'published']);
        $this->assertDatabaseHas('katalog', ['id' => $second->id, 'status' => 'published']);

        $this->actingAs($admin)->post(route('admin.katalog.bulk-action'), [
            'ids' => [$first->id, $second->id],
            'action' => 'change_category',
            'category_id' => $newCategory->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('katalog', [
            'id' => $first->id,
            'category_id' => $newCategory->id,
        ]);

        $this->actingAs($admin)->post(route('admin.katalog.bulk-action'), [
            'ids' => [$first->id],
            'action' => 'draft',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('katalog', ['id' => $first->id, 'status' => 'draft']);

        $this->actingAs($admin)->post(route('admin.katalog.bulk-action'), [
            'ids' => [$second->id],
            'action' => 'archive',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('katalog', ['id' => $second->id, 'status' => 'archived']);
    }

    public function test_incomplete_catalog_cannot_be_published(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category('Kamar Tidur');
        $catalog = $this->catalog($category, ['gambar_utama' => null]);

        $this->actingAs($admin)->post(route('admin.katalog.bulk-action'), [
            'ids' => [$catalog->id],
            'action' => 'publish',
        ])->assertRedirect()->assertSessionHasErrors('ids');

        $this->assertDatabaseHas('katalog', ['id' => $catalog->id, 'status' => 'draft']);
    }

    public function test_drafts_are_hidden_from_public_catalog_detail_api_and_sitemap(): void
    {
        $category = $this->category('Kamar Tidur');
        $published = $this->catalog($category, [
            'nama_desain' => 'Katalog Terbit',
            'status' => 'published',
        ]);
        $draft = $this->catalog($category, [
            'nama_desain' => 'Katalog Rahasia',
            'status' => 'draft',
        ]);
        $archived = $this->catalog($category, [
            'nama_desain' => 'Katalog Arsip',
            'status' => 'archived',
        ]);

        $this->get(route('katalog'))
            ->assertOk()
            ->assertSee('Katalog Terbit')
            ->assertDontSee('Katalog Rahasia')
            ->assertDontSee('Katalog Arsip');

        $this->get(route('katalog.detail', $published))->assertOk();
        $this->get(route('katalog.detail', $draft))->assertNotFound();
        $this->get(route('katalog.detail', $archived))->assertNotFound();
        $this->get(route('katalog.api', $draft))->assertNotFound();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('katalog.detail', $published), false)
            ->assertDontSee(route('katalog.detail', $draft), false);
    }

    public function test_homepage_displays_six_latest_published_catalogs(): void
    {
        $category = $this->category('Ruang Keluarga');

        for ($index = 1; $index <= 7; $index++) {
            $catalog = $this->catalog($category, [
                'nama_desain' => 'Desain Terbit '.$index,
                'status' => 'published',
            ]);
            $catalog->forceFill(['created_at' => now()->subDays(8 - $index)])->saveQuietly();
        }

        $draft = $this->catalog($category, [
            'nama_desain' => 'Draft Paling Baru',
            'status' => 'draft',
        ]);
        $draft->forceFill(['created_at' => now()->addDay()])->saveQuietly();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Desain Terbit 1')
            ->assertDontSee('Draft Paling Baru')
            ->assertSeeInOrder([
                'Desain Terbit 7',
                'Desain Terbit 6',
                'Desain Terbit 5',
                'Desain Terbit 4',
                'Desain Terbit 3',
                'Desain Terbit 2',
            ]);
    }

    public function test_homepage_and_catalog_use_the_same_order_when_publish_dates_match(): void
    {
        $category = $this->category('Dapur');
        $publishedAt = now()->subDay();

        foreach (['Desain Pertama', 'Desain Kedua', 'Desain Ketiga'] as $name) {
            $catalog = $this->catalog($category, [
                'nama_desain' => $name,
                'status' => 'published',
            ]);
            $catalog->forceFill(['created_at' => $publishedAt])->saveQuietly();
        }

        $expectedOrder = ['Desain Pertama', 'Desain Kedua', 'Desain Ketiga'];

        $this->get(route('katalog', ['sort' => 'latest']))
            ->assertOk()
            ->assertSeeInOrder($expectedOrder);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder($expectedOrder);
    }

    public function test_catalog_form_does_not_expose_legacy_category_or_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.katalog.create'))
            ->assertOk()
            ->assertDontSee('Kategori Lama')
            ->assertDontSee('Harga Estimasi');
    }

    public function test_admin_can_create_catalog_without_legacy_category_or_price_input(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category('Ruang Kerja');

        $this->actingAs($admin)->post(route('admin.katalog.store'), [
            'nama_desain' => 'Ruang Kerja Produktif',
            'category_id' => $category->id,
            'deskripsi' => 'Ruang kerja dengan penyimpanan terintegrasi.',
            'status' => 'draft',
        ])->assertRedirect(route('admin.katalog.index'));

        $this->assertDatabaseHas('katalog', [
            'nama_desain' => 'Ruang Kerja Produktif',
            'category_id' => $category->id,
            'status' => 'draft',
        ]);
    }

    public function test_editing_catalog_can_remove_and_append_gallery_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category('Dapur');
        $catalog = $this->catalog($category, [
            'galeri_gambar' => ['katalog/gallery/keep.jpg', 'katalog/gallery/remove.jpg'],
        ]);
        Storage::disk('public')->put('katalog/gallery/keep.jpg', 'keep');
        Storage::disk('public')->put('katalog/gallery/remove.jpg', 'remove');

        $this->actingAs($admin)->put(route('admin.katalog.update', $catalog), [
            'nama_desain' => $catalog->nama_desain,
            'category_id' => $category->id,
            'deskripsi' => $catalog->deskripsi,
            'status' => 'draft',
            'remove_gallery' => ['katalog/gallery/remove.jpg'],
            'galeri_gambar' => [UploadedFile::fake()->image('new.jpg')],
        ])->assertRedirect(route('admin.katalog.index'));

        $catalog->refresh();
        $this->assertCount(2, $catalog->galeri_gambar);
        $this->assertContains('katalog/gallery/keep.jpg', $catalog->galeri_gambar);
        $this->assertStringStartsWith('katalog/gallery/', $catalog->galeri_gambar[1]);
        Storage::disk('public')->assertMissing('katalog/gallery/remove.jpg');
    }

    public function test_destroy_archives_catalog_instead_of_deleting_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category('Kamar Tidur');
        $catalog = $this->catalog($category, ['status' => 'published']);

        $this->actingAs($admin)
            ->delete(route('admin.katalog.destroy', $catalog))
            ->assertRedirect(route('admin.katalog.index'));

        $this->assertDatabaseHas('katalog', [
            'id' => $catalog->id,
            'status' => 'archived',
        ]);
    }

    public function test_uploaded_catalog_image_is_resized_and_stored_as_webp(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category('Ruang Tamu');

        $this->actingAs($admin)->post(route('admin.katalog.store'), [
            'nama_desain' => 'Ruang Tamu Teroptimasi',
            'category_id' => $category->id,
            'deskripsi' => 'Gambar katalog teroptimasi untuk perangkat bergerak.',
            'status' => 'draft',
            'gambar_utama' => UploadedFile::fake()->image('ruang-tamu.jpg', 2400, 1600),
        ])->assertRedirect(route('admin.katalog.index'));

        $catalog = Katalog::where('nama_desain', 'Ruang Tamu Teroptimasi')->firstOrFail();
        $this->assertStringEndsWith('.webp', $catalog->gambar_utama);
        Storage::disk('public')->assertExists($catalog->gambar_utama);

        [$width, $height] = getimagesize(Storage::disk('public')->path($catalog->gambar_utama));
        $this->assertLessThanOrEqual(1920, max($width, $height));
    }

    private function category(string $name): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'is_active' => true,
        ]);
    }

    private function catalog(Category $category, array $attributes = []): Katalog
    {
        return Katalog::create(array_merge([
            'category_id' => $category->id,
            'nama_desain' => 'Desain Utama',
            'deskripsi' => 'Deskripsi desain yang lengkap.',
            'gambar_utama' => 'images/katalog/rumah/contoh.jpg',
            'galeri_gambar' => [],
            'status' => 'draft',
        ], $attributes));
    }
}
