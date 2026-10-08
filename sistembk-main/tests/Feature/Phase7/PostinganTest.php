<?php

namespace Tests\Feature\Phase7;

use App\Models\PostCategory;
use App\Models\Postingan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostinganTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_crud_postingan(): void
    {
        $admin = $this->buatAdmin();
        $kategori = PostCategory::query()->create([
            'name' => 'Tips BK',
            'slug' => 'tips-bk',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.postingan.store'), [
                'post_category_id' => $kategori->id,
                'judul' => 'Artikel pertama',
                'isi' => 'Konten artikel lengkap.',
                'status' => Postingan::STATUS_PUBLISHED,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $postingan = Postingan::query()->first();
        $this->assertNotNull($postingan);

        $this->actingAs($admin)
            ->put(route('admin.postingan.update', $postingan), [
                'post_category_id' => $kategori->id,
                'judul' => 'Artikel diperbarui',
                'isi' => 'Konten baru.',
                'status' => Postingan::STATUS_DRAFT,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('postingan', [
            'id' => $postingan->id,
            'judul' => 'Artikel diperbarui',
            'status' => Postingan::STATUS_DRAFT,
        ]);
    }

    public function test_siswa_hanya_lihat_postingan_publik(): void
    {
        $kategori = PostCategory::query()->create(['name' => 'Info', 'slug' => 'info']);
        $publik = Postingan::factory()->create([
            'post_category_id' => $kategori->id,
            'status' => Postingan::STATUS_PUBLISHED,
        ]);
        $draft = Postingan::factory()->draft()->create([
            'post_category_id' => $kategori->id,
        ]);

        $siswa = $this->buatSiswa();

        $this->actingAs($siswa)
            ->get(route('siswa.postingan.index'))
            ->assertOk()
            ->assertViewHas('postingan', function ($paginator) use ($publik, $draft) {
                $ids = $paginator->pluck('id');

                return $ids->contains($publik->id) && ! $ids->contains($draft->id);
            });

        $this->actingAs($siswa)
            ->get(route('siswa.postingan.show', $publik))
            ->assertOk();

        $this->actingAs($siswa)
            ->get(route('siswa.postingan.show', $draft))
            ->assertNotFound();
    }

    public function test_guru_tidak_akses_crud_postingan_admin(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]))
            ->get(route('admin.postingan.index'))
            ->assertForbidden();
    }

    private function buatAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    private function buatSiswa(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);
    }
}
