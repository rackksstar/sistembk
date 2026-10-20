import { defineConfig, devices } from '@playwright/test';

import { baseUrl } from './tests/e2e/scripts/env.mjs';

/**
 * Konfigurasi E2E Playwright untuk Sistem BK.
 *
 * - Database memakai SQLite khusus E2E (storage/e2e/database.sqlite),
 *   jadi aman terhadap database MySQL development Anda.
 * - Semua skenario CRUD berjalan sekuensial (workers: 1) karena
 *   memakai satu database bersama.
 */
export default defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/global-setup.mjs',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    timeout: 90_000,
    expect: { timeout: 15_000 },
    reporter: [['list'], ['html', { outputFolder: 'test-results/html', open: 'never' }]],
    outputDir: 'test-results/artifacts',
    use: {
        baseURL: baseUrl,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'off',
        locale: 'id-ID',
        timezoneId: 'Asia/Jakarta',
        actionTimeout: 20_000,
        navigationTimeout: 45_000,
    },
    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
                // Viewport desktop lebar agar mendekati pemakaian nyata.
                // Konten modal sudah punya area scroll sendiri
                // (max-h-[calc(100vh-2rem)] overflow-y-auto), jadi modal
                // panjang tetap bisa dijangkau walau viewport rendah.
                viewport: { width: 1440, height: 1024 },
            },
        },
    ],
    webServer: {
        command: 'node tests/e2e/scripts/serve.mjs',
        url: baseUrl,
        reuseExistingServer: !process.env.CI,
        timeout: 180_000,
    },
});
