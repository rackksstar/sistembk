import {
    answerAllLikert,
    expect,
    formByAction,
    loginAs,
    tableRow,
    test,
    uniqueValue,
} from './helpers.mjs';

/**
 * E2E — Modul Guru BK: data siswa (+import CSV), master pertanyaan,
 * soal instrumen, RPL, jurnal bulanan, laporan kelompok, dan tryout.
 */
test.describe('Guru — Manajemen Data & Layanan', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, 'guru');
    });

    test('Data Siswa: create, update, filter, delete', async ({ page }) => {
        await page.goto('/guru/students');
        await expect(page.getByRole('heading', { name: /Data Login Siswa|Siswa/i }).first()).toBeVisible();

        const nama = uniqueValue('Siswa Guru E2E');
        const nisn = `7${Date.now().toString().slice(-9)}`;

        // CREATE
        await page.getByRole('button', { name: 'Tambah siswa' }).click();
        let form = formByAction(page, '/guru/students');
        await form.locator('input[name="name"]').fill(nama);
        await form.locator('input[name="nisn"]').fill(nisn);
        await form.locator('input[name="birth_date"]').fill('2008-07-20');
        await form.locator('select[name="kelas_id"]').selectOption({ index: 1 });
        await form.getByRole('button', { name: 'Simpan data siswa' }).click();

        await expect(page.getByText('Data NISN dan tanggal lahir siswa berhasil disimpan.').first()).toBeVisible();
        await expect(tableRow(page, nama)).toBeVisible();

        // UPDATE
        await tableRow(page, nama).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/guru/students', { put: true });
        await form.locator('input[name="school"]').fill('SMA Negeri 1 Contoh');
        await form.getByRole('button', { name: 'Update data siswa' }).click();
        await expect(page.getByText('Data siswa berhasil diperbarui.').first()).toBeVisible();

        // FILTER
        await page.locator('input[name="search"]').fill(nama);
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(tableRow(page, nama)).toBeVisible();

        // DELETE
        await tableRow(page, nama).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Data siswa berhasil dihapus.').first()).toBeVisible();
    });

    test('Data Siswa: import CSV menambahkan data baru', async ({ page }) => {
        await page.goto('/guru/students');

        const nisn = `6${Date.now().toString().slice(-9)}`;
        const namaImpor = 'Siswa Impor E2E';
        // Baris tanpa kelas_id harus ditolak dengan pesan per baris, bukan
        // disimpan tanpa kelas.
        const nisnTanpaKelas = `8${Date.now().toString().slice(-9)}`;
        const namaTanpaKelas = 'Siswa Impor Tanpa Kelas E2E';

        // Siswa tanpa kelas_id tidak masuk lingkup query guru BK, jadi CSV
        // harus membawa kelas_id milik guru tersebut.
        const kelasSelect = page.locator('select[name="kelas_id"]').first();
        await kelasSelect.waitFor({ state: 'attached' });
        const kelasId = await kelasSelect.locator('option').nth(1).getAttribute('value');

        const csv = [
            'nama,nisn,tanggal_lahir,sekolah,kelas_id',
            `${namaImpor},${nisn},2008-08-09,SMA Negeri 1 Contoh,${kelasId}`,
            `${namaTanpaKelas},${nisnTanpaKelas},2008-08-10,SMA Negeri 1 Contoh,`,
        ].join('\n');

        await page.getByRole('button', { name: 'Import CSV' }).click();
        const form = formByAction(page, '/guru/students/import');
        await form.locator('input[name="csv_file"]').setInputFiles({
            name: 'siswa-e2e.csv',
            mimeType: 'text/csv',
            buffer: Buffer.from(csv, 'utf8'),
        });
        await form.getByRole('button', { name: 'Upload dan import' }).click();

        await expect(page.getByText(/Import selesai\./).first()).toBeVisible();
        // Baris 3 (data kedua) dilewati dan dilaporkan per baris.
        await expect(page.getByText('Baris 3: kelas_id wajib diisi.').first()).toBeVisible();

        await page.goto('/guru/students');
        await page.locator('input[name="search"]').fill(namaImpor);
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(tableRow(page, namaImpor)).toBeVisible();

        // Baris tanpa kelas_id tidak boleh ikut tersimpan.
        await page.locator('input[name="search"]').fill(namaTanpaKelas);
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(page.locator('tr', { hasText: namaTanpaKelas })).toHaveCount(0);

        // Bersihkan agar keadaan database kembali seperti semula.
        await page.locator('input[name="search"]').fill(namaImpor);
        await page.getByRole('button', { name: 'Cari' }).click();
        await tableRow(page, namaImpor).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Data siswa berhasil dihapus.').first()).toBeVisible();
    });

    test('Data Siswa: menolak NISN ganda', async ({ page }) => {
        await page.goto('/guru/students');
        await page.getByRole('button', { name: 'Tambah siswa' }).click();

        const form = formByAction(page, '/guru/students');
        await form.locator('input[name="name"]').fill(uniqueValue('Siswa Uji'));
        await form.locator('input[name="nisn"]').fill('0061234567');
        await form.locator('input[name="birth_date"]').fill('2008-07-20');
        await form.locator('select[name="kelas_id"]').selectOption({ index: 1 });
        await form.getByRole('button', { name: 'Simpan data siswa' }).click();

        await expect(page.getByText('NISN sudah digunakan siswa lain.').first()).toBeVisible();
    });

    test('Master Pertanyaan: buat soal tryout untuk dipakai modul tryout', async ({ page }) => {
        await page.goto('/guru/master-pertanyaan');

        const teks = uniqueValue('Seberapa yakin kamu memahami materi?');

        await page.getByRole('button', { name: 'Tambah pertanyaan' }).click();
        const form = formByAction(page, '/guru/master-pertanyaan');
        await form.locator('select[name="kategori"]').selectOption('tryout');
        await form.locator('select[name="tipe_input"]').selectOption('skala');
        await form.locator('textarea[name="teks_pertanyaan"]').fill(teks);
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan pertanyaan' }).click();

        await expect(page.getByText('Pertanyaan berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, teks.slice(0, 30))).toBeVisible();
    });

    test('Soal Instrumen (Yola): create minat kerja dengan pilihan jawaban, update, delete', async ({ page }) => {
        await page.goto('/guru/instrument-questions?module=yola');
        await expect(page.getByRole('heading', { name: /Soal Instrumen/i }).first()).toBeVisible();

        const soal = uniqueValue('Saya senang memperbaiki peralatan elektronik');

        await page.getByRole('button', { name: 'Tambah soal' }).click();
        let form = formByAction(page, '/guru/instrument-questions');
        await form.locator('select[name="category"]').selectOption('minat_kerja');
        await form.locator('textarea[name="question"]').fill(soal);
        await form.locator('input[name="options[0][label]"]').fill('Sangat Tidak Suka');
        await form.locator('input[name="options[0][score]"]').fill('1');
        await form.locator('input[name="options[1][label]"]').fill('Sangat Suka');
        await form.locator('input[name="options[1][score]"]').fill('5');
        await form.locator('input[name="is_active"]').check();
        await form.getByRole('button', { name: 'Simpan soal' }).click();

        await expect(page.getByText('Soal instrumen berhasil ditambahkan.').first()).toBeVisible();
        await expect(page.getByText(soal).first()).toBeVisible();

        // UPDATE — Edit/Hapus berada di <td> baris, bukan di dalam form.
        await tableRow(page, soal).getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/guru/instrument-questions', { put: true });
        await form.locator('textarea[name="question"]').fill(`${soal} (diubah)`);
        await form.getByRole('button', { name: 'Update soal' }).click();
        await expect(page.getByText('Soal instrumen berhasil diperbarui.').first()).toBeVisible();

        // DELETE
        await tableRow(page, soal).getByRole('button', { name: 'Hapus' }).click();
        await expect(page.getByText('Soal instrumen berhasil dihapus.').first()).toBeVisible();
    });

    test('RPL Individu: create, update, delete', async ({ page }) => {
        await page.goto('/guru/rpls');
        await expect(page.getByRole('heading', { name: /^RPL/i }).first()).toBeVisible();

        const judul = uniqueValue('RPL Individu E2E');

        await page.getByRole('button', { name: 'Tambah RPL' }).click();
        let form = formByAction(page, '/guru/rpls');
        await form.locator('input[name="title"]').fill(judul);
        await form.locator('select[name="type"]').selectOption('individu');
        await form.locator('select[name="class_id"]').selectOption({ index: 1 });
        await form.locator('select[name="status"]').selectOption('aktif');
        await form.locator('input[name="service_date"]').fill('2026-11-10');
        await form.locator('textarea[name="tujuan"]').fill('Siswa memahami hambatan belajarnya.');
        await form.locator('textarea[name="materi"]').fill('Manajemen waktu belajar.');
        await form.locator('textarea[name="metode"]').fill('Konseling individu.');
        await form.locator('textarea[name="evaluasi"]').fill('Siswa menyusun jadwal belajar.');

        // Pilih satu siswa RPL individu.
        // Catatan: radio siswa TIDAK punya atribut `name` — nilai dikirim lewat
        // input tersembunyi `student_id` yang diikat Alpine (x-model="siswaId").
        // Daftar siswa juga mencakup semua kelas, sedangkan validasi mensyaratkan
        // siswa berasal dari kelas yang sama dengan RPL, jadi kelas disamakan dulu.
        const studentLabels = form.locator('label:has(input[type="radio"])');
        const labelCount = await studentLabels.count();

        let chosenIndex = -1;
        let chosenClassId = null;
        for (let i = 0; i < labelCount; i += 1) {
            // Ekspresi x-show ter-render menjadi cocokSiswa("Nama", <class_id>, ...)
            const attr = await studentLabels.nth(i).getAttribute('x-show');
            const match = attr?.match(/cocokSiswa\([^,]+,\s*(?:"(\d+)"|(\d+))\s*,/);
            if (match) {
                chosenIndex = i;
                chosenClassId = match[1] ?? match[2];
                break;
            }
        }

        if (chosenIndex >= 0 && chosenClassId) {
            await form.locator('select[name="class_id"]').selectOption(chosenClassId);
            await studentLabels.nth(chosenIndex)
                .locator('input[type="radio"]')
                .check({ force: true });
            // Verifikasi input tersembunyi benar-benar terisi oleh Alpine.
            await expect(form.locator('input[name="student_id"]')).not.toHaveValue('');
        }

        await form.getByRole('button', { name: 'Simpan RPL' }).click();
        await expect(page.getByText('RPL berhasil dibuat.').first()).toBeVisible();
        await expect(page.getByText(judul).first()).toBeVisible();

        // UPDATE — aksi per-item ada di <article>, bukan di dalam form.
        await page.locator('article').filter({ hasText: judul }).first()
            .getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/guru/rpls', { put: true });
        await form.locator('select[name="status"]').selectOption('selesai');
        await form.getByRole('button', { name: 'Update RPL' }).click();
        await expect(page.getByText('RPL berhasil diperbarui.').first()).toBeVisible();

        // DELETE
        await page.locator('article').filter({ hasText: judul }).first()
            .getByRole('button', { name: 'Hapus RPL' }).click();
        await expect(page.getByText('RPL berhasil dihapus.').first()).toBeVisible();
    });

    test('Jurnal Bulanan: create, update, delete', async ({ page }) => {
        await page.goto('/guru/journals');
        await expect(page.getByRole('heading', { name: /Jurnal/i }).first()).toBeVisible();

        const judul = uniqueValue('Jurnal E2E');

        await page.getByRole('button', { name: 'Tambah Jurnal' }).click();
        let form = formByAction(page, '/guru/journals');
        await form.locator('input[name="entry_date"]').fill('2026-10-15');
        await form.locator('input[name="title"]').fill(judul);
        await form.locator('select[name="service_type"]').selectOption('individu');
        await form.locator('select[name="case_category"]').selectOption('belajar');
        await form.locator('select[name="target_type"]').selectOption('siswa');
        await form.locator('input[name="target_name"]').fill('Andi Siswa');
        await form.locator('input[name="individual_services"]').fill('2');
        await form.locator('input[name="group_services"]').fill('1');
        await form.locator('input[name="classical_services"]').fill('0');
        await form.locator('textarea[name="summary"]').fill('Layanan fokus pada manajemen waktu belajar.');
        await form.getByRole('button', { name: 'Simpan Jurnal' }).click();

        await expect(page.getByText('Entri jurnal BK berhasil disimpan.').first()).toBeVisible();
        await expect(page.getByText(judul).first()).toBeVisible();

        await page.locator('article').filter({ hasText: judul }).first()
            .getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/guru/journals', { put: true });
        await form.locator('textarea[name="summary"]').fill('Layanan sudah diperbarui.');
        await form.getByRole('button', { name: 'Update Jurnal' }).click();
        await expect(page.getByText('Entri jurnal BK berhasil diperbarui.').first()).toBeVisible();

        await page.locator('article').filter({ hasText: judul }).first()
            .getByRole('button', { name: 'Hapus' }).click();
        await expect(page.getByText('Entri jurnal BK berhasil dihapus.').first()).toBeVisible();
    });

    test('Laporan Kelompok: create dari RPL kelompok yang sudah ada, lalu delete', async ({ page }) => {
        await page.goto('/guru/group-reports');
        await expect(page.getByRole('heading', { name: /Laporan Konseling Kelompok/i }).first()).toBeVisible();

        const judul = uniqueValue('Laporan Kelompok E2E');

        await page.getByRole('button', { name: 'Tambah Laporan' }).click();
        const form = formByAction(page, '/guru/group-reports');

        const rplSelect = form.locator('select[name="rpl_id"]');
        const rplOptions = await rplSelect.locator('option').count();

        if (rplOptions > 1) {
            await rplSelect.selectOption({ index: 1 });
            await form.locator('input[name="title"]').fill(judul);
            await form.locator('select[name="case_category"]').selectOption('sosial');
            await form.locator('input[name="service_date"]').fill('2026-10-20');
            await form.locator('input[name="duration_minutes"]').fill('90');
            await form.locator('input[name="location"]').fill('Ruang BK');
            await form.locator('textarea[name="result"]').fill('Siswa mampu menyampaikan pendapat secara asertif.');
            await form.locator('textarea[name="evaluation"]').fill('Partisipasi aktif seluruh anggota.');
            await form.getByRole('button', { name: 'Simpan Laporan' }).click();

            await expect(page.getByText('Laporan konseling kelompok berhasil dibuat.').first()).toBeVisible();
            await expect(page.getByText(judul).first()).toBeVisible();

            await page.locator('article').filter({ hasText: judul }).first()
                .getByRole('button', { name: 'Hapus Laporan' }).click();
            await expect(page.getByText('Laporan konseling kelompok berhasil dihapus.').first()).toBeVisible();
        } else {
            // Tidak ada RPL kelompok — pastikan form validasi menolak submit kosong.
            await form.getByRole('button', { name: 'Simpan Laporan' }).click();
            await expect(form.locator('select[name="rpl_id"]')).toBeVisible();
        }
    });

    test('Tryout: create, edit, publish/unpublish, dan isi nilai', async ({ page }) => {
        const judul = uniqueValue('Tryout E2E');

        // Pastikan ada soal tryout aktif.
        await page.goto('/guru/master-pertanyaan?kategori=tryout');
        if (!(await page.locator('tbody tr').count())) {
            await page.getByRole('button', { name: 'Tambah pertanyaan' }).click();
            const qForm = formByAction(page, '/guru/master-pertanyaan');
            await qForm.locator('select[name="kategori"]').selectOption('tryout');
            await qForm.locator('select[name="tipe_input"]').selectOption('skala');
            await qForm.locator('textarea[name="teks_pertanyaan"]').fill(uniqueValue('Soal tryout uji'));
            await qForm.locator('select[name="is_active"]').selectOption('1');
            await qForm.getByRole('button', { name: 'Simpan pertanyaan' }).click();
        }

        // CREATE
        await page.goto('/guru/tryout/buat');
        await expect(page.getByRole('heading', { name: /Tryout/i }).first()).toBeVisible();

        await page.locator('#judul').fill(judul);
        await page.locator('#deskripsi').fill('Tryout hasil pengujian E2E.');
        await page.locator('#durasi_menit').fill('30');
        await page.locator('#mulai_at').fill('2026-10-01T08:00');
        await page.locator('#selesai_at').fill('2026-12-31T17:00');
        await page.locator('#status').selectOption('aktif');
        await page.locator('input[name="kelas_ids[]"]').first().check({ force: true });
        await page.locator('input[name="soal_ids[]"]').first().check({ force: true });
        // Label tombol submit pada create tryout adalah "Simpan tryout".
        await page.getByRole('button', { name: 'Simpan tryout' }).click();

        await expect(page.getByText('Tryout berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, judul)).toBeVisible();

        // UPDATE
        await tableRow(page, judul).getByRole('link', { name: 'Edit' }).click();
        await page.locator('#durasi_menit').fill('45');
        await page.getByRole('button', { name: /Simpan|Perbarui/ }).first().click();
        await expect(page.getByText('Tryout berhasil diperbarui.').first()).toBeVisible();

        // PUBLISH lalu UNPUBLISH di halaman hasil.
        await page.goto('/guru/tryout');
        await tableRow(page, judul).getByRole('link', { name: 'Hasil' }).click();
        await expect(page.getByText(/Hasil belum dipublikasikan/).first()).toBeVisible();

        // Catatan: dialog confirm sudah di-accept otomatis oleh helper (helpers.mjs),
        // jadi jangan daftarkan page.once('dialog') lagi agar tidak "already handled".
        await page.getByRole('button', { name: 'Publikasikan hasil' }).click();
        await expect(page.getByText('Hasil tryout dipublikasikan. Siswa sekarang bisa melihat skor.').first()).toBeVisible();

        await page.getByRole('button', { name: 'Batalkan publikasi' }).click();
        await expect(page.getByText('Publikasi hasil dibatalkan. Siswa tidak lagi melihat skor.').first()).toBeVisible();

        // DELETE (tryout belum punya jawaban)
        await page.goto('/guru/tryout');
        await tableRow(page, judul).getByRole('button', { name: 'Hapus' }).click();
        await expect(page.getByText('Tryout berhasil dihapus.').first()).toBeVisible();
    });

    test('Soal Sosiometri guru: create dan delete', async ({ page }) => {
        await page.goto('/guru/sociometry/questions');

        const pertanyaan = uniqueValue('Siapa yang paling mendukungmu?');

        await page.getByRole('button', { name: 'Tambah soal' }).click();
        const form = formByAction(page, '/guru/sociometry/questions');
        await form.locator('select[name="jenis"]').selectOption('positif');
        await form.locator('select[name="aspek"]').selectOption('pertemanan');
        await form.locator('textarea[name="pertanyaan"]').fill(pertanyaan);
        await form.locator('select[name="is_active"]').selectOption('1');
        await form.getByRole('button', { name: 'Simpan soal' }).click();

        await expect(page.getByText('Soal sosiometri berhasil ditambahkan.').first()).toBeVisible();
        await expect(tableRow(page, pertanyaan.slice(0, 25))).toBeVisible();

        await tableRow(page, pertanyaan.slice(0, 25)).getByRole('button', { name: 'Delete' }).click();
        await expect(page.getByText('Soal sosiometri berhasil dihapus.').first()).toBeVisible();
    });

    test('Kelola instrumen sosiometri: aktifkan / nonaktifkan per kelas', async ({ page }) => {
        await page.goto('/guru/sociometry/manage');
        await expect(page.getByRole('heading', { name: /Kelola Instrumen Sosiometri/i }).first()).toBeVisible();

        const toggleButton = page.getByRole('button', { name: /^(Nonaktifkan|Aktifkan)$/ }).first();
        if (await toggleButton.count()) {
            await toggleButton.click();
            await expect(page.getByText('Status instrumen sosiometri diperbarui.').first()).toBeVisible();
        }
    });

    test('Halaman baca guru: penilaian, angket, feedback, riwayat, statistik, hasil instrumen', async ({ page }) => {
        await page.goto('/guru/penilaian');
        await expect(page.getByRole('heading', { name: /Laporan Penilaian Layanan/i }).first()).toBeVisible();

        await page.goto('/guru/angket');
        await expect(page.getByRole('heading', { name: /Laporan Angket BK/i }).first()).toBeVisible();

        // Route legacy: Feedback layanan digantikan oleh Laporan Penilaian,
        // sehingga /guru/feedback di-redirect (bukan menampilkan halaman sendiri).
        await page.goto('/guru/feedback');
        await expect(page).toHaveURL(/\/guru\/penilaian/);
        await expect(page.getByRole('heading', { name: /Laporan Penilaian Layanan/i }).first()).toBeVisible();

        await page.goto('/guru/student-histories');
        await expect(page.getByRole('heading', { name: /Riwayat Siswa/i }).first()).toBeVisible();

        await page.goto('/guru/service-statistics');
        await expect(page.getByRole('heading', { name: /Statistik Layanan/i }).first()).toBeVisible();

        await page.goto('/guru/instrument-results?module=yola');
        await expect(page.getByRole('heading', { name: /Hasil Instrumen/i }).first()).toBeVisible();

        await page.goto('/guru/instrument-results/matriks-bakat');
        await expect(
            page.getByRole('heading', { name: /Matriks Talents Mapping/i }).first(),
        ).toBeVisible();

        await page.goto('/guru/sociometry');
        await expect(page.getByRole('heading', { name: /Peta Sosiometri/i }).first()).toBeVisible();
    });
});
