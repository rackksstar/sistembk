# PROGRESS — Sistem BK (Core Team)

> **Tracker detail:** `[docs/CORE_TEAM_TRACKER.md](./CORE_TEAM_TRACKER.md)`  
> **Semua fitur + tim lain:** `[docs/FEATURE_BREAKDOWN.md](./FEATURE_BREAKDOWN.md)`

**Terakhir diperbarui:** 2026-05-18

---

## Flow core team

```
[✅ P1 Auth] → [✅ P2 Master] → [✅ P3 Konseling] → [🔄 P4 Penilaian] → P5 Rapor
                                                      ↘ P7 Postingan (+1)
                                    P6 Tryout ─────────────────────→ P9 Finalisasi
                                    P8 API ──────────────────────────↗
```

---

## Status phase


| Phase | Modul              | Status | Progress core | Catatan                                              |
| ----- | ------------------ | ------ | ------------- | ---------------------------------------------------- |
| 1     | Foundation & Auth  | ✅      | 100%          | `[phase-1-foundation.md](./phase-1-foundation.md)`   |
| 2     | Data Master        | ✅      | 100%          | `[phase-2-data-master.md](./phase-2-data-master.md)` |
| 3     | Konseling & Jadwal | ✅      | 100%          | `[phase-3-konseling.md](./phase-3-konseling.md)`     |
| 4     | Penilaian & Angket | ⏳      | 0%            | Berikutnya                                           |
| 5     | Rapor BK           | ⏳      | 0%            |                                                      |
| 6     | Tryout             | ⏳      | ~5%           | Master soal ✅                                        |
| 7     | Postingan          | ⏳      | ~15%          | Kategori ✅ — **+1 rekomendasi**                      |
| 8     | API (Sanctum)      | ⏳      | 0%            |                                                      |
| 9     | Finalisasi         | ⏳      | 0%            |                                                      |


---

## Sprint berikutnya

**Target:** Phase 4 — `penilaian_pelayanan` + angket dari `master_questions`

**Opsional +1:** Phase 7 — CRUD postingan artikel

---

## Log singkat


| Tanggal    | Update                                                 |
| ---------- | ------------------------------------------------------ |
| 2026-05-18 | Phase 3 konseling selesai; filter siswa per kelas (+2) |
| 2026-05-18 | Tracker core team dibuat; P1–2 ✅                       |


