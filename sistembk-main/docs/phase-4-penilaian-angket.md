# Phase 4 — Penilaian & Angket

## Status: ✅ SELESAI

## Tanggal: 2026-05-30

## Yang Dikerjakan

- [x] 4.1 Migration penilaian_pelayanan (unique constraint per konseling+siswa)
- [x] 4.2 Form siswa + guard duplikat
- [x] 4.3 Laporan agregat guru (zero N+1, filter bulan/tahun)
- [x] 4.4 Migration respons_angket + alur idempotent
- [x] 4.5 Laporan angket + predikat (zero N+1)
- [x] 4.6 PDF via DomPDF
- [x] 4.7 Deprecate service_feedback (logika tidak berubah)
- [x] 4.8 Test suite + dokumen

## File Baru

| File | Agent | Deskripsi |
|------|-------|-----------|
| `database/migrations/2026_05_30_000001_*` | A | Tabel penilaian_pelayanan |
| `database/migrations/2026_05_30_000002_*` | B | Tabel respons_angket |
| `app/Models/PenilaianPelayanan.php` | A | Model + accessor |
| `app/Models/ResponsAngket.php` | B | Model angket |
| `database/factories/PenilaianPelayananFactory.php` | A | Factory test |
| `database/factories/ResponsAngketFactory.php` | B | Factory test |
| `database/factories/MasterQuestionFactory.php` | B | Factory soal angket |
| `app/Http/Controllers/Siswa/PenilaianController.php` | A | Form penilaian siswa |
| `app/Http/Controllers/Guru/PenilaianController.php` | A | Laporan agregat |
| `app/Http/Controllers/Siswa/AngketController.php` | B | Idempotent store |
| `app/Http/Controllers/Guru/AngketController.php` | B | Laporan + PDF |
| `resources/views/siswa/penilaian/` | A | Index + create |
| `resources/views/guru/penilaian/` | A | Laporan guru |
| `resources/views/siswa/angket/` | B | Index + form isi |
| `resources/views/guru/angket/` | B | Termasuk pdf.blade.php |
| `tests/Feature/Phase4/PenilaianPelayananTest.php` | C | Feature penilaian |
| `tests/Feature/Phase4/AngketTest.php` | C | Feature angket |
| `tests/Feature/Phase4/Phase4IntegrationTest.php` | C | E2E + regresi |
| `tests/Unit/Phase4/PenilaianModelTest.php` | C | Unit accessor + predikat |
| `docs/deprecated/SERVICE_FEEDBACK_DEPRECATION.md` | C | Catatan deprecasi |

## File Dimodifikasi (append only)

| File | Perubahan |
|------|-----------|
| `routes/web.php` | +8 route Phase 4 di bawah Phase 3 |
| `resources/views/layouts/app.blade.php` | +4 menu link |
| `app/Models/Student.php` | +relasi responsAngket() |
| `app/Models/ConsultationRequest.php` | +relasi penilaianPelayanan() |
| `app/Models/MasterQuestion.php` | +relasi responAngket() |
| `app/Http/Controllers/Siswa/ServiceFeedbackController.php` | +@deprecated docblock |
| `app/Http/Controllers/Guru/ServiceFeedbackController.php` | +@deprecated docblock |
| `app/Models/ServiceFeedback.php` | +@deprecated docblock |

## Catatan Arsitektur

- Penilaian: unique(consultation_request_id, student_id) di DB level
- `consultation_requests.student_id` → `users.id`; `penilaian_pelayanan.student_id` → `students.id`
- Angket: updateOrCreate → idempotent, tidak duplikat
- N+1: semua query pakai with() atau withCount() — tidak ada lazy load di loop
- PDF: CSS inline, tidak ada external dependency, tidak extends layout
- service_feedback: tetap hidup, hanya docblock deprecated

## Next Phase

Phase 5 — Rapor BK  
Dependensi: Student (Phase 2), PenilaianPelayanan (Phase 4 — opsional untuk isi rapor)
