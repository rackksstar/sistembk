/**
 * Utilitas port untuk harness E2E.
 *
 * Masalah yang diselesaikan:
 * `php artisan serve` menjalankan proses anak `php -S`. Saat Playwright
 * menghentikan webServer, proses `php -S` anak tersebut sering tetap hidup
 * (orphaned) dan terus memegang port. Run berikutnya memakai
 * `reuseExistingServer` sehingga diam-diam memakai server lama yang masih
 * memegang koneksi database lama — gejalanya: tabel terlihat kosong padahal
 * datanya ada di database.
 *
 * Fungsi di bawah memastikan port E2E selalu dipakai server yang benar-benar
 * baru.
 */
import { spawnSync } from 'node:child_process';

import { e2ePort } from './env.mjs';

/**
 * PID yang sedang/listening pada TCP port tertentu.
 *
 * @param {number} port
 * @returns {number[]}
 */
export function pidsOnPort(port) {
    if (process.platform === 'win32') {
        const result = spawnSync('netstat', ['-ano', '-p', 'TCP'], { encoding: 'utf8' });

        if (result.status !== 0) {
            return [];
        }

        const pids = new Set();
        const pattern = new RegExp(`^\\s*TCP\\s+\\S*:${port}\\s+\\S+\\s+LISTENING\\s+(\\d+)\\s*$`, 'im');

        for (const line of (result.stdout || '').split(/\r?\n/)) {
            const match = line.match(pattern);

            if (match) {
                pids.add(Number(match[1]));
            }
        }

        return [...pids];
    }

    const result = spawnSync('sh', ['-c', `lsof -ti tcp:${port} 2>/dev/null || true`], { encoding: 'utf8' });

    return (result.stdout || '')
        .split(/\r?\n/)
        .map((line) => Number(line.trim()))
        .filter((pid) => Number.isFinite(pid) && pid > 0);
}

/**
 * Hentikan semua proses yang sedang memegang port (termasuk anak-anaknya).
 *
 * @param {number} port
 * @returns {number[]} PID yang dihentikan
 */
export function killPort(port) {
    const pids = pidsOnPort(port);

    for (const pid of pids) {
        if (process.platform === 'win32') {
            // /T = sekalian seluruh proses anak, /F = paksa.
            spawnSync('taskkill', ['/PID', String(pid), '/T', '/F'], { stdio: 'ignore' });
        } else {
            try {
                process.kill(pid, 'SIGKILL');
            } catch {
                // Proses sudah berhenti.
            }
        }
    }

    return pids;
}

/**
 * Pastikan port E2E bebas sebelum server dijalankan.
 */
export function freeE2ePort() {
    const killed = killPort(e2ePort);

    if (killed.length > 0) {
        console.log(`[e2e] Port ${e2ePort} dipakai proses ${killed.join(', ')} — dihentikan agar server uji benar-benar baru.`);
    }

    return killed;
}
