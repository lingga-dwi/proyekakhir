<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Katalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_parent_and_child_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Ruang Komersial',
            'sort_order' => 10,
            'is_active' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $parent = Category::where('name', 'Ruang Komersial')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Salon',
            'parent_id' => $parent->id,
            'sort_order' => 1,
            'is_active' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $child = Category::where('name', 'Salon')->firstOrFail();
        $this->assertSame($parent->id, $child->parent_id);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Ruang Komersial')
            ->assertSee('Salon');

        $this->actingAs($admin)->put(route('admin.categories.update', $child), [
            'name' => 'Salon Kecantikan',
            'parent_id' => $parent->id,
            'sort_order' => 2,
            'is_active' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['id' => $child->id, 'name' => 'Salon Kecantikan']);
    }

    public function test_used_category_and_parent_with_children_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = Category::create(['name' => 'Rumah', 'slug' => 'rumah', 'is_active' => true]);
        $child = Category::create(['name' => 'Dapur', 'slug' => 'dapur', 'parent_id' => $parent->id, 'is_active' => true]);
        Katalog::create([
            'category_id' => $child->id,
            'nama_desain' => 'Dapur Modern',
            'deskripsi' => 'Deskripsi katalog.',
            'galeri_gambar' => [],
            'status' => 'draft',
        ]);

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $parent))
            ->assertSessionHasErrors('category');
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $child))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
        $this->assertDatabaseHas('categories', ['id' => $child->id]);
    }

    public function test_non_admin_cannot_manage_categories(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $this->actingAs($customer)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }
}
