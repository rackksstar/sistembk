/**
 * Jalankan server aplikasi (php artisan serve) khusus untuk E2E.
 * Dipakai oleh webServer di playwright.config.js.
 */
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

import { baseUrl, databaseFile, e2eEnv, projectRoot } from './env.mjs';

fs.mkdirSync(path.dirname(databaseFile), { recursive: true });

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
    },
);

const stop = () => child.kill();
process.on('SIGTERM', stop);
process.on('SIGINT', stop);
child.on('exit', (code) => process.exit(code ?? 0));
