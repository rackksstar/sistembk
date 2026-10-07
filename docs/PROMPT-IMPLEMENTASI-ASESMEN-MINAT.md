# Paket Implementasi: Asesmen Minat Bakat (v2)

**SMA → rekomendasi Program Studi PCR · SMK → rekomendasi Bidang Karier**

Halaman hasil mengacu pada referensi `paskerid.freedev.app/result` (kode RIASEC, 3 minat dominan, distribusi skor, kartu rekomendasi), **disederhanakan**.

| Bagian | Isi | Taruh di mana |
|---|---|---|
| **0** | Keputusan desain & prioritas | dibaca manusia |
| **A. Skill** | Aturan kerja "audit dulu, baru coding" | Cursor: `.cursor/rules/minat-bakat.mdc` · OpenCode: `.opencode/skill/minat-bakat-implementer/SKILL.md` |
| **B. Master Prompt** | Perintah eksekusi bertahap (Phase 0–7) | paste ke chat agent |
| **C. Lampiran Data** | Kategori, 30 soal, prodi PCR, karier SMK | dipakai seeder buatan agent |
| **D. Checklist uji** | Sebelum launching | manusia |

Dokumen referensi di repo: `docs/PANDUAN-PENGGUNAAN.md` dan `docs/PANDUAN-FITUR-CORE.md` (nama aktual; bukan `PANDUAN-CORE.md`).

---

## Peta Task (ringkas untuk eksekusi)

Branch kerja: `feature/minat-bakat`. Satu commit per phase. Scope = **P0 saja**. Phase 6 opsional (butuh persetujuan Tim asesmen).

| ID | Phase | Deliverable | Commit message (usulan) | Stop gate |
|---|---|---|---|---|
| **T0** | Prep | Skill tersimpan; branch siap | (opsional) `chore: add minat-bakat implementer skill` | — |
| **T1** | Phase 0 | Laporan audit (tanpa ubah kode) | — (tidak commit) | Stop bila temuan membatalkan rancangan |
| **T2** | Phase 1 | Migration aditif + model/relasi | `feat(minat): add interest schema and models` | `migrate --pretend` OK |
| **T3** | Phase 2 | Admin CRUD kategori / prodi / bidang karier + nav | `feat(minat): admin master data for interest assessment` | CRUD + 403 role lain |
| **T4** | Phase 3 | Guru: perluas `instrument-questions` untuk `minat_bakat` | `feat(minat): extend guru question UI for weighted RIASEC items` | Kategori lain tidak berubah |
| **T5** | Phase 4 | `InterestScoringService` + `RecommendationService` | `feat(minat): scoring and recommendation services` | Unit test hijau |
| **T6** | Phase 5 | Alur siswa, hasil sederhana, PDF | `feat(minat): student flow result page and PDF` | SMA→prodi, SMK→karier, 403 |
| **T7** | Phase 6 *(opsional)* | Guru hasil + scoping | `feat(minat): guru result kode minat [+ scoping]` | Butuh OK Tim asesmen |
| **T8** | Phase 7 | Seeder C.1–C.5, test lengkap, update panduan | `feat(minat): seeders tests and docs for P0 launch` | `php artisan test` + pint |
| **T9** | Checklist D | Uji manual manusia sebelum launching | — | Semua checkbox hijau |

### Breakdown per phase

#### T1 — Phase 0 Audit
- [ ] `route:list` instrument & siswa; skema `instrument_questions` / `instrument_submissions` / `instrument_answers`
- [ ] Cara scoring lama (lokasi, maxScore, label); bentuk answers
- [ ] Frekuensi `kelas.jenjang` null; pola dompdf
- [ ] Risiko vs rancangan; usulan penyesuaian
- [ ] **STOP** tunggu konfirmasi bila blocking

#### T2 — Phase 1 Database
- [ ] Tabel `interest_categories`
- [ ] Alter `instrument_questions`: `interest_category_id`, `jenjang_target`, `bobot`, softDeletes + index
- [ ] Alter `instrument_submissions`: `jenjang`, `kode_minat`, `category_scores`, dominant/secondary FK, `is_tied` (+ `answers_snapshot` hanya jika answers belum ada)
- [ ] Tabel `program_studis` + pivot `interest_category_program_studi`
- [ ] Tabel `career_fields` + pivot `career_field_interest_category` (**bukan** `career_infos`)
- [ ] Model, relasi, casts, scope `active()` / `forJenjang()`, accessor `kodeTag()`

#### T3 — Phase 2 Admin
- [ ] `admin.interest-categories.*` — hapus ditolak jika dipakai soal
- [ ] `admin.program-studi.*` — filter search/jurusan/verified/active; badge verifikasi
- [ ] `admin.bidang-karier.*` — job_zone, contoh pekerjaan, kategori+relevansi
- [ ] Nav grup "Data Master" + ActivityLogger

#### T4 — Phase 3 Guru soal
- [ ] Field wajib bila `minat_bakat`: kategori minat, jenjang_target, bobot
- [ ] UI opsi Alpine (2–6), Likert template, error `@if($errors->any())`
- [ ] Soft delete bila sudah ada jawaban; filter & kolom baru
- [ ] Activity log `instrument-question.*`; kategori lain tidak berubah

#### T5 — Phase 4 Service
- [ ] `InterestScoringService::score` → `InterestResult` (persen, tie, kode_minat, kolom lama terisi)
- [ ] `RecommendationService::forSma` / `forSmk` (bobot top-3, label kecocokan, fallback)
- [ ] Unit test scoring + rekomendasi

#### T6 — Phase 5 Siswa & hasil
- [ ] Filter soal by jenjang; pilih SMA/SMK bila jenjang kosong
- [ ] Halaman hasil 6 blok (kode minat, 3 kartu, distribusi, rekomendasi, disclaimer, aksi)
- [ ] PDF unduh + activity log submit/PDF
- [ ] Retake = tampil terbaru; guard pemilik 403

#### T7 — Phase 6 Guru *(opsional)*
- [ ] Tampilkan Kode Minat / minat utama / jenjang di hasil
- [ ] Scoping `CounselorStudentService` (commit terpisah)

#### T8 — Phase 7 Seed / test / docs
- [ ] `InterestCategorySeeder`, `MinatQuestionSeeder` (30), `ProgramStudiPcrSeeder` (`is_verified=false`), `CareerFieldSeeder`
- [ ] Feature Admin/Guru/Siswa + regresi instrumen lain
- [ ] Update `PANDUAN-PENGGUNAAN.md` (6, 7.10–7.11, 8.8, 9, 10, 11) dan `PANDUAN-FITUR-CORE.md`

#### T9 — Checklist launching (manusia)
Lihat Bagian D di bawah.

---

## Catatan audit awal (pra Phase 0)

Temuan cepat dari repo (bukan pengganti Phase 0 penuh):

| Item | Aktual |
|---|---|
| Branch asal | `kela` (kerja di `feature/minat-bakat`) |
| Tabel soal | `instrument_questions` (`category`, `question`, `options` json, `is_active`, `created_by`) |
| Tabel submission | `instrument_submissions` (`student_id`, `category`, `total_score`, `result_label`, `result_description`, `submitted_at`) |
| Jawaban | Sudah ada tabel `instrument_answers` (bukan hanya JSON di submission) → `answers_snapshot` kemungkinan **tidak wajib**; Phase 0 memastikan |
| Model | `InstrumentQuestion`, `InstrumentSubmission`, `InstrumentAnswer` |
| Konstanta kategori | `InstrumentQuestion::CATEGORY_MINAT_BAKAT = 'minat_bakat'` |
| Docs | `docs/PANDUAN-PENGGUNAAN.md`, `docs/PANDUAN-FITUR-CORE.md` |
| Jangan sentuh | `master_questions`, `career_infos` |

---

## 0. Keputusan Desain (agent tidak perlu bertanya lagi)

| Hal | Keputusan |
|---|---|
| Soal statis/dinamis | **Dinamis** (database + UI). Tambah/ubah/nonaktifkan tanpa ubah kode. |
| Berubah tiap tahun? | Tidak otomatis. Soal terpakai **tidak dihapus permanen** (soft delete/nonaktif). Hasil lama disimpan sebagai **snapshot**. |
| Bobot | Dua lapis: **skor tiap pilihan jawaban** (wajib) + **bobot soal** (pengali 1–5, default 1). |
| Kategori minat | Dinamis, tabel `interest_categories`. Seed awal **6 kategori model RIASEC** (R, I, A, S, E, C) dengan nama Indonesia yang ramah: Teknik, Teknologi & Sains, Kreatif, Sosial, Bisnis, Administrasi & Data. Admin bisa menambah kategori lain. |
| Penilaian | Skor kategori = Σ(skor opsi × bobot soal); persen = skor ÷ skor maksimum kategori. Peringkat memakai persen. **Kode Minat** = huruf 3 kategori teratas (contoh `RCS`). |
| Data kampus | **PCR saja.** Kampus tidak "dinilai"; prodi dicocokkan lewat relasi kategori↔prodi (relevansi 1–3). Manual via seeder + CRUD admin dari `pmb.pcr.ac.id`. Flag `is_verified`. |
| SMA vs SMK | Bank soal **satu**; tiap soal punya `jenjang_target` (`semua`/`SMA`/`SMK`). Beda ada di **output**: SMA → prodi PCR, SMK → bidang karier. |
| Jenjang siswa | `students.kelas_id → kelas.jenjang`; bila kosong, siswa memilih SMA/SMK di awal tes (disimpan di hasil). |
| Halaman hasil | Versi **sederhana** dari referensi (lihat Phase 5). Tidak ada rekomendasi pelatihan, tombol lowongan, atau program mitra. |

### Prioritas rilis

| Prioritas | Isi |
|---|---|
| **P0 – wajib launching** | Kategori, soal dinamis + bobot, scoring, Kode Minat, hasil sederhana, rekomendasi prodi PCR (SMA) & karier (SMK), unduh PDF hasil, seeder, test inti |
| **P1** | Scoping Hasil Instrumen per sekolah (Guru), batas retake, grafik radar, laporan agregat per kelas |
| **P2** | Multi-kampus, rekomendasi pelatihan, tautan lowongan, integrasi program mitra |

> Agent hanya mengerjakan **P0** kecuali diminta lain.

### Catatan koordinasi (modul ini milik Tim asesmen)
Fitur ini **memperluas** modul instrumen yang sudah ada (kategori `minat_bakat`), bukan modul baru. Kerjakan di branch `feature/minat-bakat`, satu commit per phase. Phase 6 (scoping) dikerjakan hanya jika Tim asesmen setuju. Kategori instrumen lain tidak boleh berubah perilakunya.

---

# BAGIAN A — SKILL

Sudah disimpan di:

- `.cursor/rules/minat-bakat.mdc` (`alwaysApply: false`)
- `.opencode/skill/minat-bakat-implementer/SKILL.md`

---

# BAGIAN B — MASTER PROMPT

Paste blok di bawah ke Cursor (Agent mode) / OpenCode. Jalankan **per phase**; minta agent berhenti dan melapor di akhir tiap phase.

```markdown
Kamu adalah senior Laravel engineer. Gunakan skill `minat-bakat-implementer`.
Proyek: Sistem BKK Sekolah (Laravel, Blade, Alpine, Tailwind, dompdf). Panduan: docs/PANDUAN-PENGGUNAAN.md
dan docs/PANDUAN-FITUR-CORE.md. Kerjakan HANYA scope P0. Bahasa UI/validasi/flash: Bahasa Indonesia.

# TUJUAN (10 kebutuhan)
1. Soal dinamis (tambah/ubah/hapus/atur tanpa ubah kode).
2. Tiap soal punya pilihan jawaban berbobot (skor).
3. Tiap soal dikelompokkan ke kategori minat (dinamis; seed awal RIASEC).
4. Sistem mengakumulasi bobot jawaban per kategori.
5. Sistem menentukan minat dominan (+ Kode Minat 3 huruf).
6. Hasil dibedakan jenjang SMA vs SMK.
7. SMA -> rekomendasi program studi dari kategori minat.
8. Data prodi SMA difokuskan Politeknik Caltex Riau (PCR).
9. SMK -> rekomendasi bidang karier dari kategori minat.
10. Halaman hasil menampilkan minat utama + rekomendasinya.

# PHASE 0 — AUDIT (tanpa mengubah file)
Ikuti "Audit dulu" di skill. Laporkan:
- Skema aktual tabel soal & submission, nama model/controller/view/route.
- Cara scoring saat ini (lokasi, rumus maxScore, label) dan apakah `answers` disimpan (bentuk apa).
- Seberapa sering `kelas.jenjang` null.
- Pola dompdf yang sudah ada (view PDF, nama file).
- Risiko/konflik dengan rancangan, usulan penyesuaian.
BERHENTI dan tunggu konfirmasi bila ada temuan yang membatalkan rancangan.

# PHASE 1 — DATABASE (migration aditif + model)
Migration baru (jangan edit migration lama):

1) `interest_categories`
   id, kode (string 5, unique; seed: R,I,A,S,E,C), nama (120), deskripsi (text nullable),
   warna (string 20 nullable), urutan (unsignedSmallInteger default 0), is_active (bool default true), timestamps.

2) Alter tabel soal instrumen (nama sesuai audit):
   + interest_category_id (nullable FK -> interest_categories, restrictOnDelete)
   + jenjang_target (string 10, default 'semua')   // semua|SMA|SMK
   + bobot (unsignedTinyInteger, default 1)        // pengali 1..5
   + softDeletes()
   Index (category, is_active, interest_category_id).

3) Alter tabel submission instrumen (nama sesuai audit):
   + jenjang (string 10 nullable)        // snapshot SMA|SMK
   + kode_minat (string 10 nullable)     // contoh "RCS"
   + category_scores (json nullable)     // snapshot [{id,kode,nama,raw,max,persen}] terurut peringkat
   + dominant_interest_id, secondary_interest_id (nullable FK, nullOnDelete)
   + is_tied (bool default false)
   Jika `answers` belum disimpan, tambah `answers_snapshot` (json: soal, opsi terpilih, skor, bobot).

4) `program_studis`
   id, institusi (150, default 'Politeknik Caltex Riau'), nama (150), jenjang_pendidikan (10: D3|D4),
   jurusan (150 nullable), deskripsi (text nullable), prospek_karier (text nullable), website_url (nullable),
   is_verified (bool default false), is_active (bool default true), timestamps.
   Unique (institusi, nama, jenjang_pendidikan).
   Pivot `interest_category_program_studi`: interest_category_id, program_studi_id, relevansi (tinyint 1..3 default 2); unique pair.

5) `career_fields` (JANGAN pakai `career_infos`)
   id, nama (150), deskripsi (text nullable), contoh_pekerjaan (json nullable, array string),
   job_zone (unsignedTinyInteger nullable, 1..5), is_active (bool default true), timestamps.
   Pivot `career_field_interest_category`: interest_category_id, career_field_id, relevansi (1..3); unique pair.

Model + relasi + fillable/casts + scope `active()`, `forJenjang($j)`.
Accessor `kodeTag()` pada ProgramStudi & CareerField: 2 huruf kategori dengan relevansi tertinggi, mis. "R-C".
Backfill: soal minat_bakat lama -> `interest_category_id = null`, ditandai "Belum dikategorikan" di UI Guru dan TIDAK dihitung scoring.

# PHASE 2 — MASTER DATA (Admin)
Resource admin (pola modul Kelas/Sekolah; panel di index; Form Request Store/Update; ActivityLogger):
- `admin/interest-categories` (`admin.interest-categories.*`) — CRUD; hapus ditolak bila dipakai soal
  ("Kategori tidak dapat dihapus karena masih dipakai soal. Nonaktifkan saja.").
- `admin/program-studi` (`admin.program-studi.*`) — CRUD + pilih kategori & relevansi 1–3; badge Terverifikasi/Belum;
  filter search, jurusan, verified, active.
- `admin/bidang-karier` (`admin.bidang-karier.*`) — CRUD + job_zone (1–5) + contoh pekerjaan (satu per baris) + kategori & relevansi.
Tambah ke `config/navigation.php` grup "Data Master". Activity log: `interest-category.*`, `program-studi.*`, `bidang-karier.*` (properties: nama).

# PHASE 3 — KELOLA SOAL (Guru, perluas modul yang ada)
Di `guru/instrument-questions`:
- Bila category = `minat_bakat`: tampilkan & wajibkan `interest_category_id` (kategori aktif), `jenjang_target`, dan `bobot` (1–5, default 1).
- PERBAIKI UI opsi (Alpine): tombol Tambah/Hapus opsi (min 2, maks 6), kolom label + skor (0–100 sesuai aturan lama),
  tombol "Pakai template Likert 0–4".
- Hapus banner error statis `x-alert type="error"`; ganti `@if($errors->any())`.
- Hapus soal = soft delete bila sudah ada jawaban; selain itu boleh hapus permanen. "Nonaktifkan" tetap ada.
- Filter tambahan: kategori minat, jenjang_target; kolom tabel tampilkan kategori minat, jenjang, bobot.
- Form Request: `interest_category_id` required_if category=minat_bakat (exists, aktif); `jenjang_target` in semua,SMA,SMK; `bobot` integer 1..5.
- Activity log `instrument-question.created|updated|deleted` (sebelumnya tidak ada).
- Jangan ubah perilaku kategori lain (field baru opsional untuk mereka).

# PHASE 4 — SCORING & REKOMENDASI (service, bisa di-unit-test)
`App\Services\Minat\InterestScoringService::score(Collection $questions, array $answers): InterestResult`
1. `$questions` = soal aktif, kategori minat_bakat, punya kategori minat, `jenjang_target` ∈ {semua, jenjang siswa}.
   Validasi jumlah id jawaban == jumlah soal ("Jawaban tidak sesuai dengan daftar soal aktif."); indeks opsi di luar rentang -> 422
   "Pilihan jawaban tidak valid." (pertahankan perilaku lama).
2. Per kategori: `raw = Σ(skor opsi terpilih × bobot soal)`; `max = Σ(skor opsi tertinggi × bobot soal)`;
   `persen = raw / max × 100` (1 desimal; max 0 -> 0).
3. Peringkat berdasarkan **persen** (adil bila jumlah soal per kategori tidak sama). Tie-break: raw lebih besar -> `urutan` kategori kecil.
   `is_tied = true` bila selisih persen peringkat 1 dan 2 < 0.5.
4. `kode_minat` = gabungan `kode` 3 kategori teratas (contoh "RCS"). `dominant` = peringkat 1, `secondary` = peringkat 2.
5. Kolom lama tetap diisi agar Hasil Instrumen lama tidak rusak: `result_label` = nama kategori dominan,
   `result_description` = deskripsi kategori, `total_score` = Σ raw.
6. Simpan snapshot `category_scores` (semua kategori terurut). Kategori tanpa soal pada jenjang itu tidak ikut peringkat.

`App\Services\Minat\RecommendationService`
- `forSma(InterestResult)`: prodi `is_active` AND `is_verified`. Skor = Σ relevansi × bobot peringkat (top-1: 1.0, top-2: 0.6, top-3: 0.3).
  Label kecocokan: skor >= 3 -> "Sangat Cocok", selain itu "Cocok". Urut skor desc, maks 6.
- `forSmk(InterestResult)`: sama untuk `career_fields` (maks 6), menyertakan `job_zone`.
- Fallback: bila hasil kosong untuk top-3, tampilkan catatan
  "Belum ada program studi PCR yang langsung sesuai minatmu; diskusikan pilihan lain dengan Guru BK." dan jangan error.
- Rekomendasi dihitung saat halaman dibuka (data terbaru); minat/skor memakai snapshot.
- Jenjang: `student->kelas->jenjang` (SMA|SMK); null -> siswa memilih di awal tes (disimpan `submission.jenjang`).
  SD/SMP -> pesan "Asesmen ini untuk siswa SMA/SMK."

# PHASE 5 — ALUR SISWA & HALAMAN HASIL (versi sederhana dari referensi)
`siswa/instruments`:
- Tes minat bakat: hanya soal sesuai jenjang; radio Likert; indikator progres "n/total".
- Submit -> redirect ke `siswa.instruments.hasil` (GET `/siswa/instruments/hasil/{submission}`; guard pemilik, 403).
- Index instrumen menampilkan ringkasan "Kode Minat: RCS" + tombol "Lihat hasil" / "Ulangi asesmen". Retake boleh, yang ditampilkan = terbaru.

Halaman hasil (urutan blok, Tailwind, responsif, mobile-first):
1. **Kartu utama**: judul "Kode Minat: RCS", kalimat "Tiga minat dominan kamu: R, C, S.", badge jenjang (SMA/SMK).
   Bila `is_tied`: tambahkan catatan "Minat utamamu seimbang: X dan Y."
2. **Tiga kartu minat teratas**: huruf + nama kategori + badge persen + deskripsi 1 kalimat.
3. **Distribusi skor**: bar horizontal semua kategori terurut peringkat (persen di kanan).
4. **Rekomendasi**:
   - SMA -> "Program Studi di Politeknik Caltex Riau": kartu (nama, badge D3/D4, badge kecocokan, "Tag minat: R-C", deskripsi singkat, tombol "Lihat di situs PCR" bila `website_url`).
   - SMK -> "Rekomendasi Karier": kartu (nama bidang, badge "Job Zone n", "Tag minat: R-C", deskripsi, contoh pekerjaan).
   - Bila SMK: `<details>` ringkas "Apa itu Job Zone?" berisi 5 baris (1 persiapan minimal; 2 dasar; 3 menengah/vokasi atau D3; 4 tinggi/setara S1; 5 sangat tinggi).
5. **Disclaimer**: "Hasil ini gambaran kecenderungan minat, bukan keputusan akhir. Diskusikan dengan Guru BK."
6. **Tombol**: "Ulangi asesmen", "Unduh laporan" (PDF), "Kembali ke dashboard".
TIDAK dibuat (P2): rekomendasi pelatihan, tombol "Lihat Lowongan", program mitra.

PDF "Unduh laporan": route `siswa.instruments.hasil.pdf` (GET `/siswa/instruments/hasil/{submission}/pdf`, guard pemilik),
dompdf A4 portrait, `download()`, nama `hasil-minat-{slug-nama}-{Ymd}.pdf`. Isi: identitas siswa, Kode Minat, tabel skor per kategori,
daftar rekomendasi, disclaimer. Ikuti gaya view PDF yang sudah ada.
Activity log: `instrument.minat.submitted` (submission_id, kode_minat, jenjang) dan `instrument.minat.pdf.downloaded`.

# PHASE 6 — SISI GURU (opsional, butuh persetujuan Tim asesmen)
Di `guru/instrument-results`: untuk minat_bakat tampilkan Kode Minat, minat utama, jenjang (kolom lama tetap).
Opsional (commit terpisah): scoping memakai `CounselorStudentService` agar Guru hanya melihat siswa dalam cakupannya.

# PHASE 7 — SEEDER, TEST, DOKUMENTASI
Seeder idempotent (`updateOrCreate`) sesuai Bagian C file `PROMPT-IMPLEMENTASI-ASESMEN-MINAT.md`:
`InterestCategorySeeder`, `MinatQuestionSeeder` (30 soal), `ProgramStudiPcrSeeder` (is_verified=false), `CareerFieldSeeder`.

Test (RefreshDatabase):
- Unit scoring: persen & bobot soal, peringkat, tie-break, `is_tied`, kode_minat, kategori tanpa soal, filter jenjang, opsi invalid.
- Unit rekomendasi: bobot top-3, label kecocokan, hanya is_verified & is_active, fallback kosong, SMA vs SMK.
- Feature Guru: CRUD soal dengan kategori/bobot/opsi dinamis; kategori wajib untuk minat_bakat; soal terpakai ter-soft-delete.
- Feature Admin: CRUD kategori/prodi/karier; hapus kategori terpakai ditolak; role lain 403.
- Feature Siswa: SMA -> hasil menampilkan prodi PCR; SMK -> karier + Job Zone; siswa lain 403 pada hasil orang lain; PDF terunduh.
- Regresi: kategori instrumen lain tetap berfungsi seperti sebelumnya.
Update docs/PANDUAN-PENGGUNAAN.md (bagian 6, 7.10–7.11, 8.8, 9, 10, 11) dan docs/PANDUAN-FITUR-CORE.md.

# BATASAN
- Hanya P0. Tidak menambah kampus selain PCR. Tidak menyentuh master_questions & career_infos. Tidak drop kolom lama.
- Jangan mengarang data PCR; pakai lampiran, `is_verified=false`, tulis TODO verifikasi di laporan.
- Setiap phase: jalankan test, laporkan file berubah + cara uji manual, lalu berhenti.

# FORMAT LAPORAN AKHIR
1) Ringkasan per phase 2) Daftar file 3) Perintah deploy (migrate, db:seed, optimize:clear)
4) Uji manual per role 5) Risiko & hal yang ditunda (P1/P2)
```

---

# BAGIAN C — LAMPIRAN DATA

## C.1 Kategori Minat (seed, model RIASEC)

| kode | nama | deskripsi singkat | warna | urutan |
|---|---|---|---|---|
| R | Teknik | Suka aktivitas praktis: alat, mesin, listrik, perbaikan, dan kerja lapangan. | `#f59e0b` | 1 |
| I | Teknologi & Sains | Suka menganalisis, memecahkan masalah, memprogram, dan memahami cara kerja sesuatu. | `#3b82f6` | 2 |
| A | Kreatif | Suka berkarya, mendesain, menulis, dan mengungkapkan ide orisinal. | `#8b5cf6` | 3 |
| S | Sosial | Suka membantu, mengajar, mendampingi, dan berinteraksi dengan orang lain. | `#ec4899` | 4 |
| E | Bisnis | Suka memimpin, meyakinkan orang, berjualan, dan mengambil peluang. | `#ef4444` | 5 |
| C | Administrasi & Data | Suka keteraturan, data, administrasi, dan detail yang konsisten. | `#10b981` | 6 |

## C.2 Template Opsi Jawaban

| label | score |
|---|---|
| Sangat tidak tertarik | 0 |
| Tidak tertarik | 1 |
| Netral | 2 |
| Tertarik | 3 |
| Sangat tertarik | 4 |

Semua soal seed: `bobot = 1`, `jenjang_target = semua`.

## C.3 Bank Soal Awal (30 soal)

Pertanyaan pengantar: "Seberapa tertarik kamu pada kegiatan berikut?"

**R – Teknik:** Membongkar/merakit alat; rangkaian listrik/robot; praktik bengkel/lab; memperbaiki barang rusak; cara kerja kendaraan/mesin/instalasi.

**I – Teknologi & Sains:** Utak-atik komputer/gawai; buat program/website; olah data jadi info; pelajari teknologi baru; solusi masalah dengan analisis.

**A – Kreatif:** Gambar/desain; video/foto/konten; ide orisinal; menulis cerita/naskah; estetika warna/tampilan.

**S – Sosial:** Dengarkan/bantu teman; mengajar; organisasi; kegiatan sosial; pahami perasaan orang lain.

**E – Bisnis:** Berjualan/usaha kecil; pimpin & strategi; negosiasi; tren pasar; ambil peluang & risiko.

**C – Administrasi & Data:** Rapikan data/arsip; ikuti prosedur; cek angka/laporan; susun jadwal; kelola spreadsheet.

## C.4 Prodi PCR — Seeder Awal (`is_verified = false`)

Verifikasi di `https://pmb.pcr.ac.id`, lalu set `is_verified = true` via Admin.

| Prodi (kandidat) | Jenjang | Kategori → relevansi |
|---|---|---|
| Teknik Informatika | D4 | I 3, C 1 |
| Sistem Informasi | D4 | C 3, I 2, E 1 |
| Teknologi Rekayasa Komputer | D4 | I 3, R 2 |
| Teknologi Rekayasa Sistem Elektronika | D4 | R 3, I 2 |
| Teknologi Rekayasa Jaringan Telekomunikasi | D4 | R 3, I 2 |
| Teknologi Rekayasa Mekatronika | D4 | R 3, I 2 |
| Teknik Listrik | D4 | R 3, C 1 |
| Teknik Mesin | D4 | R 3 |
| Akuntansi Perpajakan | D4 | C 3, E 1 |

Catatan: A & S belum punya prodi PCR yang jelas; E lemah → fallback / peringkat 2–3.

## C.5 Bidang Karier SMK — Seeder Awal

| Bidang | Job Zone | Contoh pekerjaan | Kategori → relevansi |
|---|---|---|---|
| Pengembang Web & Aplikasi | 3 | Web developer, mobile developer, QA tester | I 3, C 1 |
| Teknisi Jaringan & Dukungan TI | 3 | Network administrator, IT support, teknisi jaringan | R 3, I 2 |
| Analis & Pengelola Data | 3 | Staf data, analis data junior, admin database | I 3, C 2 |
| Teknisi Listrik & Elektronika | 2 | Teknisi listrik, teknisi elektronika, teknisi instrumentasi | R 3, C 1 |
| Teknisi Mesin & Otomotif | 2 | Mekanik, operator mesin, teknisi maintenance | R 3, C 1 |
| Teknisi Otomasi Industri | 3 | Teknisi mekatronika, teknisi PLC, teknisi kontrol | R 3, I 2 |
| Administrasi & Akuntansi | 3 | Staf administrasi, staf akuntansi, staf pajak | C 3, E 1 |
| Penjualan & Pemasaran | 2 | Sales, digital marketing, staf pemasaran | E 3, S 1 |
| Wirausaha | 3 | Pemilik usaha kecil, reseller, pengelola toko | E 3, R 1 |
| Instruktur & Pendampingan | 3 | Tutor, instruktur pelatihan, fasilitator komunitas | S 3, A 1 |
| HRD & Layanan Pelanggan | 3 | Staf HRD, customer relations, resepsionis | S 3, E 2 |
| Desain Grafis & Konten | 3 | Desainer grafis, editor video, fotografer, content creator | A 3, I 1 |
| Desain Antarmuka (UI/UX) | 3 | UI/UX designer, desainer produk digital | A 3, I 2 |

---

# BAGIAN D — Checklist Uji Manual (sebelum launching)

- [ ] Admin: tambah kategori, prodi (verified), bidang karier + Job Zone; relasi kategori tersimpan.
- [ ] Guru: tambah soal baru (kategori, bobot, 5 opsi berbobot); nonaktifkan soal; ubah opsi tanpa ubah kode.
- [ ] Siswa SMA: isi 30 soal → hasil menampilkan Kode Minat, 3 kartu minat, bar distribusi, prodi PCR (hanya `is_verified`).
- [ ] Siswa SMK: hasil menampilkan karier + badge Job Zone + penjelasan Job Zone.
- [ ] Siswa dengan `kelas.jenjang` kosong: diminta memilih SMA/SMK.
- [ ] Skor seimbang (tie): catatan "minat utama seimbang" tampil.
- [ ] Minat dominan tanpa prodi PCR: rekomendasi dari peringkat 2–3 atau pesan fallback.
- [ ] Tombol "Ulangi asesmen" dan "Unduh laporan" (PDF) berfungsi; siswa lain mendapat 403 di hasil orang lain.
- [ ] Instrumen lain (gaya belajar, kepribadian, masalah) tetap berjalan.
- [ ] `php artisan test` hijau, `route:list` tidak ada route hilang.
