import {
    enableFirstInterestCategory,
    expect,
    formByAction,
    loginAs,
    select2Value,
    tableRow,
    test,
    uniqueValue,
} from './helpers.mjs';

/**
 * E2E — Master referensi & modul baca Admin: kategori minat, program studi,
 * bidang karier, artikel, karier, master pertanyaan, soal sosiometri,
 * approval guru, konseling, rapor, laporan individu, log.
 */
test.describe('Admin — Referensi & Konten', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, 'admin');
    });

    test('Kategori Minat RIASEC: create, update, delete', async ({ page }) => {
        await page.goto('/admin/interest-categories');

        const kode = 'Q';
        const nama = uniqueValue('Minat Uji');

        // Hindari bentrok bila kode sudah ada di seeder.
        const existing = await page.locator('tr', { hasText: kode }).count();
        const kodePakai = existing ? 'Z' : kode;

        // Ada dua tombol "Tambah kategori" (header + empty state).
        await page.getByRole('button', { name: 'Tambah kategori' }).first().click();
        let form = formByAction(page, '/admin/interest-categories');
        await form.locator('input[name="kode"]').fill(kodePakai);
        await form.locator('input[name="urutan"]').fill('99');
        await form.locator('input[name="nama"]').fill(nama);
        await form.locator('textarea[name="deskripsi"]').fill('Kategori uji E2E');
        await form.locator('input[name="warna"]').fill('#f59e0b');
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan kategori' }).click();

        await expect(page.getByText('Kategori minat berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/interest-categories', { put: true });
        await form.locator('input[name="nama"]').fill(`${nama} Baru`);
        await form.getByRole('button', { name: 'Update kategori' }).click();
        await expect(page.getByText('Kategori minat berhasil diperbarui.').first()).toBeVisible();

        // DELETE
        await tableRow(page, `${nama} Baru`).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Kategori minat berhasil dihapus.').first()).toBeVisible();
    });

    test('Kategori Minat: kode dinormalkan otomatis menjadi huruf kapital', async ({ page }) => {
        await page.goto('/admin/interest-categories');
        await page.getByRole('button', { name: 'Tambah kategori' }).first().click();

        const nama = uniqueValue('Minat Uji');

        // prepareForValidation() menjalankan strtoupper() pada `kode`, sehingga
        // input huruf kecil tetap diterima dan tersimpan sebagai kapital.
        // Ini menggantikan skenario "menolak kode huruf kecil" yang memang
        // tidak mungkin terjadi karena normalisasi di server.
        const form = formByAction(page, '/admin/interest-categories');
        await form.locator('input[name="kode"]').fill('xy');
        await form.locator('input[name="urutan"]').fill('99');
        await form.locator('input[name="nama"]').fill(nama);
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan kategori' }).click();

        await expect(page.getByText('Kategori minat berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toContainText('XY');

        await tableRow(page, nama).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Kategori minat berhasil dihapus.').first()).toBeVisible();
    });

    test('Program Studi PCR: create wajib pilih kategori minat, update, delete', async ({ page }) => {
        await page.goto('/admin/program-studi');

        const nama = uniqueValue('Prodi E2E');

        await page.getByRole('button', { name: 'Tambah program studi' }).click();
        let form = formByAction(page, '/admin/program-studi');
        await form.locator('input[name="nama"]').fill(nama);
        await form.locator('select[name="jenjang_pendidikan"]').selectOption('D4');
        await form.locator('input[name="jurusan"]').fill('Teknik');
        await form.locator('select[name="is_verified"]').selectOption('1');
        await form.locator('select[name="is_active"]').selectOption('1');
        await enableFirstInterestCategory(form);
        await form.getByRole('button', { name: 'Simpan program studi' }).click();

        await expect(page.getByText('Program studi berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/program-studi', { put: true });
        await form.locator('input[name="jurusan"]').fill('Informatika');
        await form.getByRole('button', { name: 'Update program studi' }).click();
        await expect(page.getByText('Program studi berhasil diperbarui.').first()).toBeVisible();

        // DELETE
        await tableRow(page, nama).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Program studi berhasil dihapus.').first()).toBeVisible();
    });

    test('Program Studi: menolak submit tanpa kategori minat', async ({ page }) => {
        await page.goto('/admin/program-studi');
        await page.getByRole('button', { name: 'Tambah program studi' }).click();

        const form = formByAction(page, '/admin/program-studi');
        await form.locator('input[name="nama"]').fill(uniqueValue('Prodi Uji'));
        await form.locator('select[name="jenjang_pendidikan"]').selectOption('D3');
        await form.locator('select[name="is_verified"]').selectOption('0');
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan program studi' }).click();

        await expect(page.getByText(/Pilih minimal satu kategori minat/i).first()).toBeVisible();
    });

    test('Bidang Karier: create wajib pilih kategori, update, delete', async ({ page }) => {
        await page.goto('/admin/bidang-karier');

        const nama = uniqueValue('Bidang E2E');

        await page.getByRole('button', { name: 'Tambah bidang karier' }).click();
        let form = formByAction(page, '/admin/bidang-karier');
        await form.locator('input[name="nama"]').fill(nama);
        await form.locator('textarea[name="deskripsi"]').fill('Bidang karier uji E2E');
        await form.locator('textarea[name="contoh_pekerjaan_text"]').fill('Tester A\nQA Engineer');
        await form.locator('select[name="job_zone"]').selectOption('3');
        await form.locator('select[name="is_active"]').selectOption('1');
        await enableFirstInterestCategory(form);
        await form.getByRole('button', { name: 'Simpan bidang karier' }).click();

        await expect(page.getByText('Bidang karier berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/bidang-karier', { put: true });
        await form.locator('select[name="job_zone"]').selectOption('4');
        await form.getByRole('button', { name: 'Update bidang karier' }).click();
        await expect(page.getByText('Bidang karier berhasil diperbarui.').first()).toBeVisible();

        // DELETE
        await tableRow(page, nama).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Bidang karier berhasil dihapus.').first()).toBeVisible();
    });

    test('Kategori Postingan: create, update, delete', async ({ page }) => {
        await page.goto('/admin/kategori-postingan');

        const nama = uniqueValue('Kategori Artikel E2E');

        await page.getByRole('button', { name: 'Tambah kategori' }).first().click();
        let form = formByAction(page, '/admin/kategori-postingan');
        await form.locator('input[name="name"]').fill(nama);
        await form.getByRole('button', { name: 'Simpan kategori' }).click();

        await expect(page.getByText('Kategori postingan berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/kategori-postingan', { put: true });
        await form.locator('input[name="name"]').fill(`${nama} Baru`);
        await form.getByRole('button', { name: 'Update kategori' }).click();
        await expect(page.getByText('Kategori postingan berhasil diperbarui.').first()).toBeVisible();

        await tableRow(page, `${nama} Baru`).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Kategori postingan berhasil dihapus.').first()).toBeVisible();
    });

    test('Postingan: create publik, update jadi draft, delete', async ({ page }) => {
        // Seeder tidak membuat kategori postingan, jadi buat dulu lewat UI.
        const kategori = uniqueValue('Kategori E2E');
        await page.goto('/admin/kategori-postingan');
        await page.getByRole('button', { name: 'Tambah kategori' }).first().click();
        const kategoriForm = formByAction(page, '/admin/kategori-postingan');
        await kategoriForm.locator('input[name="name"]').fill(kategori);
        await kategoriForm.getByRole('button', { name: 'Simpan kategori' }).click();
        await expect(page.getByText('Kategori postingan berhasil dibuat.').first()).toBeVisible();

        await page.goto('/admin/postingan');

        const judul = uniqueValue('Artikel E2E');

        await page.getByRole('button', { name: 'Tambah postingan' }).first().click();
        let form = formByAction(page, '/admin/postingan');
        // Kategori postingan dikontrol Select2 (select asli disembunyikan).
        const kategoriSelect = form.locator('select[name="post_category_id"]');
        const kategoriValue = await kategoriSelect
            .locator('option', { hasText: kategori })
            .first()
            .getAttribute('value');
        await select2Value(kategoriSelect, { value: kategoriValue });
        await form.locator('input[name="judul"]').fill(judul);
        await form.locator('textarea[name="isi"]').fill('Isi artikel untuk pengujian end-to-end.');
        await form.locator('select[name="status"]').selectOption('published');
        await form.getByRole('button', { name: 'Simpan postingan' }).click();

        await expect(page.getByText('Postingan berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, judul)).toBeVisible();

        await tableRow(page, judul).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/postingan', { put: true });
        await form.locator('select[name="status"]').selectOption('draft');
        await form.getByRole('button', { name: 'Update postingan' }).click();
        await expect(page.getByText('Postingan berhasil diperbarui.').first()).toBeVisible();
        await expect(tableRow(page, judul)).toContainText(/draft/i);

        await tableRow(page, judul).getByRole('button', { name: 'Hapus' }).click();
        await expect(page.getByText('Postingan berhasil dihapus.').first()).toBeVisible();
    });

    test('Informasi Karier: create, update, delete', async ({ page }) => {
        await page.goto('/admin/careers');

        const judul = uniqueValue('Karier E2E');

        await page.getByRole('button', { name: 'Tambah karier' }).click();
        let form = formByAction(page, '/admin/careers');
        await form.locator('input[name="title"]').fill(judul);
        await form.locator('input[name="category"]').fill('Teknologi');
        await form.locator('textarea[name="description"]').fill('Deskripsi karier untuk pengujian E2E.');
        await form.getByRole('button', { name: 'Simpan karier' }).click();

        await expect(page.getByText('Informasi karier berhasil dibuat.').first()).toBeVisible();
        await expect(page.getByText(judul).first()).toBeVisible();

        // Kartu memakai <article>; scope per kartu agar tombol Edit/Delete
        // tidak ambigu dengan kartu lain atau elemen pembungkus.
        const card = page.locator('article').filter({ hasText: judul }).first();
        await card.getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/careers', { put: true });
        await form.locator('textarea[name="description"]').fill('Deskripsi karier diperbarui.');
        await form.getByRole('button', { name: 'Update karier' }).click();
        await expect(page.getByText('Informasi karier berhasil diperbarui.').first()).toBeVisible();

        await page.locator('article').filter({ hasText: judul }).first()
            .getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Informasi karier berhasil dihapus.').first()).toBeVisible();
    });

    test('Master Pertanyaan: create skala, update, delete', async ({ page }) => {
        await page.goto('/admin/master-pertanyaan');

        const teks = uniqueValue('Seberapa puas Anda terhadap layanan?');

        await page.getByRole('button', { name: 'Tambah pertanyaan' }).click();
        let form = formByAction(page, '/admin/master-pertanyaan');
        await form.locator('select[name="kategori"]').selectOption('angket');
        await form.locator('select[name="tipe_input"]').selectOption('skala');
        await form.locator('textarea[name="teks_pertanyaan"]').fill(teks);
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan pertanyaan' }).click();

        await expect(page.getByText('Pertanyaan berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, teks.slice(0, 30))).toBeVisible();

        await tableRow(page, teks.slice(0, 30)).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/master-pertanyaan', { put: true });
        await form.locator('textarea[name="teks_pertanyaan"]').fill(`${teks} (diubah)`);
        await form.getByRole('button', { name: 'Update pertanyaan' }).click();
        await expect(page.getByText('Pertanyaan berhasil diperbarui.').first()).toBeVisible();

        await tableRow(page, teks.slice(0, 30)).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Pertanyaan berhasil dihapus.').first()).toBeVisible();
    });

    test('Master Pertanyaan: pilihan ganda minimal 2 opsi', async ({ page }) => {
        await page.goto('/admin/master-pertanyaan');
        await page.getByRole('button', { name: 'Tambah pertanyaan' }).click();

        const form = formByAction(page, '/admin/master-pertanyaan');
        await form.locator('select[name="tipe_input"]').selectOption('pilihan_ganda');
        await form.locator('textarea[name="teks_pertanyaan"]').fill(uniqueValue('Pertanyaan uji pilihan ganda'));
        await form.locator('input[name="opsi[0]"]').fill('Opsi satu');
        await form.locator('input[name="opsi[1]"]').fill('');
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan pertanyaan' }).click();

        await expect(page.getByText(/Pilihan ganda minimal punya 2 opsi/i).first()).toBeVisible();
    });

    test('Soal Sosiometri: create, update, delete', async ({ page }) => {
        await page.goto('/admin/sosiometri-soal');

        const pertanyaan = uniqueValue('Siapa yang sering membantumu belajar?');

        await page.getByRole('button', { name: 'Tambah soal' }).click();
        let form = formByAction(page, '/admin/sosiometri-soal');
        await form.locator('select[name="jenis"]').selectOption('positif');
        await form.locator('select[name="aspek"]').selectOption('belajar');
        await form.locator('textarea[name="pertanyaan"]').fill(pertanyaan);
        await form.locator('input[name="urutan"]').fill('99');
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan soal' }).click();

        await expect(page.getByText('Soal sosiometri berhasil ditambahkan.').first()).toBeVisible();
        await expect(tableRow(page, pertanyaan.slice(0, 25))).toBeVisible();

        await tableRow(page, pertanyaan.slice(0, 25)).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/sosiometri-soal', { put: true });
        await form.locator('select[name="aspek"]').selectOption('karier');
        await form.getByRole('button', { name: 'Update soal' }).click();
        await expect(page.getByText('Soal sosiometri berhasil diperbarui.').first()).toBeVisible();

        await tableRow(page, pertanyaan.slice(0, 25)).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Soal sosiometri berhasil dihapus.').first()).toBeVisible();
    });

    test('Approval Guru BK: daftarkan guru baru lalu setujui', async ({ page }) => {
        // Form registrasi berada di group `guest`, jadi sesi admin harus dilepas dulu.
        await page.context().clearCookies();

        const noHp = `0818${Date.now().toString().slice(-8)}`;
        await page.goto('/register/guru-bk');
        await page.locator('input[name="name"]').fill('Guru Approval E2E');
        await page.locator('select[name="sekolah_id"]').selectOption({ index: 1 });
        await page.locator('input[name="no_hp"]').fill(noHp);
        await page.locator('input[name="nip"]').fill(`APR${Date.now()}`.slice(0, 18));
        await page.locator('input[name="password"]').fill('password');
        await page.locator('input[name="password_confirmation"]').fill('password');
        await page.getByRole('button', { name: 'Kirim pendaftaran' }).click();
        await expect(page.getByText(/Pendaftaran Guru BK berhasil dikirim/i).first()).toBeVisible();

        // Login sebagai admin lalu setujui.
        await loginAs(page, 'admin');
        await page.goto('/admin/approvals');
        await expect(page.getByRole('heading', { name: /Persetujuan|Approval|Guru/i }).first()).toBeVisible();

        const row = page.locator('tr').filter({ hasText: noHp }).first();
        await expect(row).toBeVisible();
        await row.getByRole('button', { name: 'Approve' }).click();
        await expect(page.getByText('Pendaftaran Guru BK berhasil disetujui.').first()).toBeVisible();
    });

    test('Approval: filter status pending', async ({ page }) => {
        await page.goto('/admin/approvals');
        await page.locator('select[name="status"]').selectOption('pending');
        await page.getByRole('button', { name: 'Filter' }).click();
        await expect(page).toHaveURL(/status=pending/);
    });

    test('Konseling: daftar pengajuan siswa tampil untuk admin', async ({ page }) => {
        await page.goto('/admin/consultations');
        await expect(page.getByRole('heading', { name: /Konseling/i }).first()).toBeVisible();
        await expect(page.getByText('Persiapan ujian akhir').first()).toBeVisible();

        await page.locator('#filter-status').selectOption('pending');
        await page.getByRole('button', { name: 'Filter' }).click();
        await expect(page).toHaveURL(/status=pending/);
    });

    test('Rapor BK: halaman indeks dan detail dapat dibuka', async ({ page }) => {
        await page.goto('/admin/rapor');
        await expect(page.getByRole('heading', { name: /Rapor/i }).first()).toBeVisible();

        const detailLink = page.getByRole('link', { name: 'Detail' }).first();
        if (await detailLink.count()) {
            await detailLink.click();
            await expect(page.getByRole('heading', { name: /Rapor BK/i }).first()).toBeVisible();
        }
    });

    test('Laporan Konseling Individu: admin hanya bisa melihat & mencetak', async ({ page }) => {
        await page.goto('/admin/individual-reports');
        await expect(page.getByRole('heading', { name: /Laporan|Individu/i }).first()).toBeVisible();

        // Admin bersifat read-only: blok aksi (tambah/edit/hapus) hanya dirender
        // untuk Guru BK, sehingga tombolnya tidak ada sama sekali di admin.
        await expect(page.getByRole('button', { name: 'Tambah Laporan' })).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Edit' })).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Hapus Laporan' })).toHaveCount(0);

        const detailButton = page.getByRole('button', { name: 'Detail' }).first();
        if (await detailButton.count()) {
            await detailButton.click();
            await expect(page.getByText('Detail Laporan Individu').first()).toBeVisible();
        }
    });

    test('Perubahan Profil Guru: tampil (read-only)', async ({ page }) => {
        await page.goto('/admin/perubahan-profil-guru');
        await expect(page.getByRole('heading', { name: /Perubahan Profil/i }).first()).toBeVisible();
    });

    test('Log Aktivitas: mencatat aksi CRUD admin', async ({ page }) => {
        // Lakukan satu aksi agar log terisi.
        await page.goto('/admin/kategori-postingan');
        const nama = uniqueValue('Log Uji');
        await page.getByRole('button', { name: 'Tambah kategori' }).first().click();
        const form = formByAction(page, '/admin/kategori-postingan');
        await form.locator('input[name="name"]').fill(nama);
        await form.getByRole('button', { name: 'Simpan kategori' }).click();
        await expect(page.getByText('Kategori postingan berhasil dibuat.').first()).toBeVisible();

        await page.goto('/admin/activity-logs');
        await expect(page.getByRole('heading', { name: /Log Aktivitas/i }).first()).toBeVisible();
        await expect(page.getByText(/postcategor|kategori|created/i).first()).toBeVisible();
    });
});
