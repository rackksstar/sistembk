## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Menjalankan Pengujian E2E

Suite E2E memakai Playwright dan berjalan di atas database SQLite terpisah
(`storage/e2e/database.sqlite`), jadi aman dijalankan tanpa menyentuh database
MySQL development (`sistembk`).

```bash
composer install
npm install
npm run build       # wajib: aset Vite harus dibangun lebih dulu
npm run test:e2e
```

- **`npm run build` wajib dijalankan sebelum suite.** Aplikasi membaca aset
  lewat Vite manifest, dan server E2E berjalan dengan `APP_ENV=production`.
  Tanpa `public/build/manifest.json`, halaman seperti `/guru/consultations`
  gagal dengan error 500 "Unable to locate file in Vite manifest".
  `tests/e2e/global-setup.mjs` memeriksa manifest ini lebih awal agar
  penyebabnya langsung terlihat.
- `npm run test:e2e` menjalankan seluruh skenario. Database disiapkan otomatis
  oleh `global-setup` (`migrate:fresh --seed`), sehingga data uji selalu bersih
  di setiap run.
- **Server E2E berjalan di port `8765`**, terpisah dari port development
  (`8000`). Alasannya: `php artisan serve` bisa meninggalkan proses anak
  `php -S` yang yatim dan terus memegang port; kalau E2E memakai port yang
  sama, run berikutnya bisa diam-diam memakai server lama yang masih
  memegang database versi lama. `npm run test:e2e` menjalankan *preflight*
  (`tests/e2e/scripts/preflight.mjs`) untuk membebaskan port tersebut, dan
  port bisa diubah lewat `E2E_PORT` atau `E2E_BASE_URL`.
- Skenario berjalan sekuensial (`workers: 1`) karena berbagi satu database.
- Perintah tambahan: `npm run test:e2e:ui` (mode UI) dan
  `npm run test:e2e:report` (buka laporan HTML di `test-results/html`).
- Menjalankan per modul, misalnya:

  ```bash
  npx playwright test 04-guru-manajemen --reporter=list
  ```

  Jalankan lewat `npm run test:e2e -- 04-guru-manajemen` bila ingin
  preflight port ikut dijalankan.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).