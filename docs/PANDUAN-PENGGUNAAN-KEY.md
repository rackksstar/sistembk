# Panduan Penggunaan Fitur Key (Lanjut Kuliah)

Panduan operasional untuk modul **Key** — asesmen Minat Bakat RIASEC/Talents Mapping,
rekomendasi Program Studi PCR, konsultasi prodi, dan Chatbot Konseling.

> Disusun dari pembacaan langsung kode di repo ini (route, controller, form request,
> model, view, config). Nomor baris merujuk file yang ada di repo.

Cakupan: fitur yang menandai modul `key` di sidebar (`config/navigation.php`)
ditambah dua layanan pendukung yang dipakai di alur yang sama: **Konsultasi Prodi**
dan **Chatbot Konseling**.

---

## 0. Urutan setup (wajib, jangan dibalik)

| # | Siapa | Langkah | Efek jika dilewati |
|---|---|---|---|
| 1 | Admin / Guru BK | Isi **Kategori Minat RIASEC** | Soal Key tidak bisa disimpan (kategori wajib) |
| 2 | Admin / Guru BK | Isi **Program Studi PCR** + centang minimal satu kategori + **Terverifikasi** + **Aktif** | Rekomendasi prodi kosong untuk siswa SMA |
| 3 | Admin / Guru BK | Isi **Bidang Karier** + kategori + **Aktif** | Rekomendasi karier kosong untuk siswa SMK |
| 4 | Guru BK | Tambah **Soal Minat Bakat Kuliah** (kategori `minat_bakat`, kode RIASEC, target jenjang) | Halaman asesmen siswa menampilkan "Soal belum tersedia" |
| 5 | Siswa | Isi asesmen → lihat hasil → unduh PDF / ajukan konsultasi | — |

Guru BK baru bisa dipilih sebagai konselor jika akunnya berstatus **disetujui**
(`app/Http/Controllers/Siswa/ProdiConsultationController.php:96`).p

---

## 1. Admin

Masuk: sidebar **Karier Lanjut Kuliah** (`config/navigation.php:55-71`).

Tiga menu data master di bawah ini juga tersedia untuk **Guru BK** dengan hak
**kelola penuh (CRUD)**: Kategori Minat RIASEC, Program Studi PCR, dan Bidang
Karier. Lihat 2.4 untuk perbedaan menu dan hal yang perlu diperhatikan.

### 1.1 Kategori Minat RIASEC

Menu: **Kategori Minat RIASEC** (`admin.interest-categories.index`).

Langkah:
1. Klik **Tambah kategori**.
2. Isi **Kode** (1–5 huruf kapital, unik — contoh `R`, `I`, `A`, `S`, `E`, `C`).
3. Isi **Nama** (maks. 120 karakter), **Deskripsi** (opsional, maks. 2000).
4. Pilih **Warna** (hex) sebagai penanda di daftar kategori, dan **Urutan** (0–65535)
   untuk urutan tampil.
5. Pilih **Aktif** / **Nonaktif**, lalu simpan.
6. Untuk mengubah: **Edit**; untuk menghapus: **Hapus** (ada konfirmasi).

Aturan validasi (`app/Http/Requests/Admin/StoreInterestCategoryRequest.php`):
- `kode`: wajib, maks. 5, regex `^[A-Z]{1,5}$`, unik. Input otomatis di-uppercase.
- `nama`: wajib, maks. 120.
- `warna`: format hex; otomatis diberi awalan `#` bila belum ada.
- `urutan`: bilangan bulat 0–65535.
- `is_active`: wajib boolean.

Catatan: kategori **nonaktif** tidak bisa dipakai menyimpan soal baru. Form prodi dan
bidang karier tetap menampilkan semua kategori, jadi pilih hanya kategori yang aktif
agar relasi yang dibuat benar-benar dipakai saat rekomendasi.

### 1.2 Program Studi PCR

Menu: **Program Studi PCR** (`admin.program-studi.index`).

Langkah:
1. Klik **Tambah program studi**.
2. **Institusi** otomatis `Politeknik Caltex Riau` bila dikosongkan.
3. Isi **Nama** (unik per institusi + jenjang), pilih **Jenjang** `D3`/`D4`.
4. Isi **Jurusan**, **Deskripsi**, **Prospek Karier**, **Website** (opsional).
5. Set **Verifikasi** = `Terverifikasi` dan **Status** = `Aktif`.
6. Di daftar kategori, **centang minimal satu** kategori minat dan pilih
   **Relevansi 1–3** untuk setiap kategori yang dicentang.
7. Simpan.

Aturan validasi (`app/Http/Requests/Admin/StoreProgramStudiRequest.php`):
- `nama` unik untuk kombinasi `institusi` + `jenjang_pendidikan`.
- `jenjang_pendidikan` hanya `D3` atau `D4`.
- `is_verified` dan `is_active` wajib diisi.
- Minimal satu kategori minat dicentang, dan setiap yang dicentang wajib punya
  `relevansi` 1–3
  (`app/Http/Requests/Admin/Concerns/ValidatesInterestCategoryLinks.php`).

Penting untuk rekomendasi:
- Hanya prodi dengan `is_verified = true` **dan** `is_active = true` yang dipakai
  (`app/Services/Minat/RecommendationService.php:40`).
- Ada prodi tapi belum diverifikasi → siswa melihat pesan
  *"Program studi PCR sudah ada di sistem, tetapi belum ada yang diverifikasi Admin.
  Minta Admin memverifikasi prodi di menu Program Studi agar rekomendasi muncul."*
  (`RecommendationService.php:19`).
- Bobot kecocokan: minat ke-1 = 1.0, ke-2 = 0.6, ke-3 = 0.3
  (`RecommendationService.php:11-15`).

### 1.3 Bidang Karier (jalur SMK)

Menu: **Bidang Karier** (`admin.bidang-karier.index`).

Langkah:
1. Klik **Tambah bidang karier**.
2. Isi **Nama** (maks. 150) dan **Deskripsi** (opsional, maks. 5000).
3. Isi **Contoh Pekerjaan** — **satu pekerjaan per baris**.
4. Pilih **Job Zone 1–5** (opsional, "Belum ditentukan" bila kosong).
5. Set **Status** `Aktif`.
6. Centang minimal satu kategori minat + isi **Relevansi 1–3**, lalu simpan.

Aturan validasi (`app/Http/Requests/Admin/StoreCareerFieldRequest.php`):
- `job_zone` 1–5 atau kosong.
- Minimal satu kategori minat + relevansi wajib.

Hanya bidang karier `is_active = true` yang direkomendasikan ke siswa SMK
(`RecommendationService.php:95-137`).

### 1.4 Memantau Konsultasi Prodi

Menu: **Konseling** (judul sidebar **Konseling & Laporan**,
`admin.consultations.index`).

Halaman ini bersifat **pemantauan (read-only)** — tidak ada tombol ubah status.
Filter yang tersedia: **status**, **kategori** (`prodi_kuliah` untuk konsultasi
prodi), **sekolah**, dan **pencarian** (topik, detail, nama/NISN siswa, guru BK,
atau nama prodi). Baris konsultasi prodi menampilkan badge **Prodi: <nama>** dan
kolom **Laporan** berisi `Ada laporan` bila guru BK sudah mengisi hasil/evaluasi
(`app/Http/Controllers/Admin/ConsultationController.php:14-58`,
`resources/views/admin/consultations/index.blade.php:71-98`).

Untuk mengunduh laporan konseling yang sudah diisi, gunakan menu laporan
konseling individu Admin (`admin.individual-reports.*`), yang memakai template PDF
yang sama dengan Guru BK.

---

## 2. Guru BK

Masuk: sidebar **Lanjut Kuliah** (`config/navigation.php:172-194`).

### 2.1 Menambah soal Minat Bakat Kuliah

Menu: **Soal Minat Bakat Kuliah** (`guru.instrument-questions.index`, parameter
`module=key`).

Langkah:
1. Klik **Tambah soal**.
2. Pilih **Jenis instrumen** = `Minat Bakat Kuliah (RIASEC / Talents Mapping)`.
3. Tulis **Teks soal** (satu pernyataan, maks. 1000 karakter).
4. Pilih **Kode RIASEC (Talents Mapping)** — wajib untuk kategori ini.
   Sistem otomatis menautkan `interest_category_id` dari kode tersebut bila
   kategorinya aktif (`app/Http/Requests/Guru/Concerns/ValidatesInstrumentQuestion.php:21-35`).
5. Pilih **Target Jenjang**: `Semua`, `SMA`, atau `SMK`.
6. Isi **Bobot Soal (1–5)**; kosong = 1.
7. Isi 2–6 **pilihan jawaban**, masing-masing label + skor 0–100.
   Tombol **Pakai template Likert 1–5** mengisi default
   *Sangat Tidak Suka (1) – Sangat Suka (5)*.
8. Centang **Aktif**, lalu **Simpan soal**.

Aturan validasi (`ValidatesInstrumentQuestion.php:58-112`):
- `talent_code` wajib untuk `minat_bakat` dan harus salah satu kode RIASEC.
- `interest_category_id` wajib dan harus kategori aktif (atau kategori yang sedang diedit).
- `jenjang_target` hanya `semua` / `SMA` / `SMK`.
- `bobot` 1–5; `options` 2–6, `score` 0–100.

Untuk mengubah/menghapus gunakan tombol **Edit** / **Hapus** pada baris soal.
Soal yang **nonaktif** tidak akan ditampilkan ke siswa.

Catatan: label halaman siswa menyebut "Asesmen RIASEC (99 item)", tetapi jumlah soal
yang benar-benar tampil mengikuti soal aktif yang memenuhi syarat (lihat 3.1).

### 2.2 Melihat hasil dan Matriks Talents Mapping

Menu: **Hasil Minat Bakat Kuliah** (`guru.instrument-results.index`).

Langkah:
1. Buka menu tersebut untuk melihat daftar hasil siswa: **Kode Minat** (Holland)
   dan jenjang.
2. Klik **Lihat Matriks Talents Mapping →** (`guru.instrument-results.talent-matrix`)
   untuk peta skor R–I–A–S–E–C tiap siswa dari hasil Minat Bakat terakhir.

Jika belum ada siswa yang mengisi, tampil **"Belum ada hasil"**.

### 2.3 Menangani pengajuan Konsultasi Prodi

Pengajuan siswa masuk sebagai `consultation_requests` dengan
`case_category = prodi_kuliah` dan status `pending`
(`app/Http/Controllers/Siswa/ProdiConsultationController.php:51-81`).

Siswa menerima pesan setelah mengirim:
*"Pengajuan konsultasi prodi kuliah berhasil dikirim. Guru BK akan meninjau
permintaan Anda."*

#### Siapa yang melihat pengajuan

Daftar **Konseling** hanya menampilkan pengajuan yang **belum punya konselor**
(`counselor_id` kosong) atau yang **konselornya adalah Anda**
(`app/Http/Controllers/Guru/ConsultationController.php:41-44`).

- Jika siswa memilih Guru BK tertentu, pengajuan **hanya terlihat oleh guru itu**
  dan tidak muncul di daftar guru BK lain.
- Jika pengajuan belum ditugaskan, semua Guru BK melihatnya; guru yang menekan
  **Setujui** menjadi konselor pengajuan tersebut.

Khusus konsultasi prodi, `counselor_id` **wajib dipilih siswa** saat mengajukan
(`StoreProdiConsultationRequest`), sehingga pengajuan baru selalu langsung
terarah ke Guru BK pilihan siswa. Kondisi "belum ditugaskan" berlaku untuk
pengajuan konseling umum/legacy.

#### Daftar dan filter

Menu: **Konseling**. Filter yang tersedia: **status**, **kategori** (pilih
**Konsultasi Prodi Kuliah**), dan **pencarian** yang mencakup topik, detail,
nama/NISN siswa, nama Guru BK, dan **nama prodi**
(`ConsultationController.php:45-57`).

Pada tabel, konsultasi prodi menampilkan badge **Prodi: <nama>**; tombol
**Detail** memperlihatkan jenjang prodi, preferensi tanggal/jam siswa, jadwal
sesi, catatan jadwal, dan alasan penolakan bila ada
(`resources/views/guru/consultations/index.blade.php:96-100` dan `:171-197`).

#### Aksi dan status

| Aksi | Kapan muncul | Efek |
|---|---|---|
| **Setujui** | status `pending` | Konselor = Anda, status → `disetujui` |
| **Tolak** | `pending` atau `disetujui` | Status → `ditolak`, wajib isi alasan (maks. 1000) |
| **Jadwal** | `pending`, `disetujui`, atau `dijadwalkan_ulang` | Status → `disetujui` (jadwal pertama) atau `dijadwalkan_ulang` (ubah jadwal) |
| **Laporan** | `disetujui` atau `dijadwalkan_ulang` | Status → `selesai`, laporan tersimpan |
| **PDF** | sudah ada `result` | Unduh laporan konseling (A4) |

Tombol aksi mengikuti aturan di view
(`resources/views/guru/consultations/index.blade.php:128-148`) dan controller:

- **Setujui** hanya dari `pending`; permintaan lain ditolak dengan HTTP 422
  (`ConsultationController.php:106-118`).
- **Tolak** hanya dari `pending`/`disetujui`; status `dijadwalkan_ulang` tidak
  bisa ditolak — ubah jadwal atau isi laporan
  (`app/Models/ConsultationRequest.php:167-173`). Alasan wajib
  (`app/Http/Requests/Guru/RejectConsultationRequest.php:19-21`).
- **Jadwal** (`ConsultationController.php:136-167`,
  `app/Http/Requests/Guru/ScheduleConsultationRequest.php:17-47`):
  - tanggal wajib, tidak boleh sebelum hari ini;
  - jam wajib format `HH:MM`;
  - siswa wajib valid; catatan maks. 1000;
  - sistem menolak jadwal yang **bentrok** dengan sesi lain Guru BK yang sama
    pada tanggal + jam identik.
  - Setelah tersimpan, `scheduled_at` dihitung otomatis dan siswa melihat jadwal
    di riwayatnya.
- **Laporan** (`ConsultationController.php:169-181`,
  `app/Http/Requests/Guru/StoreConsultationReportRequest.php:19-34`):
  - hanya konselor yang ditugaskan (HTTP 403 bila bukan);
  - wajib: kategori kasus, pertemuan ke- (1–100), durasi (1–600 menit), tempat,
    pendekatan/teknik (maks. 2000), semester (1/2), tahun (2020–2100), hasil
    (maks. 3000), evaluasi (maks. 3000);
  - opsional: RPL terkait (hanya RPL milik Anda yang cocok dengan siswa/kelas)
    dan tindak lanjut (maks. 3000);
  - menyimpan laporan sekaligus mengubah status menjadi `selesai`.
- **PDF** (`ConsultationController.php:183-215`): hanya konselor pengajuan atau
  Admin; memakai data sekolah konselor/siswa dan logo sekolah bila tersedia.

#### Alur status

```
pending ──Setujui──> disetujui ──Jadwal (pertama)──> disetujui
   │  └──Jadwal (dari pending)──> disetujui
   │                     │
   │                     ├──Jadwal (ubah)──> dijadwalkan_ulang ──Laporan──> selesai
   │                     ├──Laporan──────────────────────────────────────> selesai
   │                     └──Tolak──> ditolak
   └──Tolak──> ditolak
```

Catatan operasional:
- Tanggal/jam **preferensi siswa** hanya usulan; jadwal resmi tetap ditentukan
  Guru BK lewat aksi **Jadwal**.
- Konsultasi prodi tidak wajib berakhir dengan laporan, tetapi status `selesai`
  hanya tercapai lewat aksi **Laporan**.
- Setelah status `selesai`, tombol **PDF** tersedia karena laporan sudah terisi.

### 2.4 Mengelola data master Key (Kategori, Prodi, Bidang Karier)

Guru BK dapat menambah, mengubah, dan menghapus tiga data master Key tanpa
meminta bantuan Admin. Datanya **sama** dengan yang dikelola Admin — perubahan
langsung dipakai sistem rekomendasi dan asesmen siswa.

Menu (sidebar **Lanjut Kuliah**, `config/navigation.php:172-207`):
- **Kategori Minat RIASEC** — `guru.interest-categories.index`
- **Program Studi PCR** — `guru.program-studi.index`
- **Bidang Karier** — `guru.bidang-karier.index`

Langkah dan aturan validasi identik dengan bagian Admin:
- Kategori: lihat 1.1.
- Program studi: lihat 1.2.
- Bidang karier: lihat 1.3.

Yang perlu diperhatikan:
- **Kategori** yang masih dipakai soal (termasuk soal yang diarsip) **tidak bisa
  dihapus**; nonaktifkan saja. Kategori nonaktif tidak bisa dipilih saat membuat
  soal baru.
- **Program studi** harus tetap `Terverifikasi` + `Aktif` agar muncul di
  rekomendasi siswa SMA; menghapus prodi juga melepas relasi kategorinya.
- **Bidang karier** harus `Aktif` agar muncul di rekomendasi siswa SMK.
- Semua aksi tercatat di activity log (`interest-category.*`, `program-studi.*`,
  `bidang-karier.*`) sehingga tetap dapat diaudit Admin.
- Tombol **Hapus** selalu meminta konfirmasi; gunakan **Nonaktifkan** bila data
  hanya ingin disembunyikan sementara.

Teknis: route Guru memakai nama `guru.*`, tetapi controller dan view yang sama
dipakai ulang melalui `routePrefix` (lihat 6).

---

## 3. Siswa

Masuk: sidebar **Lanjut Kuliah** (`config/navigation.php:268-286`).

### 3.1 Mengisi asesmen Minat Bakat Kuliah

Menu: **Minat Bakat Kuliah** (`siswa.minat-bakat.index`).

Prasyarat tampilnya soal (`app/Http/Controllers/Siswa/InstrumentSubmissionController.php:140-195`):
- soal ber-kategori `minat_bakat`, **aktif**;
- punya `interest_category_id` dan kategori minatnya **aktif**;
- `jenjang_target` = `semua` atau sesuai jenjang siswa.

Langkah:
1. Jika profil belum punya jenjang, halaman menampilkan pilihan
   **Mulai sebagai SMA** (rekomendasi prodi PCR) atau **Mulai sebagai SMK**
   (rekomendasi bidang karier + Job Zone). Pilihan ini juga bisa dipaksa lewat
   query `?jenjang=SMA&start=1`.
2. Jawab seluruh soal pada wizard memakai opsi jawaban yang disediakan
   (default guru: skala persetujuan Likert 1–5).
3. Kirim; sistem menyimpan submission, menghitung skor RIASEC, dan menentukan
   **Kode Minat** (Holland Code).
4. Anda diarahkan ke halaman hasil.

Bila belum ada soal aktif: **"Soal belum tersedia — Guru BK belum mengaktifkan
soal Minat Bakat RIASEC untuk jenjang ini."**

### 3.2 Membaca hasil asesmen

Halaman hasil (`siswa.instruments.hasil`):
- **Kode Minat (Holland Code)** dan rincian persentase tiap dimensi RIASEC.
- Jalur **SMA** → bagian **Program Studi di Politeknik Caltex Riau**, tiap prodi
  punya tautan **Konsultasikan prodi ini →**.
- Jalur **SMK** → bagian **Rekomendasi Karier** (bidang + Job Zone).
- Peringatan: bila semua skor 0%, kode minat hanya urutan default, bukan
  kecenderungan nyata.

Aksi yang tersedia:
- **Ulangi asesmen**
- **Unduh laporan** (PDF) — hanya untuk jalur kuliah (`! $isKerja`).
- **Konsultasi Prodi Kuliah** — membawa subjek `Diskusi hasil Minat Bakat <KODE>`.
- **Kembali ke dashboard**

Jika jenjang asesmen tidak dikenali, rekomendasi tidak ditampilkan dan muncul pesan
*"Jenjang asesmen tidak diketahui, sehingga rekomendasi tidak dapat ditampilkan."*

### 3.3 Mengajukan Konsultasi Prodi

Menu: **Konsultasi Prodi** (`siswa.konsultasi-prodi.index`).

Prasyarat:
- ada **Program Studi** dengan status aktif **dan** terverifikasi Admin;
- ada **Guru BK** dengan status akun disetujui.

Bila prodi terverifikasi belum ada, form menampilkan instruksi agar Admin
memverifikasi prodi di menu **Program Studi**. Bila tidak ada Guru BK yang
disetujui, pilihan konselor kosong.

Daftar Guru BK yang bisa dipilih (`ProdiConsultationController.php:89-112`):
1. Guru BK dari **sekolah siswa** (lewat kelas siswa) — diprioritaskan;
2. bila tidak ada, semua Guru BK berstatus **disetujui**.

Langkah:
1. Isi **Subjek** (wajib, maks. 120).
2. Pilih **Program studi PCR** (wajib; hanya prodi aktif + terverifikasi).
3. Pilih **Guru BK** (wajib).
4. **Preferensi tanggal** (opsional; tidak boleh sebelum hari ini).
5. **Preferensi waktu** (opsional, maks. 80; contoh "Senin pagi / 10:00").
6. Tulis **Pertanyaan / hal yang ingin dibahas** (wajib, maks. 2000).
7. Klik **Kirim pengajuan konsultasi prodi**.

Aturan validasi (`app/Http/Requests/StoreProdiConsultationRequest.php:16-29`):
- `subject` wajib, maks. 120.
- `program_studi_id` wajib dan harus prodi `is_active = true` **dan**
  `is_verified = true`.
- `counselor_id` wajib, harus user ber-role Guru BK dengan status disetujui.
- `preferred_date` opsional, format tanggal valid, `>= hari ini`.
- `preferred_time` opsional, maks. 80.
- `details` wajib, maks. 2000.

Yang tersimpan (`ProdiConsultationController.php:61-71`):
- `case_category = prodi_kuliah`, `status = pending`;
- `counselor_id` = Guru BK yang dipilih;
- bila preferensi waktu kosong: `preferred_time` diisi
  **"Sesuai tanggal pilihan"** (jika tanggal diisi) atau
  **"Fleksibel — menunggu jadwal Guru BK"**.

Pintasan dari halaman hasil:
- **Konsultasikan prodi ini →** pada kartu prodi membuka form dengan prodi sudah
  terpilih (`resources/views/siswa/instruments/hasil.blade.php:198-200`).
- Tombol **Konsultasi Prodi Kuliah** membuka form dengan subjek terisi
  `Diskusi hasil Minat Bakat <KODE>` (`hasil.blade.php:240`).

Riwayat pengajuan tampil di bagian **Riwayat konsultasi prodi** pada halaman yang
sama (`resources/views/siswa/konsultasi-prodi/index.blade.php:132-170`):
- hanya pengajuan milik Anda dengan kategori `prodi_kuliah`;
- 10 per halaman, terbaru di atas;
- bisa difilter **status**;
- status yang mungkin: **Menunggu**, **Disetujui**, **Ditolak**,
  **Dijadwalkan ulang**, **Selesai** (`ConsultationRequest::STATUS_LABELS`);
- jadwal resmi, catatan Guru BK, dan alasan penolakan tampil setelah Guru BK
  menindaklanjuti.

Siswa **tidak** bisa membatalkan atau mengubah jadwal dari halaman ini; hubungi
Guru BK agar pengajuan dijadwalkan ulang atau ditolak dengan alasan.

### 3.4 Chatbot Konseling

Menu: **Chatbot Konseling** (`siswa.chatbot.index`).

Langkah:
1. Buka menu, ketik pesan (maks. 2000 karakter), klik ikon **Kirim pesan**.
2. Balasan diambil dari service chatbot PCR/BK. Batas 30 permintaan per menit
   (`routes/web.php:226-228`).

Prasyarat sistem (Admin/operator):
- `CHATBOT_SERVICE_URL` terisi di `.env` Laravel
  (`config/services.php` → `konseling_chatbot`).
- Service berjalan, mis. `uvicorn app:app --host 127.0.0.1 --port 8001`.
- `CHATBOT_API_TOKEN` sama di service dan Laravel (wajib di VPS/cloud).
- Setelah mengubah knowledge: `python ingest_buku.py`, lalu restart service.

Jika URL kosong, chat membalas:
*"Service chatbot belum dikonfigurasi. Hubungi admin sistem (CHATBOT_SERVICE_URL)."*

---

## 4. Alur data singkat

```
Admin / Guru BK: Kategori Minat ─┬─> Guru: Soal minat_bakat (kode RIASEC, jenjang, bobot, opsi)
                                 ├─> Prodi PCR (kategori + relevansi, verified, active)
                                 └─> Bidang Karier (kategori + relevansi, active)

Siswa mengisi asesmen
   └─> InterestScoringService: skor tiap dimensi + Kode Minat
        └─> RecommendationService:
              SMA -> prodi PCR (top-3 minat, bobot 1.0/0.6/0.3)
              SMK -> bidang karier + Job Zone
        └─> Hasil + PDF + tautan Konsultasi Prodi (prodi & subjek terisi)

Siswa mengajukan konsultasi prodi (pending, pilih Guru BK)
   └─> Guru BK (yang dipilih / belum ditugaskan):
         Setujui -> disetujui -> Jadwal -> (dijadwalkan_ulang) -> Laporan -> selesai
         Tolak   -> ditolak (alasan wajib)
   └─> Admin: memantau daftar konseling (filter status/kategori/sekolah)
```

---

## 5. Troubleshooting

| Gejala | Penyebab | Perbaikan |
|---|---|---|
| Siswa: "Soal belum tersedia" | Tidak ada soal `minat_bakat` aktif untuk jenjang siswa | Guru aktifkan soal + `jenjang_target` yang sesuai |
| Guru tidak bisa simpan soal | Kode RIASEC / kategori minat kosong atau kategori nonaktif | Isi kode RIASEC; aktifkan kategori minat |
| Rekomendasi prodi kosong | Prodi belum `Terverifikasi`/`Aktif`, atau tidak ada kategori yang cocok | Verifikasi + aktifkan prodi, cek relevansi kategori |
| Rekomendasi karier kosong | Bidang karier nonaktif atau kategori tidak cocok | Aktifkan bidang karier, cek relevansi |
| Form konsultasi prodi kosong | Belum ada prodi terverifikasi atau Guru BK disetujui | Verifikasi prodi / approve Guru BK |
| Pengajuan prodi tidak muncul di daftar Guru BK | Siswa memilih Guru BK lain (`counselor_id` sudah terisi) | Buka sebagai Guru BK yang dipilih — pengajuan hanya terlihat olehnya |
| Tombol **Setujui** gagal (422) | Status bukan `pending` | Muat ulang; gunakan **Jadwal**/**Laporan** sesuai status |
| Tombol **Tolak** tidak ada | Status `dijadwalkan_ulang` | Jadwalkan ulang atau isi **Laporan**; penolakan hanya untuk `pending`/`disetujui` |
| Jadwal ditolak "bentrok" | Guru BK yang sama punya sesi di tanggal + jam identik | Pilih jam/tanggal lain |
| Tombol **PDF** tidak muncul | Laporan konseling belum diisi | Isi **Laporan** hingga status `selesai` |
| Guru BK tidak melihat menu master Key | Akun tidak berperan Guru BK, atau cache config/navigation lama | Pastikan role `guru`; jalankan `php artisan config:clear` |
| Guru BK kena 403 saat simpan master | Akun bukan Admin/Guru BK | Masuk dengan akun Guru BK yang disetujui |
| Chatbot error 500 | `CHATBOT_SERVICE_URL` kosong atau service mati | Isi `.env`, jalankan service di port 8001 |
| Chatbot menolak topik | Di luar BK/PCR | Arahkan ke topik layanan BK/PCR |

---

## 6. Referensi kode

- Route: `routes/web.php:112-118` (admin), `:138-158` (guru), `:226-240` (siswa)
- Menu sidebar: `config/navigation.php:55-71`, `:172-207`, `:286-307`
- Skoring & rekomendasi: `app/Services/Minat/InterestScoringService.php`,
  `app/Services/Minat/RecommendationService.php`, `app/Services/Minat/InterestResult.php`
- Asesmen siswa: `app/Http/Controllers/Siswa/InstrumentSubmissionController.php:140-195`,
  `:196-403`, `:404-450`, `:451+`
- Bank soal: `app/Http/Controllers/Guru/InstrumentQuestionController.php`,
  `app/Http/Requests/Guru/Concerns/ValidatesInstrumentQuestion.php`
- Hasil & matriks: `app/Http/Controllers/Guru/InstrumentResultController.php:18,68`
- Master Key: `app/Http/Controllers/Admin/{InterestCategory,ProgramStudi,CareerField}Controller.php`,
  `app/Http/Controllers/Guru/{InterestCategory,ProgramStudi,CareerField}Controller.php`,
  `app/Http/Requests/Admin/{Store,Update}{InterestCategory,ProgramStudi,CareerField}Request.php`,
  `resources/views/admin/{interest-categories,program-studi,bidang-karier}/index.blade.php`
  (memakai `$routePrefix` agar bisa dipakai route Admin maupun Guru)
- Konsultasi prodi (siswa): `app/Http/Controllers/Siswa/ProdiConsultationController.php`,
  `app/Http/Requests/StoreProdiConsultationRequest.php`,
  `resources/views/siswa/konsultasi-prodi/index.blade.php`
- Konsultasi (guru): `app/Http/Controllers/Guru/ConsultationController.php`,
  `app/Http/Requests/Guru/{ScheduleConsultationRequest,RejectConsultationRequest,StoreConsultationReportRequest}.php`,
  `app/Services/ConsultationScheduleService.php`,
  `app/Models/ConsultationRequest.php:153-178`,
  `resources/views/guru/consultations/{index,print}.blade.php`
- Pemantauan konsultasi (admin): `app/Http/Controllers/Admin/ConsultationController.php`,
  `resources/views/admin/consultations/index.blade.php`
- Chatbot: `app/Http/Controllers/Siswa/ChatbotController.php`,
  `chatbot_service/README.md`
