# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Daiku Interior is a Laravel 12 (PHP 8.2) app for an interior design business: public catalog (katalog), consultation booking (konsultasi), and project/order management (pemesanan) with payments and document workflows. Frontend is Blade + Alpine.js + Tailwind v4, bundled with Vite. Default local DB is MySQL; tests run on in-memory SQLite. Production (Render) uses PostgreSQL and Supabase S3-compatible storage for private files — see `DEPLOY_RENDER.md`.

## Commands

- Install deps: `composer install` and `npm install`
- Local env setup: `copy .env.example .env`, set DB config, then `php artisan key:generate`
- Migrate + seed: `php artisan migrate` (or `php artisan migrate:fresh --seed`)
- Run dev stack (server + queue log + vite, concurrently): `composer run dev`
- Build frontend assets: `npm run build`
- Run all tests: `php artisan test` (or `composer test`, which also clears config cache first)
- Run a single test file: `php artisan test tests/Feature/CustomDesignWorkflowTest.php`
- Run a single test method: `php artisan test --filter=test_method_name`
- Lint/format PHP (Laravel Pint, preset `laravel`): `vendor/bin/pint`

Tests use `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` (see `phpunit.xml`), so they don't touch the local MySQL dev database.

## Architecture

### Core business flow

The B2C flow is: **User → Konsultasi (consultation) → Pemesanan (project/order) → ProjectInvoice → Payment (via PaymentEvidenceService)**. A legacy B2B path (`RFQ` model) still exists but is optional and mostly superseded. See `DATABASE_ERD_ANALYSIS.md` for the full entity/relationship breakdown.

Key models and relationships (`app/Models`):
- `User` — has `role`: `admin` | `designer` | `pelanggan` (customer). Helper methods like `isAdmin()`, `isDesigner()`, `isPelanggan()`.
- `Konsultasi` — belongs to `User`; can convert into a `Pemesanan` (see `KonsultasiController::convertToProject`).
- `Pemesanan` — the project/order aggregate. `belongsTo` `User` (`id_user`) and `designer` (`designer_id`), optional `Katalog`; `hasMany` `StatusTracking`, `ProjectDocument`, `ProjectDocumentDecision`, `ProjectInvoice`; `hasOne` DP invoice (`type = 'dp_20'`) and `konsultasi`.
- `Katalog` — design catalog items; supports both new `category_id` (FK to `Category`) and legacy `kategori` string field for backward compatibility.
- `ProjectInvoice`, `StatusTracking`, `ProjectDocument`/`ProjectDocumentDecision` — invoicing, audit trail of status changes, and designer-uploaded documents requiring customer decisions.

### Access control

Role gating is done via `RoleMiddleware` (`role:admin|designer|pelanggan`) registered per-route in `routes/web.php`, not policies/gates. Routes are grouped by role under `Route::middleware(['role:...'])` blocks. Some "detail" routes (e.g. viewing a `konsultasi` or `pemesanan`) are open to `auth` only, with owner/admin/designer authorization enforced inside the controller rather than route middleware — check the controller when modifying access rules for those.

Route naming mirrors the URL structure: `admin.*` for admin-only actions, `designer.*` for designer actions, plain names (`pemesanan.*`, `konsultasi.*`) for customer/shared actions.

### Services layer

Business logic that spans models lives in `app/Services`, not in controllers/models:
- `PaymentEvidenceService` — upload/verify payment proof, tracks state via `StatusTracking` entries (not a dedicated status column).
- `ProjectWorkflowService`, `ManualOrderService`, `AdminWorkItemService` — project/order lifecycle and admin task aggregation.
- `CustomerUploadService`, `CatalogImageService` — file upload handling for customer attachments and catalog images.
- `AccountActivationService` — user activation flow.
- `DaikoNotificationService` + `DaikuNotification` (`app/Notifications`) — in-app/website + email notifications; see `WebsiteNotificationTest.php` for expected behavior.

When adding workflow logic (status transitions, verifications), prefer extending the relevant service and writing a `StatusTracking` row rather than adding ad hoc status fields.

### File storage (multiple disks, `config/filesystems.php`)

This app uses several distinct disks — do not default to `Storage::disk('local')` without checking which one applies:
- `payment_evidence` — private disk for payment proof uploads (local in dev, Supabase S3 in production via `PAYMENT_EVIDENCE_*` env vars). Never make this bucket public.
- `catalog_images` — public-facing disk for newly uploaded catalog images (legacy curated images still ship from `public/images/katalog/curated`).
- `public` / `local` — standard Laravel disks for everything else.

### Frontend

Blade views under `resources/views`, organized by feature (`admin/`, `konsultasi/`, `layouts/`). Alpine.js for interactivity, Tailwind v4 via `@tailwindcss/vite`. No JS framework/SPA — this is server-rendered Laravel with progressive enhancement.

### Deployment

Production deploys to Render via Docker + `render.yaml` (PostgreSQL + Supabase S3), documented in Indonesian in `DEPLOY_RENDER.md`. Local dev is MySQL + local disk storage. Keep this local/production disk and DB duality in mind when writing migrations or storage code — avoid MySQL- or Postgres-specific SQL.

## Notes

- Many domain field names are in Indonesian (e.g. `nama_desain`, `tanggal_pesan`, `bukti_pembayaran`, `catatan_admin`) — match existing naming conventions when adding fields rather than introducing English names.
- `RFQ` is a legacy/optional B2B model; don't assume it's part of the primary flow.
