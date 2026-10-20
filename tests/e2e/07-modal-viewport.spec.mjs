import { expect, loginAs, test } from './helpers.mjs';

/**
 * E2E — Regresi tampilan modal pada viewport rendah.
 *
 * Sebelumnya kontainer konten modal tidak punya batas tinggi/area scroll,
 * sehingga pada layar pendek tombol Simpan berada di luar viewport dan tidak
 * bisa dijangkau. Sekarang konten modal memakai `max-h-[calc(100vh-2rem)]`
 * + `overflow-y-auto`, jadi tombol tetap terjangkau lewat scroll konten.
 *
 * Nilai `calc(100vh-2rem)` sendiri dikunci oleh uji viewport default di
 * bawah: overlay memakai `p-4` (2rem total), sehingga tinggi itu adalah
 * ruang sebenarnya yang tersedia. Bentuk `90vh` yang lama lebih ketat
 * (922px vs 992px pada layar 1024px) sehingga memunculkan scrollbar yang
 * tidak perlu dan memotong isi modal yang sebenarnya masih muat.
 */
const VIEWPORT_PENDEK = { width: 1280, height: 600 };
const VIEWPORT_DEFAULT = { width: 1440, height: 1024 };

/** Buka halaman lalu klik pemicu modal; kembalikan konten modal yang terbuka. */
async function bukaKontenModal(page, url, pemicu) {
    await page.goto(url);
    await page.getByRole('button', { name: pemicu }).first().click();

    // Satu halaman bisa punya beberapa modal (create/import/edit).
    const overlay = page
        .locator('div.fixed.inset-0.z-50.flex.items-center.justify-center:visible')
        .first();
    await expect(overlay).toBeVisible();

    // Konten modal adalah anak langsung overlay, bukan overlay-nya sendiri.
    const konten = overlay.locator('xpath=./div').first();
    await expect(konten).toHaveClass(/overflow-y-auto/);

    return konten;
}

test.describe('Modal — konten panjang tetap terjangkau di viewport rendah', () => {
    test('Tambah Siswa (admin): tombol simpan terjangkau lewat scroll konten', async ({ page }) => {
        await page.setViewportSize(VIEWPORT_PENDEK);
        await loginAs(page, 'admin');

        const konten = await bukaKontenModal(page, '/admin/students', /^Tambah siswa$/);

        // Konten memang lebih tinggi dari viewport: kontainer harus bisa scroll.
        const luber = await konten.evaluate((el) => el.scrollHeight - el.clientHeight);
        expect(luber).toBeGreaterThan(0);

        const tombol = konten.locator('form').first().locator('button').last();
        await expect(tombol).toBeVisible();

        await konten.evaluate((el) => {
            el.scrollTop = el.scrollHeight;
        });
        await expect(tombol).toBeInViewport();
    });

    test('Tambah Siswa (guru): tombol simpan terjangkau lewat scroll konten', async ({ page }) => {
        await page.setViewportSize(VIEWPORT_PENDEK);
        await loginAs(page, 'guru');

        const konten = await bukaKontenModal(page, '/guru/students', /^Tambah siswa$/);

        const tombol = konten.locator('form').first().locator('button').last();
        await expect(tombol).toBeVisible();

        await konten.evaluate((el) => {
            el.scrollTop = el.scrollHeight;
        });
        await expect(tombol).toBeInViewport();
    });

    test('Tambah Bidang Karier (admin): modal yang dulu ber-90vh tetap terjangkau', async ({ page }) => {
        await page.setViewportSize(VIEWPORT_PENDEK);
        await loginAs(page, 'admin');

        // Modal ini memakai `max-h-[90vh]` sebelum semua kontainer modal
        // disamakan. Diuji agar satuan konvensinya tidak kembali terbelah.
        const konten = await bukaKontenModal(page, '/admin/bidang-karier', /^Tambah bidang karier$/);

        const tombol = konten.locator('form').first().locator('button').last();
        await expect(tombol).toBeVisible();

        await konten.evaluate((el) => {
            el.scrollTop = el.scrollHeight;
        });
        await expect(tombol).toBeInViewport();
    });

    test('Tambah Siswa (admin): tampilan 1440x1024 tidak kembali ber-scrollbar', async ({ page }) => {
        await page.setViewportSize(VIEWPORT_DEFAULT);
        await loginAs(page, 'admin');

        const konten = await bukaKontenModal(page, '/admin/students', /^Tambah siswa$/);

        // Modal ini tingginya ~947px. Dengan `90vh` (922px) ia mendapat
        // scrollbar yang sebelumnya tidak ada; dengan `calc(100vh-2rem)`
        // (992px) seluruhnya masih muat. Kegagalan di sini berarti nilai
        // batas tinggi modal ikut berubah.
        const luber = await konten.evaluate((el) => el.scrollHeight - el.clientHeight);
        expect(luber).toBe(0);
    });
});
