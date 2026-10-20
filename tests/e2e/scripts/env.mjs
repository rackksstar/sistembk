/**
 * Environment khusus untuk E2E Playwright.
 * Sengaja memakai SQLite file terpisah supaya seluruh skenario CRUD
 * tidak menyentuh database MySQL (sistembk) yang dipakai untuk development.
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
export const projectRoot = path.resolve(here, '..', '..', '..');
export const databaseFile = path.join(projectRoot, 'storage', 'e2e', 'database.sqlite');
export const baseUrl = process.env.E2E_BASE_URL || 'http://127.0.0.1:8000';

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
