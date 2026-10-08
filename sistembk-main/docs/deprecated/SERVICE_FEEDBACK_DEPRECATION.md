# Deprecation Notice — service_feedback

**Deprecated sejak :** Phase 4, 2026-05-30  
**Pengganti        :** `penilaian_pelayanan` (Phase 4)  
**Rencana hapus    :** Phase 9

## Status komponen

| Komponen | Status |
|----------|--------|
| Tabel `service_feedback` | Tetap ada, read-only |
| Model `ServiceFeedback` | @deprecated, tidak dikembangkan |
| `Siswa/ServiceFeedbackController` | @deprecated, tidak dikembangkan |
| `Guru/ServiceFeedbackController` | @deprecated, tidak dikembangkan |
| Route `siswa.feedback.*` | Tetap aktif (backward compat) |

## Yang tidak berubah

- Data lama tetap terbaca
- Route lama tetap aktif
- Semua test Phase 1–3 tetap hijau

## Action item Phase 9

- [ ] Drop migration `service_feedback`
- [ ] Hapus `app/Models/ServiceFeedback.php`
- [ ] Hapus `Siswa/ServiceFeedbackController`
- [ ] Hapus `Guru/ServiceFeedbackController`
- [ ] Hapus route `siswa.feedback.*` dan `guru.feedback.*`
- [ ] Hapus view `siswa/feedback` dan `guru/feedback`
