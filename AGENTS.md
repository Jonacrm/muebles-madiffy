# AGENTS.md

## Project Shape
- Laravel 12 app on PHP `^8.2` with Breeze auth, Blade views, Vite, Tailwind, Alpine, and Axios.
- Laravel 12 routing/bootstrap lives in `bootstrap/app.php`; do not look for `app/Http/Kernel.php`.
- Web routes are in `routes/web.php`; Breeze auth routes are split into `routes/auth.php`.
- `/dashboard`, `/clientes`, and `/catalogo` are auth-only routes; `clientes` and `catalogo` are resource routes.

## Commands
- Fresh setup: `composer setup` runs Composer install, creates `.env`, generates the app key, migrates, installs npm packages, and builds assets.
- Full local dev stack: `composer dev` runs `php artisan serve`, `php artisan queue:listen --tries=1 --timeout=0`, `php artisan pail --timeout=0`, and `npm run dev` concurrently.
- PHP tests: `composer test` clears config first, then runs `php artisan test`.
- Focused tests: `php artisan test tests/Feature/ProfileTest.php` or `php artisan test --filter=test_new_users_can_register`.
- PHP style check/fix: `vendor/bin/pint --test` / `vendor/bin/pint`. There is no JS lint or formatter script.
- Frontend only: `npm run dev`; production asset check: `npm run build`.

## Database And Tests
- `.env.example` defaults to SQLite and `database/database.sqlite` is present.
- `phpunit.xml` forces tests to SQLite `:memory:`, array cache/session/mail, and sync queue; feature tests do not need the local database file.
- Existing migrations only create users, password resets, sessions, cache, and queue tables.
- `DatabaseSeeder` only creates `test@example.com`; there are no seeders for clients or catalog products.

## App-Specific Notes
- `ClienteController` and `CatalogoController` are mostly placeholders returning Blade views; there are no `Cliente` or catalog/product Eloquent models yet.
- `resources/views/clientes/*` and `resources/views/catalogo/*` contain static/prototype UI; implement migrations/models/controllers before assuming real CRUD exists.
- Domain UI copy and routes are Spanish (`clientes`, `catalogo`, `Catálogo`); preserve that convention in new screens.
- Main layout loads assets with `@vite(['resources/css/app.css', 'resources/js/app.js'])`; Alpine starts in `resources/js/app.js`.
- Tailwind scans Blade files under `resources/views`, cached views, and Laravel pagination views only; update `tailwind.config.js` if adding class strings outside those paths.
