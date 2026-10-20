/**
 * Jalankan `php artisan ...` dengan environment E2E (SQLite terpisah).
 * Pemakaian: node tests/e2e/scripts/artisan.mjs migrate:fresh --seed
 */
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

import { databaseFile, e2eEnv, projectRoot } from './env.mjs';

fs.mkdirSync(path.dirname(databaseFile), { recursive: true });
fs.writeFileSync(databaseFile, '');

const args = process.argv.slice(2);
const child = spawn('php', [path.join(projectRoot, 'artisan'), ...args], {
    cwd: projectRoot,
    env: { ...process.env, ...e2eEnv },
    stdio: 'inherit',
    shell: true,
});

child.on('exit', (code) => process.exit(code ?? 0));
