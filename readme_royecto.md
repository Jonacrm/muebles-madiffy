# Muebles

Aplicación Laravel para gestionar el flujo comercial de una mueblería: clientes, catálogo de productos, cotizaciones y pedidos generados desde cotizaciones aceptadas.

## Stack
- Backend: PHP `^8.2` y Laravel `^12.0`.
- Autenticación: Laravel Breeze.
- UI: Blade, Blade components, Tailwind CSS, Alpine.js, Axios y Vite.
- Base de datos local por defecto: SQLite.
- Testing: PHPUnit 11 con pruebas Feature y Unit.
- Estilo PHP: Laravel Pint.

## Instalación
```bash
composer setup
```

El script instala dependencias PHP, crea `.env` si falta, genera `APP_KEY`, ejecuta migraciones, instala dependencias npm y compila assets.

Para crear el usuario de prueba:
```bash
php artisan db:seed
```

Credenciales del seeder: `test@example.com` / `password`.

## Desarrollo
```bash
composer dev
```

Ese comando levanta `php artisan serve`, `queue:listen`, `pail` y `npm run dev` en paralelo.

Comandos útiles:
```bash
composer test
php artisan test tests/Feature/ProfileTest.php
php artisan test --filter=test_new_users_can_register
vendor/bin/pint --test
vendor/bin/pint
npm run build
```

## Módulos
- `clientes`: CRUD persistente con `Client`; no permite eliminar clientes con cotizaciones.
- `catalogo`: CRUD persistente con `Product`; no permite eliminar productos usados en cotizaciones o pedidos.
- `cotizaciones`: CRUD con `Quotation` y `QuotationItem`; maneja estados comerciales, descuentos, IVA y conversión a pedido.
- `pedidos`: módulo de consulta con `Order` y `OrderItem`; los pedidos se crean desde cotizaciones aceptadas.

## Reglas Relevantes
- Las rutas, vistas y textos de dominio están en español; modelos y tablas principales están en inglés.
- Las cotizaciones se convierten a pedido solo si están en estado `aceptada` y no tienen pedido previo.
- Los pedidos copian precios, descuentos y cantidades de la cotización como snapshot; no dependen del precio actual del catálogo.
- La conversión no descuenta stock actualmente.
- `App\Models\Catalogo` parece legado; el catálogo activo usa `Product` y la tabla `products`.
- `notas` aparece en vistas de cotización, pero no está persistido en la tabla `quotations`.

## Rutas Principales
- `/dashboard`: panel autenticado y verificado.
- `/clientes`: resource CRUD autenticado.
- `/catalogo`: resource CRUD autenticado.
- `/cotizaciones`: resource CRUD autenticado.
- `/cotizaciones/{cotizacion}/convertir`: convierte una cotización aceptada en pedido.
- `/pedidos`: listado y detalle autenticados.

## Base De Datos Y Pruebas
- `.env.example` usa `DB_CONNECTION=sqlite` y existe `database/database.sqlite`.
- `phpunit.xml` usa SQLite en memoria con cache, sesión y mail en modo array.
- `DatabaseSeeder` solo crea el usuario de prueba; no hay seeders de clientes, productos, cotizaciones o pedidos.
