# AGENTS.md

## Project Shape
- Laravel 12 app on PHP `^8.2` with Breeze auth, Blade views/components, Tailwind, Alpine, Axios, and Vite; Livewire is not installed.
- Laravel 12 bootstrap/routing is in `bootstrap/app.php`; do not look for `app/Http/Kernel.php`.
- Web routes are in `routes/web.php`; Breeze auth routes are loaded from `routes/auth.php`.
- Auth-only routes include `/dashboard`, `/clientes`, `/catalogo`, `/cotizaciones`, and `/pedidos`; `/dashboard` also requires `verified`.
- `clientes`, `catalogo`, and `cotizaciones` are resource routes. `pedidos` only exposes `index` and `show`; creation happens via `POST cotizaciones/{cotizacion}/convertir`.

## Commands
- Fresh setup: `composer setup` runs Composer install, creates `.env`, generates the app key, migrates, installs npm packages, and builds assets.
- Full dev stack: `composer dev` runs `php artisan serve`, `php artisan queue:listen --tries=1 --timeout=0`, `php artisan pail --timeout=0`, and `npm run dev` concurrently.
- PHP tests: `composer test` clears config first, then runs `php artisan test`.
- Focused tests: `php artisan test tests/Feature/ProfileTest.php` or `php artisan test --filter=test_new_users_can_register`.
- PHP style check/fix: `vendor/bin/pint --test` / `vendor/bin/pint`.
- Frontend: `npm run dev` for Vite dev server, `npm run build` for production assets. There is no JS lint/typecheck/formatter script.

## Data And Tests
- `.env.example` defaults to SQLite and `database/database.sqlite` exists; XAMPP/MySQL requires overriding DB settings in `.env`.
- `phpunit.xml` forces SQLite `:memory:`, array cache/session/mail, and sync queue; tests do not need the local SQLite file.
- `DatabaseSeeder` only creates `test@example.com` / `password`; there are no seeders or factories for clients, products, quotations, or orders.
- Current tests are mostly Breeze/profile/example coverage; add Feature tests when changing `clientes`, `catalogo`, `cotizaciones`, conversion, or `pedidos` behavior.

## Domain Notes
- Domain routes, Blade directories, and UI copy are Spanish (`clientes`, `catalogo`, `cotizaciones`, `pedidos`); core models/tables are English (`Client`, `Product`, `Quotation`, `QuotationItem`, `Order`, `OrderItem`). Keep this mixed convention unless doing a deliberate refactor.
- `CatalogoController` uses `Product` and table `products`. `App\Models\Catalogo` is legacy/minimal and points to a non-current `catalogo` table.
- Deletion guards exist: clients with quotations, products used in quotations/orders, and quotations with an order should not be deleted.
- Quote statuses are `borrador`, `enviada`, `aceptada`, `convertida`, `rechazada`, `vencida`; conversion currently only allows `aceptada` quotes with no existing order.
- `CotizacionController` calculates totals inline with 16% IVA and clamps line/base subtotals at zero. `QuotationService` and `ConversionService` exist but the active conversion route still performs conversion inline in the controller.
- Orders are snapshots of quotation line prices/discounts; converting a quotation does not decrement product stock.
- `notas` appears in cotización views/presentation data but is not persisted in `quotations` or validated by the controller.
- The cotización create view has a `+ Agregar línea` UI, but dynamic line addition is not implemented yet.

## Frontend
- Auth layout is `resources/views/layouts/app.blade.php`; navigation is `resources/views/layouts/navigation.blade.php`.
- Assets load with `@vite(['resources/css/app.css', 'resources/js/app.js'])`; Alpine starts in `resources/js/app.js`, Axios is globalized in `resources/js/bootstrap.js`.
- Tailwind scans Blade under `resources/views`, cached views, and Laravel pagination views only; update `tailwind.config.js` if class strings move outside those paths.
