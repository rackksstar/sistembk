# Phase 8 — API (Sanctum)

## Base URL

`/api/v1`

## Autentikasi

### Login

`POST /api/v1/login`

**Admin / Guru BK:** `login` = email (admin) atau username (guru), `password` = password akun.

**Siswa:** `login` = NISN, `password` = tanggal lahir (`Y-m-d`) — sama seperti login web.

```json
{
  "role": "admin|guru|siswa",
  "login": "email, username, atau NISN",
  "password": "password atau tanggal lahir (siswa)",
  "device_name": "opsional"
}
```

Response: `token`, `token_type` (`Bearer`), `user`.

Header berikutnya: `Authorization: Bearer {token}`

### Me / Logout

- `GET /api/v1/me` — profil pengguna
- `POST /api/v1/logout` — hapus token aktif

## Resource (admin & guru)

- `GET /api/v1/consultations` — daftar konseling (guru: milik sendiri)
- `GET /api/v1/students` — daftar siswa (`?q=`, `?kelas_id=`, `?per_page=`)

## Test

```bash
php artisan test --filter=Phase8
```

## Setup deploy

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```
