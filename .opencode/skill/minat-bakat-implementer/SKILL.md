---
name: minat-bakat-implementer
description: Mengimplementasikan fitur Asesmen Minat Bakat (kategori minat RIASEC dinamis, soal berbobot, scoring, Kode Minat, rekomendasi prodi PCR untuk SMA dan bidang karier untuk SMK) pada aplikasi Laravel Sistem BKK. Gunakan setiap kali diminta mengubah modul instrumen, minat bakat, rekomendasi prodi, atau rekomendasi karier. WAJIB audit basecode sebelum menulis kode.
---

# Aturan Kerja

## 1. Audit dulu, jangan berasumsi
Sebelum membuat file apa pun, jalankan dan baca hasilnya:

```bash
git status && git log --oneline -5
php artisan route:list --name=instrument
php artisan route:list --name=siswa
ls database/migrations | tail -40
grep -ril "InstrumentSubmission\|instrument_submissions" app database
```

Baca (bila ada): `app/Models/InstrumentQuestion.php`, model submission instrumen,
`app/Http/Controllers/Guru/InstrumentQuestionController.php`, controller hasil instrumen Guru,
`app/Http/Controllers/Siswa/InstrumentSubmissionController.php`,
`resources/views/guru/instrument-questions/*`, `resources/views/siswa/instruments/*`,
`app/Models/Kelas.php`, `app/Models/Student.php`, `config/navigation.php`,
`app/Support/ActivityLogger.php`, `app/Services/CounselorStudentService.php`,
view PDF yang sudah ada (mis. rapor/angket) sebagai contoh dompdf, `routes/web.php`, `tests/`.

Tulis ringkasan temuan (nama tabel/kolom/model **sebenarnya**) sebelum lanjut.
Jika dokumen panduan berbeda dari kode, **kode yang benar**.

## 2. Perluas yang ada, jangan duplikasi
- Modul instrumen (`instrument_questions`, kategori `minat_bakat`) SUDAH ADA. Perluas, jangan buat tabel soal baru.
- `master_questions` (angket/tryout) dan `career_infos` (Informasi Karier) JANGAN disentuh.
- Perilaku kategori instrumen lain (gaya_belajar, kepribadian, sosiometri, angket_masalah) tidak boleh berubah.
- Ikuti konvensi proyek: resource `->except(['create','show','edit'])`, panel/modal di index, Form Request terpisah,
  pesan validasi & flash Bahasa Indonesia, `ActivityLogger::log()`, Alpine.js, Tailwind, pagination 10, dompdf A4.

## 3. Aman
- Migration hanya **aditif** (kolom nullable/default, tabel baru). Dilarang drop/rename kolom lama.
- Jalankan `php artisan migrate --pretend` sebelum `migrate`.
- Query eager-load (tanpa N+1). Input via Form Request. Semua route dilindungi `role:`.
- Jangan menandai data PCR sebagai `is_verified = true` di seeder.

## 4. Definition of Done
- `php artisan test` hijau (termasuk test baru), `./vendor/bin/pint` bersih.
- `route:list` sebelum vs sesudah: tidak ada route lama yang hilang.
- Seeder idempotent (`updateOrCreate`), aman dijalankan ulang.
- Laporan akhir: file diubah/dibuat, perintah deploy, hal yang sengaja ditunda.
