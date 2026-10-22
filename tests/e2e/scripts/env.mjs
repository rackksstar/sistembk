/**
 * Environment khusus untuk E2E Playwright.
 * Sengaja memakai SQLite file terpisah supaya seluruh skenario CRUD
 * tidak menyentuh database MySQL (sistembk) yang dipakai untuk development.
 *
 * Port E2E dipisah dari port development (8000) dengan sengaja:
 * server `php artisan serve` bisa meninggalkan proses anak `php -S` yang
 * yatim dan terus memegang port. Kalau E2E memakai port yang sama dengan
 * server development, proses yatim itu bisa dipakai diam-diam oleh run
 * berikutnya — sehingga pengujian berjalan terhadap database yang salah.
 * Port khusus ini aman untuk dibersihkan otomatis.
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
export const projectRoot = path.resolve(here, '..', '..', '..');
export const databaseFile = path.join(projectRoot, 'storage', 'e2e', 'database.sqlite');
export const e2ePort = Number(process.env.E2E_PORT || 8765);
export const baseUrl = process.env.E2E_BASE_URL || `http://127.0.0.1:${e2ePort}`;

export const e2eEnv = {
    APP_ENV: 'production',
    APP_DEBUG: 'false',
    APP_URL: baseUrl,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: databaseFile,
    DB_FOREIGN_KEYS: 'true',
    SESSION_DRIVER: 'file',
    CACHE_STORE: 'file',
    QUEUE_CONNECTION: 'sync',
    MAIL_MAILER: 'array',
    BROADCAST_CONNECTION: 'log',
    CHATBOT_SERVICE_URL: process.env.CHATBOT_SERVICE_URL || 'http://127.0.0.1:8000',
};
