# Phase 9 — Finalisasi

## Activity logs (manual)

Tabel `activity_logs` + helper `App\Support\ActivityLogger::log()`.

Admin: **Log Aktivitas** (`admin.activity-logs.index`) — read-only.

## Dashboard admin (core)

Widget ringkasan: jumlah rapor BK, postingan, tryout.

## Legacy `assessment_responses`

Tabel lama tidak dipakai modul baru. Lihat `docs/deprecated/ASSESSMENT_RESPONSES_DEPRECATION.md`. Penghapusan fisik ditunda agar migrasi lama tidak pecah.

## Test

```bash
php artisan test --filter=Phase9
```
