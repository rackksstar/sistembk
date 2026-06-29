# Phase 6 — Tryout BK

## Ringkasan

Guru BK membuat tryout, menugaskan kelas, memilih soal dari `master_questions` (kategori `tryout`). Siswa mengerjakan dalam jendela waktu aktif; jawaban disimpan di `try_out_detail` dengan `rata_skor` otomatis.

## Tabel

| Tabel | Peran |
| --- | --- |
| `try_outs` | Header tryout (judul, jadwal, `soal_ids` JSON, status) |
| `try_out_kelas` | Pivot tryout ↔ kelas |
| `try_out_detail` | Jawaban per siswa (`jawaban` JSON, `rata_skor`, `submitted_at`) |

## Route

| Role | Route name | Keterangan |
| --- | --- | --- |
| Guru | `guru.tryout.*` | Index, buat, edit, simpan, hasil, hapus (tanpa jawaban) |
| Siswa | `siswa.tryout.*` | Daftar aktif, kerjakan, submit |

## File utama

- `app/Services/TryOutService.php`
- `app/Http/Controllers/Guru/TryoutController.php`
- `app/Http/Controllers/Siswa/TryoutController.php`
- `resources/views/guru/tryout/*`, `resources/views/siswa/tryout/*`

## Menu

Grup **Layanan BK (Core)** di sidebar guru & siswa (`config/navigation.php`).

## Test

```bash
php artisan test --filter=Phase6
```
