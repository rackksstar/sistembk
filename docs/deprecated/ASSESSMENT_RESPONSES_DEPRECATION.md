# Deprecation: `assessment_responses`

**Status:** Deprecated (tidak digunakan fitur core/tim baru)  
**Rencana hapus:** Setelah konfirmasi tidak ada integrasi eksternal

## Konteks

Tabel `assessment_responses` berasal dari migrasi awal (`2026_04_30_000001`). Alur asesmen siswa kini memakai modul **Instrumen** tim terpisah.

## Tindakan Phase 9

- Tidak menulis data baru ke tabel ini dari core team.
- Migration tetap di repo agar `migrate:fresh` konsisten.
- Hapus tabel + model hanya setelah audit dependensi selesai.
