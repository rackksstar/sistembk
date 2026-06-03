# Phase 5 — Rapor BK

## Status: ✅ SELESAI

## Tanggal: 2026-06-03

## Yang Dikerjakan

- [x] 5.1 Migration `rapor_bk` (semester, tahun ajaran, isi, status)
- [x] 5.2 Guru: daftar siswa + form buat/edit rapor
- [x] 5.3 `RaporBkService::upsertForStudent` (updateOrCreate)
- [x] 5.4 Download PDF rapor (DomPDF)
- [x] 5.5 Admin: pantau rapor (read-only)
- [x] 5.6 Test suite + dokumen (13 test PASS)

## File Baru

| File | Deskripsi |
|------|-----------|
| `database/migrations/2026_06_03_000001_create_rapor_bk_table.php` | Tabel rapor |
| `app/Models/RaporBk.php` | Model + konstanta semester/status |
| `app/Services/RaporBkService.php` | updateOrCreate per siswa/semester |
| `database/factories/RaporBkFactory.php` | Factory test |
| `app/Http/Controllers/Guru/RaporController.php` | CRUD guru + PDF |
| `app/Http/Controllers/Admin/RaporController.php` | Index + show admin |
| `resources/views/guru/rapor/*` | Index, edit, pdf |
| `resources/views/admin/rapor/*` | Index, show |
| `tests/Feature/Phase5/*` | Feature + integrasi |

## File Dimodifikasi (append only)

| File | Perubahan |
|------|-----------|
| `routes/web.php` | +route Phase 5 (guru block + admin dalam group) |
| `resources/views/layouts/app.blade.php` | +menu Rapor BK guru & admin |
| `app/Models/Student.php` | +relasi `raporBk()` |

## Catatan Arsitektur

- Unique: `(student_id, counselor_id, semester, tahun_ajaran)` — tiap guru punya rapor sendiri per periode
- PDF menampilkan ringkasan konseling/penilaian dari Phase 3–4 (read-only query, tanpa ubah modul tim)
- Modul tim (RPL, instrumen, sosiometri) tidak diubah

## Next Phase

Phase 6 — Tryout (atau +1 Phase 7 Postingan)
