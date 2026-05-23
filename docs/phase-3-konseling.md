# Phase 3 — Konseling & Jadwal

## Status: ✅ SELESAI (core deliverable)

## Yang Dikerjakan
- [x] Status lengkap: `ditolak`, `dijadwalkan_ulang` + `rejection_reason`
- [x] Form pengajuan siswa: topik, kategori, preferensi tanggal/waktu, detail
- [x] Halaman riwayat siswa: `siswa/consultations` + filter status
- [x] Guru: tolak pengajuan dengan alasan
- [x] Guru: penjadwalan + deteksi bentrok jadwal (`ConsultationScheduleService`)
- [x] Kalender FullCalendar (CDN) + endpoint `guru.consultations.events`
- [x] Widget jadwal minggu ini (guru) & jadwal mendatang (siswa)
- [x] **+2** Filter siswa per kelas (admin)

## File Baru
| File | Deskripsi |
|------|-----------|
| `database/migrations/2026_05_18_000001_extend_consultation_requests_workflow.php` | `preferred_date`, `rejection_reason` |
| `app/Services/ConsultationScheduleService.php` | Cek bentrok + event kalender |
| `app/Http/Controllers/Siswa/ConsultationController.php` | Index + store pengajuan |
| `app/Http/Requests/Guru/RejectConsultationRequest.php` | Validasi tolak |
| `resources/views/siswa/consultations/index.blade.php` | UI siswa |

## Route Ditambahkan (append)
- `siswa.consultations.index`, `siswa.consultations.store`
- `guru.consultations.events`, `guru.consultations.reject`

## Zero Bug
- [x] `php -l` file baru
- [x] `php artisan migrate`
- [x] `php artisan route:list --name=consultation`

## Testing Manual
1. Login siswa → menu **Konseling** → ajukan dengan topik + kategori
2. Login guru → **Konseling** → setujui / tolak / jadwalkan (uji bentrok jam sama)
3. Lihat kalender event & filter status

## Next
Phase 4 — Penilaian pelayanan & angket formal
