import { answerAllLikert, expect, formByAction, loginAs, tableRow, test, uniqueValue } from './helpers.mjs';

/**
 * Centang soal Likert yang sedang tampil saja, lalu kembalikan jumlahnya.
 *
 * Wizard instrumen Strategi Belajar / Asesmen Masalah menyimpan seluruh soal
 * dalam satu form, tetapi menyembunyikan bagian yang belum dibuka dengan
 * `display:none`. Menyentang soal tersembunyi langsung memicu kegagalan
 * "Element is not visible", jadi jawaban diisi bertahap per bagian —
 * sekaligus selaras dengan perilaku pengguna nyata.
 *
 * @param {import('@playwright/test').Page} page
 * @param {number} value indeks opsi yang dipilih (0-based)
 */
async function isiSoalTampil(page, value = 4) {
    const names = await page
        .locator('input[type="radio"][name^="answers["]:visible')
        .evaluateAll((nodes) => [...new Set(nodes.map((node) => node.getAttribute('name')))]);

    for (const name of names) {
        // Klik label-nya, bukan input `sr-only` yang di-`clip`. Menyentang
        // langsung dengan `check({ force: true })` bisa menyetel properti
        // `checked` tanpa memancarkan `change`, sehingga `x-model` Alpine tidak
        // terisi dan validasi wizard menolak maju meski soal tampak tercentang.
        await page
            .locator(`input[type="radio"][name="${name}"][value="${value}"]`)
            .locator('xpath=ancestor::label[1]')
            .click();
    }

    return names.length;
}

/**
 * Tunggu sampai wizard benar-benar menampilkan bagian yang diharapkan.
 *
 * `x-text` (indikator "Bagian N dari M") diperbarui lebih dulu daripada
 * `x-show`, sehingga teksnya bisa sudah berpindah padahal bagian lama masih
 * tampil. Membaca soal `:visible` pada saat itu menangkap bagian lama, lalu
 * kliknya gagal karena bagian itu segera disembunyikan. Karena itu yang
 * ditunggu adalah visibilitas pembungkus `x-show` bagian tersebut, plus
 * hilangnya bagian sebelumnya.
 *
 * @param {import('@playwright/test').Page} page
 * @param {number} langkah nomor bagian (1-based, berurutan tanpa jeda)
 */
async function tungguBagian(page, langkah) {
    await expect(page.locator(`[x-show="section === ${langkah}"]`)).toBeVisible();

    if (langkah > 1) {
        await expect(page.locator(`[x-show="section === ${langkah - 1}"]`)).toBeHidden();
    }
}

/**
 * E2E — Modul siswa: instrumen Yola, sosiometri, konseling, konsultasi prodi,
 * penilaian layanan, angket, postingan, karier, join kelas, dan chatbot.
 */
test.describe('Siswa — Layanan & Instrumen', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, 'siswa');
    });

    test('Dashboard siswa menampilkan ringkasan layanan', async ({ page }) => {
        await page.goto('/siswa/dashboard');
        await expect(page).toHaveURL(/\/siswa\/dashboard/);
        await expect(page.getByRole('heading').first()).toBeVisible();
    });

    test('Instrumen Yola: isi kategori Kepribadian sampai selesai', async ({ page }) => {
        // Catatan: kategori `minat_kerja` hanya berlaku untuk SMK, sedangkan
        // `kepribadian` berlaku bagi semua jenjang. Dashboard siswa pun hanya
        // menautkan minat_kerja saat jenjang SMK, jadi tes memakai kategori
        // yang berlaku bagi semua jenjang.
        await page.goto('/siswa/instruments?category=kepribadian');
        await expect(page.getByRole('heading', { name: /Instrumen Asesmen/i }).first()).toBeVisible();

        if (await page.getByText('Soal belum tersedia').isVisible().catch(() => false)) {
            test.skip(true, 'Belum ada soal aktif untuk kategori Kepribadian.');
        }

        const answered = await answerAllLikert(page, 4);
        expect(answered).toBeGreaterThan(0);

        // Kategori Kepribadian dirender sebagai form MBTI (yola-form), sehingga
        // label tombolnya "Kirim dan Lihat Tipe Kepribadian", bukan
        // "Kirim dan Lihat Skor" seperti kategori Yola lainnya.
        await page.getByRole('button', { name: /Kirim dan Lihat/ }).click();
        await expect(
            page.getByText('Jawaban instrumen berhasil dikirim dan diskor otomatis.').first(),
        ).toBeVisible();
    });

    test('Instrumen: URL tanpa kategori mengikuti jenjang siswa, bukan 404', async ({ page }) => {
        // Siswa demo berjenjang SMA. `minat_kerja` memang dibatasi 404 untuk SMA
        // (minatKerjaIndex), tetapi itu hanya boleh terjadi ketika kategori
        // diminta eksplisit. Tanpa parameter, controller men-default ke
        // `kepribadian` yang berlaku bagi semua jenjang.
        await page.goto('/siswa/instruments');

        await expect(page).toHaveURL(/\/siswa\/instruments$/);
        await expect(page.getByRole('heading', { name: /Instrumen Asesmen/i }).first()).toBeVisible();
        // Kategori aktif tampil sebagai judul tingkat 2 pada panel instrumen.
        await expect(page.getByRole('heading', { name: /^Kepribadian$/ }).first()).toBeVisible();
    });

    test('Hasil instrumen Strategi Belajar & Masalah terbuka tanpa error', async ({ page }) => {
        // Halaman hasil keduanya meng-`abort(404)` bila siswa belum pernah
        // mengisi instrumen, lalu me-render view `strategi-belajar-result`
        // dan `masalah-result`. Karena kedua view pernah rusak oleh pola
        // `@php` inline, uji ini mengisi instrumen sampai tuntas agar view
        // benar-benar dirender — kalau kena regresi, halaman balik 500 dan
        // heading berikut tidak akan muncul.
        const kategori = [
            { code: 'gaya_belajar', heading: 'Hasil Tes Strategi Belajar' },
            { code: 'angket_masalah', heading: 'Hasil Asesmen Masalah' },
        ];

        for (const { code, heading } of kategori) {
            await page.goto(`/siswa/instruments?category=${code}`);
            await expect(
                page.getByRole('heading', { name: /Instrumen Asesmen/i }).first(),
            ).toBeVisible();

            // Alpine menerapkan perubahan DOM secara tertunda, jadi membaca
            // soal yang `:visible` tepat setelah tombol diklik bisa menangkap
            // campuran bagian lama dan baru. Indikator "Bagian N dari M" ikut
            // diperbarui pada batch yang sama dengan `x-show`, sehingga
            // menunggu teksnya adalah cara paling andal menunggu wizard benar-
            // benar berpindah. Indikator ini juga baru muncul setelah Alpine
            // terinisialisasi (tombol aksi memakai `x-cloak`).
            const indikator = page.locator('p[x-text*="Bagian"]').first();
            await expect(indikator).toHaveText(/^Bagian \d+ dari \d+$/);

            const totalBagian = Number((await indikator.textContent()).match(/dari (\d+)/)[1]);

            for (let langkah = 1; langkah <= totalBagian; langkah += 1) {
                await tungguBagian(page, langkah);

                expect(await isiSoalTampil(page, 4)).toBeGreaterThan(0);

                const langkahTerakhir = langkah === totalBagian;
                await (langkahTerakhir
                    ? page.getByRole('button', { name: /^Kirim dan Lihat Hasil$/ })
                    : page.getByRole('button', { name: /^Simpan Bagian$/ })
                )
                    .first()
                    .click();

                // Konfirmasi modal "Sudah yakin dengan jawabanmu?".
                const dialog = page.getByRole('dialog');
                await expect(dialog).toBeVisible();
                await dialog.getByRole('button', { name: 'Simpan', exact: true }).click();
            }

            await expect(page.getByRole('heading', { name: heading }).first()).toBeVisible();

            // Flash sukses yang dikirim controller lewat redirect harus ikut
            // tampil. Kedua halaman ini pernah membisukan konfirmasi, sehingga
            // siswa tidak bisa membedakan "jawaban tersimpan" dan sekadar
            // "hasil dirender".
            await expect(
                page.getByText('Jawaban instrumen berhasil dikirim dan diskor otomatis.').first(),
            ).toBeVisible();
        }
    });

    test('Minat Bakat Kuliah (Key) menampilkan wizard asesmen', async ({ page }) => {
        await page.goto('/siswa/minat-bakat');
        await expect(page.getByRole('heading').first()).toBeVisible();
    });

    test('Sosiometri: pilih teman untuk setiap pertanyaan aktif', async ({ page }) => {
        // Prasyarat lintas role: instrumen sosiometri harus diaktifkan guru per
        // kelas, jika tidak /siswa/sociometry membalas 403 dan form tidak ada.
        await loginAs(page, 'guru');
        await page.goto('/guru/sociometry/manage');
        // Tombol tiap kelas membawa nilai hidden `is_active`, jadi hanya klik
        // "Aktifkan" (yang masih nonaktif). `exact: true` dipakai karena
        // "Aktifkan" adalah substring dari "Nonaktifkan".
        for (let i = 0; i < 12; i += 1) {
            const aktifkan = page.getByRole('button', { name: 'Aktifkan', exact: true });
            if (!(await aktifkan.count())) {
                break;
            }
            await aktifkan.first().click();
            await expect(
                page.getByText('Status instrumen sosiometri diperbarui.').first(),
            ).toBeVisible();
        }

        await loginAs(page, 'siswa');
        await page.goto('/siswa/sociometry');

        if (
            await page
                .getByText('Instrumen sosiometri untuk kelas Anda sedang dinonaktifkan')
                .first()
                .isVisible()
                .catch(() => false)
        ) {
            test.skip(true, 'Kelas siswa tidak terdaftar pada pengaturan sosiometri guru.');
        }

        if (await page.getByText('Belum ada pertanyaan sosiometri').isVisible().catch(() => false)) {
            test.skip(true, 'Belum ada soal sosiometri aktif.');
        }

        const selects = page.locator('select[name$="[chosen_student_id]"]');
        const count = await selects.count();
        test.skip(count === 0, 'Tidak ada pertanyaan sosiometri yang perlu dijawab.');

        for (let i = 0; i < count; i += 1) {
            await selects.nth(i).selectOption({ index: 1 });
            await page
                .locator('textarea[name$="[reason]"]')
                .nth(i)
                .fill('Karena kami sering belajar bersama.');
        }

        await page.getByRole('button', { name: 'Simpan Jawaban' }).click();
        await expect(page.getByText('Jawaban sosiometri berhasil disimpan.').first()).toBeVisible();
    });

    test('Konseling: ajukan pengajuan baru ke Guru BK', async ({ page }) => {
        await page.goto('/siswa/consultations');

        const topik = uniqueValue('Saya ingin membahas');
        await page.getByRole('button', { name: 'Ajukan konseling baru' }).click();

        const form = formByAction(page, '/siswa/consultations');
        await form.locator('input[name="subject"]').fill(topik);
        await form.locator('select[name="case_category"]').selectOption('belajar');
        await form.locator('select[name="counselor_id"]').selectOption({ index: 1 });
        await form.locator('textarea[name="details"]').fill('Saya kesulitan mengatur waktu belajar saya.');
        await form.getByRole('button', { name: 'Kirim pengajuan' }).click();

        await expect(
            page
                .getByText(
                    'Pengajuan konseling berhasil dikirim. Guru BK akan meninjau permintaan Anda.',
                )
                .first(),
        ).toBeVisible();
        await expect(page.getByText(topik).first()).toBeVisible();
    });

    test('Konsultasi Prodi: ajukan diskusi pilihan program studi PCR', async ({ page }) => {
        await page.goto('/siswa/konsultasi-prodi');

        await page.getByRole('button', { name: 'Ajukan konsultasi prodi' }).click();
        const form = formByAction(page, '/siswa/konsultasi-prodi');
        // Tunggu sampai form benar-benar tampil. Toggle membuka form memakai
        // x-show Alpine, dan locator.count() tidak menunggu — tanpa baris ini
        // form masih tersembunyi saat dihitung sehingga tes salah-salah me-skip.
        await expect(form).toBeVisible();

        const prodiOptions = await form.locator('select[name="program_studi_id"] option').count();
        test.skip(prodiOptions <= 1, 'Belum ada prodi PCR terverifikasi.');

        const topik = uniqueValue('Cocokkah saya masuk');
        await form.locator('input[name="subject"]').fill(topik);
        await form.locator('select[name="program_studi_id"]').selectOption({ index: 1 });
        await form.locator('select[name="counselor_id"]').selectOption({ index: 1 });
        await form.locator('textarea[name="details"]').fill('Saya ingin tahu prospek karier setelah lulus.');
        await form.getByRole('button', { name: 'Kirim pengajuan konsultasi prodi' }).click();

        await expect(
            page
                .getByText(
                    'Pengajuan konsultasi prodi kuliah berhasil dikirim. Guru BK akan meninjau permintaan Anda.',
                )
                .first(),
        ).toBeVisible();
    });

    test('Penilaian layanan: beri skor untuk konseling yang sudah selesai', async ({ page }) => {
        await page.goto('/siswa/penilaian');
        await expect(page.getByRole('heading', { name: /Penilaian Layanan BK/i }).first()).toBeVisible();

        const nilaiLink = page.getByRole('link', { name: /^(Nilai konseling terbaru|Nilai Sekarang)$/ }).first();
        if (!(await nilaiLink.count())) {
            test.skip(true, 'Belum ada konseling selesai yang perlu dinilai.');
        }

        await nilaiLink.click();
        await expect(page).toHaveURL(/\/siswa\/penilaian\/buat/);

        // Tiga aspek memakai radio star rating per aspek.
        const aspects = ['skor_materi', 'skor_cara', 'skor_manfaat'];
        for (const aspect of aspects) {
            await page.locator(`input[name="${aspect}"][value="4"]`).check({ force: true });
        }
        await page.locator('textarea[name="catatan"]').fill('Layanan sudah sangat membantu.');
        await page.getByRole('button', { name: 'Kirim penilaian' }).click();

        await expect(page.getByText('Terima kasih! Penilaianmu sudah disimpan.').first()).toBeVisible();
    });

    test('Angket BK: isi seluruh jawaban lalu simpan', async ({ page }) => {
        await page.goto('/siswa/angket');

        if (await page.getByText('Belum ada soal angket aktif').first().isVisible().catch(() => false)) {
            test.skip(true, 'Belum ada soal angket aktif.');
        }

        await page.getByRole('link', { name: /^(Isi Angket|Lihat Jawaban)$/ }).click();
        await expect(page).toHaveURL(/\/siswa\/angket\/isi/);

        // Jawab semua soal (skala -> select, pilihan ganda -> radio, isian -> textarea).
        const selects = page.locator('form select[name^="jawaban["]');
        for (let i = 0; i < (await selects.count()); i += 1) {
            await selects.nth(i).selectOption('3');
        }

        const radios = await page
            .locator('input[type="radio"][name^="jawaban["]')
            .evaluateAll((nodes) => [...new Set(nodes.map((n) => n.getAttribute('name')))]);
        for (const name of radios) {
            await page.locator(`input[type="radio"][name="${name}"]`).first().check({ force: true });
        }

        const textareas = page.locator('form textarea[name^="jawaban["]');
        for (let i = 0; i < (await textareas.count()); i += 1) {
            await textareas.nth(i).fill('Saya merasa cukup baik menjalani kegiatan sekolah.');
        }

        await page.getByRole('button', { name: 'Simpan Jawaban' }).click();
        // Form memakai konfirmasi Alpine "Yakin ingin menyimpan jawaban?".
        await page.getByRole('button', { name: 'Ya, simpan' }).click();

        await expect(page.getByText('Jawaban angket berhasil disimpan.').first()).toBeVisible();
    });

    test('Artikel BK: telusuri daftar dan baca detail', async ({ page }) => {
        await page.goto('/siswa/postingan');
        await expect(page.getByRole('heading', { name: /Artikel BK/i }).first()).toBeVisible();

        const baca = page.getByRole('link', { name: 'Baca selengkapnya' }).first();
        if (await baca.count()) {
            await baca.click();
            await expect(page).toHaveURL(/\/siswa\/postingan\//);
            await expect(page.getByRole('heading').first()).toBeVisible();
        }
    });

    test('Informasi karier tampil untuk siswa', async ({ page }) => {
        await page.goto('/siswa/careers');
        await expect(page.getByRole('heading').first()).toBeVisible();
    });

    test('Join kelas bimbingan memakai kode yang valid', async ({ page }) => {
        await page.goto('/siswa/dashboard');

        const codeInput = page.locator('input[name="code"]').first();
        if (!(await codeInput.count())) {
            test.skip(true, 'Form kode kelas tidak tampil di dashboard.');
        }

        await codeInput.fill('BK-KARIER');
        await page.getByRole('button', { name: 'Gabung' }).first().click();

        await expect(
            page.getByText(/Berhasil masuk ke kelas bimbingan|sudah tergabung/i).first(),
        ).toBeVisible();
    });

    test('Chatbot BK menjawab pertanyaan siswa', async ({ page }) => {
        await page.goto('/siswa/chatbot');

        const input = page.locator('input[placeholder*="Tulis pesan"]');
        await input.fill('Apa jalur masuk ke PCR?');
        await page.getByRole('button', { name: 'Kirim pesan' }).click();

        // Balasan chatbot muncul di dalam daftar percakapan.
        await expect(page.getByText('Apa jalur masuk ke PCR?').first()).toBeVisible();
        await expect(page.locator('text=/PCR|jalur|masuk/i').first()).toBeVisible({ timeout: 20000 });
    });

    test('Tryout siswa: kerjakan tryout aktif lalu kumpulkan jawaban', async ({ page }) => {
        const judul = uniqueValue('Tryout Siswa E2E');

        // --- Persiapan lintas role: guru membuat tryout yang mencakup kelas siswa.
        await loginAs(page, 'guru');

        // Pastikan ada soal tryout aktif.
        await page.goto('/guru/master-pertanyaan?kategori=tryout');
        if (!(await page.locator('tbody tr').count())) {
            await page.getByRole('button', { name: 'Tambah pertanyaan' }).click();
            const qForm = formByAction(page, '/guru/master-pertanyaan');
            await qForm.locator('select[name="kategori"]').selectOption('tryout');
            await qForm.locator('select[name="tipe_input"]').selectOption('skala');
            await qForm
                .locator('textarea[name="teks_pertanyaan"]')
                .fill(uniqueValue('Soal tryout uji'));
            await qForm.locator('select[name="is_active"]').selectOption('1');
            await qForm.getByRole('button', { name: 'Simpan pertanyaan' }).click();
        }

        await page.goto('/guru/tryout/buat');
        await page.locator('#judul').fill(judul);
        await page.locator('#deskripsi').fill('Tryout untuk pengujian alur siswa.');
        await page.locator('#durasi_menit').fill('30');
        await page.locator('#mulai_at').fill('2026-10-01T08:00');
        await page.locator('#selesai_at').fill('2027-12-31T17:00');
        await page.locator('#status').selectOption('aktif');
        // Centang SEMUA kelas supaya pasti mencakup kelas siswa terlepas dari seed.
        const kelasBoxes = page.locator('input[name="kelas_ids[]"]');
        for (let i = 0; i < (await kelasBoxes.count()); i += 1) {
            await kelasBoxes.nth(i).check({ force: true });
        }
        await page.locator('input[name="soal_ids[]"]').first().check({ force: true });
        await page.getByRole('button', { name: 'Simpan tryout' }).click();
        await expect(page.getByText('Tryout berhasil dibuat.').first()).toBeVisible();
        await expect(tableRow(page, judul)).toBeVisible();

        // --- Siswa mengerjakan.
        await loginAs(page, 'siswa');
        await page.goto('/siswa/tryout');

        // Lingkupkan pada kartu tryout milik judul di atas. `getByRole('link').first()`
        // tanpa lingkup akan selalu menemukan link navigasi sidebar, sehingga skip
        // tidak pernah terpicu dan tombol kumpulan tidak pernah ada.
        const kerjakan = page
            .locator('article')
            .filter({ hasText: judul })
            .locator('a[href*="/siswa/tryout/"]');
        if (!(await kerjakan.count())) {
            test.skip(true, 'Tryout aktif tidak tampil untuk kelas siswa ini.');
        }

        await kerjakan.first().click();

        // Isi seluruh soal lalu kumpulkan.
        const selects = page.locator('form select[name^="jawaban["]');
        for (let i = 0; i < (await selects.count()); i += 1) {
            await selects.nth(i).selectOption('3');
        }

        const radios = await page
            .locator('input[type="radio"][name^="jawaban["]')
            .evaluateAll((nodes) => [...new Set(nodes.map((n) => n.getAttribute('name')))]);
        for (const name of radios) {
            await page.locator(`input[type="radio"][name="${name}"]`).first().check({ force: true });
        }

        const textareas = page.locator('form textarea[name^="jawaban["]');
        for (let i = 0; i < (await textareas.count()); i += 1) {
            await textareas.nth(i).fill('Jawaban saya untuk soal esai tryout.');
        }

        await page.getByRole('button', { name: 'Kumpulkan jawaban' }).click();
        await expect(
            page
                .getByText(
                    'Jawaban tryout berhasil dikumpulkan. Skor tampil setelah Guru BK mempublikasikan hasil.',
                )
                .first(),
        ).toBeVisible({ timeout: 20000 });
    });

    test('Siswa dibatasi mengakses modul admin dan guru', async ({ page }) => {
        await page.goto('/admin/dashboard');
        await expect(page.getByRole('heading', { name: /Akses Ditolak/i }).first()).toBeVisible();

        await page.goto('/guru/dashboard');
        await expect(page.getByRole('heading', { name: /Akses Ditolak/i }).first()).toBeVisible();
    });
});
