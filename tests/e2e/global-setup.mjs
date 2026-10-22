/**
 * Global setup Playwright: menyiapkan database SQLite khusus E2E
 * (migrate:fresh --seed) sebelum seluruh skenario dijalankan.
 */
import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

import { e2eEnv, projectRoot } from './scripts/env.mjs';

export default function globalSetup() {
    // Server E2E berjalan dengan APP_ENV=production, jadi aset dibaca lewat
    // Vite manifest. Tanpa manifest, halaman gagal 500 "Unable to locate file
    // in Vite manifest" (mis. /guru/consultations) dan penyebabnya sulit
    // terlihat. Gagalkan lebih awal dengan pesan yang jelas.
    const manifest = path.join(projectRoot, 'public', 'build', 'manifest.json');

    if (!fs.existsSync(manifest)) {
        throw new Error(
            'Aset Vite belum dibangun (public/build/manifest.json tidak ada).\n' +
                'Jalankan "npm run build" sebelum "npm run test:e2e" — tanpa itu ' +
                'halaman akan gagal dengan 500 "Unable to locate file in Vite manifest".',
        );
    }

    const result = spawnSync(
        'php',
        [path.join(projectRoot, 'artisan'), 'migrate:fresh', '--seed', '--force'],
        {
            cwd: projectRoot,
            env: { ...process.env, ...e2eEnv },
            stdio: 'inherit',
            shell: true,
        },
    );

    if (result.status !== 0) {
        throw new Error('Gagal menyiapkan database E2E (migrate:fresh --seed).');
    }
}
