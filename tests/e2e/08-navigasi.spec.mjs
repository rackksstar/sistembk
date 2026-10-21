import { expect, loginAs, test } from './helpers.mjs';

/**
 * E2E — Navigasi: pencarian menu di sidebar, palet perintah (Ctrl+K),
 * dan penyimpanan status buka/tutup grup.
 */
test.describe('Navigasi — Sidebar & Palet Perintah', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, 'admin');
        await page.goto('/admin/dashboard');
    });

    test('Sidebar: pencarian menu menyaring item yang tidak cocok', async ({ page }) => {
        const sidebar = page.locator('.app-sidebar');
        const search = sidebar.locator('input[aria-label="Cari menu navigasi"]');

        await expect(sidebar.getByRole('link', { name: 'Data Siswa', exact: true })).toBeVisible();

        await search.fill('Sosiometri');

        // Hanya grup & item yang cocok yang tersisa.
        await expect(sidebar.getByRole('link', { name: 'Soal Sosiometri', exact: true })).toBeVisible();
        await expect(sidebar.getByRole('link', { name: 'Data Siswa', exact: true })).not.toBeVisible();
        await expect(sidebar.getByRole('link', { name: 'Kelas', exact: true })).not.toBeVisible();

        // Tombol bersihkan mengembalikan seluruh menu.
        await sidebar.getByRole('button', { name: 'Bersihkan pencarian' }).click();
        await expect(sidebar.getByRole('link', { name: 'Data Siswa', exact: true })).toBeVisible();
    });

    test('Sidebar: menampilkan pesan kosong saat tidak ada menu yang cocok', async ({ page }) => {
        const sidebar = page.locator('.app-sidebar');

        await sidebar.locator('input[aria-label="Cari menu navigasi"]').fill('menuYangTidakAda');

        await expect(sidebar.getByText('Menu tidak ditemukan')).toBeVisible();
        await expect(sidebar.getByRole('link', { name: 'Data Siswa', exact: true })).not.toBeVisible();
    });

    test('Palet perintah: Ctrl+K mencari menu lalu Enter membukanya', async ({ page }) => {
        const palette = page.getByRole('dialog', { name: 'Cari menu navigasi' });

        await page.locator('button[aria-label="Cari menu (Ctrl+K)"]').click();
        await expect(palette.locator('input[aria-label="Kata kunci pencarian menu"]')).toBeVisible();

        await palette.locator('input[aria-label="Kata kunci pencarian menu"]').fill('Rapor');
        await expect(palette.getByRole('link', { name: /Rapor BK/ })).toBeVisible();

        await page.keyboard.press('Enter');
        await page.waitForURL(/\/admin\/rapor/);
        await expect(palette).not.toBeVisible();
    });

    test('Palet perintah: pintasan Ctrl+K membuka dan Esc menutup', async ({ page }) => {
        const palette = page.getByRole('dialog', { name: 'Cari menu navigasi' });

        await page.keyboard.press('Control+k');
        await expect(palette.locator('input[aria-label="Kata kunci pencarian menu"]')).toBeVisible();

        await page.keyboard.press('Escape');
        await expect(palette).not.toBeVisible();
    });

    test('Sidebar: status buka/tutup grup tersimpan antar halaman', async ({ page }) => {
        const sidebar = page.locator('.app-sidebar');
        const toggle = sidebar.locator('.app-sidebar__group-toggle', { hasText: 'Data Master' });
        const linkSekolah = sidebar.getByRole('link', { name: 'Sekolah', exact: true });

        await expect(linkSekolah).toBeVisible();

        await toggle.click();
        await expect(linkSekolah).not.toBeVisible();

        // Status disimpan di localStorage -> tetap tertutup setelah reload.
        await page.reload();
        await expect(sidebar.locator('.app-sidebar__group-toggle', { hasText: 'Data Master' })).toBeVisible();
        await expect(sidebar.getByRole('link', { name: 'Sekolah', exact: true })).not.toBeVisible();

        // Dibuka kembali -> menu kembali tampil.
        await sidebar.locator('.app-sidebar__group-toggle', { hasText: 'Data Master' }).click();
        await expect(sidebar.getByRole('link', { name: 'Sekolah', exact: true })).toBeVisible();
    });
});
