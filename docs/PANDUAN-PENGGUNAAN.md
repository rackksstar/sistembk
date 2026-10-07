# Panduan Penggunaan Sistem Bimbingan Konseling (BKK) Sekolah

Dokumen ini adalah panduan operasional **end-to-end** untuk tiga role pada Sistem BKK Sekolah:
**Admin**, **Guru BK**, dan **Siswa** — mulai dari proses login hingga penggunaan seluruh modul.

> **Sumber dokumen:** seluruh isi di bawah disusun dari pembacaan langsung kode sumber Laravel
> di repository `sistembk` (routes, controller, form request, model, dan view Blade).
> Nama route, nama field, konstanta status, aturan validasi, dan seluruh string pesan
> disalin apa adanya dari kode — bukan hasil penafsiran.

---

## Daftar Isi

1. [Sekilas Sistem](#1-sekilas-sistem)
2. [Autentikasi](#2-autentikasi)
3. [Pendaftaran](#3-pendaftaran)
4. [Profil](#4-profil)
5. [Keluar](#5-keluar)
6. [Role: Admin](#6-role-admin)
7. [Role: Guru BK](#7-role-guru-bk)
8. [Role: Siswa](#8-role-siswa)
9. [Activity Log](#9-activity-log)
10. [Daftar Route Lengkap](#10-daftar-route-lengkap)
11. [Known Issues & Catatan Audit](#11-known-issues--catatan-audit)

---

## 1. Sekilas Sistem

### 1.1 Profil Akun

| Role | Pengenal login | Kredensial | Status akun yang boleh login |
|---|---|---|---|
| Admin | `email` | Email + Password | `disetujui` |
| Guru BK | No HP / NIP | No HP atau NIP + Password | `disetujui` |
| Siswa | NISN | NISN + Tanggal Lahir | `disetujui` |

Konstanta status akun (`app/Models/User.php`):

| Konstanta | Nilai | Arti |
|---|---|---|
| `STATUS_PENDING` | `pending` | Menunggu approval admin |
| `STATUS_APPROVED` | `disetujui` | Aktif |
| `STATUS_REJECTED` | `ditolak` | Ditolak |

Role yang tersedia: `admin`, `guru`, `siswa`.

### 1.2 Routing Dashboard

`GET /dashboard` (route `dashboard`) adalah pengalih umum yang mengarahkan user ke
dashboard sesuai role-nya:

| Role | Route tujuan |
|---|---|
| Admin | `admin.dashboard` → `/admin/dashboard` |
| Guru BK | `guru.dashboard` → `/guru/dashboard` |
| Siswa | `siswa.dashboard` → `/siswa/dashboard` |

### 1.3 Middleware

Semua route aplikasi memakai kombinasi:

- `auth` — harus sudah login.
- `verified` — **tidak efektif**, lihat [Known Issues](#112-verifikasi-email-tidak-ditegakkan).
- `role:admin` / `role:guru` / `role:siswa` — pemeriksa role (`EnsureUserHasRole`).

Role tidak cocok menghasilkan **HTTP 403** dengan pesan:

> Anda tidak memiliki akses ke halaman ini.

Route profil (`/profile`) memakai `role:admin,guru` — **Siswa tidak bisa** membuka
halaman profil.

---

## 2. Autentikasi

### 2.1 Endpoint

| Method | URI | Nama route | Middleware |
|---|---|---|---|
| GET | `/login` | `login` | `guest` |
| POST | `/login` | `login` | `guest`, `throttle:10,1` |

Alamat halaman login dipanggil dengan query string penentu role:

| Alamat | Judul halaman |
|---|---|
| `/login?role=admin` | Login Admin |
| `/login?role=guru` | Login Guru BK |
| `/login?role=siswa` | Login Siswa |
| `/login` (tanpa role) | Selamat datang kembali |

Nilai `role` yang tidak dikenal dianggap `null`.

### 2.2 Field Form per Role

**Admin** (`resources/views/auth/login.blade.php`)
- Email — `name="email"`, placeholder `email@sekolah.id`
- Password — `name="password"`, wajib
- Checkbox "Ingat saya" — `name="remember"`
- Link "Lupa password?" → `password.request`

**Guru BK**
- No HP / NIP — `name="login_id"`, placeholder "No HP atau NIP"
- Password — `name="password"`, wajib
- Checkbox "Ingat saya"
- Link "Lupa password?"
- Link "Ajukan akun Guru BK" → `guru.register`
- Teks bantuan: "Jika sudah disetujui admin, masukkan no HP atau NIP untuk melanjutkan ke dashboard."

**Siswa**
- NISN — `name="nisn"`, placeholder "NISN siswa"
- Tanggal Lahir — `name="birth_date"`, `type="date"`
- **Tidak ada** field password, checkbox "Ingat saya", maupun link lupa password.

### 2.3 Aturan Validasi (`app/Http/Requests/Auth/LoginRequest.php`)

| Field | Aturan |
|---|---|
| `nisn` | `required_if:selected_role,siswa`, `nullable`, `string`, `max:20` |
| `birth_date` | `required_if:selected_role,siswa`, `nullable`, `date`, `before:today` |
| `login_id` | `required_if:selected_role,guru`, `nullable`, `string`, `max:255` |
| `email` | `required_unless:selected_role,siswa,guru`, `nullable`, `string`, `max:255` |
| `password` | `required_unless:selected_role,siswa`, `nullable`, `string` |
| `selected_role` | `nullable`, `string`, `in:admin,guru,siswa` |

`LoginRequest` tidak mendefinisikan pesan kustom — semua pesan memakai default Laravel (English).

### 2.4 Pencocokan Kredensial

**Admin** — `users.email` dicocokkan apa adanya (tanpa normalisasi). Password dicek dengan `Hash::check`.

**Guru BK** — identifier = `trim(login_id)`. Sistem membuat dua kandidat:

1. Nilai asli, contoh `0812-3456`
2. Hasil `preg_replace('/\D+/', '', ...)` — hanya digit, contoh `08123456`

Kedua kandidat dicocokkan ke `users.username`, `guru_bks.no_hp`, atau `guru_bks.nip`, dengan syarat `users.role = guru`. Konsekuensi praktis: format dengan/tanpa tanda hubung dan dengan/tanpa nol depan sama-sama berhasil.

**Siswa** — tidak memakai password sama sekali, lihat [8.1 Login Siswa](#81-login-siswa).

### 2.5 Rate Limit

| Lapis | Key | Batas |
|---|---|---|
| Route `throttle:10,1` | per IP | 10 request/menit |
| `LoginRequest` | `strtolower(identifier)\|ip` | 5 percobaan |
| Siswa (terpisah) | `student-login\|{nisn lowercase}\|{ip}` | 5 percobaan |

Pesan saat kena batas: pesan `auth.throttle` standar Laravel, pada field `email` / `login_id` / `nisn` sesuai role.

### 2.6 Gerbang Setelah Autentikasi

Setelah kredensial cocok, `AuthenticatedSessionController@store` memeriksa:

1. **Role tidak cocok dengan pilihan** → sesi langsung logout, di-invalidate, token di-regenerate, lalu error:
   > Akun ini tidak sesuai dengan role yang dipilih. Silakan pilih role yang benar di landing page.

2. **Status `pending`** → sesi logout, error:
   > Akun Anda masih menunggu persetujuan admin.

3. **Status selain `disetujui`** → sesi logout, error:
   > Pendaftaran akun Anda ditolak. Silakan hubungi admin sekolah.

4. **Berhasil** → session di-regenerate, redirect ke dashboard sesuai role.

---

## 3. Pendaftaran

### 3.1 Pendaftaran Guru BK — Halaman Khusus

| Method | URI | Nama route |
|---|---|---|
| GET | `/register/guru-bk` | `guru.register` |
| POST | `/register/guru-bk` | `guru.register.store` |

Form request: `app/Http/Requests/Auth/RegisterGuruRequest.php`

| Field | Aturan |
|---|---|
| `name` | required, string, max 255 |
| `sekolah_id` | required, harus sekolah **aktif dan berstatus MOU** (`is_active = true` dan `is_mou = true`) |
| `no_hp` | required, string, max 30, unik di `guru_bks.no_hp`, unik di `users.username` |
| `nip` | required, string, max 40, unik di `guru_bks.nip` |
| `password` | required, confirmed, `Rules\Password::defaults()` |

Pesan sukses:
> Pendaftaran Guru BK berhasil dikirim. Silakan tunggu sampai admin menyetujui akun Anda.

Pesan gagal No HP/NIP bentrok (dari controller, tampil di bawah field No HP):
> No. HP atau NIP sudah digunakan Guru BK lain.

Akun yang dibuat berstatus `pending` — **tidak bisa login** sampai admin menyetujui.

### 3.2 Pendaftaran dari Halaman Landing

| Method | URI | Nama route |
|---|---|---|
| GET | `/register` | `register` |
| POST | `/register` | `register` |

Controller: `app/Http/Controllers/Auth/RegisteredUserController.php`

Validasi:

| Field | Aturan |
|---|---|
| `name` | required, string, max 255 |
| `email` | required, string, lowercase, email, max 255, unik |
| `role` | nullable, in `guru`, `siswa` |
| `nisn` | required_if role siswa, nullable, string, max 20 |
| `birth_date` | required_if role siswa, nullable, date, before:today |
| `password` | required, confirmed, `Rules\Password::defaults()` |

Pesan kustom:
- "NISN wajib diisi untuk pendaftaran siswa."
- "Tanggal lahir wajib diisi untuk pendaftaran siswa."
- "Tanggal lahir harus valid dan sebelum hari ini."

Pengecekan tambahan bila mendaftarkan **siswa** (berdasarkan NISN + tanggal lahir yang sudah ada di tabel `students`):

| Kondisi | Pesan (field `nisn`) |
|---|---|
| NISN tidak ditemukan | "NISN tidak ditemukan pada data siswa. Hubungi admin sekolah." |
| Tanggal lahir tidak cocok (field `birth_date`) | "Tanggal lahir tidak cocok dengan data siswa." |
| NISN sudah terhubung akun | "NISN ini sudah terhubung dengan akun siswa." |

Perilaku:
- Role `guru` → akun dibuat berstatus `pending`, pesan:
  > Pendaftaran Guru BK berhasil dikirim dan menunggu persetujuan admin.

  Lalu redirect ke halaman login.
- Role `siswa` → akun dibuat berstatus `disetujui`, langsung login otomatis, dan
  field `user_id` pada data siswa terisi.

> **Catatan:** flash sukses pada 3.1 ("…Silakan tunggu sampai admin menyetujui akun Anda.")
> dan 3.2 ("…dan menunggu persetujuan admin.") memang berbeda karena keduanya milik
> dua alur yang berbeda.

---

## 4. Profil

| Method | URI | Nama route | Middleware |
|---|---|---|---|
| GET | `/profile` | `profile.edit` | `auth`, `verified`, `role:admin,guru` |
| PATCH | `/profile` | `profile.update` | idem |
| DELETE | `/profile` | `profile.destroy` | idem |

### 4.1 Field Tampil

**Admin** (`resources/views/profile/partials/update-profile-information-form.blade.php`)
- `name` — label "Name", wajib
- `email` — label "Email", wajib
- Field `no_hp`, `nip`, sekolah read-only **tidak tampil** (khusus Guru)
- Deskripsi: "Update your account's profile information and email address."

**Guru BK**
- Deskripsi: "Perbarui identitas akun Guru BK yang digunakan untuk login."
- `name` — `users.name`
- `no_hp` — `guru_bks.no_hp`, fallback `users.username`
- `nip` — `guru_bks.nip`
- Sekolah — read-only/disabled, dari `guru_bks.sekolah.nama`, fallback `users.school`, fallback `-`

### 4.2 Validasi (`ProfileUpdateRequest`)

| Role | Field | Aturan |
|---|---|---|
| Guru | `name` | required, string, max 255 |
| Guru | `no_hp` | required, string, max 30, unik `guru_bks.no_hp` (ignore profil ini), unik `users.username` (ignore user ini) |
| Guru | `nip` | required, string, max 40, unik `guru_bks.nip` (ignore profil ini) |
| Admin / lain | `name` | required, string, max 255 |
| Admin / lain | `email` | required, lowercase, email, max 255, unik (ignore diri sendiri) |

### 4.3 Efek Penyimpanan (`ProfileController@update`)

**Guru BK:**
1. `users.name` dan `users.username = no_hp` diperbarui — No HP baru langsung menjadi username login.
2. `guru_bks.no_hp` / `nip` di-*upsert*.
3. Jika ada nilai yang berubah, dibuat record `GuruProfileChange` dengan `old_values` dan
   `new_values`; `reviewed_at` dibiarkan `null` → muncul di modul
   [Perubahan Profil Guru](#613-perubahan-profil-guru).

> **Penting:** karena No HP menjadi `users.username`, mengganti No HP pada profil
> berarti No HP untuk login Guru juga ikut berubah. Gunakan No HP baru pada login berikutnya.

**Admin:** `name` dan `email` diperbarui. Bila email berubah, `email_verified_at` di-reset ke `null`.

**Semua role:** redirect ke `profile.edit` dengan flash `status=profile-updated`,
ditampilkan sebagai teks **"Saved."** yang hilang otomatis sekitar 2 detik.

Perubahan profil **tidak** menulis activity log.

### 4.4 Update Password

Partial `update-password-form` (Breeze): password saat ini + password baru + konfirmasi.

### 4.5 Hapus Akun

`DELETE /profile` meminta konfirmasi password pada field `current_password`.
Error muncul pada bag `userDeletion`. Jika berhasil: logout, hapus user, invalidate session,
regenerate token, redirect ke `/`.

---

## 5. Keluar

| Method | URI | Nama route | Middleware |
|---|---|---|---|
| POST | `/logout` | `logout` | `auth` |

Proses: `Auth::guard('web')->logout()` → session di-invalidate → token di-regenerate → redirect ke `/`.

Tidak ada dialog konfirmasi — klik langsung memproses form.
Logout **tidak** menulis activity log.

---

## 6. Role: Admin

### 6.1 Dashboard

Route: `GET /admin/dashboard` → `admin.dashboard`
Controller: `App\Http\Controllers\Admin\DashboardController@index`
View: `resources/views/admin/dashboard.blade.php`

**Hero**
- Badge: "Admin Panel"
- Judul: "Pusat kendali Sistem BK sekolah."
- Subteks: "Admin dapat mengatur semua role, termasuk siswa, serta memantau approval, kelas, konseling, laporan, dan informasi karier."
- Tombol: "Kelola Semua Role" → `admin.users.index`, "Kelola Siswa" → `admin.students.index`

**Empat kartu metrik**

| Kartu | Nilai |
|---|---|
| Total pengguna — "Admin, Guru BK, dan Siswa aktif." | `User::count()` |
| Guru BK — "Konselor yang tersedia di sistem." | jumlah user role `guru` |
| Permintaan menunggu — "Butuh tindak lanjut dari Guru BK." | `ConsultationRequest` status `pending` |
| Update profil guru — "Perubahan profil Guru BK yang belum dibaca." | `GuruProfileChange` dengan `reviewed_at` NULL |

**Ringkasan Role** — "Jumlah akun berdasarkan role aktif di sistem." Menampilkan jumlah Admin, Guru BK, dan Siswa.

**Modul Admin** — 8 kartu berisi judul, deskripsi, jumlah, dan tautan:

| Kartu | Deskripsi |
|---|---|
| Approval Guru BK | Setujui atau tolak pendaftaran Guru BK. |
| Sekolah MOU | Input dan kelola sekolah yang sudah MOU dengan PCR. |
| Perubahan Profil Guru | Lihat perubahan no HP, NIP, dan nama Guru BK. |
| Manajemen Pengguna | Atur akun admin, Guru BK, dan siswa. |
| Data Siswa | Kelola NISN, tanggal lahir, dan profil siswa. |
| Kelas Bimbingan | Buat kelas dan tambahkan siswa ke kelas. |
| Informasi Karier | Kelola konten karier read-only untuk siswa. |
| Konseling & Laporan | Pantau pengajuan, jadwal, hasil, dan evaluasi. |

**Aktivitas Konseling Terbaru** — "Pengajuan, jadwal, dan laporan konseling terbaru dari siswa dan Guru BK."
5 data terbaru. Setiap baris: topik · nama siswa · nama kelas · "Guru BK: {nama}" (atau "Belum dipilih") · badge status.
Tombol "Lihat semua" → `admin.consultations.index`.
*Empty state:* "Belum ada permintaan" / "Saat siswa mengirim permintaan konseling, data terbaru akan muncul di sini."

**Ringkasan layanan BK (Core)** — "Metrik modul tim inti." 4 kartu dengan link "Kelola →":

| Kartu | Nilai | Tujuan |
|---|---|---|
| Rapor BK | `RaporBk::count()` | `admin.rapor.index` |
| Postingan | `Postingan::count()` | `admin.postingan.index` |
| Soal Tryout | `MasterQuestion` kategori `tryout` | `admin.master-pertanyaan.index?kategori=tryout` |
| Kelas | `Kelas::count()` | `admin.kelas.index` |

**Sekolah aktif** — "Pantau sekolah MOU, paket aktivasi, dan status aktif."
Lima statistik: Total sekolah / Aktif / Nonaktif / Sudah MOU / Paket aktif.
(`paket aktif` = sekolah dengan `paket_aktif` terisi.)
Tombol "Kelola sekolah" → `admin.sekolah.index`.
Daftar 5 sekolah terbaru: nama · "NPSN {npsn}" · "· Paket {paket_aktif}" · badge "MOU" (bila `is_mou`) · badge "Aktif"/"Nonaktif".
*Empty state:* "Belum ada sekolah" / "Tambahkan sekolah MOU untuk mulai memantau aktivitas."

**Postingan terbaru** — hanya dirender bila ada data. "Artikel BK yang baru disimpan." 3 postinginan terbaru: judul · kategori · status (Draft/Publik). Tombol "Kelola postingan" → `admin.postingan.index`.

---

### 6.2 Menu Sidebar (`config/navigation.php`, key `admin`)

6 grup, 16 item:

| Grup | Item | Route | Judul topbar |
|---|---|---|---|
| **Utama** | Dashboard | `admin.dashboard` | Dashboard |
| **Layanan BK** | Konseling | `admin.consultations.index` | Konseling & Laporan |
| | Rapor BK | `admin.rapor.index` | Rapor BK |
| **Data Master** | Sekolah | `admin.sekolah.index` | Sekolah |
| | Kelas | `admin.kelas.index` | Kelas |
| | Guru BK | `admin.guru-bk.index` | Guru BK |
| | Kategori Minat | `admin.interest-categories.index` | Kategori Minat |
| | Program Studi PCR | `admin.program-studi.index` | Program Studi PCR |
| | Bidang Karier | `admin.bidang-karier.index` | Bidang Karier |
| | Master Pertanyaan | `admin.master-pertanyaan.index` | Master Pertanyaan |
| | Kategori Artikel | `admin.kategori-postingan.index` | Kategori Artikel |
| | Artikel BK | `admin.postingan.index` | Artikel BK |
| **Pengguna** | Approval Guru | `admin.approvals.index` | Approval Guru BK |
| | Manajemen Akun | `admin.users.index` | Manajemen Pengguna |
| | Data Siswa | `admin.students.index` | Data Siswa |
| | Kelas Bimbingan | `admin.guidance-classes.index` | Kelas Bimbingan |
| | Perubahan Profil | `admin.guru-profile-changes.index` | Perubahan Profil Guru |
| **Modul Tim Lain** | Informasi Karier | `admin.careers.index` | Informasi Karier |
| **Platform** | Log Aktivitas | `admin.activity-logs.index` | Log Aktivitas |

Di luar `config/navigation.php`, sidebar juga memuat link **Profil** (`profile.edit`).

Route yang ada tapi tidak punya item sidebar sendiri: `admin.rapor.show`, `admin.rapor.pdf`,
`admin.guru-profile-changes.reviewed`, `admin.approvals.approve`, `admin.approvals.reject`,
`admin.guidance-classes.students.attach`, `admin.guidance-classes.students.detach`,
serta seluruh aksi `store`/`update`/`destroy` pada modul resource.

Semua modul resource admin memakai `->except(['create','show','edit'])`, sehingga tidak ada
halaman create/edit/show terpisah — semuanya berupa panel/modal di halaman index, kecuali
Rapor BK yang punya halaman detail (`show`).

---

### 6.3 Approval Guru BK

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/approvals` | `admin.approvals.index` |
| PATCH | `/admin/approvals/{user}/approve` | `admin.approvals.approve` |
| PATCH | `/admin/approvals/{user}/reject` | `admin.approvals.reject` |

- Judul: "Persetujuan Guru BK"
- Deskripsi: "Tinjau pendaftaran Guru BK lalu setujui atau tolak akses dashboard."
- **Filter:** `status`. Default `pending`. Opsi: "semua" (label "Semua status") + Pending / Disetujui / Ditolak.
- **Kolom:** Nama | Sekolah | No HP / NIP | Status | Aksi
  - Sekolah: `guruBkProfile.sekolah.nama` → fallback `user.school` → `-`
  - No HP / NIP: `no_hp / nip`, masing-masing fallback `-`
- **Aksi:** tombol "Approve" (hijau) dan "Reject" (merah), form POST dengan `@method('PATCH')`.
- **Guard:** `abort_unless($user->role === 'guru', 404)`
- **Flash:**
  - "Pendaftaran Guru BK berhasil disetujui."
  - "Pendaftaran Guru BK berhasil ditolak."
- *Empty state:* "Tidak ada data guru" / "Data Guru BK dengan filter ini belum tersedia."
- **Tidak ada activity log.**

> Aksi approve/reject tidak dibatasi status — admin bisa menyetujui akun yang sudah `ditolak`.

---

### 6.4 Manajemen Pengguna (User)

Resource `users`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/users` | `admin.users.index` |
| POST | `/admin/users` | `admin.users.store` |
| PUT/PATCH | `/admin/users/{user}` | `admin.users.update` |
| DELETE | `/admin/users/{user}` | `admin.users.destroy` |

- Judul: "Manajemen Pengguna" — "Kelola akun admin, Guru BK, dan siswa dari satu tabel."
- **Filter:** `search` ("Cari nama atau email...", cocok ke `name`/`email`), `role`, `status`.
- **Kolom:** Nama | Email | Role | Status | Aksi
- **Field form:**

| Field | Aturan |
|---|---|
| `name` | required, max 255 |
| `email` | required, lowercase, valid email, max 255, unik |
| `role` | required, in `admin`/`guru`/`siswa` (default tampilan: siswa) |
| `status` | required, in `pending`/`disetujui`/`ditolak` (default tampilan: disetujui) |
| `password` | required saat create; `nullable\|confirmed\|Rules\Password::defaults()` saat update |
| `password_confirmation` | wajib saat membuat |

- `store`: `email_verified_at = now()` — akun langsung terverifikasi.
- `update`: password hanya diubah bila diisi.
- `destroy`: menolak menghapus akun sendiri dengan flash error:
  > Akun yang sedang digunakan tidak dapat dihapus.
- **Flash:** "Akun pengguna berhasil dibuat." / "… diperbarui." / "… dihapus."
- *Empty state:* "Pengguna tidak ditemukan" / "Coba ubah kata kunci pencarian atau filter role."
- **Tidak ada activity log.**

---

### 6.5 Sekolah

Resource `sekolah`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/sekolah` | `admin.sekolah.index` |
| POST | `/admin/sekolah` | `admin.sekolah.store` |
| PUT/PATCH | `/admin/sekolah/{sekolah}` | `admin.sekolah.update` |
| DELETE | `/admin/sekolah/{sekolah}` | `admin.sekolah.destroy` |

- Judul: "Daftar Sekolah MOU"
- Deskripsi: "Kelola sekolah yang sudah MOU dengan PCR dan bisa dipilih saat Guru BK mendaftar."
- Panel create: "Input Sekolah MOU" · tombol "Tambah sekolah MOU" / "Tutup formulir"
- Panel edit: "Edit Sekolah" · "Perbarui data sekolah."
- **Filter:** `search` ("Cari nama/NPSN..."), `mou` (Semua status MOU / Sudah MOU / Belum MOU), `active` (Semua status / Aktif / Nonaktif)
- **Field form:**

| Field | Aturan |
|---|---|
| `nama` | required, max 255, unik `sekolahs.nama` |
| `npsn` | required, max 20, unik `sekolahs.npsn` |
| `alamat` | required |
| `logo` | optional, image, max 2048 |
| `paket_aktif` | optional, max 120 |
| `tanggal_aktivasi` | optional, date |
| `is_mou` | required boolean (Sudah MOU / Belum MOU, default: 1) |
| `is_active` | required boolean (Aktif / Nonaktif, default: 1) |

- Logo disimpan di disk `public`, folder `sekolah-logos`, kolom `logo_path`. Upload baru
  menghapus file lama; `destroy` juga menghapus file logo.
- **Flash:** "Sekolah berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Activity log:** `sekolah.created` / `sekolah.updated` / `sekolah.deleted` — properties `nama`, `npsn`
- *Empty state:* "Belum ada sekolah" / "Tambahkan sekolah untuk mulai mengelola kelas dan guru BK."

---

### 6.6 Kelas

Resource `kelas`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/kelas` | `admin.kelas.index` |
| POST | `/admin/kelas` | `admin.kelas.store` |
| PUT/PATCH | `/admin/kelas/{kela}` | `admin.kelas.update` |
| DELETE | `/admin/kelas/{kela}` | `admin.kelas.destroy` |

> Parameter URI adalah `{kela}` — hasil *singularize* otomatis atas kata "kelas" oleh Laravel.
> `route('admin.kelas.update', $kelas)` tetap bekerja normal karena binding mengikuti posisi.

- Judul: "Manajemen Kelas" — "Kelola kelas per sekolah dengan filter jenjang."
- **Filter:** `search` ("Cari nama kelas..."), `sekolah_id` ("Semua sekolah"), `jenjang` ("Semua jenjang")
  - Dropdown `jenjang` diisi dari nilai distinct **yang sudah ada di database**, bukan dari daftar konstanta lengkap.
- **Field form:**

| Field | Aturan |
|---|---|
| `sekolah_id` | required, exists |
| `nama` | required, max 120, unik per sekolah (`Rule::unique('kelas','nama')->where(sekolah_id)`) |
| `jenjang` | optional, teks; harus salah satu dari `Kelas::JENJANG_OPTIONS` |
| `tingkatan` | optional, teks; harus salah satu dari `Kelas::TINGKATAN_OPTIONS` |

Pilihan sah (`app/Models/Kelas.php`):

| Konstanta | Nilai |
|---|---|
| `JENJANG_OPTIONS` | `SD`, `SMP`, `SMA`, `SMK` |
| `TINGKATAN_OPTIONS` | `1` … `12`, `X`, `XI`, `XII` |

**Pesan validasi kustom** (`StoreKelasRequest` / `UpdateKelasRequest`):

| Aturan | Pesan |
|---|---|
| `nama.unique` | "Kelas dengan nama ini sudah ada di sekolah tersebut." |
| `jenjang.in` | "Jenjang harus salah satu dari: SD, SMP, SMA, SMK." |
| `tingkatan.in` | "Tingkatan harus salah satu dari: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, X, XI, XII." |

- **Flash:** "Kelas berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Activity log:** `kelas.created` / `kelas.updated` / `kelas.deleted` — properties `nama`
- *Empty state:* "Belum ada kelas" / "Tambahkan kelas untuk mengelompokkan siswa berdasarkan sekolah."

---

### 6.7 Guru BK

Resource `guru-bk`, parameter `guruBk`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/guru-bk` | `admin.guru-bk.index` |
| POST | `/admin/guru-bk` | `admin.guru-bk.store` |
| PUT/PATCH | `/admin/guru-bk/{guruBk}` | `admin.guru-bk.update` |
| DELETE | `/admin/guru-bk/{guruBk}` | `admin.guru-bk.destroy` |

- Judul: "Manajemen Guru BK"
- Deskripsi: "Input manual Guru BK langsung menjadi akun aktif sesuai status yang dipilih."
- **Filter:** `search` ("Cari nama/no HP/NIP..." — cocok ke `user.name`, `user.username`, `guru_bks.nip`, `guru_bks.no_hp`), `sekolah_id` (opsi sekolah ditandai " - MOU" bila `is_mou`), `status`
- **Field form:**

| Field | Aturan |
|---|---|
| `name` | required, max 255 |
| `password` + `password_confirmation` | required saat create; optional saat update (placeholder "Kosongkan jika tetap"), min 8, confirmed |
| `status` | required, in `User::STATUSES` (default tampilan: disetujui) |
| `sekolah_id` | required, exists |
| `no_hp` | required, max 30 — label "No HP / Username", unik di `guru_bks.no_hp` **dan** `users.username` |
| `nip` | optional, max 40, unik di `guru_bks.nip` |
| `jabatan` | optional, max 120 |
| `bidang_studi` | optional, max 120 |

**Perilaku `store`** (dalam satu `DB::transaction`):
- `users.username` = `no_hp` (fallback `nip`), `role = guru`, password di-hash, `school` diisi nama sekolah.
- `QueryException` (mis. bentrok unique di level DB) ditangkap → error pada field `no_hp`:
  > No. HP atau NIP sudah digunakan Guru BK lain.

  (disertai `withInput()`)

**Perilaku `update`:** `users` diperbarui (name, username, school, status, password bila ada), lalu `guru_bks` diperbarui.

**Perilaku `destroy`:** menghapus profil `guru_bks` **dan** user terkait.

- **Flash:** "Data Guru BK berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Activity log:** `guru-bk.created` / `guru-bk.updated` / `guru-bk.deleted` — properties `nama` (`user.name`), `nip`
- *Empty state:* "Belum ada data Guru BK" / "Tambahkan Guru BK untuk mulai mengelola sesi konseling."

---

### 6.8 Data Siswa

Resource `students`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/students` | `admin.students.index` |
| POST | `/admin/students` | `admin.students.store` |
| PUT/PATCH | `/admin/students/{student}` | `admin.students.update` |
| DELETE | `/admin/students/{student}` | `admin.students.destroy` |

- Judul: "Manajemen Data Siswa" — "CRUD siswa dengan validasi NISN unik dan tanggal lahir valid."
- **Filter:** `search` ("Cari nama, NISN, sekolah..."), `kelas_id` ("Semua kelas")
- **Field form:**

| Field | Aturan |
|---|---|
| `name` | required, max 255 |
| `nisn` | required, max 20, unik `students.nisn` |
| `birth_date` | required, date, before:today |
| `kelas_id` | required, exists `kelas` — label "Kelas BK", opsi "nama (sekolah)" |
| `jenis_kelamin` | optional, L/P |
| `alamat` | optional, max 2000 |
| `school` | optional, max 255 — label "Sekolah (teks legacy)" |
| `user_id` | optional; akun siswa yang belum punya profil atau sudah terhubung ke siswa lain; harus role siswa & unik |

`status_biodata` dihitung otomatis: `lengkap` bila `jenis_kelamin` **dan** `alamat` terisi,
selain itu `belum_lengkap`.

**Pesan validasi kustom** (`StoreStudentRequest`):

| Aturan | Pesan |
|---|---|
| `nisn.unique` | "NISN sudah digunakan siswa lain." |
| `birth_date.before` | "Tanggal lahir harus valid dan sebelum hari ini." |
| `user_id.exists` | "Akun login harus akun dengan role siswa." |
| `user_id.unique` | "Akun login sudah terhubung ke siswa lain." |

`UpdateStudentRequest` **tidak** mengulang pesan `nisn.unique` dan `birth_date.before` —
pada update, pesan uniqueness NISN muncul dalam bentuk default Laravel.

- **Flash:** "Data siswa berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Activity log:** `student.created` / `student.updated` (properties kosong), `student.deleted` (property `name`)
- *Empty state:* "Belum ada data siswa" / "Tambahkan data siswa pertama untuk mulai mengelola kelas bimbingan."

> **Tidak ada import/unggah massal siswa di sisi admin.** Import hanya ada di modul Guru.

---

### 6.9 Master Pertanyaan

Resource `master-pertanyaan`, parameter `masterPertanyaan`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/master-pertanyaan` | `admin.master-pertanyaan.index` |
| POST | `/admin/master-pertanyaan` | `admin.master-pertanyaan.store` |
| PUT/PATCH | `/admin/master-pertanyaan/{masterPertanyaan}` | `admin.master-pertanyaan.update` |
| DELETE | `/admin/master-pertanyaan/{masterPertanyaan}` | `admin.master-pertanyaan.destroy` |

- Judul: "Master Pertanyaan" — "Kelola pertanyaan aktif untuk angket dan tryout."
- **Filter:** `search` ("Cari teks pertanyaan..."), `kategori` (Semua kategori + angket/tryout), `active` (Semua status / Aktif / Nonaktif)
- **Field form:**

| Field | Aturan |
|---|---|
| `kategori` | required, select (default tampilan: `angket`, label uppercase) |
| `tipe_input` | required, select (default tampilan: `skala`, label underscore diganti spasi) |
| `teks_pertanyaan` | required, textarea, max 2000 |
| `is_active` | required boolean (Aktif / Nonaktif, default tampilan: aktif) |

Konstanta model:

| Konstanta | Nilai |
|---|---|
| `KATEGORI` | `angket`, `tryout` |
| `TIPE_INPUT` | `pilihan_ganda`, `skala`, `isian` |

- **Flash:** "Pertanyaan berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Activity log:** `master-pertanyaan.created` / `.updated` (property `kategori`), `.deleted` (property `teks`, dibatasi 80 karakter)
- *Empty state:* "Belum ada pertanyaan" / "Tambahkan pertanyaan untuk kebutuhan angket dan tryout."

> `MasterQuestion` **tidak punya kolom jawaban/opsi** — hanya menyimpan teks + tipe.
> Modul ini adalah katalog soal dasar; opsi jawaban dikelola terpisah di modul instrumen.

---

### 6.10 Kategori Postingan

Resource `kategori-postingan`, parameter `kategoriPostingan`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/kategori-postingan` | `admin.kategori-postingan.index` |
| POST | `/admin/kategori-postingan` | `admin.kategori-postingan.store` |
| PUT/PATCH | `/admin/kategori-postingan/{kategoriPostingan}` | `admin.kategori-postingan.update` |
| DELETE | `/admin/kategori-postingan/{kategoriPostingan}` | `admin.kategori-postingan.destroy` |

- Judul: "Kategori Postingan" — "Kelola kategori untuk konten postingan."
- Panel create: "Tambah Kategori" · "Nama kategori akan dibuatkan slug otomatis."
- **Filter:** `search` ("Cari kategori...", cocok ke `name`)
- **Field form:** `name` saja — required, max 120, unik di `post_categories`.
- Slug dibuat otomatis via `Str::slug`, dijamin unik dengan sufiks angka `-2`, `-3`, dst (method `uniqueSlug`).
- **Guard hapus** — kategori yang masih memiliki postingan ditolak dengan error pada field `postingan`:
  > Kategori tidak dapat dihapus karena masih memiliki postingan. Pindahkan atau hapus postingannya terlebih dahulu.
- **Flash:** "Kategori postingan berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Activity log:** `kategori-postingan.created` / `.updated` / `.deleted` — property `name`
- *Empty state:* "Belum ada kategori" / "Tambahkan kategori pertama untuk postingan."

---

### 6.11 Artikel BK (Postingan)

Resource `postingan`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/postingan` | `admin.postingan.index` |
| POST | `/admin/postingan` | `admin.postingan.store` |
| PUT/PATCH | `/admin/postingan/{postingan}` | `admin.postingan.update` |
| DELETE | `/admin/postingan/{postingan}` | `admin.postingan.destroy` |

- Judul: "Postingan Artikel" — "Kelola artikel BK untuk dibaca siswa."
- Panel: "Tambah Postingan" / "Edit Postingan"
- **Filter:** `search` ("Cari judul atau isi..."), `kategori`, `status` (Semua status / Draft / Publik)
- **Field form:**

| Field | Aturan |
|---|---|
| `post_category_id` | required, integer, exists `post_categories` |
| `judul` | required, max 255 |
| `isi` | required, max 20000, textarea |
| `status` | required, in `Postingan::STATUSES` (`draft`, `published`; default tampilan: `draft`) |
| `gambar` | optional, image, max 2048 |

- Slug dibuat otomatis unik dari judul (sufiks `-2`, `-3`, dst).
- Gambar disimpan di disk `public`, folder `postingan`, kolom `gambar_path`. Upload baru
  menghapus gambar lama; `destroy` menghapus file gambar.
- **Flash:** "Postingan berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Activity log:** `postingan.created` / `.updated` (tanpa properties), `.deleted` (property `judul`)
- *Empty state:* "Belum ada postingan" / "Buat artikel pertama untuk siswa."

---

### 6.12 Kelas Bimbingan

Resource `guidance-classes`, parameter `guidanceClass`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/guidance-classes` | `admin.guidance-classes.index` |
| POST | `/admin/guidance-classes` | `admin.guidance-classes.store` |
| PUT/PATCH | `/admin/guidance-classes/{guidanceClass}` | `admin.guidance-classes.update` |
| DELETE | `/admin/guidance-classes/{guidanceClass}` | `admin.guidance-classes.destroy` |
| POST | `/admin/guidance-classes/{guidanceClass}/students` | `admin.guidance-classes.students.attach` |
| DELETE | `/admin/guidance-classes/{guidanceClass}/students/{student}` | `admin.guidance-classes.students.detach` |

- Judul: "Kelas Bimbingan" — "Buat kelas dan tambahkan siswa ke kelompok bimbingan."
- Tampilan berupa **kartu**, bukan tabel. Setiap kelas menampilkan:
  - Kode: `BK-XXXXXX` — dibuat otomatis `Str::upper(Str::random(6))`
  - Deskripsi, atau "Belum ada deskripsi."
  - Tombol Edit / Delete
  - Form tambah siswa + daftar siswa dalam kelas
- **Field form kelas:** `name` (required, max 120), `description` (optional, max 1000)
- **Tambah siswa:** `student_id` required exists `students,id`, memakai `syncWithoutDetaching` (mencegah duplikasi)
  - Flash: "Siswa berhasil ditambahkan ke kelas."
- **Lepas siswa:** `detach`
  - Flash: "Siswa berhasil dihapus dari kelas."
- **Flash kelas:** "Kelas bimbingan berhasil dibuat." / "… diperbarui." / "… dihapus."
- *Empty kelas:* "Belum ada kelas" / "Buat kelas bimbingan pertama untuk mengelompokkan siswa."
- *Empty siswa:* "Belum ada siswa" / "Tambahkan siswa ke kelas ini lewat pilihan di atas."
- **Tidak ada activity log.**

> `update` memakai `StoreGuidanceClassRequest` (bukan request terpisah).

Siswa bergabung ke kelas secara mandiri melalui form "Kelas Bimbingan" di dashboard siswa
([8.9](#813-gabung-kelas-bimbingan)) menggunakan kode kelas.

---

### 6.13 Perubahan Profil Guru

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/perubahan-profil-guru` | `admin.guru-profile-changes.index` |
| PATCH | `/admin/perubahan-profil-guru/{change}/dibaca` | `admin.guru-profile-changes.reviewed` |

- Judul: "Perubahan Profil Guru"
- Deskripsi: "Pantau perubahan nama, no HP, dan NIP yang dilakukan Guru BK dari halaman profil."
- **Filter:** `status`. Default `baru` (hanya `reviewed_at IS NULL`). Label dropdown:
  "Belum dibaca" / "Sudah dibaca" / "Semua".
- Menampilkan label "Dibaca"/"Baru" per baris.
- **Tandai dibaca:** set `reviewed_at = now()`. Flash: "Perubahan profil ditandai sudah dibaca."
- *Empty state:* "Belum ada perubahan" / "Perubahan profil Guru BK akan muncul di sini setelah guru mengubah profilnya."
- **Tidak ada activity log.**

> Data dibuat otomatis oleh `ProfileController@update`. Admin **tidak** bisa
> membuat/mengubah/menghapus lewat UI.

---

### 6.14 Konseling (Admin) — READ-ONLY

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/consultations` | `admin.consultations.index` |

- Judul: "Konseling & Laporan"
- Deskripsi: "Monitoring semua pengajuan, jadwal, hasil konseling, dan evaluasi dari Guru BK."
- **Tidak ada** form create/edit/delete — halaman sepenuhnya baca.
- **Filter:** `search` ("Cari topik, siswa, NISN, atau guru BK..." — LIKE pada
  `subject`/`details`, atau nama siswa, NISN siswa, nama Guru BK),
  `status` (Semua status + Menunggu/Disetujui/Ditolak/Dijadwalkan ulang/Selesai),
  `kategori` (Semua kategori + Pribadi/Sosial/Belajar/Karier/Kedisiplinan)
- **Kolom:** Siswa | Kelas | Guru BK | Keluhan/Topik (`details`, fallback `subject`) | Jadwal (tanggal + 5 karakter waktu) | Status | Laporan
  - Laporan: "Ada laporan" bila `result` atau `evaluation` terisi, else "Belum ada"
- **Tidak ada** aksi approve/reject/schedule/report/print dari sisi admin — semuanya hanya di modul Guru.
- **Tidak ada activity log.**
- *Empty state:* "Belum ada data konseling" / "Data akan muncul setelah siswa mengajukan konseling."

---

### 6.15 Rapor BK

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/rapor` | `admin.rapor.index` |
| GET | `/admin/rapor/{rapor}` | `admin.rapor.show` |
| GET | `/admin/rapor-cetak/{rapor}/pdf` | `admin.rapor.pdf` |

**Daftar**
- Judul: "Pantau Rapor BK" — "Tampilan read-only seluruh rapor yang dibuat guru BK."
- **Filter:** `semester` (Semua + Semester Ganjil/Genap), `tahun_ajaran` (placeholder "2025/2026"), `status` (Semua + Draft/Final)
- **Kolom:** Siswa | Kelas | Guru BK | Periode ("Semester · tahun_ajaran") | Status | Aksi (Detail + PDF)
- *Empty state:* "Belum ada rapor" / "Rapor akan muncul setelah guru BK menyimpan data."

**Detail (`show`)** — read-only
- Judul: "Rapor BK — {nama}" · subjudul "{semesterLabel} · {tahun_ajaran} · {statusLabel}"
- Metadata: Guru BK, Kelas, Sekolah, NISN, Diperbarui
- Isi 5 seksi (tiap bagian fallback `-`): perkembangan akademik, sosial, psikologis, saran & tindak lanjut, catatan guru
- Tombol "Kembali ke daftar" + "Unduh PDF"

**PDF (`exportPdf`)**
- Memakai view `guru.rapor.pdf`, A4 portrait, DOMPDF
- Nama file: `rapor-bk-{slug nama}-{semester}-{tahun_ajaran dengan / → -}.pdf`
- `tanggalCetak` = `now()->format('d M Y')`
- Menyertakan ringkasan konseling (`ringkasanKonseling`):
  - `total_konseling` — jumlah `ConsultationRequest` selesai untuk siswa + Guru BK yang sama
  - `total_dinilai` — jumlah `PenilaianPelayanan` pada request selesai tersebut
  - `rata_penilaian` — rata-rata (skor_materi + skor_cara + skor_manfaat) / 3, dibulatkan 1 desimal

- **Tidak ada** create/update/delete dan **tidak ada** activity log.

---

### 6.16 Informasi Karier

Resource `careers`, `->except(['create','show','edit'])`

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/careers` | `admin.careers.index` |
| POST | `/admin/careers` | `admin.careers.store` |
| PUT/PATCH | `/admin/careers/{career}` | `admin.careers.update` |
| DELETE | `/admin/careers/{career}` | `admin.careers.destroy` |

- Judul: "Informasi Karier"
- Deskripsi: "Kelola konten karier untuk membantu siswa mengenali pilihan studi dan pekerjaan."
- **Filter:** `search` ("Cari judul atau deskripsi..."), `category` (Semua kategori + daftar kategori distinct)
- **Field form:**

| Field | Aturan |
|---|---|
| `title` | required, max 255 |
| `category` | required, max 120, teks bebas (placeholder "Contoh: Teknologi") |
| `description` | required, max 2000 |
| `image` | optional, image, max 2048 |

  Bila ada gambar lama dan tidak memilih file baru, gambar lama tetap dipakai.
- Gambar disimpan di disk `public`, folder `career-infos`, kolom `image_path`.
  Upload baru / `destroy` menghapus file lama.
- **Flash:** "Informasi karier berhasil dibuat." / "… diperbarui." / "… dihapus."
- **Tidak ada activity log.**
- *Empty state:* "Belum ada informasi karier" / "Tambahkan konten pertama agar siswa bisa mulai membaca referensi karier."

---

### 6.17 Log Aktivitas

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/activity-logs` | `admin.activity-logs.index` |

- Judul: "Log Aktivitas" — "Catatan aksi penting di sistem (read-only)."
- **Filter:** `search` ("Cari aksi atau nama pengguna..."), `action` ("Semua aksi" + daftar action distinct)
- **Kolom:** Waktu (`d M Y H:i`) | Pengguna (nama, fallback "Sistem") | Aksi (string mentah) | Subjek (`ClassBasename #id` atau `-`)
- Sepenuhnya read-only. **Tidak ada activity log** untuk halaman ini sendiri.
- *Empty state:* "Belum ada log" / "Log akan muncul saat pengguna melakukan aksi penting."

---

### 6.18 Kategori Minat (RIASEC)

Resource `interest-categories`, `->except(['create','show','edit'])`. Menu: **Data Master → Kategori Minat**.

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/interest-categories` | `admin.interest-categories.index` |
| POST | `/admin/interest-categories` | `admin.interest-categories.store` |
| PUT/PATCH | `/admin/interest-categories/{interestCategory}` | `admin.interest-categories.update` |
| DELETE | `/admin/interest-categories/{interestCategory}` | `admin.interest-categories.destroy` |

**Alur admin**
1. Buka **Kategori Minat** → daftar kategori aktif/nonaktif.
2. **Tambah kategori** (modal) → isi kode, nama, deskripsi, warna, urutan, status → Simpan.
3. **Edit** / **Nonaktifkan** bila tidak ingin dipakai soal baru; hapus hanya jika tidak dipakai soal.

- Seed awal 6 kategori RIASEC: R Teknik, I Teknologi & Sains, A Kreatif, S Sosial, E Bisnis, C Administrasi & Data (`InterestCategorySeeder`).
- Field: `kode` (max 5, unik), `nama`, `deskripsi`, `warna`, `urutan`, `is_active`.
- Select di form/filter memakai **Select2** (cari opsi).
- Hapus ditolak bila kategori masih dipakai soal: *"Kategori tidak dapat dihapus karena masih dipakai soal. Nonaktifkan saja."*
- Activity log: `interest-category.created|updated|deleted` (properties: `nama`).

### 6.19 Program Studi PCR

Resource `program-studi`. Fokus **Politeknik Caltex Riau** saja. Menu: **Data Master → Program Studi PCR**.

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/program-studi` | `admin.program-studi.index` |
| POST | `/admin/program-studi` | `admin.program-studi.store` |
| PUT/PATCH | `/admin/program-studi/{programStudi}` | `admin.program-studi.update` |
| DELETE | `/admin/program-studi/{programStudi}` | `admin.program-studi.destroy` |

**Alur admin**
1. Seed awal lewat `ProgramStudiPcrSeeder` (`is_verified = false`).
2. Verifikasi nama/jenjang di `https://pmb.pcr.ac.id`.
3. Edit prodi → centang kategori minat + relevansi 1–3 → set **Terverifikasi** + **Aktif**.
4. Filter daftar: search, jurusan, verified, active (Select2).

- Field utama: `institusi`, `nama`, `jenjang_pendidikan` (D3/D4), `jurusan`, `is_verified`, `is_active`, plus pivot kategori minat + relevansi 1–3.
- Rekomendasi siswa SMA **hanya** menampilkan prodi `is_active` dan `is_verified`.
- Activity log: `program-studi.created|updated|deleted`.

### 6.20 Bidang Karier (SMK)

Resource `bidang-karier` (model `CareerField`; **bukan** `career_infos` / Informasi Karier). Menu: **Data Master → Bidang Karier**.

| Method | URI | Nama route |
|---|---|---|
| GET | `/admin/bidang-karier` | `admin.bidang-karier.index` |
| POST | `/admin/bidang-karier` | `admin.bidang-karier.store` |
| PUT/PATCH | `/admin/bidang-karier/{careerField}` | `admin.bidang-karier.update` |
| DELETE | `/admin/bidang-karier/{careerField}` | `admin.bidang-karier.destroy` |

**Alur admin**
1. Seed awal lewat `CareerFieldSeeder` (13 bidang + Job Zone + contoh pekerjaan).
2. Tambah/Edit → isi nama, deskripsi, Job Zone 1–5, contoh pekerjaan (satu per baris), relasi kategori + relevansi.
3. Dipakai rekomendasi hasil asesmen untuk siswa **SMK**.

- Activity log: `bidang-karier.created|updated|deleted`.

---

## 7. Role: Guru BK

### 7.1 Login Guru BK

Form `/login?role=guru`. Lihat [2.4](#24-pencocokan-kredensial) untuk mekanisme pencocokan No HP/NIP.

Pengalihan setelah login:

| Kondisi | Pesan |
|---|---|
| Role tidak cocok | "Akun ini tidak sesuai dengan role yang dipilih. Silakan pilih role yang benar di landing page." |
| Status `pending` | "Akun Anda masih menunggu persetujuan admin." |
| Status `ditolak` | "Pendaftaran akun Anda ditolak. Silakan hubungi admin sekolah." |

Berhasil → `guru.dashboard`.

### 7.2 Dashboard Guru BK

Route: `GET /guru/dashboard` → `guru.dashboard`
Controller: `App\Http\Controllers\Guru\DashboardController@index`

**Header**
- Judul: "Dashboard Guru BK"
- Subjudul: "Kelola antrian konseling siswa dan pantau sesi yang perlu ditindaklanjuti."
- Tombol aksi: Kelola konseling · Laporan penilaian · Laporan angket · Buat tryout · Kelola rapor

**Tiga kartu metrik** — query dasar: `counselor_id IS NULL OR counselor_id = auth()->id()`

| Kartu | Kondisi | Deskripsi |
|---|---|---|
| Antrian baru | `status = 'pending'` | Permintaan konseling yang belum diproses. |
| Dijadwalkan | `status IN ('disetujui','dijadwalkan_ulang')` | Sesi yang sudah punya jadwal. |
| Selesai | `status = 'selesai'` | Sesi konseling yang sudah ditutup. |

**Jadwal Minggu Ini** — `counselor_id = auth()->id()`, status disetujui/dijadwalkan_ulang,
`consultation_date` antara hari ini dan +7 hari. Urut tanggal lalu jam. Limit 5.
Tiap entri: nama siswa, tanggal (format `d M`), jam, kategori kasus.
*Empty state:* "Belum ada jadwal minggu ini"

**Statistik Kategori Kasus** — `counselor_id = auth()->id()`, `case_category` tidak null,
`GROUP BY case_category`. Lebar bar = `min(total * 18, 100)` persen.

**Riwayat Siswa** — `counselor_id = auth()->id()`, `status = 'selesai'`, urut `consultation_date` terbaru. Limit 8.
Menampilkan Topik, Hasil, dan Tindak lanjut.

**Daftar Pengajuan Konseling** — `counselor_id = auth()->id()`,
status `pending`/`disetujui`/`dijadwalkan_ulang`, terbaru. Limit 20.
Tombol "Tindaklanjuti" → `guru.consultations.index?status={status}`

**Widget Rata-rata Skor Penilaian** — dari `penilaian_pelayanan` yang berelasi ke konseling
milik Guru ini berstatus `selesai`:

| Aspek | Sumber |
|---|---|
| Materi | `avg(skor_materi)` |
| Cara | `avg(skor_cara)` |
| Manfaat | `avg(skor_manfaat)` |

Semua dibulatkan 1 desimal. Overall = rata-rata dari ketiga nilai bulat tersebut.
Bar persen = (skor / 5) × 100.

Ambang predikat **pada widget dashboard**:

| Skor | Predikat |
|---|---|
| ≥ 4.5 | Sangat Baik |
| ≥ 3.5 | Baik |
| ≥ 2.5 | Cukup |
| selain itu | Perlu Perbaikan |

Widget menampilkan peringatan bila ada konseling selesai yang belum dinilai siswa:
> N sesi selesai belum dinilai siswa.

**Widget Progres Angket Siswa** (method `angketAggregate()`):

| Nilai | Arti |
|---|---|
| `total_soal` | jumlah `MasterQuestion` kategori `angket` dengan `is_active = true` |
| `siswa` | seluruh siswa yang kelasnya berada di sekolah milik Guru BK ini |
| `sudah` | jumlah siswa berbeda yang sudah punya respons angket untuk soal aktif |
| `belum` | siswa − sudah |
| `persen` | (sudah / siswa) × 100 |

Bar "Cakupan pengisian". *Empty state* bila `total_soal = 0`: "Belum ada soal angket aktif".

> **Perbedaan cakupan:** widget dashboard memakai cakupan **sekolah saja**, sedangkan
> modul Laporan Angket memakai `CounselorStudentService` (sekolah **atau** siswa yang punya
> riwayat konseling/rapor). Siswa dari sekolah lain yang pernah konseling terlihat di tabel
> Laporan Angket tetapi tidak terhitung di widget dashboard.

---

### 7.3 Menu Sidebar Guru BK (`config/navigation.php`, grup `guru`)

| Grup | Item | Route | Judul topbar |
|---|---|---|---|
| **Utama** | Dashboard | `guru.dashboard` | Dashboard |
| **Layanan BK** | Konseling | `guru.consultations.index` | Konseling |
| | Penilaian | `guru.penilaian.index` | Laporan Penilaian |
| | Angket | `guru.angket.index` | Laporan Angket |
| | Rapor BK | `guru.rapor.index` | Rapor BK |
| | Tryout | `guru.tryout.index` | Tryout |
| | Data Siswa | `guru.students.index` | Data Siswa |
| **Modul Tim Lain** | Soal Instrumen | `guru.instrument-questions.index` | Soal Instrumen |
| | Hasil Instrumen | `guru.instrument-results.index` | Hasil Instrumen |
| | Peta Sosiometri | `guru.sociometry.index` | Peta Sosiometri |
| | RPL | `guru.rpls.index` | RPL |
| | Jurnal Bulanan | `guru.journals.index` | Jurnal Bulanan |

Route `guru.feedback.index` **tidak terdaftar** di sidebar mana pun — hanya bisa diakses
lewat URL langsung, dan langsung redirect (lihat [7.14](#715-umpan-balik-layanan-legacy--deprecated)).

Semua route Guru berada pada `prefix: guru`, `name: guru.`, middleware `auth`, `verified`, `role:guru`.

---

### 7.4 Data Siswa

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/students` | `guru.students.index` |
| POST | `/guru/students` | `guru.students.store` |
| POST | `/guru/students/import` | `guru.students.import` |
| PUT | `/guru/students/{student}` | `guru.students.update` |
| DELETE | `/guru/students/{student}` | `guru.students.destroy` |

Controller: `App\Http\Controllers\Guru\StudentController`

> **Penting:** modul ini **tidak membuat akun Users** — hanya membuat baris tabel `students`.
> Akun login siswa harus dibuat admin dan dihubungkan lewat `user_id`
> (lihat [6.8](#68-data-siswa)).

**Daftar**
- Judul: "Data Login Siswa"
- Subjudul: "Masukkan NISN dan tanggal lahir siswa agar siswa dapat login ke dashboard."
- Tombol: "Import CSV" dan "Tambah siswa" (juga tampil di empty state)
- Query memakai `CounselorStudentService::queryForCounselor()` — hanya siswa dalam cakupan Guru ini
- **Filter:** `search` cocok ke `students.name`, `students.nisn`, `students.school`, `kelas.nama`, `kelas.sekolah.nama`
- **Kolom:** Nama | NISN | Tanggal Lahir | Kelas | Sekolah | Aksi (Edit, Delete)
  - Delete memakai konfirmasi JS `confirm("Hapus data siswa ini?")`

**Form tambah/edit**

| Field | Aturan |
|---|---|
| `name` | required, string, max 255 |
| `nisn` | required, string, max 20, unik `students.nisn` (ignore saat edit) — "NISN sudah digunakan siswa lain." |
| `birth_date` | required, date, before:today — "Tanggal lahir harus valid dan sebelum hari ini." |
| `school` | optional, string, max 255 (teks bebas) |
| `kelas_id` | lihat scoping di bawah |

**Scoping `kelas_id`:**

| Kondisi Guru | Aturan |
|---|---|
| Punya profil dengan `sekolah_id` terisi | required, integer, harus kelas yang diizinkan — required: "Pilih kelas BK agar siswa masuk cakupan sekolah Anda."; in: "Kelas tidak valid untuk sekolah Anda." |
| Tidak punya `sekolah_id` | nullable, integer, harus kelas yang diizinkan — in: "Kelas tidak valid untuk sekolah Anda." |

**Sisi efek & guard**

| Aksi | Guard | Log | Properties | Flash |
|---|---|---|---|---|
| Store | — | `siswa.created` | `nama`, `nisn` | "Data NISN dan tanggal lahir siswa berhasil disimpan." |
| Update | `abort_unless(CounselorStudentService::canAccess($student, auth()->user()), 403)` | `siswa.updated` | `nama`, `nisn` | "Data siswa berhasil diperbarui." |
| Destroy | idem | `siswa.deleted` (ditulis **sebelum** delete) | `nama`, `nisn` | "Data siswa berhasil dihapus." |

**Import CSV**

| Item | Nilai |
|---|---|
| Field | `csv_file` |
| Rules | `required`, `file`, `mimes:csv,txt`, `max:2048` |
| Pesan | "Pilih file CSV terlebih dahulu." / "File harus berformat CSV." |

Nama header dinormalisasi: lowercase, trim, spasi diubah menjadi underscore.

Kolom yang didukung:

| Kolom | Alias |
|---|---|
| `nama` | `name` |
| `nisn` | — |
| `tanggal_lahir` | `birth_date` |
| `sekolah` | `school` |
| `kelas_id` | opsional |

Format tanggal diterima (dicoba berurutan): `Y-m-d`, `d/m/Y`, `d-m/Y`. Bila tidak cocok ke
tiga format, baris dilewati.

**Aturan per baris:**
- Baris tanpa nama, tanpa NISN, atau tanpa tanggal lahir → dilewati (`skipped++`)
- `kelas_id` di luar kelas sekolah Guru → dipaksa `null`
- NISN sudah ada → update (`diperbarui++`)
- NISN baru → create (`baru++`)

Penyimpanan memakai `Student::updateOrCreate(['nisn' => ...], payload)`. Pembaruan hanya
menyalin kolom yang tidak `null`, sehingga kolom yang tidak ada di CSV tidak menimpa nilai lama.

**Log:** `siswa.imported` (subject = `null`) dengan properties
`file` (nama file), `baru`, `diperbarui`, `dilewati`.

**Flash:** "Import selesai. X data baru, Y data diperbarui, Z baris dilewati."

---

### 7.5 Konseling

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/consultations` | `guru.consultations.index` |
| GET | `/guru/consultations/events` | `guru.consultations.events` |
| PATCH | `/guru/consultations/{consultation}/approve` | `guru.consultations.approve` |
| PATCH | `/guru/consultations/{consultation}/reject` | `guru.consultations.reject` |
| PATCH | `/guru/consultations/{consultation}/schedule` | `guru.consultations.schedule` |
| PATCH | `/guru/consultations/{consultation}/report` | `guru.consultations.report` |
| GET | `/guru/consultations/{consultation}/print` | `guru.consultations.print` |

Controller: `App\Http\Controllers\Guru\ConsultationController`

#### Status & kategori (`app/Models/ConsultationRequest.php`)

| Konstanta | Nilai | Label |
|---|---|---|
| `STATUS_PENDING` (alias `STATUS_MENUNGGU`) | `pending` | Menunggu |
| `STATUS_APPROVED` (alias `STATUS_DIJADWALKAN`) | `disetujui` | Disetujui |
| `STATUS_REJECTED` | `ditolak` | Ditolak |
| `STATUS_RESCHEDULED` | `dijadwalkan_ulang` | Dijadwalkan ulang |
| `STATUS_SELESAI` | `selesai` | Selesai |

Kategori kasus (`CASE_CATEGORIES`): `pribadi` → Pribadi · `sosial` → Sosial ·
`belajar` → Belajar · `karier` → Karier · `kedisiplinan` → Kedisiplinan

Helper model:

| Method | Perilaku |
|---|---|
| `caseCategoryLabel()` | label kategori, atau nilai mentah, atau `-` |
| `statusLabel()` | label status, atau nilai mentah |
| `isSchedulable()` | true bila status ∈ (pending, disetujui, dijadwalkan_ulang) |
| `canBeRejected()` | true bila status ∈ (pending, disetujui) |
| `belongsToCounselor($id)` | true bila `counselor_id` null **atau** = `$id` |

Kolom tabel `consultation_requests`: `student_id`, `counselor_id`, `subject`, `case_category`,
`preferred_time`, `preferred_date`, `consultation_date`, `consultation_time`, `details`,
`status`, `rejection_reason`, `scheduled_at`, `notes`, `result`, `evaluation`, `follow_up`.
Casts: `consultation_date` dan `preferred_date` = `date`, `scheduled_at` = `datetime`.

#### Halaman Daftar

- **Kalender Jadwal Konseling** — "Visualisasi sesi yang sudah dijadwalkan."
  FullCalendar 6.1.15 (CDN), locale `id`, `initialView: dayGridMonth`,
  toolbar `prev,next today | title | dayGridMonth,timeGridWeek,listWeek`.
  Sumber event: `guru.consultations.events`.
- **Daftar "Minggu ini"** — kartu berisi nama siswa, tanggal (`d M`), jam, kategori kasus.
- **Filter (GET):**
  - `search` — LIKE pada `subject`, `details`, `student.name`, `studentProfile.nisn`, `counselor.name`
  - `status` — dropdown dari `filterableStatuses()`
  - `kategori` — dropdown dari `CASE_CATEGORIES`
  - Tombol "Filter" + komponen reset filter
- **Kolom tabel:** Siswa | Kelas | Topik | Kategori | Jadwal | Status | Aksi
  - Kolom Siswa: nama siswa + "Guru: {nama}" bila sudah ada counselor, atau badge amber "Belum ditugaskan" bila `counselor_id` null
  - Kolom Jadwal: tanggal + jam (5 karakter pertama `consultation_time`) bila `consultation_date` terisi; jika belum, tampilkan `preferred_time` dan `preferred_date`
- *Empty state:* "Belum ada pengajuan" / "Pengajuan konseling siswa akan muncul di tabel ini."

**Tombol aksi per baris (kondisi di view)**

| Aksi | Tampil bila |
|---|---|
| Detail | selalu — modal read-only |
| Setujui | status = `pending` |
| Tolak | status = `pending` (tombol solid) atau `canBeRejected()` (tombol outline) |
| Jadwal | `isSchedulable()` |
| Laporan | status ∈ (`disetujui`, `dijadwalkan_ulang`) |
| PDF | `result` terisi |

Modal memakai Alpine.js dengan state `detailOpen`, `scheduleOpen`, `reportOpen`, `rejectOpen`.
Form yang gagal validasi otomatis membuka kembali modal yang sesuai.

**Isi modal**

- **Detail Konseling:** Siswa, Kelas, Status, Kategori, Guru BK, Preferensi siswa, Jadwal sesi, Diajukan, Detail siswa, Catatan jadwal, Alasan ditolak (kartu merah bila ada), Hasil, Evaluasi, Tindak lanjut
- **Tolak pengajuan:** textarea `rejection_reason` (required)
- **Penjadwalan:** `consultation_date` (date, required), `consultation_time` (time, required), hidden `student_id`, info nama siswa, textarea `notes` — catatan "Sistem mengecek bentrok jadwal otomatis."
- **Laporan:** select `case_category` (required), textarea `result` (required), textarea `evaluation` (required), textarea `follow_up` (opsional)

#### Endpoint Kalender

Service: `ConsultationScheduleService@calendarEventsForCounselor()`

Hanya status `disetujui`, `dijadwalkan_ulang`, `selesai` yang jadi event, dan hanya bila
`consultation_date` serta `consultation_time` terisi.

Format JSON tiap event:

```json
{
  "id": 12,
  "title": "Nama Siswa - Topik",
  "start": "2026-03-10 09:00:00",
  "end": "2026-03-10 09:00:00",
  "backgroundColor": "#10b981",
  "borderColor": "transparent",
  "extendedProps": { "status": "disetujui", "statusLabel": "Disetujui" }
}
```

| Status | Warna |
|---|---|
| `selesai` | `#3b82f6` |
| `dijadwalkan_ulang` | `#f59e0b` |
| selain itu | `#10b981` |

#### Aksi 1 — Setujui (`PATCH approve`)

- **Guard:** `abort_unless(status === 'pending', 422)`
- **Update:** `counselor_id = auth()->id()`, `status = 'disetujui'`
- **Log:** `consultation.approved` (properties kosong → disimpan `null`)
- **Flash:** "Pengajuan konseling berhasil disetujui."

#### Aksi 2 — Tolak (`PATCH reject`)

Form request: `App\Http\Requests\Guru\RejectConsultationRequest`
- `authorize()`: role harus `guru`
- `rejection_reason`: required, string, max 1000 — "Alasan penolakan wajib diisi."
- **Guard 1:** `abort_unless(canBeRejected(), 422)`
- **Guard 2:** `abort_unless(belongsToCounselor(auth()->id()), 403)`
- **Update:** `counselor_id = auth()->id()`, `status = 'ditolak'`, `rejection_reason`
- **Log:** `consultation.rejected` (properties kosong → `null`)
- **Flash:** "Pengajuan konseling ditolak."

#### Aksi 3 — Jadwal / Jadwal Ulang (`PATCH schedule`)

Form request: `App\Http\Requests\Guru\ScheduleConsultationRequest`

| Field | Aturan |
|---|---|
| `consultation_date` | required, date, after_or_equal:today |
| `consultation_time` | required, date_format:H:i |
| `student_id` | required, exists `users,id` |
| `notes` | nullable, string, max 1000 |

`after()` memeriksa bentrok memakai `ConsultationScheduleService@hasConflict()`:
- `counselor_id` yang sama
- status ∈ (`disetujui`, `dijadwalkan_ulang`) — `selesai` **tidak** dihitung
- `whereDate(consultation_date)` sama
- `consultation_time` dinormalisasi (dipotong 5 karakter, ditambah `:00` bila panjang < 5)
- record yang sedang diedit dikecualikan

Bila bentrok, `ValidationException` pada field `consultation_time`:
> Jadwal bentrok dengan sesi konseling lain pada tanggal dan jam yang sama.

**Guard:** `abort_unless(isSchedulable(), 422)` dan `abort_unless(belongsToCounselor(auth()->id()), 403)`

`hadSchedule` = `consultation_date !== null` **dan** status ∈ (`disetujui`, `dijadwalkan_ulang`).

**Update:** `student_id`, `counselor_id`, `consultation_date`, `consultation_time`, `notes`
(atau `null`), `scheduled_at` dari service. Status menjadi `dijadwalkan_ulang` bila
`hadSchedule`, else `disetujui`.

| Kondisi | Log | Flash |
|---|---|---|
| `hadSchedule = true` | `consultation.rescheduled` | "Jadwal konseling berhasil diperbarui (dijadwalkan ulang)." |
| `hadSchedule = false` | `consultation.scheduled` | "Jadwal konseling berhasil disimpan." |

> `student_id` hanya divalidasi dengan `exists:users,id` dan **tidak** membatasi apakah siswa
> tersebut berada dalam cakupan Guru. Nilai ini berasal dari hidden input form.

#### Aksi 4 — Laporan / Penyelesaian (`PATCH report`)

Form request: `App\Http\Requests\Guru\StoreConsultationReportRequest`

| Field | Aturan |
|---|---|
| `case_category` | required, in `CASE_CATEGORIES` |
| `result` | required, string, max 3000 |
| `evaluation` | required, string, max 3000 |
| `follow_up` | nullable, string, max 3000 |

**Guard — hanya satu:**
```php
abort_unless($consultation->counselor_id === auth()->id(), 403);
```

> **Tidak ada pemeriksaan status sama sekali** — berbeda dari `approve`, `reject`, dan
> `schedule` yang punya guard status 422. Lihat [Known Issues](#113-report-konseling-tanpa-guard-status).

- **Update:** seluruh field tervalidasi + `status = 'selesai'`
- **Log:** `consultation.completed` (properties kosong → `null`)
- **Flash:** "Laporan konseling berhasil disimpan."

#### Aksi 5 — Cetak PDF (`GET print`)

- **Guard:** `counselor_id === auth()->id()` **atau** role user = `admin` (`abort_unless`, 403)
- Paper A4, mode `stream()`, nama file `laporan-konseling-{id}.pdf` (dibuka di tab baru)
- Isi PDF: judul "LAPORAN KONSELING INDIVIDU"; tabel Nama Siswa, Kelas, Sekolah, Guru BK,
  Topik, Kategori, Jadwal; seksi Hasil Konseling, Evaluasi, Tindak Lanjut
- **Tidak ada activity log** untuk pencetakan.

#### Matriks Status → Aksi

| Status | Setujui | Tolak | Jadwal | Laporan (UI) | Laporan (PATCH langsung) | PDF otomatis |
|---|---|---|---|---|---|---|
| `pending` | ya | ya | ya | tidak | tidak (403, unassigned) | tidak |
| `disetujui` | tidak | ya | ya → dijadwalkan_ulang | ya | ya | tidak |
| `dijadwalkan_ulang` | tidak | tidak | ya → dijadwalkan_ulang | ya | ya | tidak |
| `ditolak` | tidak | tidak | tidak | tidak | **ya** (Guru penolak) | tidak |
| `selesai` | tidak | tidak | tidak | tidak | **ya** (timpa laporan) | ya* |

---

### 7.6 Laporan Penilaian Layanan

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/penilaian` | `guru.penilaian.index` |

- Judul: "Laporan Penilaian Layanan"
- Subjudul: "Ringkasan penilaian siswa untuk konseling yang sudah selesai."
- Topbar: "Laporan Penilaian"

**Filter:** `bulan` (dropdown 1–12 dengan nama bulan Indonesia, default bulan sekarang),
`tahun` (default tahun sekarang, rentang tahun sekarang s.d. tahun sekarang − 3),
tombol "Terapkan filter" + reset filter.

**Cakupan query:** `counselor_id = auth()->id()`, `status = 'selesai'`, difilter periode dengan
urutan prioritas: `scheduled_at` (bila tidak null) → `consultation_date` (bila tidak null) → `updated_at`.

**Empat kartu ringkasan:**

| Kartu | Nilai |
|---|---|
| Rata-rata Materi | `avg(skor_materi)`, 1 desimal — deskripsi "Skor 1-5" |
| Rata-rata Cara | `avg(skor_cara)` |
| Rata-rata Manfaat | `avg(skor_manfaat)` |
| Overall | rata-rata dari ketiga nilai — "{total_dinilai} / {total_konseling} dinilai" |

**Tabel "Detail penilaian"** — Tanggal | Nama Siswa | Kelas | Skor Materi | Skor Cara | Skor Manfaat | Rata-rata | Predikat

- Tanggal: `scheduled_at` → `consultation_date` → `updated_at` (format `d M Y`)
- Skor kosong ditampilkan `-`
- Predikat dari `PenilaianPelayanan`:

| Rata-rata | Predikat | Warna |
|---|---|---|
| ≥ 4.5 | Sangat Baik | hijau |
| ≥ 3.5 | Baik | biru |
| ≥ 2.5 | Cukup | amber |
| selainnya | Kurang | merah |

> **Perbedaan fallback:** model `PenilaianPelayanan` memakai fallback **"Kurang"**,
> sedangkan widget dashboard memakai fallback **"Perlu Perbaikan"**.

*Empty state:* "Tidak ada data" / "Belum ada konseling selesai pada bulan dan tahun yang dipilih."

---

### 7.7 Laporan Angket BK

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/angket` | `guru.angket.index` |
| GET | `/guru/angket/{student}` | `guru.angket.show` |
| GET | `/guru/angket/{student}/pdf` | `guru.angket.pdf` |

**Sumber soal aktif** (`App\Support\AngketQuestions`)
- `activeIds()` — `MasterQuestion` kategori `angket`, `is_active = true`, `orderBy id`
- `activeCount()` — jumlah id tersebut
- Query di-cache dengan `once()`

Soal angket dikelola admin lewat menu Master Pertanyaan, **bukan** dari menu Guru.

**Daftar**
- Judul: "Laporan Angket BK"
- Subjudul: "Progress angket siswa di sekolah Anda atau yang pernah konseling dengan Anda."
- Cakupan: `CounselorStudentService::queryForCounselor(auth()->user())` — sekolah Guru **atau** siswa yang punya riwayat konseling/rapor dengan Guru
- **Filter:** `search` cocok ke `students.name`, `students.nisn`, relasi `user.name`, `kelas.nama`
- `withCount` relasi `responsAngket` sebagai `total_dijawab`, **dibatasi pada id soal angket aktif**.
  Bila tidak ada soal aktif, memakai `whereRaw('0 = 1')` sehingga hasilnya 0.
- `AngketProgress::predikat(total_dijawab, totalSoalAktif)`:

| Kondisi | Predikat |
|---|---|
| total 0 | Belum Ada Soal |
| ≥ 80 persen | Lengkap (hijau) |
| ≥ 50 persen | Sebagian (amber) |
| selainnya | Belum Lengkap (merah) |

- **Kolom:** Nama Siswa | Kelas (+ nama sekolah) | Dijawab / Total | Progress (bar + persen) | Predikat | Aksi (Detail, Download PDF)
- *Empty state:* "Belum ada siswa dalam cakupan" / "Hubungkan profil Guru BK ke sekolah atau tunggu siswa mengajukan konseling."

**Detail (`show`)**
- **Guard:** `abort_unless(CounselorStudentService::canAccess($student, auth()->user()), 403)`
- Judul: "Detail Angket Siswa" — "Jawaban angket BK per pertanyaan."
- Badge nama, kelas, sekolah, dan predikat
- **Tabel:** No | Pertanyaan | Jawaban — hanya soal angket aktif yang sudah terjawab
- *Empty state:* "Belum ada jawaban" / "Siswa belum mengisi angket BK."
- Tombol: "Download PDF" dan "Kembali ke daftar"

**Export PDF (`exportPdf`)**
- **Guard:** sama seperti `show` (403)
- A4 portrait, mode `download()`, nama file `angket-{slug-nama}-{Ymd}.pdf`
- Isi: judul "LAPORAN ANGKET BIMBINGAN KONSELING"; info Nama, Kelas, Sekolah, Tanggal Cetak,
  Total Soal, Dijawab; tabel No | Pertanyaan | Jawaban; blok Predikat di bagian bawah
- **Log:** `angket.pdf.downloaded` dengan properties `nama`, `predikat`, `total_dijawab`

---

### 7.8 Rapor BK

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/rapor` | `guru.rapor.index` |
| GET | `/guru/rapor/{student}/edit` | `guru.rapor.edit` |
| PUT | `/guru/rapor/{student}` | `guru.rapor.update` |
| GET | `/guru/rapor-cetak/{rapor}/pdf` | `guru.rapor.pdf` |

Controller: `App\Http\Controllers\Guru\RaporController` · Service: `App\Services\RaporBkService`

**Periode**

| Semester | Label |
|---|---|
| `ganjil` | Semester Ganjil |
| `genap` | Semester Genap |

| Status | Label |
|---|---|
| `draft` | Draft |
| `final` | Final |

Default periode (`App\Models\RaporBk`):
- `defaultSemester()` — `now()->month <= 6 ? 'genap' : 'ganjil'`
- `defaultTahunAjaran()` — bulan ≥ 7 → `{Y}/{Y+1}`, bulan ≤ 6 → `{Y-1}/{Y}`

**Daftar**
- Judul: "Rapor BK"
- Subjudul: "Siswa di sekolah Anda atau yang pernah konseling / memiliki rapor dengan Anda."
- **Filter:** `semester` (ganjil/genap), `tahun_ajaran` (regex `\d{4}/\d{4}`, placeholder "2025/2026"), `search` (nama/NISN/nama akun)
- Relasi `raporBk` difilter `counselor_id + semester + tahun_ajaran` sehingga tabel ikut terfilter sesuai pilihan
- **Kolom:** Nama Siswa | Kelas (+ sekolah) | Status Rapor | Aksi
  - Status Rapor: `statusLabel()` dari rapor, atau "Belum ada" bila rapor null; `final` hijau, `draft` amber
  - Aksi: "Buat"/"Edit" sesuai ada/tidaknya rapor pada periode; "PDF" hanya bila rapor sudah ada
- *Empty state:* "Belum ada siswa dalam cakupan" / "Pastikan profil Guru BK terhubung ke sekolah, atau ada siswa yang pernah konseling dengan Anda."

**Form (`edit`)**
- **Guard:** `abort_unless(CounselorStudentService::canAccess($student, auth()->user()), 403)`
- Judul: "Rapor BK - {nama siswa}" — "Periode {Semester} - {tahun_ajaran}"
- Baris identitas: Kelas, Sekolah, NISN
- **Kotak ringkasan (Phase 3-4):** "{total_konseling} sesi selesai, {total_dinilai} penilaian siswa, rata-rata {x}/5."

| Field | Aturan |
|---|---|
| `perkembangan_akademik` | nullable, string, max 5000 |
| `perkembangan_sosial` | nullable, string, max 5000 |
| `perkembangan_psikologis` | nullable, string, max 5000 |
| `saran_tindak_lanjut` | nullable, string, max 5000 |
| `catatan_guru` | nullable, string, max 2000 |
| `status` | required, in `STATUSES` (default `draft`) |
| `semester` | required, in `SEMESTERS` (hidden input) |
| `tahun_ajaran` | required, string, regex `/^\d{4}\/\d{4}$/` (hidden input) |

**Penyimpanan** — `RaporBkService::upsertForStudent()` memakai `updateOrCreate` dengan kunci
`student_id + counselor_id + semester + tahun_ajaran`. Menyimpan ulang untuk periode yang sama
**menimpa**, bukan menambah. Isi laporan pada periode berbeda tetap terpisah.

- **Log:** `rapor_bk.saved` dengan properties `student_id`, `status`
- **Redirect:** `guru.rapor.index` dengan query `semester` dan `tahun_ajaran`
- **Flash:** "Rapor BK berhasil disimpan."

**Export PDF**
- **Guard:** `abort_unless($rapor->counselor_id === auth()->id(), 403)`
- A4 portrait, mode `download()`, nama file `rapor-bk-{slug-nama}-{semester}-{YYYY-YYYY}.pdf`
- Isi: judul "RAPOR BIMBINGAN KONSELING"; tabel Nama, NISN, Kelas, Sekolah, Semester, Tahun Ajaran,
  Guru BK, Status, Tanggal Cetak; baris ringkasan konseling; lima seksi pengembangan; footer tanggal cetak
- **Tidak ada activity log.**

---

### 7.9 Tryout

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/tryout` | `guru.tryout.index` |
| GET | `/guru/tryout/buat` | `guru.tryout.create` |
| POST | `/guru/tryout` | `guru.tryout.store` |
| GET | `/guru/tryout/{tryout}` | `guru.tryout.show` |
| GET | `/guru/tryout/{tryout}/edit` | `guru.tryout.edit` |
| PUT | `/guru/tryout/{tryout}` | `guru.tryout.update` |
| DELETE | `/guru/tryout/{tryout}` | `guru.tryout.destroy` |

Controller: `App\Http\Controllers\Guru\TryoutController` · Service: `App\Services\TryOutService`

**Status:** `draft` → Draft · `aktif` → Aktif · `selesai` → Selesai

Tabel utama `try_outs`, pivot kelas `try_out_kelas`, detail `try_out_detail`. Kolom `soal_ids`
di-cast sebagai array.

| Helper model | Perilaku |
|---|---|
| `hasSubmissions()` | apakah sudah ada baris di `try_out_detail` |
| `isActiveNow()` | status `aktif` **dan** `now` berada di antara `mulai_at` dan `selesai_at` |

**Daftar**
- Judul: "Tryout BK" — "Buat dan pantau hasil tryout per kelas."
- Tombol: "Buat tryout"
- Cakupan: hanya tryout dengan `counselor_id = auth()->id()`
- **Kolom:** Judul | Periode (`mulai_at` – `selesai_at`) | Kelas | Peserta (`details_count`) | Status | Aksi (Hasil, Edit, Hapus)
  - Tombol "Hapus" **hanya** tampil bila `details_count = 0`
- *Empty state:* "Belum ada tryout" / "Buat tryout pertama untuk siswa di kelas yang ditugaskan."

**Form buat/edit**
- Judul buat: "Buat Tryout" · Judul edit: "Edit Tryout"
- Subjudul: "Pilih kelas, soal tryout dari master, dan jadwal pengerjaan."
- **Opsi kelas:** `Kelas::orderBy('nama')`, difilter `sekolah_id` Guru bila ada.
  Kosong → "Belum ada kelas di sekolah Anda."
- **Opsi soal:** `MasterQuestion` kategori `tryout`, `is_active`, `orderBy id`.
  Kosong → "Belum ada soal tryout aktif." (dilengkapi ajakan mengelola Master Pertanyaan ke admin)

**Validasi (`validateTryout`)**

| Field | Aturan |
|---|---|
| `judul` | required, string, max 255 |
| `deskripsi` | nullable, string, max 2000 |
| `durasi_menit` | required, integer, min 5, max 180 |
| `mulai_at` | required, date |
| `selesai_at` | required, date, after `mulai_at` |
| `status` | required, in `STATUSES` |
| `kelas_ids` | required, array, min 1 |
| `kelas_ids.*` | integer, exists `kelas,id` |
| `soal_ids` | required, array, min 1 |
| `soal_ids.*` | integer, exists `master_questions,id` |

Tidak ada aturan `distinct` pada `kelas_ids` maupun `soal_ids`.

**Scoping kelas tambahan** — bila `kelas_ids` tidak kosong, dibandingkan dengan daftar kelas
yang diizinkan. Bila ada selisih, `ValidationException` pada field `kelas_ids`:
> Satu atau lebih kelas tidak termasuk sekolah Anda.

**Validasi kunci (locked)** — bila tryout sudah punya submission (`hasSubmissions()`):
- Aturan `kelas_ids` dan `soal_ids` **tidak dipakai**; keduanya diambil dari data tryout yang sudah ada
- `TryOutService::updateForCounselor()` tidak mengubah `soal_ids` dan tidak menyinkronkan ulang kelas
- View menampilkan peringatan:
  - "Kelas tidak dapat diubah karena sudah ada siswa yang mengumpulkan jawaban."
  - "Daftar soal tidak dapat diubah setelah ada pengumpulan jawaban."

**Sisi efek**

| Aksi | Guard | Log (properties) | Flash |
|---|---|---|---|
| Create | scoping kelas | `tryout.created` (`judul`) | "Tryout berhasil dibuat." |
| Update | `abort_unless($tryout->counselor_id === auth()->id(), 403)` | `tryout.updated` (`judul`) | "Tryout berhasil diperbarui." |
| Delete | idem | `tryout.deleted` (`judul`) — ditulis **sebelum** delete | "Tryout berhasil dihapus." |

Bila service delete melempar `RuntimeException` karena ada submission, entri log
`tryout.deleted` **sudah tertulis** meskipun tryout tidak terhapus. Pesan pada field `tryout`:
> Tryout tidak dapat dihapus karena sudah ada jawaban siswa.

**Hasil (`show`)**
- **Guard:** `abort_unless($tryout->counselor_id === auth()->id(), 403)`
- Judul: judul tryout · subjudul "Rata-rata keseluruhan: {x}"
- Baris: periode, durasi menit, daftar kelas
- Tombol: "Kembali", "Edit tryout", "Buat tryout baru"
- **Tabel "Hasil per siswa":** Siswa | Kelas | Rata skor | Dikumpulkan
- *Empty state:* "Belum ada peserta" / "Hasil muncul setelah siswa mengumpulkan tryout."

**Skoring (`TryOutService::hitungRataSkor()`)** — dipakai sisi siswa:

| Tipe soal | Nilai |
|---|---|
| `skala` | dikunci 1–5, dikali 20 |
| selainnya | 80 bila `strlen(jawaban) >= 3`, selain itu 40 |

Rata-rata dihitung dari soal yang terjawab saja. Disimpan di `try_out_detail.rata_skor`.

`siswaBisaAkses()` mensyaratkan `isActiveNow()` **dan** kelas siswa terdaftar pada tryout.
Submission ganda dicegah dengan `updateOrCreate` + 403.

---

### 7.10 Soal Instrumen Asesmen

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/instrument-questions` | `guru.instrument-questions.index` |
| POST | `/guru/instrument-questions` | `guru.instrument-questions.store` |
| PUT | `/guru/instrument-questions/{question}` | `guru.instrument-questions.update` |
| DELETE | `/guru/instrument-questions/{question}` | `guru.instrument-questions.destroy` |

Controller: `App\Http\Controllers\Guru\InstrumentQuestionController`  
Form Request: `StoreInstrumentQuestionRequest` / `UpdateInstrumentQuestionRequest`

**Kategori (`InstrumentQuestion::CATEGORIES`)**

| Key | Label |
|---|---|
| `minat_bakat` | Minat Bakat |
| `gaya_belajar` | Gaya Belajar |
| `kepribadian` | Kepribadian |
| `sosiometri` | Sosiometri |
| `angket_masalah` | Masalah |

**Alur Guru — kelola soal Minat Bakat**
1. Pastikan Admin sudah punya **Kategori Minat** aktif (seed RIASEC atau CRUD).
2. Buka **Soal Instrumen** → Filter kategori = Minat Bakat (Select2 + pencarian).
3. **Tambah soal** (modal Alpine `modalCrud`) → pilih jenis **Minat Bakat**.
4. Isi wajib: **Kategori Minat**, **Target jenjang** (`semua`/`SMA`/`SMK`), **Bobot** 1–5.
5. Atur opsi (2–6 baris) atau klik **Pakai template Likert 0–4**.
6. Simpan. Soal tanpa kategori minat tampil badge **Belum dikategorikan** dan **tidak** ikut scoring RIASEC.
7. Seed 30 soal awal: `MinatQuestionSeeder` (`bobot = 1`, `jenjang_target = semua`).

**Daftar**
- Judul: "Soal Instrumen Asesmen"
- Tombol: "Tambah soal" / "Edit" memakai `Alpine.data('modalCrud')` + re-init Select2 saat modal dibuka
- **Filter:** `category`, `interest_category_id`, `jenjang_target` (Select2)
- **Kolom:** Kategori instrumen | Soal | Kategori minat | Jenjang | Bobot | Status | Aksi

**Form**

| Field | Aturan |
|---|---|
| `category` | required, in `CATEGORIES` |
| `question` | required, string, max 1000 |
| `is_active` | checkbox/boolean, nullable (default true) |
| `options` | required, array, min 2, max 6 |
| `options.*.label` | required, string, max 255 |
| `options.*.score` | integer, min 0, max 100 |
| `interest_category_id` | **wajib** bila `category = minat_bakat` (kategori aktif) |
| `jenjang_target` | `semua` / `SMA` / `SMK` (default `semua`) |
| `bobot` | integer 1–5 (default 1); pengali skor opsi |

Options dinormalisasi menjadi array `{ label: string, score: int }`. UI Alpine: tambah/hapus opsi + template Likert 0–4. Field minat **hanya** muncul bila kategori = `minat_bakat` (kategori lain tidak berubah perilakunya).

**Sisi efek**

| Aksi | Log | Flash |
|---|---|---|
| Store (`created_by = auth()->id()`) | `instrument-question.created` | "Soal instrumen berhasil ditambahkan." |
| Update | `instrument-question.updated` | "Soal instrumen berhasil diperbarui." |
| Destroy | `instrument-question.deleted` | "Soal instrumen berhasil dihapus." |

Hapus: **soft delete** bila soal sudah punya jawaban; selain itu force delete.

---

### 7.11 Hasil Instrumen

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/instrument-results` | `guru.instrument-results.index` |

- Judul: "Hasil Skoring Instrumen"
- **Filter:** `category` (Select2)
- Urutan: `latest("submitted_at")`
- **Kartu per submission:** badge kategori, nama siswa, waktu, kelas/sekolah, `total_score`, `result_label`, `result_description`
- Untuk `minat_bakat`: tampilkan juga **Kode Minat** (`kode_minat`) dan **jenjang** snapshot bila ada
- *Empty state:* "Belum ada hasil"

> **Tidak ada scoping per sekolah/Guru (P1)** — seluruh submission terlihat. Berbeda dengan Angket/Rapor yang memakai `CounselorStudentService`.

---

### 7.12 Peta Sosiometri

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/sociometry` | `guru.sociometry.index` |

Controller: `App\Http\Controllers\Guru\SociometryMapController`

**Query:** users role `siswa` dan status `disetujui`, `withCount("receivedSociometryChoices")`,
diurutkan jumlah pilihan menurun lalu nama menaik.

| Kelompok | Definisi |
|---|---|
| `popular` | siswa dengan jumlah pilihan > 0, diambil 5 teratas |
| `isolated` | siswa dengan jumlah pilihan 0 |
| `responses` | seluruh `SociometryResponse`, `latest("submitted_at")` |

**Tiga blok tampilan**

1. **"Siswa Populer"** — "Urutan berdasarkan jumlah dipilih." Kartu nama siswa + jumlah pilihan.
2. **"Siswa Terisolasi"** — "Siswa yang belum dipilih oleh teman lain."
3. **"Relasi Sosiometri"** (tabel) — Pemilih | Dipilih | Relasi | Alasan.
   Relasi memakai `SociometryResponse::TYPES[relation_type]`.

> **Tidak ada scoping per sekolah/Guru**, tidak ada pagination, tidak ada activity log.

---

### 7.13 RPL (Rencana Pembelajaran)

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/rpls` | `guru.rpls.index` |
| POST | `/guru/rpls` | `guru.rpls.store` |
| PUT | `/guru/rpls/{rpl}` | `guru.rpls.update` |
| DELETE | `/guru/rpls/{rpl}` | `guru.rpls.destroy` |
| GET | `/guru/rpls/{rpl}/print` | `guru.rpls.print` |

**Jenis:** `individu` → Individu · `kelompok` → Kelompok

**Daftar**
- Judul: "RPL" — "Susun dan kelola RPL layanan individu maupun kelompok."
- Tombol: "Tambah RPL"
- **Filter:** `type` (individu/kelompok)
- **Kartu RPL:** badge jenis, judul, "Sasaran / tanggal" dengan fallback
  ("Sasaran belum diisi" bila `target` kosong, "Tanggal fleksibel" bila `service_date` kosong),
  tombol "Cetak PDF" (target `_blank`), tombol "Edit", blok Tujuan/Materi/Metode/Evaluasi
  (dipotong 3 baris), form "Hapus RPL" dengan konfirmasi `confirm("Hapus RPL ini?")`

**Validasi**

| Field | Aturan |
|---|---|
| `title` | required, string, max 255 |
| `type` | required, in `TYPES` |
| `service_date` | nullable, date |
| `target` | nullable, string, max 255 |
| `tujuan` | required, string, max 3000 |
| `materi` | required, string, max 3000 |
| `metode` | required, string, max 3000 |
| `evaluasi` | required, string, max 3000 |

**Guard** update/destroy/print: `abort_unless($rpl->teacher_id === auth()->id(), 403)`

**Flash:** "RPL berhasil dibuat." / "… diperbarui." / "… dihapus." · **Tidak ada activity log.**

**Cetak PDF:** view `guru/rpls/print.blade.php`, A4, mode `stream()`, nama file `rpl-{id}.pdf`

---

### 7.14 Jurnal Bulanan BK

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/journals` | `guru.journals.index` |
| POST | `/guru/journals` | `guru.journals.store` |
| PUT | `/guru/journals/{journal}` | `guru.journals.update` |
| DELETE | `/guru/journals/{journal}` | `guru.journals.destroy` |
| GET | `/guru/journals/{journal}/print` | `guru.journals.print` |

Controller: `App\Http\Controllers\Guru\MonthlyJournalController`

**Daftar**
- Judul: "Jurnal Bulanan BK" — "Catat rekap layanan bulanan dan export ke PDF."
- Tombol: "Tambah Jurnal"
- Urutan: tahun lalu bulan, menurun
- **Kartu jurnal:** periode (`translatedFormat('F Y')`), judul,
  ringkasan "Individu {n} | Kelompok {n} | Klasikal {n}", tombol "PDF" (target `_blank`),
  tombol "Edit", ringkasan 3 baris, form "Hapus" dengan konfirmasi `confirm("Hapus jurnal ini?")`

**Validasi**

| Field | Aturan |
|---|---|
| `month` | required, integer, min 1, max 12 |
| `year` | required, integer, min 2020, max 2100 |
| `title` | required, string, max 255 |
| `individual_services` | required, integer, min 0 |
| `group_services` | required, integer, min 0 |
| `classical_services` | required, integer, min 0 |
| `summary` | required, string, max 5000 |
| `evaluation` | nullable, string, max 5000 |
| `follow_up` | nullable, string, max 5000 |

**Penyimpanan** — `updateOrCreate` dengan kunci `teacher_id + month + year`.
Menyimpan ulang untuk Guru/bulan/tahun yang sama akan **menimpa** — tidak ada riwayat revisi per bulan.

**Guard** update/destroy/print: `abort_unless($journal->teacher_id === auth()->id(), 403)`

**Flash:** "Jurnal bulanan BK berhasil disimpan." / "… diperbarui." / "… dihapus." · **Tidak ada activity log.**

**Cetak PDF:** mode `stream()`, nama file `jurnal-bulanan-bk-{month}-{year}.pdf`

---

### 7.15 Umpan Balik Layanan (Legacy / Deprecated)

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/feedback` | `guru.feedback.index` |

Method `index()` **tidak lagi membaca data apa pun**. Langsung redirect:
```
redirect()->route('guru.penilaian.index')
    ->with('info', 'Feedback layanan telah digantikan oleh Laporan Penilaian.');
```

- Ditandai `@deprecated` (Phase 4).
- Tabel `service_feedback` di database **tidak** di-drop.
- View `guru/feedback/index.blade.php` masih ada namun tidak pernah dirender.
- Route tidak muncul di sidebar mana pun.
- **Fitur pengganti:** [Laporan Penilaian Layanan](#76-laporan-penilaian-layanan).

---

## 8. Role: Siswa

### 8.1 Login Siswa

Form `/login?role=siswa` — **tanpa password**.

Alur `AuthenticatedSessionController::storeStudentSession()`:

1. Cek rate limiter siswa (key `student-login|{nisn}|{ip}`, maks 5).
2. Cari `Student::where('nisn', input)->first()`.
3. Normalisasi `birth_date` ke format `Y-m-d` (`Carbon::parse` dengan fallback string asli).
4. Bandingkan dengan `$student->birth_date?->toDateString()`.
5. Ambil relasi `$student->user`.
6. Validasi `user.role === 'siswa'`.
7. Validasi `user->isApproved()`.
8. Sukses → `RateLimiter::clear()`, `Auth::login($user)`, session regenerate, redirect `siswa.dashboard`.

Setiap kegagalan pada langkah 2–7 juga menaikkan hit rate limiter.

**Semua pesan error login siswa**

| Kondisi | Field | Pesan |
|---|---|---|
| NISN tidak ditemukan | `nisn` | "NISN tidak ditemukan pada data siswa. Hubungi admin atau Guru BK." |
| Tanggal lahir tidak cocok | `birth_date` | "Tanggal lahir tidak cocok dengan data siswa." |
| `$student->user` null | `nisn` | "Akun siswa belum aktif. Hubungi admin untuk menghubungkan data siswa dengan akun login." |
| User terhubung bukan role siswa | `nisn` | "Data siswa ini terhubung dengan akun yang tidak valid. Hubungi admin." |
| User belum disetujui | `nisn` | "Akun siswa ini belum aktif. Silakan hubungi admin." |
| Rate limited | `nisn` | `auth.throttle` (standar Laravel) |

Validasi form (`nisn` kosong / `birth_date` tidak valid) memakai pesan default Laravel.

---

### 8.2 Dashboard Siswa

Route: `GET /siswa/dashboard` → `siswa.dashboard`
Controller: `App\Http\Controllers\Siswa\DashboardController@index`

**Tiga kartu metrik**

| Kartu | Nilai | Deskripsi |
|---|---|---|
| Total pengajuan | `ConsultationRequest` milik siswa | "Semua permintaan konseling Anda." |
| Menunggu | status `pending` | "Sedang menunggu respon Guru BK." |
| Selesai | status `selesai` | "Sesi yang sudah selesai." |

**Widget lain**

| Widget | Sumber |
|---|---|
| Pengajuan terbaru | 10 terbaru milik siswa, dengan `counselor:id,name` |
| Daftar Guru BK | user role `guru` status `disetujui`, urut nama |
| Postingan terbaru | 3 `Postingan` status `published`, dengan kategori |
| Tryout aktif | bila siswa punya `kelas_id`: `TryOut` status `aktif`, `mulai_at <= now`, `selesai_at >= now`, kelas siswa terdaftar, 3 terbaru |
| Jadwal mendatang | status `disetujui`/`dijadwalkan_ulang`, `consultation_date >=` hari ini, urut tanggal + jam, limit 3 |

**Hero**
- Badge: "Dashboard Siswa"
- Judul: "Selamat datang, {{ nama depan }}. Ruang BK Anda siap digunakan."
- CTA: Ajukan Konseling · Buka Chatbot · Isi Angket · Nilai Layanan · Kerjakan Tryout · Baca Artikel

**Warning** bila profil siswa belum terhubung:
> Profil siswa belum terhubung

> Akun Anda belum punya data NISN/kelas. Hubungi Guru BK agar modul penilaian, angket, dan tryout bisa dipakai.

**9 Action Cards**

| Kartu | Tujuan | CTA |
|---|---|---|
| Konseling | `siswa.consultations.index` | Ajukan Konseling |
| Chatbot Konseling | `siswa.chatbot.index` | Buka Chat |
| Penilaian Layanan | `siswa.penilaian.index` | Nilai Layanan |
| Angket BK | `siswa.angket.index` | Isi Angket |
| Tryout BK | `siswa.tryout.index` | Kerjakan Tryout |
| Artikel BK | `siswa.postingan.index` | Baca Artikel |
| Instrumen Asesmen | `siswa.instruments.index` | Isi Instrumen |
| Profil Siswa SMK | anchor `#smk-profile` | Cek Profil |
| Kelas Bimbingan | anchor `#classes` | Cek Kelas |

**Empty state tryout:** "Tidak ada tryout aktif" / "Coba lagi nanti atau hubungi Guru BK."
**Empty state profil SMK:** "Profil SMK belum tersedia"

---

### 8.3 Menu Sidebar Siswa (`config/navigation.php`, grup `siswa`)

| Grup | Item | Route | Judul topbar |
|---|---|---|---|
| **Utama** | Dashboard | `siswa.dashboard` | Dashboard |
| **Layanan BK** | Konseling | `siswa.consultations.index` | Konseling |
| | Chatbot Konseling | `siswa.chatbot.index` | Chatbot Konseling |
| | Penilaian | `siswa.penilaian.index` | Penilaian Layanan |
| | Angket BK | `siswa.angket.index` | Angket BK |
| | Tryout | `siswa.tryout.index` | Tryout |
| | Artikel BK | `siswa.postingan.index` | Artikel BK |
| **Modul Tim Lain** | Instrumen | `siswa.instruments.index` | Instrumen Asesmen |
| | Sosiometri | `siswa.sociometry.index` | Sosiometri |
| | Karier | `siswa.careers.index` | Informasi Karier |

Route yang ada tapi tidak ada di sidebar: `siswa.consultation-requests.store` (legacy),
`siswa.classes.join` (dipakai lewat form di dashboard), `siswa.feedback.*` (deprecated).

Route profil **tidak tersedia untuk siswa** (middleware `role:admin,guru`).

---

### 8.4 Konseling

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/consultations` | `siswa.consultations.index` |
| POST | `/siswa/consultations` | `siswa.consultations.store` |
| POST | `/siswa/consultation-requests` | `siswa.consultation-requests.store` (legacy) |

Controller: `App\Http\Controllers\Siswa\ConsultationController`

**Daftar**
- Filter `status` (dropdown "Semua status" + `STATUS_LABELS`), tombol Filter, reset filter
- **Kolom:** Topik + cuplikan `details` · Kategori · Guru BK (`—` bila belum) · Jadwal
  (`d M Y` + jam bila `consultation_date` ada, else `preferred_time`) · Status
  (bila `ditolak`, tampilkan `rejection_reason`)
- **Daftar Guru BK** untuk dipilih: user role `guru` status `disetujui`, urut nama

**Form pengajuan** (`StoreConsultationRequest`)

| Field | Aturan | Pesan kustom |
|---|---|---|
| `subject` | required, string, max 120 | "Topik konseling wajib diisi." |
| `case_category` | required, in `CASE_CATEGORIES` | "Pilih kategori masalah." |
| `preferred_date` | nullable, date, after_or_equal:today | default |
| `preferred_time` | nullable, string, max 80 | default |
| `details` | required, string, max 2000 | "Ceritakan keluhan atau hal yang ingin dibahas." |
| `counselor_id` | required, exists `users,id` | "Silakan pilih Guru BK." |

Fallback `preferred_time`:

| Kondisi | Nilai tersimpan |
|---|---|
| `preferred_time` terisi | nilai tersebut |
| kosong, `preferred_date` terisi | "Sesuai tanggal pilihan" |
| keduanya kosong | "Fleksibel — menunggu jadwal Guru BK" |

- Status awal: `pending`
- **Log:** `consultation.submitted` (properties kosong → `null`)
- **Flash:** "Pengajuan konseling berhasil dikirim. Guru BK akan meninjau permintaan Anda."

> **Tidak ada aksi membatalkan pengajuan oleh siswa.** Kolom `counselor_id` nullable di database dan
> dashboard menangani antrian yang belum ditugaskan, tetapi form siswa **mewajibkan**
> pemilihan Guru BK.

---

### 8.5 Penilaian Layanan

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/penilaian` | `siswa.penilaian.index` |
| GET | `/siswa/penilaian/buat` | `siswa.penilaian.create` (query `?consultation={id}`) |
| POST | `/siswa/penilaian` | `siswa.penilaian.store` |

- Hanya konseling **milik siswa itu sendiri** dengan status `selesai` yang boleh dinilai (`firstOrFail`).
- **Daftar:** No · Tanggal (`scheduled_at` → `consultation_date` → `—`) · Topik + Guru BK ·
  Status Penilaian (Sudah Dinilai / Belum Dinilai) · Aksi
- Tombol "Nilai konseling terbaru" tampil bila ada item belum dinilai.
- *Empty state:* "Belum ada konseling selesai"

**Aspek penilaian** (skala bintang 1–5)

| Key | Label | Hint |
|---|---|---|
| `skor_materi` | Materi / Konten | Kejelasan dan relevansi materi yang dibahas |
| `skor_cara` | Cara Penyampaian | Sikap, empati, dan komunikasi Guru BK |
| `skor_manfaat` | Manfaat yang Dirasakan | Seberapa membantu sesi ini bagi Anda |

**Validasi**

| Field | Aturan |
|---|---|
| `consultation_request_id` | required, integer, exists `consultation_requests,id` |
| `skor_materi` | required, integer, min 1, max 5 |
| `skor_cara` | required, integer, min 1, max 5 |
| `skor_manfaat` | required, integer, min 1, max 5 |
| `catatan` | nullable, string, max 1000 |

Duplikat ditolak dengan 403. Tabel punya unique index `uniq_penilaian_per_konseling`
pada `(consultation_request_id, student_id)`.

| Lokasi | Pesan |
|---|---|
| Controller (`create`) | "Kamu sudah memberikan penilaian untuk konseling ini." |
| Controller (`store`) | "Penilaian sudah diberikan." |

- **Log:** `penilaian_pelayanan.submitted` dengan properties `consultation_request_id`
- **Flash:** "Terima kasih! Penilaianmu sudah disimpan."

---

### 8.6 Angket BK

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/angket` | `siswa.angket.index` |
| GET | `/siswa/angket/isi` | `siswa.angket.show` |
| POST | `/siswa/angket` | `siswa.angket.store` |

- Soal aktif: `AngketQuestions::activeIds()`
- Progress bar persentase, tombol "Isi Angket" atau "Lihat Jawaban"
- **Tabel status:** No | Pertanyaan | Status Sudah/Belum dijawab
- **Form:** textarea per soal `jawaban[{id}]`, required, max 500 karakter, dengan konfirmasi modal Alpine sebelum submit
- Jawaban yang sudah ada ditampilkan sebagai nilai awal (dapat diperbarui)

**Validasi**

| Field | Aturan |
|---|---|
| `jawaban` | required, array, min 1 |
| `jawaban.*` | required, string, max 500 |

Hanya id soal yang valid **dan aktif** yang disimpan, dalam `DB::transaction` memakai
`ResponsAngket::updateOrCreate()`. Tabel punya unique `uniq_respons_per_soal` pada
`(student_id, master_question_id)`.

- **Log:** `angket.submitted` dengan properties `jumlah_jawaban`
- **Flash:** "Jawaban angket berhasil disimpan."

Tidak ada skoring di sisi siswa dan tidak ada ekspor PDF.

---

### 8.7 Tryout

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/tryout` | `siswa.tryout.index` |
| GET | `/siswa/tryout/{tryout}` | `siswa.tryout.show` |
| POST | `/siswa/tryout/{tryout}` | `siswa.tryout.store` |

**Daftar**
- Tryout aktif: jika siswa punya `kelas_id` — `TryOut` status `aktif`, kelas siswa terdaftar,
  `mulai_at <= now`, `selesai_at >= now`, terbaru. Tombol "Kerjakan".
- Riwayat: 10 `TryOutDetail` terbaru — judul, "Skor {rata_skor} · {tanggal submit}"
- Empty state: "Belum ada tryout aktif"

**Mengerjakan (`show`)**
- **Guard:** `abort_unless($tryOutService->siswaBisaAkses($tryout, $student), 404)`
- Jika sudah pernah submit: `abort(403, 'Tryout ini sudah kamu kumpulkan.')`
- Timer session-based (`tryout_started_{id}`), countdown dengan auto-submit saat ≤ 0
- Per soal: `skala` → select 1–5 (required); selain itu → textarea (required, max 2000)

**Submit (`store`)**

| Field | Aturan |
|---|---|
| `jawaban` | required, array |
| `jawaban.*` | nullable, string, max 2000 |

Bila waktu habis, `ValidationException` pada field `jawaban`:
> Waktu pengerjaan tryout sudah habis. Jawaban tidak dapat dikumpulkan.

- **Skoring:** `skala` 1–5 → nilai × 20 (clamp 1–5); non-skala → 80 bila `strlen >= 3`, selain itu 40.
  Rata-rata dibulatkan 1 desimal. **Tidak ada koreksi benar/salah berdasarkan kunci.**
- **Log:** `tryout.submitted` dengan properties `try_out_id`
- **Flash:** "Jawaban tryout berhasil dikumpulkan."

Tidak ada halaman hasil detail terpisah — hasil hanya tampil di riwayat.

---

### 8.8 Instrumen Asesmen

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/instruments` | `siswa.instruments.index` |
| POST | `/siswa/instruments` | `siswa.instruments.store` |
| GET | `/siswa/instruments/hasil/{submission}` | `siswa.instruments.hasil` |
| GET | `/siswa/instruments/hasil/{submission}/pdf` | `siswa.instruments.hasil.pdf` |

Controller: `App\Http\Controllers\Siswa\InstrumentSubmissionController`  
Service: `App\Services\Minat\InterestScoringService`, `App\Services\Minat\RecommendationService`

**Kategori** — sama dengan [7.10](#710-soal-instrumen-asesmen). Default tab: `minat_bakat`.

#### 8.8.1 Alur pengisian Minat Bakat (wizard)

```
Login siswa → Instrumen → tab Minat Bakat
        │
        ▼
[1] Intro
    • Penjelasan 3 langkah (isi soal → Kode Minat → rekomendasi)
    • Jenjang: dari kelas siswa (SMA/SMK)
      - jika kosong → pilih SMA/SMK (Select2) atau tombol "Mulai sebagai SMA/SMK"
      - SD/SMP → pesan "Asesmen ini untuk siswa SMA/SMK."
        │
        ▼
[2] Quiz (satu soal per layar)
    • Soal aktif + punya interest_category_id + jenjang_target ∈ {semua, jenjang siswa}
    • Progress bar: terjawab / total
    • Pilih skala → otomatis lanjut soal berikutnya
    • Tombol Sebelumnya / Lewati / Berikutnya
        │
        ▼
[3] Kirim (semua soal terjawab) → POST store
    • InterestScoringService menghitung skor + Kode Minat
    • Simpan submission + instrument_answers + snapshot category_scores
    • Activity: instrument.minat.submitted
        │
        ▼
[4] Halaman hasil GET /siswa/instruments/hasil/{submission}
    • Kartu Kode Minat + 3 minat dominan (+ catatan tie bila is_tied)
    • Bar distribusi persen semua kategori
    • Rekomendasi:
        - SMA → Program Studi PCR (hanya is_verified + is_active)
        - SMK → Bidang Karier + Job Zone + penjelasan Job Zone
    • Tombol: Ulangi asesmen | Unduh laporan (PDF) | Kembali dashboard
    • Guard pemilik: siswa lain → 403
```

**Ringkasan di index:** kartu Minat Bakat menampilkan **Kode Minat** + tautan **Lihat hasil**. Retake = isi ulang; yang ditampilkan = submission terbaru.

#### 8.8.2 Validasi & skoring

| Field | Aturan |
|---|---|
| `category` | required, in `CATEGORIES` |
| `jenjang` | required untuk minat bila kelas belum punya jenjang (`SMA`/`SMK`) |
| `answers` | required, array; kunci = id soal aktif yang difilter |
| `answers.*` | integer, indeks opsi ≥ 0 |

- Jumlah jawaban ≠ jumlah soal aktif → *"Jawaban tidak sesuai dengan daftar soal aktif."*
- Indeks opsi invalid → 422 *"Pilihan jawaban tidak valid."*

**Skoring Minat Bakat (RIASEC)** — `InterestScoringService`:
- Per kategori: `raw = Σ(skor opsi × bobot soal)`; `max = Σ(skor opsi tertinggi × bobot)`; `persen = raw/max×100` (1 desimal)
- Peringkat by persen; tie-break: raw lebih besar → `urutan` kategori lebih kecil
- `is_tied` bila selisih persen peringkat 1 dan 2 < 0.5
- **Kode Minat** = gabungan `kode` 3 kategori teratas (contoh `RCS`)
- Kolom lama tetap diisi: `result_label` = nama dominan, `result_description` = deskripsi, `total_score` = Σ raw

**Rekomendasi** (`RecommendationService`, dihitung saat halaman hasil dibuka):
- Bobot peringkat: top-1 ×1.0, top-2 ×0.6, top-3 ×0.3 terhadap relevansi pivot
- Label: skor ≥ 3 → "Sangat Cocok", selain itu "Cocok" (maks 6 item)
- Fallback kosong: pesan diskusikan dengan Guru BK (tidak error)

**PDF:** `hasil-minat-{slug-nama}-{Ymd}.pdf` (dompdf A4 portrait); activity `instrument.minat.pdf.downloaded`.

#### 8.8.3 Instrumen kategori lain (bukan Minat Bakat)

Form klasik: semua soal di satu halaman (radio). Skoring lama tetap:
`$maxScore = max($questionCount * 4, 1)`; `$percentage = ($score / $maxScore) × 100`.

Kategori **Masalah** (`angket_masalah`):

| Persentase | Label | Deskripsi |
|---|---|---|
| ≥ 70% | Prioritas Tinggi | "Siswa membutuhkan perhatian dan tindak lanjut Guru BK lebih cepat." |
| ≥ 40% | Perlu Dipantau | "Ada beberapa area masalah yang perlu didalami melalui percakapan lanjutan." |
| < 40% | Ringan | "Belum tampak indikasi masalah berat dari jawaban instrumen." |

Kategori Gaya Belajar, Kepribadian, Sosiometri:

| Persentase | Label | Deskripsi |
|---|---|---|
| ≥ 70% | Sangat Menonjol | "Potensi atau kecenderungan siswa terlihat kuat pada instrumen ini." |
| ≥ 40% | Cukup Berkembang | "Potensi siswa sudah terlihat dan dapat diperkuat melalui bimbingan." |
| < 40% | Perlu Eksplorasi | "Siswa masih perlu mengeksplorasi diri pada area ini." |

- Redirect kembali ke index + flash sukses (bukan halaman hasil RIASEC).

---

### 8.9 Sosiometri

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/sociometry` | `siswa.sociometry.index` |
| POST | `/siswa/sociometry` | `siswa.sociometry.store` |

**Validasi**

| Field | Aturan | Pesan kustom |
|---|---|---|
| `close_friend_id` | required, integer, exists `users,id`, different `study_friend_id` | "Teman dekat dan teman belajar harus berbeda." |
| `study_friend_id` | required, integer, exists `users,id` | default |
| `reason` | nullable, string, max 1000 | default |

Tambahan: memilih diri sendiri menghasilkan 422 "Tidak boleh memilih diri sendiri."

Hanya user siswa berstatus `disetujui` yang dapat dipilih. **Tidak ada activity log.**

---

### 8.10 Postingan / Feed

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/postingan` | `siswa.postingan.index` |
| GET | `/siswa/postingan/{postingan}` | `siswa.postingan.show` |

**Daftar**
- **Filter:** `search` (judul atau isi, LIKE), `kategori` (kategori yang punya postings published)
- Hanya `Postingan` berstatus `published`, urut terbaru
- *Detail:* `abort_unless($postingan->isPublished(), 404)`

> Feed bersifat **read-only** — tidak ada aksi like maupun komentar di sisi siswa.

---

### 8.11 Informasi Karier

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/careers` | `siswa.careers.index` |

- **Filter:** `search` (judul atau deskripsi, LIKE), `category`
- Daftar informasi karier read-only, urut terbaru
- Tidak ada ekspor PDF dan tidak ada activity log.

---

### 8.12 Chatbot

| Method | URI | Nama route |
|---|---|---|
| GET | `/siswa/chatbot` | `siswa.chatbot.index` |
| POST | `/siswa/chatbot` | `siswa.chatbot.store` |

**Validasi:** `message` required, string, max 2000

**Alur:** service URL diambil dari `config('services.konseling_chatbot.service_url')`, lalu
HTTP POST ke `{service_url}/chat` dengan body `message` dan `studentId`, header JSON,
timeout 30 detik.

| Kondisi | Status HTTP | Pesan `reply` |
|---|---|---|
| `service_url` kosong | 500 | "Service chatbot belum dikonfigurasi." |
| Gagal dihubungi (exception) | 502 | "Service chatbot belum bisa dihubungi. Pastikan FastAPI berjalan di server lokal." |
| HTTP error | 502 | "Service chatbot mengembalikan status {status}: {detail}" |
| Respons `reply` bukan string / kosong | 502 | "Service chatbot berhasil dipanggil, tetapi responsnya kosong." |
| Sukses | 200 | `trim($reply)` |

UI: Alpine `fetch` POST dengan header `X-CSRF-TOKEN`, chat bubbles, auto-scroll, typing indicator.
Tidak ada activity log.

---

### 8.13 Gabung Kelas Bimbingan

| Method | URI | Nama route |
|---|---|---|
| POST | `/siswa/classes/join` | `siswa.classes.join` |

Controller: `App\Http\Controllers\Siswa\ClassJoinController@store`

Tidak ada halaman terpisah di sidebar — form-nya berada di dashboard siswa pada anchor `#classes`.

**Validasi**

| Field | Aturan | Pesan |
|---|---|---|
| `code` | required, string, max 40 | "Silakan isi kode kelas." |

**Alur:**
1. Ambil `$request->user()->studentProfile`. Null → "Profil siswa belum tersedia. Hubungi admin untuk melengkapi data siswa."
2. Cari `GuidanceClass::where('code', strtoupper(trim($code)))`. Tidak ditemukan → error `code`: "Kode kelas tidak ditemukan."
3. `$student->guidanceClasses()->syncWithoutDetaching([$class->id])`
4. Flash sukses: "Berhasil masuk ke kelas bimbingan."

Tidak ada activity log.

---

## 9. Activity Log

### 9.1 Mekanisme

Helper: `App\Support\ActivityLogger`

```php
ActivityLogger::log(string $action, ?Model $subject = null, array $properties = [])
```

| Field yang disimpan | Sumber |
|---|---|
| `user_id` | `Auth::id()` |
| `action` | parameter `$action` |
| `subject_type` | class model (bila `subject` ada) |
| `subject_id` | id model (bila `subject` ada) |
| `properties` | array; **disimpan `null` bila kosong** |
| `ip_address` | `request()->ip()` |

### 9.2 Seluruh Action String

#### Dari role Admin

| Action | Ditempakkan di | Properties |
|---|---|---|
| `sekolah.created` | `SekolahController@store` | `nama`, `npsn` |
| `sekolah.updated` | `SekolahController@update` | `nama`, `npsn` |
| `sekolah.deleted` | `SekolahController@destroy` | `nama`, `npsn` |
| `kelas.created` | `KelasController@store` | `nama` |
| `kelas.updated` | `KelasController@update` | `nama` |
| `kelas.deleted` | `KelasController@destroy` | `nama` |
| `guru-bk.created` | `GuruBkController@store` | `nama`, `nip` |
| `guru-bk.updated` | `GuruBkController@update` | `nama`, `nip` |
| `guru-bk.deleted` | `GuruBkController@destroy` | `nama`, `nip` |
| `student.created` | `StudentController@store` | (kosong) |
| `student.updated` | `StudentController@update` | (kosong) |
| `student.deleted` | `StudentController@destroy` | `name` |
| `master-pertanyaan.created` | `MasterQuestionController@store` | `kategori` |
| `master-pertanyaan.updated` | `MasterQuestionController@update` | `kategori` |
| `master-pertanyaan.deleted` | `MasterQuestionController@destroy` | `teks` (`Str::limit`, 80) |
| `kategori-postingan.created` | `PostCategoryController@store` | `name` |
| `kategori-postingan.updated` | `PostCategoryController@update` | `name` |
| `kategori-postingan.deleted` | `PostCategoryController@destroy` | `name` |
| `postingan.created` | `PostinganController@store` | (kosong) |
| `postingan.updated` | `PostinganController@update` | (kosong) |
| `postingan.deleted` | `PostinganController@destroy` | `judul` |
| `interest-category.created` | `InterestCategoryController@store` | `nama` |
| `interest-category.updated` | `InterestCategoryController@update` | `nama` |
| `interest-category.deleted` | `InterestCategoryController@destroy` | `nama` |
| `program-studi.created` | `ProgramStudiController@store` | `nama` |
| `program-studi.updated` | `ProgramStudiController@update` | `nama` |
| `program-studi.deleted` | `ProgramStudiController@destroy` | `nama` |
| `bidang-karier.created` | `CareerFieldController@store` | `nama` |
| `bidang-karier.updated` | `CareerFieldController@update` | `nama` |
| `bidang-karier.deleted` | `CareerFieldController@destroy` | `nama` |

#### Dari role Guru BK

| Action | Ditempakkan di | Properties |
|---|---|---|
| `siswa.created` | `StudentController@store` | `nama`, `nisn` |
| `siswa.imported` | `StudentController@import` (subject `null`) | `file`, `baru`, `diperbarui`, `dilewati` |
| `siswa.updated` | `StudentController@update` | `nama`, `nisn` |
| `siswa.deleted` | `StudentController@destroy` (sebelum delete) | `nama`, `nisn` |
| `consultation.approved` | `ConsultationController@approve` | (kosong) |
| `consultation.rejected` | `ConsultationController@reject` | (kosong) |
| `consultation.scheduled` | `ConsultationController@schedule` (jadwal baru) | (kosong) |
| `consultation.rescheduled` | `ConsultationController@schedule` (jadwal ulang) | (kosong) |
| `consultation.completed` | `ConsultationController@report` | (kosong) |
| `rapor_bk.saved` | `RaporController@update` | `student_id`, `status` |
| `angket.pdf.downloaded` | `AngketController@exportPdf` | `nama`, `predikat`, `total_dijawab` |
| `tryout.created` | `TryoutController@store` | `judul` |
| `tryout.updated` | `TryoutController@update` | `judul` |
| `tryout.deleted` | `TryoutController@destroy` (sebelum delete) | `judul` |
| `instrument-question.created` | `InstrumentQuestionController@store` | `question` (limit 80) |
| `instrument-question.updated` | `InstrumentQuestionController@update` | `question` (limit 80) |
| `instrument-question.deleted` | `InstrumentQuestionController@destroy` | `question` (limit 80) |

#### Dari role Siswa

| Action | Ditempakkan di | Properties |
|---|---|---|
| `consultation.submitted` | `ConsultationController@store` | (kosong) |
| `penilaian_pelayanan.submitted` | `PenilaianController@store` | `consultation_request_id` |
| `tryout.submitted` | `TryoutController@store` | `try_out_id` |
| `angket.submitted` | `AngketController@store` | `jumlah_jawaban` |
| `instrument.minat.submitted` | submit Minat Bakat | `submission_id`, `kode_minat`, `jenjang` |
| `instrument.minat.pdf.downloaded` | unduh PDF hasil Minat | (submission) |

### 9.3 Aksi yang TIDAK menulis Activity Log

- Approval/penolakan Guru BK oleh admin
- Manajemen Akun (users)
- Informasi Karier
- Konseling admin (read-only)
- Rapor BK (admin, read-only)
- Log Aktivitas itu sendiri
- Kelas Bimbingan dan tambah/lepas siswa
- Perubahan Profil Guru (menggunakan `GuruProfileChange`)
- Cetak PDF konseling, rapor BK, RPL, dan jurnal bulanan
- CRUD RPL
- CRUD jurnal bulanan
- Instrumen non-minat, Sosiometri, Chatbot, Gabung Kelas, Postingan, Karier (sisi siswa)
- Logout dan update profil

### 9.4 Catatan Audit

Untuk `siswa.deleted` dan `tryout.deleted`, entry log ditulis **sebelum** operasi delete. Bila
delete gagal atau ditolak, entry log tetap sudah tercatat meskipun data tidak terhapus.

---

## 10. Daftar Route Lengkap

### 10.1 Admin

Semua berada pada middleware `auth`, `verified` → prefix `admin`, name prefix `admin.`, `role:admin`.

| Method | URI | Nama route |
|---|---|---|
| GET | `admin/dashboard` | `admin.dashboard` |
| GET | `admin/interest-categories` | `admin.interest-categories.index` |
| POST | `admin/interest-categories` | `admin.interest-categories.store` |
| PUT/PATCH | `admin/interest-categories/{interestCategory}` | `admin.interest-categories.update` |
| DELETE | `admin/interest-categories/{interestCategory}` | `admin.interest-categories.destroy` |
| GET | `admin/program-studi` | `admin.program-studi.index` |
| POST | `admin/program-studi` | `admin.program-studi.store` |
| PUT/PATCH | `admin/program-studi/{programStudi}` | `admin.program-studi.update` |
| DELETE | `admin/program-studi/{programStudi}` | `admin.program-studi.destroy` |
| GET | `admin/bidang-karier` | `admin.bidang-karier.index` |
| POST | `admin/bidang-karier` | `admin.bidang-karier.store` |
| PUT/PATCH | `admin/bidang-karier/{careerField}` | `admin.bidang-karier.update` |
| DELETE | `admin/bidang-karier/{careerField}` | `admin.bidang-karier.destroy` |
| GET | `admin/approvals` | `admin.approvals.index` |
| PATCH | `admin/approvals/{user}/approve` | `admin.approvals.approve` |
| PATCH | `admin/approvals/{user}/reject` | `admin.approvals.reject` |
| GET | `admin/careers` | `admin.careers.index` |
| POST | `admin/careers` | `admin.careers.store` |
| PUT/PATCH | `admin/careers/{career}` | `admin.careers.update` |
| DELETE | `admin/careers/{career}` | `admin.careers.destroy` |
| GET | `admin/consultations` | `admin.consultations.index` |
| GET | `admin/guidance-classes` | `admin.guidance-classes.index` |
| POST | `admin/guidance-classes` | `admin.guidance-classes.store` |
| PUT/PATCH | `admin/guidance-classes/{guidanceClass}` | `admin.guidance-classes.update` |
| DELETE | `admin/guidance-classes/{guidanceClass}` | `admin.guidance-classes.destroy` |
| POST | `admin/guidance-classes/{guidanceClass}/students` | `admin.guidance-classes.students.attach` |
| DELETE | `admin/guidance-classes/{guidanceClass}/students/{student}` | `admin.guidance-classes.students.detach` |
| GET | `admin/guru-bk` | `admin.guru-bk.index` |
| POST | `admin/guru-bk` | `admin.guru-bk.store` |
| PUT/PATCH | `admin/guru-bk/{guruBk}` | `admin.guru-bk.update` |
| DELETE | `admin/guru-bk/{guruBk}` | `admin.guru-bk.destroy` |
| GET | `admin/perubahan-profil-guru` | `admin.guru-profile-changes.index` |
| PATCH | `admin/perubahan-profil-guru/{change}/dibaca` | `admin.guru-profile-changes.reviewed` |
| GET | `admin/kategori-postingan` | `admin.kategori-postingan.index` |
| POST | `admin/kategori-postingan` | `admin.kategori-postingan.store` |
| PUT/PATCH | `admin/kategori-postingan/{kategoriPostingan}` | `admin.kategori-postingan.update` |
| DELETE | `admin/kategori-postingan/{kategoriPostingan}` | `admin.kategori-postingan.destroy` |
| GET | `admin/kelas` | `admin.kelas.index` |
| POST | `admin/kelas` | `admin.kelas.store` |
| PUT/PATCH | `admin/kelas/{kela}` | `admin.kelas.update` |
| DELETE | `admin/kelas/{kela}` | `admin.kelas.destroy` |
| GET | `admin/master-pertanyaan` | `admin.master-pertanyaan.index` |
| POST | `admin/master-pertanyaan` | `admin.master-pertanyaan.store` |
| PUT/PATCH | `admin/master-pertanyaan/{masterPertanyaan}` | `admin.master-pertanyaan.update` |
| DELETE | `admin/master-pertanyaan/{masterPertanyaan}` | `admin.master-pertanyaan.destroy` |
| GET | `admin/postingan` | `admin.postingan.index` |
| POST | `admin/postingan` | `admin.postingan.store` |
| PUT/PATCH | `admin/postingan/{postingan}` | `admin.postingan.update` |
| DELETE | `admin/postingan/{postingan}` | `admin.postingan.destroy` |
| GET | `admin/rapor` | `admin.rapor.index` |
| GET | `admin/rapor/{rapor}` | `admin.rapor.show` |
| GET | `admin/rapor-cetak/{rapor}/pdf` | `admin.rapor.pdf` |
| GET | `admin/sekolah` | `admin.sekolah.index` |
| POST | `admin/sekolah` | `admin.sekolah.store` |
| PUT/PATCH | `admin/sekolah/{sekolah}` | `admin.sekolah.update` |
| DELETE | `admin/sekolah/{sekolah}` | `admin.sekolah.destroy` |
| GET | `admin/students` | `admin.students.index` |
| POST | `admin/students` | `admin.students.store` |
| PUT/PATCH | `admin/students/{student}` | `admin.students.update` |
| DELETE | `admin/students/{student}` | `admin.students.destroy` |
| GET | `admin/users` | `admin.users.index` |
| POST | `admin/users` | `admin.users.store` |
| PUT/PATCH | `admin/users/{user}` | `admin.users.update` |
| DELETE | `admin/users/{user}` | `admin.users.destroy` |
| GET | `admin/activity-logs` | `admin.activity-logs.index` |

### 10.2 Guru BK

Middleware `auth`, `verified`, `role:guru`; prefix `guru`; name prefix `guru.`

| Method | URI | Nama route |
|---|---|---|
| GET | `/guru/dashboard` | `guru.dashboard` |
| GET | `/guru/consultations` | `guru.consultations.index` |
| GET | `/guru/consultations/events` | `guru.consultations.events` |
| PATCH | `/guru/consultations/{id}/approve` | `guru.consultations.approve` |
| PATCH | `/guru/consultations/{id}/reject` | `guru.consultations.reject` |
| PATCH | `/guru/consultations/{id}/schedule` | `guru.consultations.schedule` |
| PATCH | `/guru/consultations/{id}/report` | `guru.consultations.report` |
| GET | `/guru/consultations/{id}/print` | `guru.consultations.print` |
| GET | `/guru/penilaian` | `guru.penilaian.index` |
| GET | `/guru/angket` | `guru.angket.index` |
| GET | `/guru/angket/{student}` | `guru.angket.show` |
| GET | `/guru/angket/{student}/pdf` | `guru.angket.pdf` |
| GET | `/guru/rapor` | `guru.rapor.index` |
| GET | `/guru/rapor/{student}/edit` | `guru.rapor.edit` |
| PUT | `/guru/rapor/{student}` | `guru.rapor.update` |
| GET | `/guru/rapor-cetak/{rapor}/pdf` | `guru.rapor.pdf` |
| GET | `/guru/tryout` | `guru.tryout.index` |
| GET | `/guru/tryout/buat` | `guru.tryout.create` |
| POST | `/guru/tryout` | `guru.tryout.store` |
| GET | `/guru/tryout/{tryout}` | `guru.tryout.show` |
| GET | `/guru/tryout/{tryout}/edit` | `guru.tryout.edit` |
| PUT | `/guru/tryout/{tryout}` | `guru.tryout.update` |
| DELETE | `/guru/tryout/{tryout}` | `guru.tryout.destroy` |
| GET | `/guru/students` | `guru.students.index` |
| POST | `/guru/students` | `guru.students.store` |
| POST | `/guru/students/import` | `guru.students.import` |
| PUT | `/guru/students/{student}` | `guru.students.update` |
| DELETE | `/guru/students/{student}` | `guru.students.destroy` |
| GET | `/guru/instrument-questions` | `guru.instrument-questions.index` |
| POST | `/guru/instrument-questions` | `guru.instrument-questions.store` |
| PUT | `/guru/instrument-questions/{question}` | `guru.instrument-questions.update` |
| DELETE | `/guru/instrument-questions/{question}` | `guru.instrument-questions.destroy` |
| GET | `/guru/instrument-results` | `guru.instrument-results.index` |
| GET | `/guru/sociometry` | `guru.sociometry.index` |
| GET | `/guru/rpls` | `guru.rpls.index` |
| POST | `/guru/rpls` | `guru.rpls.store` |
| PUT | `/guru/rpls/{rpl}` | `guru.rpls.update` |
| DELETE | `/guru/rpls/{rpl}` | `guru.rpls.destroy` |
| GET | `/guru/rpls/{rpl}/print` | `guru.rpls.print` |
| GET | `/guru/journals` | `guru.journals.index` |
| POST | `/guru/journals` | `guru.journals.store` |
| PUT | `/guru/journals/{journal}` | `guru.journals.update` |
| DELETE | `/guru/journals/{journal}` | `guru.journals.destroy` |
| GET | `/guru/journals/{journal}/print` | `guru.journals.print` |
| GET | `/guru/feedback` | `guru.feedback.index` (redirect) |

### 10.3 Siswa

Middleware `role:siswa`; prefix `siswa`; name prefix `siswa.`

| Method | URI | Nama route | Controller@Action |
|---|---|---|---|
| GET | `/siswa/dashboard` | `siswa.dashboard` | `Siswa\DashboardController@index` |
| GET | `/siswa/instruments` | `siswa.instruments.index` | `Siswa\InstrumentSubmissionController@index` |
| POST | `/siswa/instruments` | `siswa.instruments.store` | `Siswa\InstrumentSubmissionController@store` |
| GET | `/siswa/instruments/hasil/{submission}` | `siswa.instruments.hasil` | hasil Minat Bakat (pemilik) |
| GET | `/siswa/instruments/hasil/{submission}/pdf` | `siswa.instruments.hasil.pdf` | PDF hasil Minat Bakat |
| GET | `/siswa/sociometry` | `siswa.sociometry.index` | `Siswa\SociometryController@index` |
| POST | `/siswa/sociometry` | `siswa.sociometry.store` | `Siswa\SociometryController@store` |
| GET | `/siswa/consultations` | `siswa.consultations.index` | `Siswa\ConsultationController@index` |
| POST | `/siswa/consultations` | `siswa.consultations.store` | `Siswa\ConsultationController@store` |
| GET | `/siswa/chatbot` | `siswa.chatbot.index` | `Siswa\ChatbotController@index` |
| POST | `/siswa/chatbot` | `siswa.chatbot.store` | `Siswa\ChatbotController@store` |
| POST | `/siswa/consultation-requests` | `siswa.consultation-requests.store` | `Siswa\ConsultationRequestController@store` (legacy) |
| POST | `/siswa/classes/join` | `siswa.classes.join` | `Siswa\ClassJoinController@store` |
| GET | `/siswa/careers` | `siswa.careers.index` | `Siswa\CareerInfoController@index` |
| GET | `/siswa/postingan` | `siswa.postingan.index` | `Siswa\PostinganController@index` |
| GET | `/siswa/postingan/{postingan}` | `siswa.postingan.show` | `Siswa\PostinganController@show` |
| GET | `/siswa/feedback` | `siswa.feedback.create` | `Siswa\ServiceFeedbackController@create` (redirect) |
| POST | `/siswa/feedback` | `siswa.feedback.store` | `Siswa\ServiceFeedbackController@store` (redirect) |
| GET | `/siswa/penilaian` | `siswa.penilaian.index` | `Siswa\PenilaianController@index` |
| GET | `/siswa/penilaian/buat` | `siswa.penilaian.create` | `Siswa\PenilaianController@create` |
| POST | `/siswa/penilaian` | `siswa.penilaian.store` | `Siswa\PenilaianController@store` |
| GET | `/siswa/angket` | `siswa.angket.index` | `Siswa\AngketController@index` |
| GET | `/siswa/angket/isi` | `siswa.angket.show` | `Siswa\AngketController@show` |
| POST | `/siswa/angket` | `siswa.angket.store` | `Siswa\AngketController@store` |
| GET | `/siswa/tryout` | `siswa.tryout.index` | `Siswa\TryoutController@index` |
| GET | `/siswa/tryout/{tryout}` | `siswa.tryout.show` | `Siswa\TryoutController@show` |
| POST | `/siswa/tryout/{tryout}` | `siswa.tryout.store` | `Siswa\TryoutController@store` |

Route model binding: `{postingan}` → `Postingan`, `{tryout}` → `TryOut`.

### 10.4 Auth & Umum

| Method | URI | Nama route | Middleware |
|---|---|---|---|
| GET | `/login` | `login` | `guest` |
| POST | `/login` | `login` | `guest`, `throttle:10,1` |
| GET | `/register` | `register` | `guest` |
| POST | `/register` | `register` | `guest` |
| GET | `/register/guru-bk` | `guru.register` | `guest` |
| POST | `/register/guru-bk` | `guru.register.store` | `guest` |
| GET | `/dashboard` | `dashboard` | `auth` |
| GET | `/profile` | `profile.edit` | `role:admin,guru` |
| PATCH | `/profile` | `profile.update` | `role:admin,guru` |
| DELETE | `/profile` | `profile.destroy` | `role:admin,guru` |
| POST | `/logout` | `logout` | `auth` |

---

## 11. Known Issues & Catatan Audit

Bagian ini mendokumentasikan perilaku yang **saat ini ada** di kode dan berpotensi membingungkan.
Daftar ini bukan daftar fitur — semuanya adalah perilaku yang perlu diketahui pengguna.

### 11.1 Login Siswa Tidak Memakai Password

Siapa pun yang mengetahui **NISN + tanggal lahir** seorang siswa dapat login sebagai siswa itu
tanpa password. Tanggal lahir adalah data pribadi yang mudah ditebak/ditemukan.

### 11.2 Verifikasi Email Tidak Ditegakkan

`App\Models\User` **tidak** mengimplementasikan `MustVerifyEmail` (import di-comment pada
`app/Models/User.php` baris 5). Akibatnya middleware `verified` selalu lolos — verifikasi email
tidak pernah menghalangi login role mana pun. Route admin/guru tetap aman karena `role` middleware
tetap ditegakkan.

### 11.3 Report Konseling Tanpa Guard Status

`Guru\ConsultationController@report` hanya memeriksa
`abort_unless($consultation->counselor_id === auth()->id(), 403)` — **tidak ada** pemeriksaan status
(tidak ada `abort_unless(status IN (disetujui, dijadwalkan_ulang), 422)` seperti pada `approve`,
`reject`, dan `schedule`).

Konsekuensi:
- Laporan dapat dikirim pada status **apa pun** selama konseling memiliki `counselor_id` milik Guru
  yang sedang login.
- Status `ditolak` tetap punya `counselor_id` (karena `reject` menyetel `counselor_id = auth()->id()`),
  sehingga Guru yang menolak sebuah pengajuan dapat mengirim laporan pada pengajuan yang ia tolak
  dan mengubahnya menjadi `selesai`.
- Laporan dapat dijalankan ulang pada status `selesai`, menimpa `result`, `evaluation`, dan `follow_up`.
- Setiap pengiriman ulang menulis log `consultation.completed` lagi.

Tidak ada tombol UI untuk aksi ini pada status ditolak/selesai — hanya bisa dilakukan lewat
PATCH langsung ke URL.

### 11.4 `student_id` pada Jadwal Tidak di-scope

`ScheduleConsultationRequest` hanya memvalidasi `student_id` dengan `exists:users,id`. Tidak ada
pemeriksaan apakah siswa tersebut berada dalam cakupan `CounselorStudentService` milik Guru yang
sedang login. Nilai `student_id` dikirim sebagai hidden input form. Yang dilindungi hanya konseling
itu sendiri lewat `abort_unless(belongsToCounselor(auth()->id()), 403)`.

### 11.5 Log Destruktif Ditulis Sebelum Aksi

`siswa.deleted` dan `tryout.deleted` dicatat **sebelum** operasi delete dijalankan. Bila delete
gagal atau ditolak, entry log tetap sudah tercatat meskipun data tidak benar-benar terhapus.

### 11.6 Modul Tanpa Scoping per Sekolah/Guru

Dua modul menampilkan data **seluruh sistem** tanpa filter sekolah maupun Guru BK yang login:

| Modul | Route | Dampak |
|---|---|---|
| Peta Sosiometri | `/guru/sociometry` | Guru melihat seluruh siswa disetujui, bukan hanya sekolahnya |
| Hasil Instrumen | `/guru/instrument-results` | Guru melihat seluruh submission instrumen dari seluruh siswa |

Keduanya tidak memakai `CounselorStudentService`, berbeda dari modul Angket, Rapor BK, dan Data Siswa.

### 11.7 Soal Instrumen Tidak Punya Guard Kepemilikan

`update` dan `destroy` pada `/guru/instrument-questions/{question}` tidak memverifikasi `created_by`.
Setiap Guru yang punya akses `role:guru` dapat mengubah atau menghapus soal yang dibuat Guru lain.

Soal yang sudah punya jawaban di-soft-delete (bukan force delete) agar histori submission tetap utuh.
Untuk Minat Bakat, opsi jawaban bersifat dinamis (2–6 baris, template Likert).

### 11.8 Data Siswa Belum Tentu Punya Akun Login

Modul Data Siswa (Admin dan Guru) hanya membuat baris di tabel `students`, **bukan** baris di tabel
`users`. Siswa yang datanya sudah dibuat tetapi belum terhubung ke akun Users akan gagal login dengan
pesan "Akun siswa belum aktif. Hubungi admin untuk menghubungkan data siswa dengan akun login."

Karena itu modul penilaian, angket, dan tryout memakai `profileOrFail()` — modul tersebut akan
menolak siswa yang profilnya belum terhubung. Dashboard siswa sendiri tetap toleran (opsional) dan
menampilkan peringatan "Profil siswa belum terhubung".

Selain itu, ringkasan konseling pada PDF Rapor BK bergantung pada `student->user_id`. Siswa yang
belum punya akun User akan mendapat ringkasan kosong (0 / 0 / 0.0) walaupun rapornya sudah ada.

### 11.9 Login Admin Tidak Menormalisasi Email

Login admin mencocokkan `users.email` **apa adanya** tanpa normalisasi atau `lowercase`, sementara
form pembuatan akun admin memaksa `lowercase`. Email yang disimpan dengan huruf kapital **tidak akan
cocok** saat login.

### 11.10 Approval Guru BK Tidak Membatasi Status

`AdminApprovalController@approve` dan `@reject` tidak memeriksa status akun sebelum mengubahnya.
Admin dapat menyetujui akun yang sudah `ditolak` atau sudah `disetujui`. Tidak ada activity log untuk
audit persetujuan.

### 11.11 Bentrok Jadwal Hanya Mempertimbangkan Status Aktif

`ConsultationScheduleService@hasConflict()` hanya menghitung status `disetujui` dan
`dijadwalkan_ulang`. Sesi berstatus `selesai` **tidak** dihitung sebagai bentrok, sehingga Guru bisa
menjadwalkan sesi baru pada slot yang sama dengan sesi selesai sebelumnya.

### 11.12 Log Destroy Ditolak Tetap Tercatat

`tryout.deleted` ditulis sebelum pemanggilan service delete. Bila service melempar `RuntimeException`
karena ada submission siswa, entri log sudah tertulis meskipun tryout tidak terhapus.

### 11.13 Action Log Kosong Disimpan `null`

`ActivityLogger::log()` menyimpan `properties` sebagai `null` bila array kosong. Action seperti
`consultation.approved`, `consultation.rejected`, `consultation.scheduled`, `consultation.rescheduled`,
`consultation.completed`, `consultation.submitted`, `student.created`, `student.updated`,
`postingan.created`, dan `postingan.updated` tercatat tanpa properties.

### 11.14 Perbedaan Cakupan & Label Antar Halaman

| Hal | Perbedaan |
|---|---|
| Cakupan angket | Widget dashboard memakai cakupan **sekolah**; modul Laporan Angket memakai `CounselorStudentService` (sekolah **atau** riwayat konseling/rapor) |
| Fallback predikat penilaian | Widget dashboard memakai "Perlu Perbaikan"; model `PenilaianPelayanan` memakai "Kurang" |
| Dropdown filter jenjang kelas | Diisi dari nilai distinct yang **sudah ada di database**, bukan dari `Kelas::JENJANG_OPTIONS` lengkap |

### 11.15 Default Semester Rapor BK

`RaporBk::defaultSemester()` memakai `now()->month <= 6 ? 'genap' : 'ganjil'`. Ini mengikuti siklus
tahun ajaran Indonesia (semester genap berlangsung sekitar Januari–Juni, semester ganjil sekitar
Juli–Desember).

### 11.16 Jurnal Bulanan Unik per Bulan/Tahun

Penyimpanan memakai `updateOrCreate` dengan kunci `teacher_id + month + year`. Menyimpan ulang untuk
Guru, bulan, dan tahun yang sama akan **menimpa**, bukan menambah. Tidak ada riwayat revisi per bulan.
Hal yang sama berlaku untuk Rapor BK dengan kunci `student_id + counselor_id + semester + tahun_ajaran`.

### 11.17 Dependency Eksternal

| Modul | Dependency |
|---|---|
| Chatbot Siswa | FastAPI di `config('services.konseling_chatbot.service_url')`, timeout 30 detik |
| Ekspor PDF | dompdf (konseling, angket, rapor, RPL, jurnal) |
| Kalender konseling | FullCalendar 6.1.15 dari CDN |

### 11.18 Pagination

| Halaman | Per halaman |
|---|---|
| Sekolah, Kelas, Guru BK, User, Siswa, Master Pertanyaan, Kategori, Postingan, Konseling (admin & guru), Approval, Perubahan Profil, Data Siswa (guru), RPL, Jurnal, Instrumen | 10 |
| Instrumen Guru | 10 |
| Penilaian layanan, Rapor BK, Rapor admin | 20 |
| Log Aktivitas | 25 |
| Angket | 25 |
| Instrumen (hasil) | 12 |
| Karier, Postingan siswa | 9 |
| Kelas Bimbingan | 8 |
| Penilaian siswa | 15 |
| Konseling siswa | 10 |

### 11.19 Yang Tidak Tersedia

**Admin tidak memiliki:**

- CRUD Tryout/Penilaian (khusus Guru & Siswa). Kartu "Soal Tryout" di dashboard sebenarnya menghitung
  `MasterQuestion` kategori `tryout`, bukan entitas TryOut.
- Import siswa massal (import hanya di modul Guru).
- Konfigurasi Chatbot (chatbot hanya Siswa).
- Modul profil siswa SMK.
- Manajemen angket, sosiometri, atau instrumen.
- Endpoint approval konseling.

**Guru BK tidak memiliki:** ekspor PDF rapor dari daftar rapor harus lewat halaman edit;
`guru.feedback` sudah deprecated.

**Siswa tidak memiliki:** ekspor PDF di seluruh modul; halaman edit profil (route `/profile`
menggunakan middleware `role:admin,guru`); aksi like/komentar pada artikel; aksi membatalkan
pengajuan konseling; route `GET /siswa/rapor` (URL tersebut menghasilkan **404** karena route
tidak pernah didefinisikan).