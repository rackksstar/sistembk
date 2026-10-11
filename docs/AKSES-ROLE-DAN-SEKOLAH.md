# Akses, Role, dan Model Sekolah (SMA vs SMK)

Dokumen ini menjelaskan tiap role akun, cara login, relasi guru ke sekolah, sistem
admin, dan apakah guru SMA serta SMK memakai sistem yang sama. Disusun dari pembacaan
langsung `database/seeders/DatabaseSeeder.php`, `database/seeders/MultiSchoolTestingSeeder.php`,
`app/Models/*`, `routes/web.php`, dan controller terkait.

> Referensi yang **terbaru dan dipakai aplikasi**:
> `database/seeders/DatabaseSeeder.php` (bukan `sistembk-main/...` yang merupakan
> salinan lama: cuma 1 sekolah SMA, belum ada pemisahan SMK dan sub-seedernya).

---

## 1. Tiga Role Akun

| Role | Konstanta | Login dengan | Password | Status wajib |
|---|---|---|---|---|
| Admin | `admin` | email | password | `disetujui` |
| Guru BK | `guru` | No. HP / NIP | password | `disetujui` |
| Siswa | `siswa` | NISN | tidak dipakai | `disetujui` |

Detail login (`app/Http/Controllers/Auth/AuthenticatedSessionController.php` +
`app/Http/Requests/Auth/LoginRequest.php`):

- **Admin** — login lewat email + password. Role dipilih di halaman login.
- **Guru BK** — login lewat `login_id` (username/No. HP, atau NIP yang dicocokkan
  lewat relasi `guru_bks.no_hp` / `guru_bks.nip`). Angka dibersihkan `/\D+/`
  jadi pemisah `-`, spasi, dst. diabaikan.
- **Siswa** — TANPA password. Login pakai NISN + tanggal lahir (`birth_date`).
  Wajib ada profil `students` yang sudah terhubung `user_id`, dan akun berstatus
  `disetujui`.

Gate status: akun `pending` atau `ditolak` ditolak saat login. Admin & guru yang
login harus sudah disetujui (`App\Models\User::isApproved()`).

`User::ROLES = ['admin', 'guru', 'siswa']`; `User::STATUSES = ['pending', 'disetujui', 'ditolak']`.

### Skema relasi akun

- `users.role` → role global satu kolom.
- `users.school` (string) + `users.school_id` → sekolah "core" (tabel `schools`).
- `users.class_id` → kelas core (tabel `classes` / `SchoolClass`), dipakai modul
  RPL & laporan.
- Guru: `users` 1:1 `guru_bks` (`guru_bks.sekolah_id` → `sekolahs`). Ini kunci
  cakupan sekolah guru.
- Siswa: `users` 1:1 `students` (NISN, `kelas_id` → `kelas` → `sekolahs`).
- Siswa SMK (opsional): `students` 1:1 `siswa_smk` (profil pencari kerja:
  jurusan, keahlian, pengalaman, status_kerja).

---

## 2. Dua Struktur "Sekolah" Paralel

Sistem punya DUA pasang tabel sekolah/kelas/siswa (warisan modul lama + port referensi):

| Sistem | Tabel sekolah | Tabel kelas | Tabel siswa | Dipakai oleh |
|---|---|---|---|---|
| Core ("SMA") | `schools` (`School`) | `classes` (`SchoolClass`) | `users.class_id` + `students` | RPL, laporan konseling, jurnal, peta sosiometri |
| Master/SMK (port referensi) | `sekolahs` (`Sekolah`) | `kelas` (`Kelas`, punya `jenjang` SD/SMP/SMA/SMK) | `students.kelas_id` | Data Siswa, angket, rapor, tryout, dashboard guru |

Satu sekolah asli dibuat `updateOrCreate` di dua tabel sekaligus (contoh seeder:
`SMA Negeri 1 Contoh` ada di `schools` DAN `sekolahs`, dengan NPSN sama).
Guru terhubung ke sekolah via **dua sisi**: `users.school_id` (core) dan
`guru_bks.sekolah_id` (master). Scoping yang benar-benar dipakai kode adalah
`guru_bks.sekolah_id`.

---

## 3. Guru: Satu Akun, Satu Sekolah? Mampu Akses SMK & SMA?

**Secara data: satu guru = satu sekolah (MOU).** `GuruBk` adalah relasi
`belongsTo(Sekolah)` — tidak ada many-to-many. Admin membuat/merubah Guru BK lewat
form yang hanya memilih satu `sekolah_id` (`Admin\GuruBkController`). Pendaftaran
mandiri guru (`Auth\GuruRegistrationController`) juga hanya pilih 1 `sekolah_id`.

**Tapi cakupan data TIDAK konsisten antar modul:**

- **Dibatasi sekolah guru** (pakai `guru_bks.sekolah_id`):
  - Data Siswa (`Guru\StudentController`, `CounselorStudentService`) — hanya kelas
    & siswa di sekolahnya (plus siswa hasil konseling/rapor guru).
  - Dashboard agregat angket (`Guru\DashboardController`).
  - Tryout (`Guru\TryoutController`) — daftar kelas difilter ke sekolah guru.
  - Sisi siswa, konsultasi prodi memilih Guru BK yang `sekolah_id`-nya sama
    dengan sekolah siswa (`Siswa\ProdiConsultationController`).
- **TIDAK dibatasi sekolah** (hanya by `teacher_id` / `counselor_id`):
  - RPL (`Guru\RplController`) — list kelas (`classes`) dan daftar siswa (`users`)
    diambil **GLOBAL**, tanpa filter sekolah guru. Form RPL bisa menampilkan kelas
    & siswa dari semua sekolah.
  - Jurnal bulanan (`Guru\MonthlyJournalController`) — by `teacher_id`.
  - Konseling (`Guru\ConsultationController`) — by `counselor_id =
    auth()->id()` atau `counselor_id = null`. Tidak ada filter sekolah, jadi dua
    guru sekolah berbeda bisa "melihat" permintaan tanpa konselor secara global.

Jadi jawaban singkat: **secara kelembagaan guru terikat 1 sekolah, tapi karena
filter sekolah tidak dipakai di semua modul, di layar tertentu (terutama RPL)
guru bisa melihat/memilih siswa lintas SMA dan SMK.** Ini bukan fitur, melainkan
lubang cakupan data yang perlu diperbaiki (lihat dokumen PANDUAN-PENGGUNAAN.md,
bagian "Known Issues").

---

## 4. Sistem Admin

- Admin **global, tidak dibatasi sekolah**. Sidebar & route prefix `/admin`
  (`role:admin`) mencakup: dashboard (jumlah akun & pemakaian per sekolah),
  konseling, rapor, sekolah (`sekolahs`), kelas (`kelas`), Guru BK (`guru_bks`),
  approval pendaftaran guru, manajemen akun, data siswa, kelas bimbingan, master
  pertanyaan, sosiometri, kategori/artikel, log aktivitas.
- Admin boleh approve/reject guru pending (`Admin\AdminApprovalController`).
- `users.school_id` pada akun admin ada (menunjuk ke 1 sekolah "core") tapi
  **tidak dipakai untuk scoping** — semua perhitungan dashboard admin memakai
  seluruh record global (`User::count()`, `Sekolah::count()`, dst.).
- Ada 2 akun admin seeder: satu "SMA", satu "SMK" — semata pembagian contoh data,
  bukan pembatasan sistem.

---

## 5. Guru SMA vs Guru SMK — Sistemnya Sama?

**Sama.** Tidak ada branch/percabangan berdasarkan jenjang:

- Role, middleware, route, login, status approval: identik (`role:guru`).
- Bedanya hanya data, bukan kode:
  - `kelas.jenjang` bisa `SD/SMP/SMA/SMK` (kolom data).
  - Profil `siswa_smk` (pencari kerja) sengaja diisi **hanya untuk siswa SMK** —
    seeder bahkan hapus profil SMK yang terlanjur dibuat untuk siswa SMA
    (`SiswaSmk::...->delete()`).
  - Akun demo SMA & SMK dibuat terpisah per sekolah agar uji data membedakan
    jenjang — tapi keduanya masuk grup route yang sama.
- Kesimpulan: untuk keperluan pengguruan, satu Guru BK SMA maupun SMK memakai
  dashboard, menu, dan alur kerja yang persis sama.

---

## 6. Daftar Akun Seeder + Akses Login

Semua dibuat `updateOrCreate` (idempoten, aman dijalankan ulang). Password guru/admin = `password`.

### 6.1 Admin (login: email + password)

| Email | Password | Nama | Sekolah (core `schools`) |
|---|---|---|---|
| `admin@bk.test` | `password` | Admin BK | SMA Negeri 1 Contoh |
| `admin.demo@bk.test` | `password` | Admin Demo | SMK Negeri 1 Contoh |

### 6.2 Guru BK (login: No. HP/username + password)

| Username (No. HP) / NIP | Password | Nama | Status | Sekolah (sekolah_id) |
|---|---|---|---|---|
| `081234567890` (NIP `1987654321001`) | `password` | Ibu Rina Guru BK | disetujui | SMA Negeri 1 Contoh |
| `081234567891` | `password` | Bapak Dedi Guru BK | disetujui | SMA Negeri 2 Pekanbaru |
| `081234567892` | `password` | Ibu Sari Guru BK | disetujui | SMK Negeri 1 Riau |

> Catatan: seeder utama sempat membuat `Pak Dimas` (username `081234567891`,
> status `pending`) lalu `MultiSchoolTestingSeeder` menimpa akun yang sama menjadi
> guru B (disetujui). Jadi di basis data final tidak ada lagi guru pending dari seed.

### 6.3 Siswa (login: NISN + tanggal lahir, tanpa password)

| NISN | Tanggal lahir | Nama | Sekolah | Kelas |
|---|---|---|---|---|
| `0061234567` | `2008-05-14` | Andi Siswa | SMA Negeri 1 Contoh | XII IPA 1 |
| `0061234568` | `2008-06-20` | Alya Putri | SMA Negeri 1 Contoh | XII IPA 1 |
| `0061234569` | `2008-08-02` | Bima Pratama | SMA Negeri 1 Contoh | XII IPA 1 |
| `0061234570` | `2008-09-11` | Citra Lestari | SMA Negeri 1 Contoh | XI IPS 2 |
| `0061234571` | `2008-10-21` | Dimas Arya | SMA Negeri 1 Contoh | XI IPS 2 |
| `0061234601` | `2008-04-12` | Rina SMK | SMK Negeri 1 Contoh | XII RPL 1 |
| `9100000001` | `2008-01-15` | Andi Uji Sekolah A | SMA Negeri 1 Contoh | XII Uji 1 |
| `9100000002` | `2008-01-15` | Bunga Uji Sekolah B | SMA Negeri 2 Pekanbaru | XII Uji 2 |
| `9100000003` | `2008-01-15` | Citra Uji Sekolah B | SMA Negeri 2 Pekanbaru | XII Uji 2 |
| `9100000004` | `2008-01-15` | Dimas Uji Sekolah C | SMK Negeri 1 Riau | XII Uji 3 |
| `9100000005` | `2008-01-15` | Eka Uji Sekolah C | SMK Negeri 1 Riau | XII Uji 3 |

> Siswa demo tambahan (`User::factory()->count(6)` di seeder utama,
> alternasi SMA/SMK) TIDAK punya profil `students`, jadi **tidak bisa login**
> sebagai siswa (login NISN wajib ada profil).

### 6.4 Sekolah yang dibuat (tabel `sekolahs`)

| Nama | NPSN | Jenjang kelas | Status |
|---|---|---|---|
| SMA Negeri 1 Contoh | `20260001` | SMA | aktif, MOU, paket Basic |
| SMA Negeri 2 Pekanbaru | `20260002` | SMA | aktif, MOU, paket Basic |
| SMK Negeri 1 Contoh | `20260002` | SMK | aktif, MOU, paket Basic |
| SMK Negeri 1 Riau | `20260003` | SMK | aktif, MOU, paket Basic |

> `SMK Negeri 1 Contoh` dan `SMA Negeri 2 Pekanbaru` sama-sama NPSN `20260002` —
> mengikuti `updateOrCreate` tiap seeder terhadap sekolah "core" dengan NPSN itu.
> Di sistem aktif, yang dibentuk ad hoc di utama hanyalah SMA & SMK Negeri 1 Contoh,
> sedangkan sekolah 2 Pekanbaru & SMK 1 Riau datang dari `MultiSchoolTestingSeeder`.

---

## 7. Ringkasan

- Login: admin=email, guru=No.HP/NIP, siswa=NISN+tanggal lahir. Semua wajib status `disetujui`.
- Guru terikat ke 1 sekolah lewat `guru_bks.sekolah_id`; modul Data Siswa/Angket/Tryout
  di-scope ke sekolah itu, tapi RPL dan konseling (belum konsisten) tidak.
- Admin global, bebas semua sekolah, tanpa scoping `school_id`.
- Guru SMA dan SMK pakai sistem yang identik; perbedaan murni data (jenjang,
  profil `siswa_smk`).