# Phase 2 — Manajemen Data Master

## Status: ✅ SELESAI

## Yang Dikerjakan
- [x] CRUD Sekolah (Admin): list + search + filter status + modal create/edit.
- [x] CRUD Kelas (Admin): list + search + filter sekolah + filter jenjang + modal create/edit.
- [x] CRUD Guru BK (Admin): buat akun user (role `guru`) sekaligus profil `guru_bks`.
- [x] CRUD Master Pertanyaan (Admin): kategori `angket/tryout`, tipe input, toggle aktif/nonaktif.
- [x] CRUD Kategori Postingan (Admin): slug otomatis + uniqueness.
- [x] Tambah menu sidebar admin untuk modul phase 2.

## File yang Dibuat (BARU)
| File | Deskripsi |
|------|-----------|
| `app/Models/Sekolah.php` | Model sekolah |
| `app/Models/Kelas.php` | Model kelas |
| `app/Models/GuruBk.php` | Profil guru BK (relasi ke user role `guru`) |
| `app/Models/MasterQuestion.php` | Master pertanyaan angket/tryout |
| `app/Models/PostCategory.php` | Kategori postingan |
| `app/Http/Controllers/Admin/SekolahController.php` | CRUD sekolah |
| `app/Http/Controllers/Admin/KelasController.php` | CRUD kelas |
| `app/Http/Controllers/Admin/GuruBkController.php` | CRUD guru BK + user |
| `app/Http/Controllers/Admin/MasterQuestionController.php` | CRUD master pertanyaan |
| `app/Http/Controllers/Admin/PostCategoryController.php` | CRUD kategori postingan |
| `resources/views/admin/sekolah/*` | UI sekolah |
| `resources/views/admin/kelas/*` | UI kelas |
| `resources/views/admin/guru-bk/*` | UI guru BK |
| `resources/views/admin/master-pertanyaan/*` | UI master pertanyaan |
| `resources/views/admin/kategori-postingan/*` | UI kategori postingan |

## File yang Dimodifikasi
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route admin untuk Phase 2 (di bawah route existing) |
| `resources/views/layouts/app.blade.php` | Tambah menu admin untuk Phase 2 |
| `app/Models/Student.php` | Tambah relasi `kelas()` dan field baru |
| `database/seeders/DatabaseSeeder.php` | Tambah seeder sekolah/kelas/guru_bk + link siswa |
| `docs/PROGRESS.md` | Update status phase |

## Migration yang Dibuat
- `2026_05_06_000100_create_sekolahs_table.php` → tabel: `sekolahs`
- `2026_05_06_000101_create_kelas_table.php` → tabel: `kelas`
- `2026_05_06_000102_create_guru_bks_table.php` → tabel: `guru_bks`
- `2026_05_06_000103_add_kelas_fields_to_students_table.php` → tambah kolom `kelas_id`, `jenis_kelamin`, `alamat`, `status_biodata`
- `2026_05_06_000104_create_master_questions_table.php` → tabel: `master_questions`
- `2026_05_06_000105_create_post_categories_table.php` → tabel: `post_categories`

## Zero Bug Checklist
- [x] `php -l` file baru/diubah → PASS
- [x] `php artisan route:list` → PASS
- [x] Verifikasi route lama masih ada (grep `admin.approvals|guru.consultations|siswa.assessments`) → PASS
- [x] `php artisan migrate --pretend` → PASS
- [x] `storage/logs/laravel.log` → no new errors file (belum terbentuk)

## Cara Testing Manual
1. Login admin `admin@bk.test` / `password`
2. Buka menu: **Sekolah**, **Kelas**, **Guru BK**, **Master Pertanyaan**, **Kategori Postingan**
3. Coba create/edit/delete masing-masing modul (cek validasi unique dan feedback)

## Catatan / Known Issues
- Modul “Siswa” admin masih memakai tabel `students` yang sudah ada; integrasi penuh ke `kelas` bisa dipoles lebih lanjut di phase berikutnya bila dibutuhkan (mis. filter siswa per kelas).

## Next Phase Preview
Phase 3 akan fokus ke pengajuan/jadwal konseling + kalender (FullCalendar CDN) dan endpoint event.

