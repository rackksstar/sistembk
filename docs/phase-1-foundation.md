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
- [ ] php -l semua file baru/diubah → (akan diisi saat eksekusi check)
- [ ] php artisan route:list → (akan diisi saat eksekusi check)
- [ ] Verifikasi route lama masih ada (grep admin.approvals, guru.consultations, siswa.assessments) → (akan diisi saat eksekusi check)
- [ ] php artisan migrate --pretend → (akan diisi saat eksekusi check)
- [ ] storage/logs/laravel.log → (akan diisi saat eksekusi check)

## Cara Testing Manual
1. Jalankan `php artisan migrate:fresh --seed`
2. Buka landing page `/` → klik **Siswa** → login pakai NISN + tanggal lahir dari data seeder (lihat `DatabaseSeeder`).
3. Pastikan redirect ke `siswa.dashboard` dan tidak ada 500 error.
4. Login guru `guru@bk.test` / `password` → pastikan redirect ke `guru.dashboard`.
5. Login admin `admin@bk.test` / `password` → pastikan redirect ke `admin.dashboard`.

## Catatan / Known Issues
- Sanctum + `routes/api.php` sengaja belum diaktifkan di Phase 1 (menunggu kebutuhan Phase 8 / konfirmasi eksplisit).

## Next Phase Preview
Phase 2 akan fokus ke master data (sekolah/kelas/guruBK/siswa) dan CRUD yang terstruktur.

