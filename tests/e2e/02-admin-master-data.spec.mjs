import { spawnSync } from 'node:child_process';
import path from 'node:path';

import {
    disableNativeValidation,
    expect,
    formByAction,
    loginAs,
    tableRow,
    test,
    uniqueValue,
} from './helpers.mjs';
import { e2eEnv, projectRoot } from './scripts/env.mjs';

/**
 * E2E — Master data Admin: Sekolah, Kelas, Siswa, Guru BK, Akun, Kelas Bimbingan.
 */
test.describe('Admin — Data Master', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, 'admin');
    });

    test('Sekolah: create, update, filter, delete', async ({ page }) => {
        await page.goto('/admin/sekolah');
        await expect(page.getByRole('heading', { name: 'Daftar Sekolah MOU' })).toBeVisible();

        const nama = uniqueValue('SMA E2E');
        const npsn = `9${Date.now().toString().slice(-7)}`;

        // CREATE (form inline, bukan modal)
        await page.getByRole('button', { name: 'Tambah sekolah MOU' }).click();
        const createForm = formByAction(page, '/admin/sekolah');
        await createForm.locator('#create-nama').fill(nama);
        await createForm.locator('#create-npsn').fill(npsn);
        await createForm.locator('#create-alamat').fill('Jl. Uji E2E No. 1');
        await createForm.getByRole('button', { name: 'Simpan sekolah MOU' }).click();

        await expect(page.getByText('Sekolah berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        const namaBaru = `${nama} Diperbarui`;
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        const editForm = formByAction(page, '/admin/sekolah', { put: true });
        await editForm.locator('textarea[name="alamat"]').fill('Jl. Uji E2E No. 2 (diperbarui)');
        await editForm.getByRole('button', { name: 'Update sekolah' }).click();
        await expect(page.getByText('Sekolah berhasil diperbarui.').first()).toBeVisible();

        // RENAME agar filter pencarian bisa diverifikasi
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        const editForm2 = formByAction(page, '/admin/sekolah', { put: true });
        await editForm2.locator('input[name="nama"]').fill(namaBaru);
        await editForm2.getByRole('button', { name: 'Update sekolah' }).click();
        await expect(tableRow(page, namaBaru)).toBeVisible();

        // FILTER
        await page.locator('input[name="search"]').fill(namaBaru);
        await page.getByRole('button', { name: 'Terapkan' }).click();
        await expect(tableRow(page, namaBaru)).toBeVisible();

        // DELETE
        await tableRow(page, namaBaru).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Sekolah berhasil dihapus.').first()).toBeVisible();
    });

    test('Sekolah: validasi nama & NPSN wajib diisi', async ({ page }) => {
        await page.goto('/admin/sekolah');
        await page.getByRole('button', { name: 'Tambah sekolah MOU' }).click();

        const createForm = formByAction(page, '/admin/sekolah');
        // Matikan validasi HTML5 supaya submit benar-benar sampai ke server.
        await disableNativeValidation(createForm);
        await createForm.locator('#create-nama').fill('');
        await createForm.locator('#create-npsn').fill('');
        await createForm.getByRole('button', { name: 'Simpan sekolah MOU' }).click();

        await expect(createForm.locator('input[name="nama"]')).toBeVisible();
        // StoreSekolahRequest tidak punya pesan custom, jadi memakai pesan bawaan
        // Laravel (APP_LOCALE=en) dengan nama field asli: "nama" dan "npsn".
        await expect(page.getByText(/nama field is required/i).first()).toBeVisible();
    });

    test('Kelas: create, update, delete dengan relasi sekolah', async ({ page }) => {
        await page.goto('/admin/kelas');
        await expect(page.getByRole('heading', { name: /Kelas/i }).first()).toBeVisible();

        const nama = uniqueValue('XII E2E');

        // CREATE
        await page.getByRole('button', { name: 'Tambah kelas' }).click();
        let form = formByAction(page, '/admin/kelas');
        await form.locator('select[name="sekolah_id"]').selectOption({ index: 1 });
        await form.locator('input[name="nama"]').fill(nama);
        await form.locator('input[name="jenjang"]').fill('SMA');
        await form.locator('input[name="tingkatan"]').fill('XII');
        await form.getByRole('button', { name: 'Simpan kelas' }).click();

        await expect(page.getByText('Kelas berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/kelas', { put: true });
        await form.locator('input[name="nama"]').fill(`${nama} B`);
        await form.getByRole('button', { name: 'Update kelas' }).click();
        await expect(page.getByText('Kelas berhasil diperbarui.').first()).toBeVisible();

        // DELETE
        await tableRow(page, `${nama} B`).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Kelas berhasil dihapus.').first()).toBeVisible();
    });

    test('Kelas: menolak jenjang di luar daftar', async ({ page }) => {
        await page.goto('/admin/kelas');
        await page.getByRole('button', { name: 'Tambah kelas' }).click();

        const form = formByAction(page, '/admin/kelas');
        await form.locator('select[name="sekolah_id"]').selectOption({ index: 1 });
        await form.locator('input[name="nama"]').fill(uniqueValue('Kelas Uji'));
        await form.locator('input[name="jenjang"]').fill('SMA-KHUSUS');
        await form.getByRole('button', { name: 'Simpan kelas' }).click();

        await expect(page.getByText(/Jenjang harus salah satu dari/i).first()).toBeVisible();
    });

    test('Siswa: create, update, filter, delete', async ({ page }) => {
        await page.goto('/admin/students');
        await expect(page.getByRole('heading', { name: 'Manajemen Data Siswa' })).toBeVisible();

        const nama = uniqueValue('Siswa E2E');
        const nisn = `8${Date.now().toString().slice(-9)}`;

        // CREATE
        await page.getByRole('button', { name: 'Tambah siswa' }).click();
        let form = formByAction(page, '/admin/students');
        await form.locator('input[name="name"]').fill(nama);
        await form.locator('input[name="nisn"]').fill(nisn);
        await form.locator('input[name="birth_date"]').fill('2008-03-15');
        await form.locator('select[name="kelas_id"]').selectOption({ index: 1 });
        await form.locator('select[name="jenis_kelamin"]').selectOption('L');
        await form.locator('textarea[name="alamat"]').fill('Jl. Siswa E2E');
        await form.getByRole('button', { name: 'Simpan siswa' }).click();

        await expect(page.getByText('Data siswa berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/students', { put: true });
        await form.locator('textarea[name="alamat"]').fill('Jl. Siswa E2E (baru)');
        await form.getByRole('button', { name: 'Update siswa' }).click();
        await expect(page.getByText('Data siswa berhasil diperbarui.').first()).toBeVisible();

        // FILTER
        await page.locator('input[name="search"]').fill(nama);
        await page.getByRole('button', { name: 'Filter' }).first().click();
        await expect(tableRow(page, nama)).toBeVisible();

        // DELETE
        await tableRow(page, nama).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Data siswa berhasil dihapus.').first()).toBeVisible();
    });

    test('Siswa: menolak NISN ganda dan tanggal lahir masa depan', async ({ page }) => {
        await page.goto('/admin/students');
        await page.getByRole('button', { name: 'Tambah siswa' }).click();

        const form = formByAction(page, '/admin/students');
        await form.locator('input[name="name"]').fill(uniqueValue('Siswa Uji'));
        await form.locator('input[name="nisn"]').fill('0061234567'); // sudah dipakai seeder
        await form.locator('input[name="birth_date"]').fill('2008-03-15');
        await form.locator('select[name="kelas_id"]').selectOption({ index: 1 });
        await form.getByRole('button', { name: 'Simpan siswa' }).click();

        await expect(page.getByText('NISN sudah digunakan siswa lain.').first()).toBeVisible();
    });

    test('Siswa: import Excel dari template (dropdown kelas) dan laporkan baris bermasalah', async ({ page }) => {
        await page.goto('/admin/students');
        await expect(page.getByRole('heading', { name: 'Manajemen Data Siswa' })).toBeVisible();

        // Template Excel memuat dropdown kelas; berkas harus bisa diunduh.
        await page.getByRole('button', { name: 'Import Excel/CSV' }).click();
        await expect(page.getByRole('link', { name: /unduh template excel/i })).toBeVisible();
        const [download] = await Promise.all([
            page.waitForEvent('download'),
            page.getByRole('link', { name: /unduh template excel/i }).click(),
        ]);
        expect(download.suggestedFilename().toLowerCase()).toMatch(/\.xlsx$/);

        // Berkas .xlsx berisi kolom kelas "Nama Kelas (ID)" -> import harus
        // menyimpan siswa dan melaporkan baris tanpa kelas per baris.
        const fixture = path.join(projectRoot, 'storage', 'app', 'e2e-import-siswa-admin.xlsx');
        const out = spawnSync('php', [
            path.join(projectRoot, 'tests', 'e2e', 'scripts', 'make-student-import-fixture.php'),
            fixture,
        ], {
            cwd: projectRoot,
            env: { ...process.env, ...e2eEnv },
            stdio: 'inherit',
            shell: true,
        });
        expect(out.status).toBe(0);

        await page.goto('/admin/students');
        await page.getByRole('button', { name: 'Import Excel/CSV' }).click();
        const form = formByAction(page, '/admin/students/import');
        await form.locator('input[name="import_file"]').setInputFiles(fixture);
        await form.getByRole('button', { name: 'Upload dan import' }).click();

        await expect(page.getByText(/Import selesai\./).first()).toBeVisible();
        await expect(page.getByText('Baris 3: kelas_id wajib diisi.').first()).toBeVisible();

        await page.goto('/admin/students');
        await page.locator('input[name="search"]').fill('Siswa Impor Xlsx');
        await page.getByRole('button', { name: 'Filter' }).first().click();
        await expect(tableRow(page, 'Siswa Impor Xlsx')).toBeVisible();

        // Bersihkan agar database kembali seperti semula.
        await tableRow(page, 'Siswa Impor Xlsx').getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Data siswa berhasil dihapus.').first()).toBeVisible();
    });

    test('Guru BK: create membuat akun login, lalu update dan delete', async ({ page }) => {
        await page.goto('/admin/guru-bk');
        await expect(page.getByRole('heading', { name: /Guru BK/i }).first()).toBeVisible();

        const nama = uniqueValue('Guru E2E');
        const noHp = `0817${Date.now().toString().slice(-8)}`;

        // CREATE
        await page.getByRole('button', { name: 'Tambah Guru BK' }).click();
        let form = formByAction(page, '/admin/guru-bk');
        await form.locator('input[name="name"]').fill(nama);
        await form.locator('input[name="password"]').fill('password');
        await form.locator('input[name="password_confirmation"]').fill('password');
        await form.locator('select[name="status"]').selectOption('disetujui');
        await form.locator('select[name="sekolah_id"]').selectOption({ index: 1 });
        await form.locator('input[name="no_hp"]').fill(noHp);
        await form.locator('input[name="nip"]').fill(`E2E${Date.now()}`.slice(0, 18));
        await form.locator('input[name="jabatan"]').fill('Guru BK');
        await form.getByRole('button', { name: 'Simpan Guru BK' }).click();

        await expect(page.getByText('Data Guru BK berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/guru-bk', { put: true });
        await form.locator('input[name="jabatan"]').fill('Guru BK Senior');
        await form.getByRole('button', { name: 'Update Guru BK' }).click();
        await expect(page.getByText('Data Guru BK berhasil diperbarui.').first()).toBeVisible();

        // DELETE — akun user ikut terhapus
        await tableRow(page, nama).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Data Guru BK berhasil dihapus.').first()).toBeVisible();
    });

    test('Guru BK: menolak no HP yang sudah dipakai', async ({ page }) => {
        await page.goto('/admin/guru-bk');
        await page.getByRole('button', { name: 'Tambah Guru BK' }).click();

        const form = formByAction(page, '/admin/guru-bk');
        await form.locator('input[name="name"]').fill(uniqueValue('Guru Uji'));
        await form.locator('select[name="sekolah_id"]').selectOption({ index: 1 });
        await form.locator('input[name="no_hp"]').fill('081234567890'); // milik seeder
        await form.locator('input[name="password"]').fill('password');
        await form.locator('input[name="password_confirmation"]').fill('password');
        await form.getByRole('button', { name: 'Simpan Guru BK' }).click();

        // Validasi unique ditegakkan oleh FormRequest Laravel (bukan blok QueryException),
        // sehingga pesan yang tampil adalah pesan bawaan Laravel.
        await expect(page.getByText(/no hp has already been taken/i).first()).toBeVisible();
    });

    test('Akun pengguna: create, update role/status, delete', async ({ page }) => {
        await page.goto('/admin/users');
        await expect(page.getByRole('heading', { name: /Manajemen Pengguna|Pengguna/i }).first()).toBeVisible();

        const nama = uniqueValue('Akun E2E');
        const email = `${Date.now().toString(36)}@e2e.test`;

        // CREATE
        await page.getByRole('button', { name: 'Tambah pengguna' }).click();
        let form = formByAction(page, '/admin/users');
        await form.locator('input[name="name"]').fill(nama);
        await form.locator('input[name="email"]').fill(email);
        await form.locator('select[name="role"]').selectOption('siswa');
        await form.locator('select[name="status"]').selectOption('disetujui');
        await form.locator('input[name="password"]').fill('password');
        await form.locator('input[name="password_confirmation"]').fill('password');
        await form.getByRole('button', { name: 'Simpan pengguna' }).click();

        await expect(page.getByText('Akun pengguna berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE — ganti status jadi pending
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/admin/users', { put: true });
        await form.locator('select[name="status"]').selectOption('pending');
        await form.getByRole('button', { name: 'Update pengguna' }).click();
        await expect(page.getByText('Akun pengguna berhasil diperbarui.').first()).toBeVisible();

        // DELETE
        await tableRow(page, nama).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Akun pengguna berhasil dihapus.').first()).toBeVisible();
    });

    test('Akun pengguna: menolak email ganda', async ({ page }) => {
        await page.goto('/admin/users');
        await page.getByRole('button', { name: 'Tambah pengguna' }).click();

        const form = formByAction(page, '/admin/users');
        await form.locator('input[name="name"]').fill(uniqueValue('Akun Uji'));
        await form.locator('input[name="email"]').fill('admin@bk.test');
        await form.locator('select[name="role"]').selectOption('siswa');
        await form.locator('select[name="status"]').selectOption('disetujui');
        await form.locator('input[name="password"]').fill('password');
        await form.locator('input[name="password_confirmation"]').fill('password');
        await form.getByRole('button', { name: 'Simpan pengguna' }).click();

        // Batasi pada form create — halaman punya banyak form edit berisi input email.
        await expect(form.locator('input[name="email"]')).toBeVisible();
        await expect(page.getByText(/email has already been taken/i).first()).toBeVisible();
    });

    test('Kelas Bimbingan: create, tambahkan siswa, lepas siswa, delete', async ({ page }) => {
        await page.goto('/admin/guidance-classes');
        await expect(page.getByRole('heading', { name: /Kelas Bimbingan|Bimbingan/i }).first()).toBeVisible();

        const nama = uniqueValue('Kelas BK E2E');

        // CREATE — action store persis "/admin/guidance-classes", sedangkan form
        // attach siswa memakai "/admin/guidance-classes/{id}/students". Keduanya
        // sama-sama POST tanpa _method, jadi dibedakan lewat field `name`.
        await page.getByRole('button', { name: 'Tambah kelas' }).click();
        const form = formByAction(page, '/admin/guidance-classes', { has: 'input[name="name"]' });
        await form.locator('input[name="name"]').fill(nama);
        await form.locator('textarea[name="description"]').fill('Kelas bimbingan untuk pengujian E2E.');
        await form.getByRole('button', { name: 'Simpan kelas' }).click();

        await expect(page.getByText('Kelas bimbingan berhasil dibuat.').first()).toBeVisible();
        await expect(page.getByRole('heading', { name: nama })).toBeVisible();

        // Nama kelas berada di <h3> kartu, sehingga aksi per-kelas harus
        // di-scope ke <article> yang memuat nama tersebut.
        const card = page.locator('article').filter({ hasText: nama }).first();

        // ATTACH siswa
        const attachForm = card
            .locator('form')
            .filter({ has: page.locator('select[name="student_id"]') })
            .first();
        await attachForm.locator('select[name="student_id"]').selectOption({ index: 1 });
        await attachForm.getByRole('button', { name: 'Tambah', exact: true }).click();
        await expect(page.getByText('Siswa berhasil ditambahkan ke kelas.').first()).toBeVisible();

        // DETACH siswa (tombol "Hapus" membedakannya dari tombol "Delete" kelas)
        await page.locator('article').filter({ hasText: nama }).first()
            .getByRole('button', { name: 'Hapus', exact: true }).click();
        await expect(page.getByText('Siswa berhasil dihapus dari kelas.').first()).toBeVisible();
        await expect(page.getByText('Belum ada siswa').first()).toBeVisible();

        // DELETE kelas
        await page.locator('article').filter({ hasText: nama }).first()
            .getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Kelas bimbingan berhasil dihapus.').first()).toBeVisible();
        await expect(page.getByRole('heading', { name: nama })).toHaveCount(0);
    });
});
