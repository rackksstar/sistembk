# Phase 1 — Foundation & Auth Core

## Status: ✅ SELESAI

## Yang Dikerjakan
- [x] Stabilkan alur login multi-role yang sudah ada (admin/guru/siswa) tanpa mengubah struktur route existing.
- [x] Perbaiki login siswa: autentikasi menggunakan NISN + tanggal lahir, dan **wajib** sudah terhubung ke akun siswa (`students.user_id`).
- [x] Perbaiki rate limit key untuk login siswa agar tidak bergantung pada email.
- [x] Tambah integritas data: `students.user_id` dibuat unique agar relasi 1 siswa ↔ 1 akun lebih aman.
- [x] Perkaya seeder: tambah beberapa akun siswa yang sudah linked ke `students` untuk testing.

## File yang Dibuat (BARU)
| File | Deskripsi |
|------|-----------|
| `database/migrations/2026_05_06_000001_add_unique_user_id_to_students_table.php` | Enforce unique `students.user_id` |
| `docs/PROGRESS.md` | Status keseluruhan phase |
| `docs/phase-1-foundation.md` | Laporan phase 1 |

## File yang Dimodifikasi
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Login siswa tidak lagi auto-create user; wajib sudah linked ke `students.user_id` |
| `app/Http/Requests/Auth/LoginRequest.php` | Throttle key & error key untuk siswa pakai `nisn` (bukan email) |
| `resources/views/auth/login.blade.php` | Form login siswa disederhanakan (NISN + tanggal lahir) |
| `database/seeders/DatabaseSeeder.php` | Tambah data siswa linked untuk testing |

## Migration yang Dibuat
- `2026_05_06_000001_add_unique_user_id_to_students_table.php` → tabel: `students`

## Zero Bug Checklist
- [x] php -l semua file baru/diubah → PASS
- [x] php artisan route:list → PASS
- [x] Verifikasi route lama masih ada (grep admin.approvals, guru.consultations, siswa.instruments) → PASS
- [x] php artisan migrate --pretend → PASS
- [x] Test suite auth → PASS

## Cara Testing Manual
1. Jalankan `php artisan migrate:fresh --seed`
2. Buka landing page `/` → klik **Siswa** → login pakai NISN + tanggal lahir dari data seeder (lihat `DatabaseSeeder`).
3. Pastikan redirect ke `siswa.dashboard` dan tidak ada 500 error.
4. Login guru `guru@bk.test` / `password` → pastikan redirect ke `guru.dashboard`.
5. Login admin `admin@bk.test` / `password` → pastikan redirect ke `admin.dashboard`.

## Catatan / Known Issues
- Sanctum + `routes/api.php` diaktifkan di Phase 8.

## Next Phase Preview
Phase 2 akan fokus ke master data (sekolah/kelas/guruBK/siswa) dan CRUD yang terstruktur.

