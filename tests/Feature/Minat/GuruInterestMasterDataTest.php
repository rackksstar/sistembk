<?php

namespace Tests\Feature\Minat;

use App\Models\CareerField;
use App\Models\InterestCategory;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruInterestMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_membuka_halaman_master_key(): void
    {
        $guru = $this->buatGuru();

        foreach ([
            'guru.interest-categories.index',
            'guru.program-studi.index',
            'guru.bidang-karier.index',
        ] as $route) {
            $this->actingAs($guru)->get(route($route))->assertOk();
        }
    }

    public function test_guru_dapat_crud_kategori_minat(): void
    {
        $guru = $this->buatGuru();

        $this->actingAs($guru)
            ->post(route('guru.interest-categories.store'), [
                'kode' => 'G',
                'nama' => 'Kategori Guru',
                'urutan' => 5,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $category = InterestCategory::query()->where('kode', 'G')->firstOrFail();

        $this->actingAs($guru)
            ->put(route('guru.interest-categories.update', $category), [
                'kode' => 'G',
                'nama' => 'Kategori Guru Baru',
                'urutan' => 6,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('interest_categories', [
            'id' => $category->id,
            'nama' => 'Kategori Guru Baru',
        ]);

        $this->actingAs($guru)
            ->delete(route('guru.interest-categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('interest_categories', ['id' => $category->id]);
    }

    public function test_guru_dapat_crud_program_studi_dan_bidang_karier(): void
    {
        $guru = $this->buatGuru();
        $category = InterestCategory::query()->create([
            'kode' => 'I',
            'nama' => 'Teknologi',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($guru)
            ->post(route('guru.program-studi.store'), [
                'institusi' => 'Politeknik Caltex Riau',
                'nama' => 'Teknik Informatika',
                'jenjang_pendidikan' => 'D4',
                'is_verified' => 1,
                'is_active' => 1,
                'categories' => [
                    $category->id => ['enabled' => 1, 'relevansi' => 3],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $prodi = ProgramStudi::query()->where('nama', 'Teknik Informatika')->firstOrFail();

        $this->actingAs($guru)
            ->put(route('guru.program-studi.update', $prodi), [
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
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($guru)
            ->delete(route('guru.program-studi.destroy', $prodi))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('program_studis', ['id' => $prodi->id]);

        $this->actingAs($guru)
            ->post(route('guru.bidang-karier.store'), [
                'nama' => 'Rekayasa Perangkat Lunak',
                'contoh_pekerjaan_text' => "Programmer\nAnalis Sistem",
                'job_zone' => 4,
                'is_active' => 1,
                'categories' => [
                    $category->id => ['enabled' => 1, 'relevansi' => 3],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $field = CareerField::query()->where('nama', 'Rekayasa Perangkat Lunak')->firstOrFail();

        $this->actingAs($guru)
            ->put(route('guru.bidang-karier.update', $field), [
                'nama' => 'Rekayasa Perangkat Lunak',
                'job_zone' => 5,
                'is_active' => 1,
                'categories' => [
                    $category->id => ['enabled' => 1, 'relevansi' => 2],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('career_fields', [
            'id' => $field->id,
            'job_zone' => 5,
        ]);

        $this->actingAs($guru)
            ->delete(route('guru.bidang-karier.destroy', $field))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('career_fields', ['id' => $field->id]);
    }

    public function test_siswa_ditolak_403_pada_route_master_guru(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($siswa)
            ->get(route('guru.interest-categories.index'))
            ->assertForbidden();
    }

    private function buatGuru(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
    }
}
