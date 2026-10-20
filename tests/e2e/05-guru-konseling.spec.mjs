import { accounts, expect, formByAction, loginAs, test, uniqueValue } from './helpers.mjs';

/**
 * E2E — Alur konseling (siswa → guru), laporan individu, dan rapor BK.
 */
test.describe('Guru — Konseling, Laporan & Rapor', () => {
    /**
     * Ajukan konseling baru sebagai siswa, lalu kembalikan topiknya.
     */
    async function ajukanKonseling(page, topik) {
        await loginAs(page, 'siswa');
        await page.goto('/siswa/consultations');

        await page.getByRole('button', { name: 'Ajukan konseling baru' }).click();
        const form = formByAction(page, '/siswa/consultations');
        await form.locator('input[name="subject"]').fill(topik);
        await form.locator('select[name="case_category"]').selectOption('belajar');
        // Pilih konselor = guru yang akan login. Daftar konseling guru hanya
        // memuat pengajuan tanpa konselor atau miliknya sendiri, jadi memilih
        // konselor lain membuat barisnya tidak pernah muncul.
        await form.locator('select[name="counselor_id"]').selectOption({ label: accounts.guru.name });
        await form.locator('textarea[name="details"]').fill('Saya ingin membahas kendala belajar saya.');
        await form.getByRole('button', { name: 'Kirim pengajuan' }).click();

        await expect(
            page.getByText('Pengajuan konseling berhasil dikirim. Guru BK akan meninjau permintaan Anda.').first(),
        ).toBeVisible();
    }

    test('Alur penuh: siswa mengajukan, guru menyetujui, menjadwalkan, dan menyelesaikan', async ({ page }) => {
        const topik = uniqueValue('Konseling E2E');

        await ajukanKonseling(page, topik);

        // --- Guru meninjau ---
        await loginAs(page, 'guru');
        await page.goto('/guru/consultations');
        const row = page.locator('tr').filter({ hasText: topik }).first();
        await expect(row).toBeVisible();

        // SETUJUI
        await row.getByRole('button', { name: 'Setujui' }).click();
        await expect(page.getByText('Pengajuan konseling berhasil disetujui.').first()).toBeVisible();

        // JADWAL
        await page.goto('/guru/consultations');
        const rowDisetujui = page.locator('tr').filter({ hasText: topik }).first();
        await rowDisetujui.getByRole('button', { name: 'Jadwal' }).click();

        // Modal jadwal, tolak, dan laporan semuanya PATCH ke /guru/consultations,
        // jadi butuh pembeda `has` agar tidak memilih modal yang salah.
        let form = formByAction(page, '/guru/consultations', {
            method: 'PATCH',
            has: 'input[name="consultation_date"]',
        });
        await form.locator('input[name="consultation_date"]').fill('2026-11-15');
        await form.locator('input[name="consultation_time"]').fill('10:00');
        await form.locator('textarea[name="notes"]').fill('Sesi awal konseling belajar.');
        await form.getByRole('button', { name: 'Simpan jadwal' }).click();
        await expect(page.getByText('Jadwal konseling berhasil disimpan.').first()).toBeVisible();

        // LAPORAN (menyelesaikan sesi)
        await page.goto('/guru/consultations');
        const rowDijadwalkan = page.locator('tr').filter({ hasText: topik }).first();
        await rowDijadwalkan.getByRole('button', { name: 'Laporan' }).click();

        form = formByAction(page, '/guru/consultations', {
            method: 'PATCH',
            has: 'textarea[name="approach_technique"]',
        });
        await form.locator('select[name="case_category"]').selectOption('belajar');
        await form.locator('input[name="meeting_number"]').fill('1');
        await form.locator('input[name="duration_minutes"]').fill('60');
        await form.locator('input[name="location"]').fill('Ruang BK');
        await form.locator('select[name="semester"]').selectOption('1');
        await form.locator('input[name="year"]').fill('2026');
        await form.locator('textarea[name="approach_technique"]').fill('Konseling tatap muka.');
        await form.locator('textarea[name="result"]').fill('Siswa memahami penyebab penundaan tugas.');
        await form.locator('textarea[name="evaluation"]').fill('Siswa aktif menyusun rencana belajar.');
        await form.locator('textarea[name="follow_up"]').fill('Pantau progres minggu depan.');
        await form.getByRole('button', { name: 'Simpan laporan' }).click();

        await expect(page.getByText('Laporan konseling berhasil disimpan.').first()).toBeVisible();
        await expect(page.locator('tr').filter({ hasText: topik }).first()).toContainText(/Selesai/i);
    });

    test('Guru dapat menolak pengajuan dengan alasan', async ({ page }) => {
        const topik = uniqueValue('Konseling Ditolak E2E');
        await ajukanKonseling(page, topik);

        await loginAs(page, 'guru');
        await page.goto('/guru/consultations');
        const row = page.locator('tr').filter({ hasText: topik }).first();
        await row.getByRole('button', { name: 'Tolak' }).click();

        const form = formByAction(page, '/guru/consultations', {
            method: 'PATCH',
            has: 'textarea[name="rejection_reason"]',
        });
        await form.locator('textarea[name="rejection_reason"]').fill('Jadwal sedang penuh, mohon ajukan ulang.');
        await form.getByRole('button', { name: 'Tolak pengajuan' }).click();

        await expect(page.getByText('Pengajuan konseling ditolak.').first()).toBeVisible();
    });

    test('Penjadwalan menolak jadwal yang bentrok', async ({ page }) => {
        const topikA = uniqueValue('Konseling Bentrok A');
        const topikB = uniqueValue('Konseling Bentrok B');

        await ajukanKonseling(page, topikA);
        await ajukanKonseling(page, topikB);

        await loginAs(page, 'guru');

        // Jadwalkan pengajuan pertama.
        await page.goto('/guru/consultations');
        await page.locator('tr').filter({ hasText: topikA }).first().getByRole('button', { name: 'Setujui' }).click();
        await page.goto('/guru/consultations');
        await page.locator('tr').filter({ hasText: topikA }).first().getByRole('button', { name: 'Jadwal' }).click();
        let form = formByAction(page, '/guru/consultations', {
            method: 'PATCH',
            has: 'input[name="consultation_date"]',
        });
        await form.locator('input[name="consultation_date"]').fill('2026-11-20');
        await form.locator('input[name="consultation_time"]').fill('09:00');
        await form.getByRole('button', { name: 'Simpan jadwal' }).click();
        await expect(page.getByText(/Jadwal konseling berhasil disimpan/).first()).toBeVisible();

        // Coba jadwalkan pengajuan kedua di tanggal & jam yang sama.
        await page.goto('/guru/consultations');
        await page.locator('tr').filter({ hasText: topikB }).first().getByRole('button', { name: 'Setujui' }).click();
        await page.goto('/guru/consultations');
        await page.locator('tr').filter({ hasText: topikB }).first().getByRole('button', { name: 'Jadwal' }).click();
        form = formByAction(page, '/guru/consultations', {
            method: 'PATCH',
            has: 'input[name="consultation_date"]',
        });
        await form.locator('input[name="consultation_date"]').fill('2026-11-20');
        await form.locator('input[name="consultation_time"]').fill('09:00');
        await form.getByRole('button', { name: 'Simpan jadwal' }).click();

        await expect(page.getByText(/Jadwal bentrok dengan sesi konseling lain/i).first()).toBeVisible();
    });

    test('Laporan konseling individu: dibuat, diperbarui, dan dihapus', async ({ page }) => {
        const topik = uniqueValue('Laporan Individu E2E');
        await ajukanKonseling(page, topik);

        await loginAs(page, 'guru');

        // Siapkan sesi sampai berstatus dijadwalkan.
        await page.goto('/guru/consultations');
        await page.locator('tr').filter({ hasText: topik }).first().getByRole('button', { name: 'Setujui' }).click();
        await page.goto('/guru/consultations');
        await page.locator('tr').filter({ hasText: topik }).first().getByRole('button', { name: 'Jadwal' }).click();
        const scheduleForm = formByAction(page, '/guru/consultations', {
            method: 'PATCH',
            has: 'input[name="consultation_date"]',
        });
        await scheduleForm.locator('input[name="consultation_date"]').fill('2026-11-25');
        await scheduleForm.locator('input[name="consultation_time"]').fill('11:00');
        await scheduleForm.getByRole('button', { name: 'Simpan jadwal' }).click();

        // Buat laporan individu.
        await page.goto('/guru/individual-reports');
        const addButton = page.getByRole('button', { name: 'Tambah Laporan' });
        await expect(addButton).toBeVisible();
        await expect(addButton).toBeEnabled();

        await addButton.click();
        let form = formByAction(page, '/guru/individual-reports');
        await form.locator('select[name="consultation_id"]').selectOption({ index: 1 });
        await form.locator('input[name="report_title"]').fill(topik);
        await form.locator('input[name="meeting_number"]').fill('1');
        await form.locator('input[name="duration_minutes"]').fill('45');
        await form.locator('input[name="location"]').fill('Ruang BK 2');
        await form.locator('select[name="semester"]').selectOption('1');
        await form.locator('input[name="year"]').fill('2026');
        await form.locator('textarea[name="result"]').fill('Siswa mengenali pola belajarnya.');
        await form.locator('textarea[name="evaluation"]').fill('Siswa menunjukkan kemajuan.');
        await form.locator('textarea[name="follow_up"]').fill('Sesi lanjutan bulan depan.');
        await form.getByRole('button', { name: 'Simpan Laporan' }).click();

        await expect(page.getByText('Laporan konseling individu berhasil dibuat.').first()).toBeVisible();
        await expect(page.getByText(topik).first()).toBeVisible();

        // UPDATE (tombol Edit berada di dalam kartu, di luar form)
        const card = page.locator('article').filter({ hasText: topik }).first();
        await card.getByRole('button', { name: 'Edit' }).click();
        form = formByAction(page, '/guru/individual-reports', { method: 'PUT' });
        await form.locator('textarea[name="result"]').fill('Siswa mengenali pola belajarnya (diperbarui).');
        await form.getByRole('button', { name: 'Update Laporan' }).click();
        await expect(page.getByText('Laporan konseling individu berhasil diperbarui.').first()).toBeVisible();

        // DELETE (form hapus inline di dalam kartu)
        await page
            .locator('article')
            .filter({ hasText: topik })
            .locator('form[action*="/guru/individual-reports"]')
            .getByRole('button', { name: 'Hapus Laporan' })
            .click();
        await expect(page.getByText('Laporan individu dihapus; sesi konseling tetap tersimpan.').first()).toBeVisible();
    });

    test('Rapor BK: buat/isi rapor siswa dan simpan', async ({ page }) => {
        await loginAs(page, 'guru');
        await page.goto('/guru/rapor');
        await expect(page.getByRole('heading', { name: /Rapor BK/i }).first()).toBeVisible();

        const editLink = page.getByRole('link', { name: /^(Edit|Buat)$/ }).first();
        await expect(editLink).toBeVisible();
        await editLink.click();

        await expect(page).toHaveURL(/\/guru\/rapor\/\d+\/edit/);

        await page.locator('textarea[name="perkembangan_akademik"]').fill('Menunjukkan peningkatan pada pelajaran utama.');
        await page.locator('textarea[name="perkembangan_sosial"]').fill('Aktif dalam kerja kelompok.');
        await page.locator('textarea[name="perkembangan_psikologis"]').fill('Lebih percaya diri.');
        await page.locator('textarea[name="saran_tindak_lanjut"]').fill('Pertahankan jadwal belajar harian.');
        await page.locator('textarea[name="catatan_guru"]').fill('Perlu pendampingan membaca.');
        await page.locator('select[name="status"]').selectOption('final');
        await page.getByRole('button', { name: 'Simpan rapor' }).click();

        await expect(page.getByText('Rapor BK berhasil disimpan.').first()).toBeVisible();
    });

    test('Filter konseling guru berdasarkan status', async ({ page }) => {
        await loginAs(page, 'guru');
        await page.goto('/guru/consultations');
        await page.locator('select[name="status"]').selectOption('pending');
        await page.getByRole('button', { name: 'Filter' }).click();
        await expect(page).toHaveURL(/status=pending/);
    });
});
