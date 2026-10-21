/**
 * Preflight sebelum Playwright dijalankan.
 *
 * Port E2E dibebaskan dari proses yatim supaya `webServer` selalu
 * menjalankan server baru yang terhubung ke database E2E (SQLite).
 * Tanpa ini, `reuseExistingServer` bisa memakai server lama — server itu
 * masih memegang koneksi database lama, sehingga `migrate:fresh --seed`
 * pada global-setup tidak terlihat dan skenario gagal dengan keadaan
 * data yang tidak bisa dijelaskan.
 */
import { e2ePort } from './env.mjs';
import { freeE2ePort } from './port.mjs';

freeE2ePort();

console.log(`[e2e] Preflight selesai — port ${e2ePort} siap dipakai.`);
