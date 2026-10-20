import { expect, loginAs, test, uniqueValue } from './helpers.mjs';

/**
 * E2E — Autentikasi, otorisasi role, dan profil.
 */
test.describe('Autentikasi & Otorisasi', () => {
    test('landing page menampilkan pilihan login per role', async ({ page }) => {
        await page.goto('/');
        await expect(page.locator('a[href*="/login?role=siswa"]')).toBeVisible();
        await expect(page.locator('a[href*="/login?role=guru"]')).toBeVisible();
        await expect(page.locator('a[href*="/register/guru-bk"]')).toBeVisible();
    });

    test('login admin sukses dan redirect ke dashboard admin', async ({ page }) => {
        await page.goto('/login?role=admin');
        await expect(page.getByRole('heading', { name: 'Login Admin' })).toBeVisible();

        await page.locator('#email').fill('admin@bk.test');
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: 'Login' }).click();

        await page.waitForURL(/\/admin\/dashboard/);
        await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('login admin dengan password salah ditolak', async ({ page }) => {
        await page.goto('/login?role=admin');
        await page.locator('#email').fill('admin@bk.test');
        await page.locator('#password').fill('salah-banget');
        await page.getByRole('button', { name: 'Login' }).click();

        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('#password')).toBeVisible();
    });

    test('login guru BK memakai No HP / NIP', async ({ page }) => {
        await page.goto('/login?role=guru');
        await expect(page.getByRole('heading', { name: 'Login Guru BK' })).toBeVisible();

        await page.locator('#login_id').fill('081234567890');
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: 'Login' }).click();

        await page.waitForURL(/\/guru\/dashboard/);
    });

    test('guru yang belum disetujui tidak bisa login', async ({ page }) => {
        const noHp = uniqueValue('0821').replace(/[^0-9]/g, '').slice(0, 13);

        // Akun pending tidak lagi tersedia di seeder (ditimpa MultiSchoolTestingSeeder),
        // jadi buat lewat jalur registrasi publik yang resmi menghasilkan status pending.
        await page.goto('/register/guru-bk');
        await page.locator('input[name="name"]').fill('Guru Pending Uji E2E');
        await page.locator('select[name="sekolah_id"]').selectOption({ index: 1 });
        await page.locator('input[name="no_hp"]').fill(noHp);
        await page.locator('input[name="nip"]').fill(`E2E${Date.now()}`.slice(0, 18));
        await page.locator('input[name="password"]').fill('password');
        await page.locator('input[name="password_confirmation"]').fill('password');
        await page.getByRole('button', { name: 'Kirim pendaftaran' }).click();
        await expect(
            page.getByText('Pendaftaran Guru BK berhasil dikirim. Silakan tunggu sampai admin menyetujui akun Anda.'),
        ).toBeVisible();

        await page.goto('/login?role=guru');
        await page.locator('#login_id').fill(noHp);
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: 'Login' }).click();

        await expect(page.locator('#login_id')).toBeVisible();
        await expect(page.getByText('Akun Anda masih menunggu persetujuan admin.')).toBeVisible();
    });

    test('login siswa memakai NISN dan tanggal lahir', async ({ page }) => {
        await page.goto('/login?role=siswa');
        await expect(page.getByRole('heading', { name: 'Login Siswa' })).toBeVisible();

        await page.locator('#nisn').fill('0061234567');
        await page.locator('#birth_date').fill('2008-05-14');
        await page.getByRole('button', { name: 'Login' }).click();

        await page.waitForURL(/\/siswa\/dashboard/);
    });

    test('login siswa gagal jika tanggal lahir tidak cocok', async ({ page }) => {
        await page.goto('/login?role=siswa');
        await page.locator('#nisn').fill('0061234567');
        await page.locator('#birth_date').fill('1999-01-01');
        await page.getByRole('button', { name: 'Login' }).click();

        await expect(page.getByText('Tanggal lahir tidak cocok dengan data siswa.')).toBeVisible();
    });

    test('login siswa gagal jika NISN tidak terdaftar', async ({ page }) => {
        await page.goto('/login?role=siswa');
        await page.locator('#nisn').fill('9999999999');
        await page.locator('#birth_date').fill('2008-05-14');
        await page.getByRole('button', { name: 'Login' }).click();

        await expect(page.getByText('NISN tidak ditemukan pada data siswa. Hubungi admin atau Guru BK.')).toBeVisible();
    });

    test('akun dengan role lain ditolak saat memilih role tertentu', async ({ page }) => {
        // Provider login dipisah per role: akun Guru BK (tanpa email) tidak
        // bisa lolos autentikasi pada form login Admin, begitu pun sebaliknya.
        await page.goto('/login?role=guru');
        await page.locator('#login_id').fill('admin@bk.test');
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: 'Login' }).click();

        await expect(page.getByText('These credentials do not match our records.')).toBeVisible();
        await expect(page).toHaveURL(/\/login/);
    });

    test('siswa tidak boleh membuka area admin (403)', async ({ page }) => {
        await loginAs(page, 'siswa');
        const response = await page.goto('/admin/students');

        expect(response.status()).toBe(403);
        await expect(page.getByRole('heading', { name: 'Akses Ditolak' })).toBeVisible();
    });

    test('guru tidak boleh membuka area admin (403)', async ({ page }) => {
        await loginAs(page, 'guru');
        const response = await page.goto('/admin/users');

        expect(response.status()).toBe(403);
        await expect(page.getByRole('heading', { name: 'Akses Ditolak' })).toBeVisible();
    });

    test('halaman terproteksi redirect ke login bila belum masuk', async ({ page }) => {
        await page.goto('/admin/users');
        await page.waitForURL(/\/login/);
        await expect(page).toHaveURL(/\/login/);
    });

    test('menu sidebar tiap role menampilkan tautan sesuai hak akses', async ({ page }) => {
        await loginAs(page, 'admin');
        await expect(page.locator('nav a[href*="/admin/students"]').first()).toBeVisible();
        await expect(page.locator('nav a[href*="/guru/students"]')).toHaveCount(0);

        await loginAs(page, 'guru');
        await expect(page.locator('nav a[href*="/guru/students"]').first()).toBeVisible();
        await expect(page.locator('nav a[href*="/admin/users"]')).toHaveCount(0);
    });

    test('logout dari menu user mengakhiri sesi', async ({ page }) => {
        await loginAs(page, 'admin');

        // Tombol "Keluar" di sidebar membuka modal konfirmasi (bukan window.confirm).
        await page.getByRole('button', { name: 'Keluar' }).first().click();
        await page.getByRole('button', { name: 'Ya, logout' }).click();

        await page.waitForURL(/\/$/);
        await page.goto('/admin/users');
        await page.waitForURL(/\/login/);
        await expect(page).toHaveURL(/\/login/);
    });

    test('registrasi guru BK baru masuk status menunggu persetujuan', async ({ page }) => {
        const noHp = uniqueValue('0819').replace(/[^0-9]/g, '').slice(0, 13);

        await page.goto('/register/guru-bk');
        await page.locator('input[name="name"]').fill('Guru Uji E2E');
        await page.locator('select[name="sekolah_id"]').selectOption({ index: 1 });
        await page.locator('input[name="no_hp"]').fill(noHp);
        await page.locator('input[name="nip"]').fill(`E2E${Date.now()}`.slice(0, 18));
        await page.locator('input[name="password"]').fill('password');
        await page.locator('input[name="password_confirmation"]').fill('password');
        await page.getByRole('button', { name: 'Kirim pendaftaran' }).click();

        await expect(page.getByText('Pendaftaran Guru BK berhasil dikirim. Silakan tunggu sampai admin menyetujui akun Anda.')).toBeVisible();
    });

    test('admin dapat memperbarui profil sendiri', async ({ page }) => {
        await loginAs(page, 'admin');
        await page.goto('/profile');

        // Ada dua form di halaman profil; ambil form "Profile Information".
        const form = page.locator('form').filter({ has: page.locator('#name') }).first();
        const newName = uniqueValue('Admin Uji');
        await form.locator('input[name="name"]').fill(newName);
        await form.getByRole('button', { name: 'Save' }).click();

        // Notifikasi "Saved." disembunyikan otomatis setelah 2 detik,
        // jadi verifikasi keberhasilan update lewat data tersimpan di server.
        await expect(page.locator('#name')).toHaveValue(newName);

        await page.reload();
        await expect(page.locator('#name')).toHaveValue(newName);
    });
});
