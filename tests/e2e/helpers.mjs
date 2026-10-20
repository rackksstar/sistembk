import fs from 'node:fs';
import path from 'node:path';

import { test as base, expect } from '@playwright/test';

import { projectRoot } from './scripts/env.mjs';

/**
 * Bersihkan cache file Laravel (termasuk penghitung rate limiter `throttle:*`).
 *
 * Rute login memakai `throttle:10,1`. Karena seluruh skenario melakukan login
 * dalam satu menit yang sama, penghitung tersebut cepat penuh dan memicu 429
 * "Terlalu Banyak Permintaan". Menghapus cache antar test menjaga perilaku
 * produksi tetap utuh tanpa perlu melonggarkan limit di kode aplikasi.
 */
function clearRateLimiterCache() {
    const cacheDir = path.join(projectRoot, 'storage', 'framework', 'cache', 'data');
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true });
        return;
    }

    // Bersihkan isi direktori saja, jangan direktorinya. Berkas `.gitignore`
    // bawaan Laravel di dalamnya berstatus ter-track; bila direktorinya
    // dihapus mentah, berkas itu ikut hilang dan working tree selalu muncul
    // sebagai "deleted" setelah suite dijalankan.
    for (const entry of fs.readdirSync(cacheDir)) {
        if (entry === '.gitignore') {
            continue;
        }
        fs.rmSync(path.join(cacheDir, entry), { recursive: true, force: true });
    }
}

/**
 * Akun demo hasil `php artisan db:seed` (DatabaseSeeder).
 */
export const accounts = {
    admin: { email: 'admin@bk.test', password: 'password' },
    adminSmk: { email: 'admin.demo@bk.test', password: 'password' },
    // `name` dipakai saat memilih konselor pada form siswa: daftar konseling
    // guru hanya menampilkan pengajuan tanpa konselor atau miliknya sendiri,
    // sehingga pilihan konselor harus jatuh pada guru yang sedang login.
    guru: { loginId: '081234567890', password: 'password', name: 'Ibu Rina Guru BK' },
    guruPending: { loginId: '081234567891', password: 'password' },
    siswa: { nisn: '0061234567', birthDate: '2008-05-14' },
    siswaSmk: { nisn: '0061234601', birthDate: '2008-04-12' },
};

/**
 * Login lewat halaman login resmi sesuai role.
 * Cookies dibersihkan dulu supaya aman dipakai untuk berganti role
 * di tengah satu skenario (mis. siswa → guru).
 */
export async function loginAs(page, role) {
    await page.context().clearCookies();

    if (role === 'siswa' || role === 'siswaSmk') {
        const account = accounts[role];
        await page.goto(`/login?role=siswa`);
        await page.locator('#nisn').fill(account.nisn);
        await page.locator('#birth_date').fill(account.birthDate);
        await page.getByRole('button', { name: 'Login' }).click();
    } else if (role === 'guru') {
        await page.goto(`/login?role=guru`);
        await page.locator('#login_id').fill(accounts.guru.loginId);
        await page.locator('#password').fill(accounts.guru.password);
        await page.getByRole('button', { name: 'Login' }).click();
    } else {
        await page.goto(`/login?role=admin`);
        await page.locator('#email').fill(accounts[role].email);
        await page.locator('#password').fill(accounts[role].password);
        await page.getByRole('button', { name: 'Login' }).click();
    }

    await page.waitForURL(/\/dashboard$/);
}

/**
 * Nilai unik per test agar data CRUD tidak bentrok antar skenario
 * maupun saat test dijalankan ulang.
 */
export function uniqueValue(prefix) {
    return `${prefix} ${Date.now().toString(36)}${Math.floor(Math.random() * 1e4)}`;
}

/**
 * Cari form CRUD berdasarkan potongan URL action.
 * - method 'POST' : form biasa tanpa method spoofing (create)
 * - method 'PUT'/'PATCH': form dengan @method(...) yang sesuai (update)
 * - method 'ANY'  : form apa pun yang punya input _method (update apa pun)
 * Hanya form yang sedang terlihat (modal/inline terbuka) yang dipilih.
 *
 * Catatan: atribut `action` selalu berupa URL absolut, jadi pencocokan selalu
 * memakai substring. Untuk membedakan form yang action-nya saling menaungi
 * (mis. /admin/guidance-classes vs /admin/guidance-classes/{id}/students),
 * gunakan opsi `has` untuk menunjuk field khas form tersebut.
 */
export function formByAction(page, actionFragment, { method = null, put = null, has = null } = {}) {
    const resolved = put === true ? 'PUT' : put === false ? 'POST' : (method ?? 'POST');

    // Selalu batasi pada <form method="POST"> agar form filter GET
    // (yang action-nya sama dengan URL halaman) tidak ikut terpilih.
    let locator = page.locator(`form:visible[method="POST"][action*="${actionFragment}"]`);

    if (has) {
        locator = locator.filter({ has: page.locator(has) });
    }

    if (resolved === 'POST') {
        locator = locator.filter({ hasNot: page.locator('input[name="_method"]') });
    } else if (resolved === 'ANY') {
        locator = locator.filter({ has: page.locator('input[name="_method"]') });
    } else {
        locator = locator.filter({ has: page.locator(`input[name="_method"][value="${resolved}"]`) });
    }

    return locator.first();
}

/**
 * Baris tabel yang mengandung teks tertentu — dipakai untuk tombol Edit/Delete per-row.
 */
export function tableRow(page, text) {
    return page.locator('tr').filter({ hasText: text }).first();
}

/**
 * Centang satu kategori minat RIASEC (checkbox `categories[{id}][enabled]`).
 * Dipakai form Program Studi & Bidang Karier yang mewajibkan minimal satu kategori.
 */
export async function enableFirstInterestCategory(form) {
    const checkbox = form.locator('input[type="checkbox"][name$="[enabled]"]').first();
    await checkbox.check();
}

/**
 * Set nilai <select> yang dikontrol Select2.
 *
 * Elemen asli Select2 disembunyikan (class `select2-hidden-accessible`,
 * aria-hidden) sehingga `selectOption()` bawaan Playwright tidak bisa dipakai.
 * Nilai di-set melalui instance jQuery milik Select2 lalu memicu event
 * `change` supaya listener Alpine/XHR form ikut bereaksi.
 *
 * Terima `{ value }` atau `{ optionIndex }` (indeks <option>, 0 = placeholder).
 */
export async function select2Value(select, { value = null, optionIndex = null } = {}) {
    await select.evaluate((el, opts) => {
        let next = opts.value;

        if (next == null && opts.optionIndex != null) {
            next = el.options[opts.optionIndex]?.value ?? null;
        }

        const $ = window.jQuery || window.$;

        if ($ && typeof $.fn?.select2 === 'function') {
            $(el).val(next).trigger('change');
        } else {
            el.value = next ?? '';
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }, { value, optionIndex });
}

/**
 * Isi seluruh radio Likert (name="answers[{id}]") dengan pilihan pertama.
 * Dipakai instrumen siswa (Yola / Kepribadian / Masalah).
 */
export async function answerAllLikert(page, value = 2) {
    const names = await page.locator('input[type="radio"][name^="answers["]').evaluateAll((nodes) => [
        ...new Set(nodes.map((node) => node.getAttribute('name'))),
    ]);

    for (const name of names) {
        const radio = page.locator(`input[type="radio"][name="${name}"][value="${value}"]`).first();
        if (await radio.count()) {
            await radio.check({ force: true });
        }
    }

    return names.length;
}

/**
 * Nonaktifkan validasi HTML5 bawaan browser pada sebuah form.
 * Diperlukan saat menguji validasi server: atribut `required` di <input>
 * akan memblokir submit sebelum mencapai Laravel.
 */
export async function disableNativeValidation(form) {
    await form.evaluate((node) => {
        node.setAttribute('novalidate', 'novalidate');
        node.querySelectorAll('[required]').forEach((el) => el.removeAttribute('required'));
    });
}

/**
 * Konfirmasi dialog bawaan browser (window.confirm) — dipakai tombol Hapus.
 */
function autoConfirm(page) {
    page.on('dialog', async (dialog) => {
        if (dialog.type() === 'confirm') {
            await dialog.accept();
        }
    });
    return page;
}

export const test = base.extend({
    page: async ({ page }, use) => {
        clearRateLimiterCache();
        await use(autoConfirm(page));
    },
});

export { expect };
