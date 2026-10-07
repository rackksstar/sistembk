<?php

namespace Tests\Feature\Minat;

use App\Models\CareerField;
use App\Models\InstrumentQuestion;
use App\Models\InterestCategory;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInterestMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_crud_kategori_minat(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->post(route('admin.interest-categories.store'), [
                'kode' => 'X',
                'nama' => 'Eksperimen',
                'deskripsi' => 'Kategori uji',
                'warna' => '#111111',
                'urutan' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $category = InterestCategory::query()->where('kode', 'X')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.interest-categories.update', $category), [
                'kode' => 'X',
                'nama' => 'Eksperimen Baru',
                'deskripsi' => 'Kategori uji diperbarui',
                'warna' => '#222222',
                'urutan' => 11,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('interest_categories', [
            'id' => $category->id,
            'nama' => 'Eksperimen Baru',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.interest-categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('interest_categories', ['id' => $category->id]);
    }

    public function test_hapus_kategori_yang_dipakai_soal_ditolak(): void
    {
        $admin = $this->buatAdmin();
        $category = InterestCategory::query()->create([
            'kode' => 'R',
            'nama' => 'Teknik',
            'urutan' => 1,
            'is_active' => true,
        ]);

        InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $category->id,
            'jenjang_target' => 'semua',
            'bobot' => 1,
            'question' => 'Soal terpakai kategori',
            'options' => [
                ['label' => 'Ya', 'score' => 1],
                ['label' => 'Tidak', 'score' => 0],
            ],
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.interest-categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('interest_categories', ['id' => $category->id]);
    }

    public function test_hapus_kategori_dengan_soal_soft_deleted_ditolak(): void
    {
        $admin = $this->buatAdmin();
        $category = InterestCategory::query()->create([
            'kode' => 'A',
            'nama' => 'Kreatif',
            'urutan' => 3,
            'is_active' => true,
        ]);

        $question = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $category->id,
            'jenjang_target' => 'semua',
            'bobot' => 1,
            'question' => 'Soal yang diarsip',
            'options' => [
                ['label' => 'Ya', 'score' => 1],
                ['label' => 'Tidak', 'score' => 0],
            ],
            'is_active' => true,
        ]);
        $question->delete();

        $this->actingAs($admin)
            ->delete(route('admin.interest-categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('interest_categories', ['id' => $category->id]);
    }

    public function test_admin_crud_program_studi_dengan_relevansi(): void
    {
        $admin = $this->buatAdmin();
        $category = InterestCategory::query()->create([
            'kode' => 'I',
            'nama' => 'Teknologi & Sains',
            'urutan' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.program-studi.store'), [
                'institusi' => 'Politeknik Caltex Riau',
                'nama' => 'Teknik Informatika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknik Informatika',
                'is_verified' => 0,
                'is_active' => 1,
                'categories' => [
                    $category->id => ['enabled' => 1, 'relevansi' => 3],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $prodi = ProgramStudi::query()->where('nama', 'Teknik Informatika')->firstOrFail();
        $this->assertFalse($prodi->is_verified);
        $this->assertTrue($prodi->interestCategories()->where('interest_categories.id', $category->id)->exists());

        $this->actingAs($admin)
            ->put(route('admin.program-studi.update', $prodi), [
                'institusi' => 'Politeknik Caltex Riau',
                'nama' => 'Teknik Informatika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Informatika',
                'is_verified' => 1,
                'is_active' => 1,
                'categories' => [
                    $category->id => ['enabled' => 1, 'relevansi' => 2],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('program_studis', [
            'id' => $prodi->id,
            'jurusan' => 'Informatika',
            'is_verified' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.program-studi.destroy', $prodi))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('program_studis', ['id' => $prodi->id]);
    }

    public function test_admin_crud_bidang_karier(): void
    {
        $admin = $this->buatAdmin();
        $category = InterestCategory::query()->create([
            'kode' => 'E',
            'nama' => 'Bisnis',
            'urutan' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bidang-karier.store'), [
                'nama' => 'Penjualan & Pemasaran',
                'deskripsi' => 'Bidang penjualan',
                'contoh_pekerjaan_text' => "Sales\nDigital marketing",
                'job_zone' => 2,
                'is_active' => 1,
                'categories' => [
                    $category->id => ['enabled' => 1, 'relevansi' => 3],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $field = CareerField::query()->where('nama', 'Penjualan & Pemasaran')->firstOrFail();
        $this->assertSame(['Sales', 'Digital marketing'], $field->contoh_pekerjaan);

        $this->actingAs($admin)
            ->put(route('admin.bidang-karier.update', $field), [
                'nama' => 'Penjualan Digital',
                'deskripsi' => 'Bidang penjualan digital',
                'contoh_pekerjaan_text' => "Sales\nContent marketing",
                'job_zone' => 3,
                'is_active' => 1,
                'categories' => [
                    $category->id => ['enabled' => 1, 'relevansi' => 2],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('career_fields', [
            'id' => $field->id,
            'nama' => 'Penjualan Digital',
            'job_zone' => 3,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.bidang-karier.destroy', $field))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('career_fields', ['id' => $field->id]);
    }

    public function test_non_admin_ditolak_403_pada_master_data_minat(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        foreach ([$guru, $siswa] as $user) {
            $this->actingAs($user)
                ->get(route('admin.interest-categories.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.program-studi.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.bidang-karier.index'))
                ->assertForbidden();
        }
    }

    private function buatAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
    }
}
