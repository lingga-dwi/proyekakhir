# Daiku Interior

Laravel app for Daiku Interior: katalog desain, konsultasi, dan pemesanan proyek interior.

## Tech Stack

- PHP 8.2, Laravel 12
- MySQL
- Vite + Tailwind

## Setup (Local)

1) Install dependencies
   - `composer install`
   - `npm install`
2) Create env
   - `copy .env.example .env`
   - Set DB config in `.env`
3) Generate app key
   - `php artisan key:generate`
4) Run migrations and seed
   - `php artisan migrate`
   - `php artisan db:seed`
5) Run dev server
   - `composer run dev`

## Useful Commands

- `php artisan migrate:fresh --seed`
- `php artisan test`
- `npm run build`

## Notes

- Uploaded images are stored on the `public` disk.
- Katalog supports both `category_id` (new) and `kategori` (legacy) for compatibility.
