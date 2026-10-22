/**
 * Jalankan server aplikasi (php artisan serve) khusus untuk E2E.
 * Dipakai oleh webServer di playwright.config.js.
 *
 * Dua hal yang dijaga di sini:
 * 1. Port E2E dibebaskan dulu dari proses yatim (lihat scripts/port.mjs).
 * 2. Saat berhenti, SELURUH pohon proses dihentikan. `php artisan serve`
 *    menjalankan anak `php -S`; kalau hanya proses induknya yang dibunuh,
 *    anak itu tetap hidup dan memegang port untuk run berikutnya.
 */
import { spawn, spawnSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

import { baseUrl, databaseFile, e2eEnv, e2ePort, projectRoot } from './env.mjs';
import { freeE2ePort } from './port.mjs';

fs.mkdirSync(path.dirname(databaseFile), { recursive: true });

// Bersihkan sisa proses dari run sebelumnya sebelum start.
freeE2ePort();

const url = new URL(baseUrl);
const port = url.port || '80';

const child = spawn(
    'php',
    [path.join(projectRoot, 'artisan'), 'serve', `--host=${url.hostname}`, `--port=${port}`],
    {
        cwd: projectRoot,
        env: { ...process.env, ...e2eEnv },
        stdio: 'inherit',
        shell: true,
        detached: process.platform !== 'win32',
    },
);

/**
 * Hentikan server beserta seluruh proses turunannya.
 */
function stop() {
    if (process.platform === 'win32') {
        // /T ikut membunuh proses anak, /F memaksa.
        spawnSync('taskkill', ['/PID', String(child.pid), '/T', '/F'], { stdio: 'ignore' });
    } else {
        try {
            process.kill(-child.pid, 'SIGKILL');
        } catch {
            // Proses sudah berhenti.
        }
    }
}

process.on('SIGTERM', stop);
process.on('SIGINT', stop);
process.on('exit', stop);

child.on('exit', (code) => {
    stop();
    process.exit(code ?? 0);
});

console.log(`[e2e] Server uji berjalan di ${baseUrl} (port ${e2ePort}).`);
