# Laporan Pengujian End-to-End (Playwright)

> Tanggal pelaksanaan : 18–20 Oktober 2026
> Branch              : `yola_new`
> Kerangka pengujian  : Playwright Test (Chromium, desktop 1440x1024)

---

## 1. Ringkasan Hasil

| Metrik | Nilai |
| --- | --- |
| Total skenario | **83** |
| Lulus | **83** |
| Gagal | **0** |
| Di-skip | **0** |
| Waktu eksekusi | 4,3–6 menit (berfluktuasi antar run) |
| Kode keluaran | `0` |

Rincian per modul:

| # | Berkas uji | Peran | Jumlah | Lulus |
| --- | --- | --- | ---: | ---: |
| 1 | `tests/e2e/01-auth.spec.mjs` | Auth & otorisasi (semua role) | 16 | 16 |
| 2 | `tests/e2e/02-admin-master-data.spec.mjs` | Admin — data master | 11 | 11 |
| 3 | `tests/e2e/03-admin-content.spec.mjs` | Admin — referensi & konten | 18 | 18 |
| 4 | `tests/e2e/04-guru-manajemen.spec.mjs` | Guru — data & layanan | 12 | 12 |
| 5 | `tests/e2e/05-guru-konseling.spec.mjs` | Guru — konseling, laporan & rapor | 6 | 6 |
| 6 | `tests/e2e/06-siswa.spec.mjs` | Siswa — layanan & instrumen | 16 | 16 |
| 7 | `tests/e2e/07-modal-viewport.spec.mjs` | Regresi tampilan modal | 4 | 4 |
| | | **Total** | **83** | **83** |

---

## 2. Cara Menjalankan

### 2.1 Prasyarat

```bash
composer install
npm install
npm run build          # WAJIB — lihat Temuan Bug #5
```

`npm run build` **wajib dijalankan sebelum suite pertama kali**. Berkas
`public/build` di-*ignore* oleh Git, sehingga manifest Vite tidak ikut
ter-clone. Tanpa build, `/guru/consultations` membalas **500**
(*"Unable to locate file in Vite manifest"*). Kini
`tests/e2e/global-setup.mjs` memeriksa `public/build/manifest.json` lebih
awal, sehingga kegagalan muncul sebagai pesan yang jelas (minta menjalankan
`npm run build`) alih-alih 500 di tengah skenario.

Tidak perlu menyiapkan database secara manual: `global-setup` otomatis
menjalankan `migrate:fresh` + seeder pada database khusus uji (lihat §3).

### 2.2 Perintah

```bash
npm run test:e2e                     # seluruh suite (83 skenario)
npm run test:e2e:ui                  # mode UI interaktif
npm run test:e2e:report              # buka laporan HTML

# Menjalankan per modul
npx playwright test 01-auth --reporter=list
npx playwright test 06-siswa --reporter=list
```

### 2.3 Akun demo yang dipakai

Semua akun dibuat oleh seeder, kata sandi `password`.

| Peran | Identitas |
| --- | --- |
| Admin (SMA) | `admin@bk.test` |
| Admin (SMK) | `admin.demo@bk.test` |
| Guru BK | `081234567890` — "Ibu Rina Guru BK" |
| Siswa SMA | NISN `0061234567`, tanggal lahir `2008-05-14` |
| Siswa SMK | NISN `0061234601`, tanggal lahir `2008-04-12` |

---

## 3. Desain Infrastruktur Uji

| Keputusan | Keterangan |
| --- | --- |
| Database terisolasi | Memakai SQLite file terpisah `storage/e2e/database.sqlite` lewat `tests/e2e/scripts/env.mjs`. **Database MySQL development (sistembk) tidak pernah tersentuh.** |
| `APP_ENV` | `production` + `APP_DEBUG=false` supaya pengujian menangkap perilaku produksi (error page, tanpa debug bar). |
| Store | `CACHE_STORE=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array`. |
| `workers: 1`, `fullyParallel: false` | Banyak skenario bergantung urutan & data yang saling menyiapkan (mis. uji siswa membuat prasyarat lewat peran guru). |
| Login | Selalu melalui **UI resmi** per peran: `/login?role=admin\|guru\|siswa`, bukan lewat sesi buatan. |
| Dialog konfirmasi | Di-*auto accept* hanya oleh handler global di `helpers.mjs` agar tidak bentrok dengan handler per uji. |

### 3.1 Berkas bantu

```
playwright.config.js
tests/e2e/
├── global-setup.mjs      # migrate:fresh + seeder database E2E
├── helpers.mjs           # loginAs, formByAction, answerAllLikert, dll.
├── scripts/
│   ├── env.mjs           # definisi environment & path SQLite E2E
│   ├── artisan.mjs       # menjalankan artisan dengan env E2E
│   └── serve.mjs         # menjalankan server Laravel untuk uji
├── 01-auth.spec.mjs              (16)
├── 02-admin-master-data.spec.mjs (11)
├── 03-admin-content.spec.mjs     (18)
├── 04-guru-manajemen.spec.mjs    (12)
├── 05-guru-konseling.spec.mjs    (6)
├── 06-siswa.spec.mjs             (16)
└── 07-modal-viewport.spec.mjs    (2)
```

Catatan penting yang dipakai berulang di seluruh uji:

- Semua `href`/`action` di DOM bersifat **absolut**, sehingga selektor selalu
  memakai `a[href*="..."]` / `action*="..."`.
- Saat beberapa modal berbagi `method` + `action`, `formByAction()` **wajib**
  diberi pembeda (`has`) agar tidak memilih form yang salah.
- `loginAs()` selalu `clearCookies()` lebih dulu, dan rate limiter dibersihkan
  per uji agar login beruntun tidak kena `429`.

---

## 4. Cakupan Skenario

### 4.1 Modul 01 — Autentikasi & Otorisasi (16)

- Landing page menampilkan pilihan login per role
- Login admin sukses dan redirect ke dashboard admin
- Login admin dengan password salah ditolak
- Login guru BK memakai No HP / NIP
- Guru yang belum disetujui tidak bisa login
- Login siswa memakai NISN dan tanggal lahir
- Login siswa gagal bila tanggal lahir tidak cocok
- Login siswa gagal bila NISN tidak terdaftar
- Akun dengan role lain ditolak saat memilih role tertentu
- Siswa tidak boleh membuka area admin (403)
- Guru tidak boleh membuka area admin (403)
- Halaman terproteksi redirect ke login bila belum masuk
- Menu sidebar tiap role menampilkan tautan sesuai hak akses
- Logout dari menu user mengakhiri sesi
- Registrasi guru BK baru masuk status menunggu persetujuan
- Admin dapat memperbarui profil sendiri

### 4.2 Modul 02 — Admin: Data Master (11)

- Sekolah: create, update, filter, delete
- Sekolah: validasi nama & NPSN wajib diisi
- Kelas: create, update, delete dengan relasi sekolah
- Kelas: menolak jenjang di luar daftar
- Siswa: create, update, filter, delete
- Siswa: menolak NISN ganda dan tanggal lahir masa depan
- Guru BK: create membuat akun login, lalu update dan delete
- Guru BK: menolak no HP yang sudah dipakai
- Akun pengguna: create, update role/status, delete
- Akun pengguna: menolak email ganda
- Kelas Bimbingan: create, tambahkan siswa, lepas siswa, delete

### 4.3 Modul 03 — Admin: Referensi & Konten (18)

- Kategori Minat RIASEC: create, update, delete
- Kategori Minat: kode dinormalkan otomatis menjadi huruf kapital
- Program Studi PCR: create wajib pilih kategori minat, update, delete
- Program Studi: menolak submit tanpa kategori minat
- Bidang Karier: create wajib pilih kategori, update, delete
- Kategori Postingan: create, update, delete
- Postingan: create publik, update jadi draft, delete
- Informasi Karier: create, update, delete
- Master Pertanyaan: create skala, update, delete
- Master Pertanyaan: pilihan ganda minimal 2 opsi
- Soal Sosiometri: create, update, delete
- Approval Guru BK: daftarkan guru baru lalu setujui
- Approval: filter status pending
- Konseling: daftar pengajuan siswa tampil untuk admin
- Rapor BK: halaman indeks dan detail dapat dibuka
- Laporan Konseling Individu: admin hanya bisa melihat & mencetak
- Perubahan Profil Guru: tampil (read-only)
- Log Aktivitas: mencatat aksi CRUD admin

### 4.4 Modul 04 — Guru: Manajemen Data & Layanan (12)

- Data Siswa: create, update, filter, delete
- Data Siswa: import CSV menambahkan data baru **dan menolak baris tanpa `kelas_id`** (pesan per baris)
- Data Siswa: menolak NISN ganda
- Master Pertanyaan: buat soal tryout untuk dipakai modul tryout
- Soal Instrumen (Yola): create minat kerja dengan pilihan jawaban, update, delete
- RPL Individu: create, update, delete
- Jurnal Bulanan: create, update, delete
- Laporan Kelompok: create dari RPL kelompok yang sudah ada, lalu delete
- Tryout: create, edit, publish/unpublish, dan isi nilai
- Soal Sosiometri guru: create dan delete
- Kelola instrumen sosiometri: aktifkan / nonaktifkan per kelas
- Halaman baca guru: penilaian, angket, feedback, riwayat, statistik, hasil instrumen

### 4.5 Modul 05 — Guru: Konseling, Laporan & Rapor (6)

- **Alur penuh**: siswa mengajukan → guru menyetujui → menjadwalkan → menyelesaikan
- Guru dapat menolak pengajuan dengan alasan
- Penjadwalan menolak jadwal yang bentrok
- Laporan konseling individu: dibuat, diperbarui, dan dihapus
- Rapor BK: buat/isi rapor siswa dan simpan
- Filter konseling guru berdasarkan status

### 4.6 Modul 06 — Siswa: Layanan & Instrumen (16)

- Dashboard siswa menampilkan ringkasan layanan
- Instrumen Yola: isi kategori Kepribadian sampai selesai
- Hasil instrumen Strategi Belajar & Masalah terbuka tanpa error *(baru)*
- URL `/siswa/instruments` tanpa kategori mengikuti jenjang siswa, bukan 404 *(baru)*
- Minat Bakat Kuliah (Key) menampilkan wizard asesmen
- Sosiometri: pilih teman untuk setiap pertanyaan aktif
- Konseling: ajukan pengajuan baru ke Guru BK
- Konsultasi Prodi: ajukan diskusi pilihan program studi PCR
- Penilaian layanan: beri skor untuk konseling yang sudah selesai
- Angket BK: isi seluruh jawaban lalu simpan
- Artikel BK: telusuri daftar dan baca detail
- Informasi karier tampil untuk siswa
- Join kelas bimbingan memakai kode yang valid
- Chatbot BK menjawab pertanyaan siswa
- Tryout siswa: kerjakan tryout aktif lalu kumpulkan jawaban
- Siswa dibatasi mengakses modul admin dan guru

### 4.7 Modul 07 — Regresi Tampilan Modal (4) *(baru)*

- Tambah Siswa (admin): tombol simpan terjangkau lewat scroll konten
- Tambah Siswa (guru): tombol simpan terjangkau lewat scroll konten
- Tambah Bidang Karier (admin): modal yang dulu ber-`90vh` tetap terjangkau
- Tambah Siswa (admin): tampilan 1440x1024 tidak kembali ber-scrollbar

Dua skenario pertama mengunci perbaikan #C di §6: konten modal panjang punya
area scroll sendiri, sehingga tombol Simpan tidak terpotong di luar layar
pada viewport rendah (1280x600).

Dua skenario terakhir mengunci perbaikan #G di §6. Skenario terakhir
memeriksa bahwa modal "Tambah Siswa" yang tingginya ~947px tidak mendapat
scrollbar pada viewport default 1440x1024. Nilai batas `90vh` (922px) akan
membuatnya menggulir 25px tanpa perlu — persis gejala yang melahirkan
perbaikan ini — sehingga uji ini gagal bila konvensi dikembalikan.

---

## 5. Temuan Bug Aplikasi & Perbaikan

Tujuh bug ditemukan selama pengujian dan **sudah diperbaiki langsung** pada
kode aplikasi.

### Bug #1 — Route `kelas` memakai parameter rusak `{kela}`

- **Berkas:** `routes/web.php`
- **Gejala:** seluruh aksi CRUD kelas (update/hapus) meleset; route
  `Route::resource('kelas', ...)` menghasilkan bound parameter `{kela}`
  (sepotong kata, bukan `{kelas}`).
- **Perbaikan:** `Route::resource('kelas', ...)->parameters(['kelas' => 'kelas'])`.

### Bug #2 — `StoreStudentRequest` tanpa import `Rule` → error 500

- **Berkas:** `app/Http/Requests/Admin/StoreStudentRequest.php`
- **Gejala:** simpan siswa dari admin membalas **500** karena
  `Illuminate\Validation\Rule` dipakai tanpa `use`.
- **Perbaikan:** menambahkan `use Illuminate\Validation\Rule;`.

### Bug #3 — Modal detail Laporan Individu terkunci untuk admin

- **Berkas:** `resources/views/admin/individual-reports/index.blade.php`
- **Gejala:** tombol "detail" tidak membuka apa pun bagi admin karena modal
  berada di dalam guard `@if($isGuru)`; modal edit ikut terkunci.
- **Perbaikan:** modal detail dikeluarkan dari guard, modal edit dibungkus
  `@if($isGuru)`.

### Bug #4 — View tryout 500 akibat `@php` inline di dalam blok Blade

- **Berkas:** `resources/views/guru/tryout/show.blade.php`
- **Gejala:** `/guru/tryout/{id}` membalas **500**
  (`foreach` tanpa `foreach` / `endforeach` tanpa pasangan).
- **Akar masalah:** kompilator Blade memakai regex lazy
  `/(?<!@)@php(.*?)@endphp/s`. Bentuk inline `@php($pending = ...)` pada satu
  baris "melahap" seluruh isi hingga `@endphp` blok yang jauh di bawahnya, sehingga
  `@if`/`@foreach` di antaranya lolos mentah ke `view()`.
- **Perbaiki:** `@php($pending = ...)` diubah ke bentuk blok `@php ... @endphp`.
- **Peringatan:** menulis literal `@php(...)` di dalam komentar Blade justru
  akan memicu kerusakan serupa — jangan dilakukan.

### Bug #5 — Manifest Vite basi → 500 `/guru/consultations`

- **Berkas:** `vite.config.js` / `public/build/manifest.json`
- **Gejala:** halaman konsultasi guru 500
  *"Unable to locate file in Vite manifest"*.
- **Akar masalah:** `vite.config.js` mendaftarkan 3 entry, tetapi manifest yang
  ter-generate (belum lama) hanya berisi 2 — `resources/js/calendar.js`
  tidak ada. Satu-satunya konsumen `calendar.js` adalah
  `resources/views/guru/consultations/index.blade.php`.
- **Perbaikan:** `npm run build`. Karena `/public/build` di-*ignore* Git,
  langkah ini dijadikan **prasyarat dokumentasi** (lihat §2.1).

### Bug #6 — Flash sukses Rapor BK tidak pernah tampil

- **Berkas:** `resources/views/guru/rapor/index.blade.php`
- **Gejala:** controller redirect dengan `->with('success', ...)`, tetapi view
  tidak pernah me-render flash — pengguna menyimpan rapor tanpa umpan balik.
- **Perbaikan:** menambah `<x-alert type="success" :message="session('success')" />`
  pada awal konten.

### Bug #7 — Deteksi bentrok jadwal konseling tidak bekerja di luar MySQL

- **Berkas:** `app/Services/ConsultationScheduleService.php` (`hasConflict()`)
- **Gejala:** jadwal bentrok tetap tersimpan (terbukti pada data uji: dua
  pengajuan `disetujui` pada `2026-11-20 09:00`).
- **Akar masalah:** input divalidasi dengan format `H:i` (`09:00`) lalu
  dibandingkan ke `normalizeTime()` yang menghasilkan `09:00:00`. MySQL
  menormalkan kolom `time`, sedangkan SQLite menyimpan `09:00` apa adanya,
  sehingga tidak pernah cocok dan deteksi bentrok lolos.
- **Perbaiki:** `whereIn('consultation_time', [$time, normalizeTime($time)])`
  — membandingkan kedua format sehingga benar di MySQL maupun SQLite.

### Perbaikan sisi uji (bukan bug aplikasi)

| No | Temuan | Tindakan |
| --- | --- | --- |
| 1 | Seeder tidak membuat kategori postingan padahal uji membutuhkannya. | Uji membuat kategori lewat UI lebih dulu. |
| 2 | `page.getByRole('link').first()` selalu cocok dengan link sidebar → `count()` tak pernah 0, blok `test.skip` tidak pernah aktif dan link navigasi yang diklik. | Selektor dikecualikan ke `a[href*="/siswa/tryout/"]` dalam kartu tryout bersangkutan. |
| 3 | `locator.count()` tidak menunggu; tepat setelah klik toggle `x-show` Alpine, form masih tersembunyi → opsi dihitung 0 → uji salah me-skip ("Belum ada prodi PCR"). | `await expect(form).toBeVisible()` sebelum menghitung opsi. |
| 4 | Label tombol submit kategori Kepribadian diasumsikan "Kirim dan Lihat Skor", padahal kategori itu dirender sebagai form MBTI → **"Kirim dan Lihat Tipe Kepribadian"**. | Selektor menjadi `/Kirim dan Lihat/`. |
| 5 | Uji instrumen membuka `/siswa/instruments` tanpa parameter. | Dibuka dengan `?category=kepribadian` — URL telanjang kini tidak lagi 404, lihat perbaikan #B di §6. |

---

## 6. Perbaikan Lanjutan — Sesi 19–20 Oktober 2026

Lima temuan laporan ditutup tanpa merusak skenario yang sudah lulus, ditambah
dua perbaikan yang muncul dari pemeriksaan akhir (#F dan #G).

### #A — `@php` inline pada file Blade panjang berisiko merusak kompilasi

- **Berkas:** `siswa/instruments/{index,hasil-simple,masalah-result,strategi-belajar-result}`, `guru/instruments/results/matrix` (6 titik)
- **Perbaikan:** seluruh `@php(...)` inline diubah ke blok `@php ... @endphp`.
  Kompilator Blade memakai regex lazy (lihat Bug #4), jadi bentuk inline pada
  file panjang sangat rapuh. Diverifikasi: 0 file lagi mencampur kedua bentuk.
- **Uji baru:** Modul 06 — mengisi & menyimpan instrumen **Strategi Belajar**
  dan **Masalah** sampai tampil hasilnya (sebelumnya tidak pernah ter-render).

### #B — `/siswa/instruments` tanpa `category` membalas 404

- **Berkas:** `app/Http/Controllers/Siswa/InstrumentSubmissionController.php` (`index()`)
- **Akar masalah:** default kategori adalah `minat_kerja`, yang memang SMK-only
  → siswa SMA yang membuka URL polos selalu kena 404 (lama).
- **Perbaikan:** kategori kosong kini mengikuti jenjang siswa — SMK →
  `minat_kerja`, selain itu → `kepribadian`. 404 untuk **kategori eksplisit**
  yang tidak berlaku tetap dipertahankan (mis. `?category=minat_kerja` bagi
  siswa SMA), jadi jaminan aksesnya tidak berubah.
- **Uji baru:** Modul 06 — siswa SMA membuka `/siswa/instruments` tanpa
  parameter dan halaman Kepribadian tampil (bukan 404).

### #C — Konten modal panjang tidak punya area scroll

- **Berkas:** 10 file (lihat §10): `admin/{guidance-classes,guru-bk,kategori-postingan,kelas,sekolah,students,users}`, `guru/{consultations,students}`, `sociometry-questions`
- **Akar masalah:** konten modal hanya memakai `w-full max-w-…` sehingga
  tingginya tak terbatas; pada layar pendek tombol Simpan berada di luar
  viewport modal `fixed` dan tidak bisa dijangkau.
- **Perbaikan:** seluruh konten modal (bukan overlay) mendapat
  `max-h-[calc(100vh-2rem)] overflow-y-auto`. Nilai `calc` dipilih alih-alih
  `90vh` (sesuai usulan awal) karena overlay memakai padding `p-4` (2rem);
  dengan `calc`, modal selalu muat tepat dalam viewport dan **tidak
  memunculkan scrollbar baru** pada 1440x1024 — syarat "tampilan tidak
  berubah" terpenuhi. Kelas baru ini wajib lewat `npm run build` karena
  Tailwind JIT membacanya dari berkas sumber.
- **Uji baru:** Modul 07 (regresi, viewport 1280x600).
- **Verifikasi visual:** 8 modal dibuka pada 1280x600, 390x700, dan 1440x1024;
  pada 1280x600 tingkat keluwihan konten 0–407px (scroll aktif untuk yang
  panjang), sedangkan pada 1440x1024 semuanya 0px → tampilan tidak berubah.
- **Modal pendek tidak ikut diubah:** 4 dialog konfirmasi singkat
  (`layouts/app`, `siswa/angket/show`, `welcome`, `auth/verify-email`) dan
  `components/modal.blade.php` sudah punya `overflow-y-auto` sendiri.
- **Catatan:** `admin/sekolah` membuat data lewat form inline (bukan modal);
  hanya modal edit yang disesuaikan.

### #D — `kelas_id` tidak wajib pada form siswa guru & baris import CSV

- **Berkas:** `app/Http/Controllers/Guru/StudentController.php`,
  `resources/views/guru/students/partials/form.blade.php`,
  `resources/views/guru/students/index.blade.php`
- **Akar masalah:** validasi `kelas_id` hanya `required` bila guru punya
  `sekolah_id`, dan select-nya tidak punya atribut `required` sehingga bisa
  dikirim kosong lewat UI. Impor CSV mengubah `kelas_id` kosong menjadi
  `null` lalu menyimpan siswa **tanpa kelas** — padahal siswa tanpa kelas
  tidak pernah muncul di daftar/guru BK (konseling, angket, rapor).
- **Perbaikan:**
  - form tambah/edit guru: `kelas_id` selalu `required` (server) + atribut
    `required` (browser);
  - impor CSV: baris tanpa `kelas_id` valid **ditolak**, bukan disimpan tanpa
    kelas, dan dilaporkan **per baris** ("Baris N: kelas_id wajib diisi." /
    "… bukan kelas sekolah Anda.") lewat `session('import_errors')`;
  - modal import otomatis terbuka kembali bila ada baris yang dilewati.
  - **Data lama tidak dihapus** — siswa yang sudah tersimpan tetap ada; hanya
    baris impor berikutnya yang wajib berkelas.
- **Uji diperbarui:** Modul 04 — CSV kini berisi 2 baris: satu sah (tersimpan)
  dan satu tanpa `kelas_id` (ditolak, pesan "Baris 3: kelas_id wajib diisi.",
  dan dipastikan tidak ikut tersimpan).

### #E — Dokumentasi E2E dan pemeriksaan manifest

- **Berkas:** `README.md`, `tests/e2e/global-setup.mjs`, `playwright.config.js`
- **Perbaikan:** README mendapat bagian **"Menjalankan Pengujian E2E"**
  (composer install → npm install → `npm run build` → `npm run test:e2e`)
  beserta alasan `npm run build` wajib. `global-setup` memeriksa
  `public/build/manifest.json` lebih awal agar kegagalan Vite manifest
  terlihat jelas sejak awal, bukan sebagai 500 di tengah skenario. Komentar
  viewport pada `playwright.config.js` diperbarui karena alasan lamanya kini
  sudah tidak berlaku (modal sudah punya scroll sendiri).

### #F — Flash sukses dibisukan di halaman hasil Masalah & Strategi Belajar

- **Berkas:** `siswa/instruments/{masalah-result,strategi-belajar-result}.blade.php`
- **Akar masalah:** `InstrumentSubmissionController` mengirim flash
  `success` lewat **semua** jalur redirect (baris 326–345), tetapi hanya
  `hasil-simple` yang me-render `session('success')`. Dua halaman hasil
  lainnya membuangnya diam-diam, sehingga siswa tidak bisa membedakan
  "jawaban sudah tersimpan" dan sekadar "hasil dirender".
- **Perbaikan:** `<x-alert type="success" :message="session('success')" />`
  ditambahkan di kedua halaman, mengikuti penempatan yang sama seperti
  `hasil-simple` (di dalam pembungkus `space-y-6`, sebelum hero). Karena
  berbasis `session()`, banner otomatis hilang saat halaman di-refresh —
  tidak ada banner usang.
- **Uji diperbarui:** Modul 06 — setiap halaman hasil kini juga meng-assert
  banner "Jawaban instrumen berhasil dikirim dan diskor otomatis."

### #G — Konvensi batas tinggi modal terbelah jadi dua nilai

- **Berkas:** 13 file `index.blade.php` (admin & guru) — 26 kontainer modal
- **Akar masalah:** perbaikan #C di atas hanya menyentuh modal yang sama
  sekali belum punya `max-h`, sehingga 26 kontainer modal lain yang sudah
  memakai `max-h-[90vh]` tertinggal. Akibatnya kode mengenal dua konvensi
  berbeda untuk komponen yang sama secara visual.
- **Mengapa `90vh` yang diseragamkan, bukan sebaliknya:** overlay tiap modal
  memakai `p-4` (1rem tiap sisi = 2rem total), jadi ruang yang benar-benar
  tersedia adalah `100vh - 2rem` (992px pada layar 1024px). Nilai `90vh`
  memberi 922px — selalu lebih ketat dari perlu pada layar mana pun, dan
  menimbulkan scrollbar 25px yang tidak perlu pada modal yang muat di layar.
- **Ruang lingkup:** 26 baris diganti, 0 sisa. Pemeriksaan ulang seluruh
  `resources/views` menemukan 46 kontainer modal dengan `calc` dan **0**
  dengan `90vh`. Seluruh overlay terverifikasi `p-4`, jadi satu nilai ini
  berlaku untuk semuanya; dropdown di dalam modal tidak memakai `90vh`
  sehingga tidak ada yang tertukar.
- **Uji diperbarui:** Modul 07 bertambah dua skenario, satu di antaranya
  memverifikasi langsung bahwa modal "Tambah Siswa" tidak ber-scrollbar
  pada 1440x1024.

---

## 7. Temuan Perilaku Aplikasi (Diterima, Tidak Diubah)

### URL tryout & sosiometri bergantung prasyarat yang harus disiapkan peran guru

- `/siswa/sociometry` membalas **403** (*"Instrumen sosiometri untuk kelas
  Anda sedang dinonaktifkan"*) sampai guru menaktifkan instrumen per kelas di
  `/guru/sociometry/manage`.
- `/siswa/tryout` hanya menampilkan tryout berstatus `aktif`, rentang
  `mulai_at`–`selesai_at` mencakup waktu sekarang, **dan** mencakup `kelas_id`
  siswa.

Perilaku ini benar; keduanya kini menjadi prasyarat lintas peran yang
disiapkan di dalam uji sendiri, sehingga alurnya benar-benar dieksekusi
(bukan sekadar di-skip).

### URL instrumen tanpa `category` membalas 404 untuk siswa SMA *(sudah diperbaiki — lihat §6 #B)*

`InstrumentSubmissionController::index` men-default kategori menjadi
`minat_kerja`, sedangkan `minatKerjaIndex()` sengaja memanggil
`abort_if($profileJenjang === 'SMA', 404)` — kategori itu memang SMK-only.

Perilaku 404-nya sendiri **konsisten** dengan desain: kartu "Minat Bakat
Kerja" pada dashboard siswa pun hanya dirender untuk jenjang SMK, dan
seluruh tautan internal selalu menyertakan parameter `category`.

**Catatan UX (kini sudah ditutup):** default bawaan route tersebut adalah
kategori SMK-only, sehingga siswa SMA yang membuka URL polos mendapat 404.
Pada sesi 19 Oktober 2026 defaultnya dialihkan mengikuti jenjang siswa
(SMA → `kepribadian`) — lihat perbaikan #B di §6. Jaminan 404 untuk
kategori **eksplisit** yang tidak berlaku tetap dipertahankan.

---

## 8. Temuan Minor / Risiko Berikutnya

| # | Temuan | Keterangan |
| --- | --- | --- |
| 1 | `StoreInterestCategoryRequest` memakai `strtoupper` | Kode kategori dinormalkan otomatis (diuji pada Modul 03: *"kode dinormalkan otomatis menjadi huruf kapital"*). |
| 2 | Guru tanpa `sekolah_id` kini wajib memilih kelas | Dampak samping perbaikan #D di §6. Semua akun guru pada seeder punya sekolah sehingga tidak berdampak pada data uji, tetapi guru produksi tanpa profil sekolah kini dipaksa memilih satu kelas yang tersedia. |
| 3 | Browser uji baru Chromium | Belum ada Firefox/WebKit (lihat §9). |

Temuan minor sebelumnya yang **sudah ditutup**:

- *Pola `@php` inline + blok di 5 file Blade* → perbaikan #A di §6.
- *Import CSV menghasilkan siswa tanpa `kelas_id`* → perbaikan #D di §6.
- *Modal UI tanpa scroll pada viewport pendek* → perbaikan #C di §6.
- *Flash sukses hilang di halaman hasil Masalah & Strategi Belajar* → perbaikan #F di §6.

---

## 9. Batasan Pengujian

- Browser yang diuji baru **Chromium**; cross-browser (Firefox/WebKit) belum
  dijalankan.
- Pengujian hanya menyentuh aplikasi pada skema SQLite uji. Validasi khusus
  MySQL (mis. tipe kolom `time`) tidak tercakup kecuali melalui pembenahan
  Bug #7.
- Endpoint eksternal (chatbot) diuji memakai stub lokal
  (`CHATBOT_SERVICE_URL` mengarah ke server uji sendiri).
- Pembuatan data lintas peran dibatasi pada kebutuhan skenario, bukan
  pemetaan seluruh kombinasi role × resource.

---

## 10. Berkas Berubah

**Perbaikan aplikasi — Sesi 18 Oktober 2026**

```
routes/web.php                                            # Bug #1 — resource kelas memakai {kela}
app/Http/Requests/Admin/StoreStudentRequest.php           # Bug #2 — use Illuminate\Validation\Rule
resources/views/admin/individual-reports/index.blade.php # Bug #3 — modal detail keluar dari guard
resources/views/guru/tryout/show.blade.php                # Bug #4 — @php inline → blok
resources/views/guru/rapor/index.blade.php                # Bug #6 — flash success dirender
app/Services/ConsultationScheduleService.php              # Bug #7 — deteksi bentrok lintas DB
```

**Perbaikan lanjutan — Sesi 19–20 Oktober 2026**

```
app/Http/Controllers/Siswa/InstrumentSubmissionController.php   # #B default kategori mengikuti jenjang
app/Http/Controllers/Guru/StudentController.php                 # #D kelas_id wajib + pesan per baris
resources/views/guru/students/partials/form.blade.php          # #D required pada select kelas
resources/views/guru/students/index.blade.php                  # #C modal scroll, #D daftar baris dilewati
resources/views/siswa/instruments/index.blade.php              # #A @php → blok
resources/views/siswa/instruments/hasil-simple.blade.php       # #A
resources/views/siswa/instruments/masalah-result.blade.php     # #A, #F flash sukses
resources/views/siswa/instruments/strategi-belajar-result.blade.php   # #A, #F
resources/views/guru/instruments/results/matrix.blade.php      # #A
resources/views/admin/guidance-classes/index.blade.php         # #C modal scroll
resources/views/admin/guru-bk/index.blade.php                  # #C
resources/views/admin/kategori-postingan/index.blade.php       # #C
resources/views/admin/kelas/index.blade.php                    # #C
resources/views/admin/sekolah/index.blade.php                  # #C (modal edit saja)
resources/views/admin/students/index.blade.php                 # #C
resources/views/admin/users/index.blade.php                    # #C
resources/views/guru/consultations/index.blade.php             # #C
resources/views/sociometry-questions/index.blade.php           # #C
README.md                                                      # #E bagian "Menjalankan E2E"
docs/LAPORAN-E2E.md                                            # laporan ini
```

**Penyeragaman konvensi modal — Sesi 20 Oktober 2026**

```
resources/views/admin/bidang-karier/index.blade.php        # #G max-h [90vh] → [calc(100vh-2rem)]
resources/views/admin/careers/index.blade.php              # #G
resources/views/admin/individual-reports/index.blade.php   # #G (3 modal)
resources/views/admin/interest-categories/index.blade.php  # #G
resources/views/admin/master-pertanyaan/index.blade.php    # #G
resources/views/admin/postingan/index.blade.php            # #G
resources/views/admin/program-studi/index.blade.php        # #G
resources/views/guru/consultations/index.blade.php         # #G (2 modal)
resources/views/guru/group-reports/index.blade.php         # #G
resources/views/guru/instruments/questions/index.blade.php # #G
resources/views/guru/journals/index.blade.php              # #G
resources/views/guru/rpls/index.blade.php                  # #G
resources/views/guru/tryout/show.blade.php                 # #G
```

**Infrastruktur uji (baru)**

```
playwright.config.js
tests/e2e/…           # 7 berkas uji + helpers + global-setup + scripts
.gitignore            # ignore /storage/e2e, /test-results, /playwright-report
package.json          # skrip test:e2e, test:e2e:ui, test:e2e:report
```

---

## 11. Kesimpulan

Seluruh **83 skenario lulus tanpa kegagalan dan tanpa skip**, mencakup
alur CRUD pada modul autentikasi/otorisasi, data master dan konten admin,
manajemen data serta layanan guru, konseling/laporan/rapor guru, seluruh
layanan siswa, serta regresi tampilan modal pada viewport rendah.

Dua belas perbaikan kode diterapkan sepanjang dua sesi — empat di antaranya
menutup kasus **HTTP 500** (route parameter rusak, import `Rule` yang hilang,
`@php` inline yang merusak kompilasi Blade, dan manifest Vite basi), ditambah
satu bypass validasi bentrok jadwal, kegagalan tombol Simpan pada modal di
layar pendek, penyeragaman batas tinggi seluruh 46 kontainer modal, siswa
impor tanpa kelas yang tak pernah muncul bagi guru BK, default kategori
instrumen yang mengunci siswa SMA pada 404, dan konfirmasi "jawaban
tersimpan" yang dibisukan di dua halaman hasil. Enam temuan laporan sesi
pertama kini sudah ditutup (lihat §6).

Seluruh perbaikan terbukti lulus oleh suite. **Satu perubahan perilaku
berdampak produk** — `kelas_id` kini wajib pada form siswa guru dan baris
impor CSV, sehingga baris tanpa kelas yang dulu ikut tersimpan kini ditolak
dengan pesan per baris — ditandai pada §8 dan menunggu konfirmasi Anda.
