# Muebles

Aplicación web en Laravel para gestionar el flujo comercial de una mueblería: clientes, catálogo de productos, cotizaciones y pedidos generados desde cotizaciones aceptadas.

## Estado Actual

- Autenticación implementada con Laravel Breeze.
- CRUD persistente para clientes.
- CRUD persistente para productos del catálogo.
- Cotizaciones persistentes con líneas, estados comerciales, descuentos, IVA y total.
- Conversión de cotizaciones aceptadas a pedidos con snapshot de precios.
- Pedidos disponibles como módulo de consulta: listado y detalle.
- Dashboard autenticado disponible, pero todavía sin métricas de negocio.
- Livewire no está instalado ni usado actualmente; la UI usa Blade, Tailwind, Alpine y Vite.

## Stack Técnico

| Área | Tecnología |
| --- | --- |
| Backend | PHP `^8.2`, Laravel `^12.0` |
| Autenticación | Laravel Breeze |
| Vistas | Blade y Blade Components |
| Frontend | Tailwind CSS, Alpine.js, Axios |
| Assets | Vite |
| Base de datos local | SQLite por defecto |
| Testing | PHPUnit `11`, pruebas Feature y Unit |
| Estilo PHP | Laravel Pint |

## Requisitos

- PHP 8.2 o superior.
- Composer.
- Node.js y npm.
- SQLite para el entorno local por defecto, o MySQL/MariaDB configurando `.env`.

## Instalación

El proyecto incluye un script de setup en `composer.json`:

```bash
composer setup
```

Ese comando instala dependencias PHP, crea `.env` si no existe, genera la app key, ejecuta migraciones, instala dependencias npm y compila assets.

Instalación manual equivalente:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Para crear el usuario de prueba del seeder:

```bash
php artisan db:seed
```

Credenciales del usuario generado por `DatabaseSeeder`:

| Campo | Valor |
| --- | --- |
| Email | `test@example.com` |
| Password | `password` |

## Desarrollo Local

Levantar el stack completo de desarrollo:

```bash
composer dev
```

Ese comando ejecuta en paralelo `php artisan serve`, `php artisan queue:listen --tries=1 --timeout=0`, `php artisan pail --timeout=0` y `npm run dev`.

Comandos individuales útiles:

```bash
php artisan serve
npm run dev
npm run build
composer test
vendor/bin/pint --test
vendor/bin/pint
```

## Variables Y Base De Datos

`.env.example` viene configurado con SQLite:

```env
DB_CONNECTION=sqlite
```

El archivo `database/database.sqlite` existe en el proyecto. Si se usa MySQL con XAMPP, ajustar en `.env` los valores `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

Las pruebas usan SQLite en memoria mediante `phpunit.xml`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## Rutas Principales

Las rutas web están en `routes/web.php`. Laravel 12 configura routing desde `bootstrap/app.php`.

| Ruta | Nombre | Estado |
| --- | --- | --- |
| `/` | Sin nombre | Vista pública `welcome` |
| `/dashboard` | `dashboard` | Requiere auth y usuario verificado |
| `/clientes` | `clientes.*` | Resource CRUD autenticado |
| `/catalogo` | `catalogo.*` | Resource CRUD autenticado |
| `/cotizaciones` | `cotizaciones.*` | Resource CRUD autenticado |
| `/cotizaciones/{cotizacion}/convertir` | `cotizaciones.convertir` | Convierte cotización aceptada a pedido |
| `/pedidos` | `pedidos.index` | Listado autenticado |
| `/pedidos/{pedido}` | `pedidos.show` | Detalle autenticado |
| `/profile` | `profile.*` | Perfil de Breeze |
| `/login`, `/register`, `/logout` | Auth Breeze | Autenticación |

## Estructura Relevante

```text
app/
  Http/Controllers/
    CatalogoController.php
    ClienteController.php
    CotizacionController.php
    PedidoController.php
    ProfileController.php
  Models/
    Client.php
    Product.php
    Quotation.php
    QuotationItem.php
    Order.php
    OrderItem.php
    User.php
  Services/
    CotizacionTotals.php
    QuotationService.php
    ConversionService.php
database/
  migrations/
resources/
  views/
    clientes/
    catalogo/
    cotizaciones/
    pedidos/
    layouts/
routes/
  web.php
  auth.php
```

## Modelo De Datos

| Tabla | Propósito |
| --- | --- |
| `clients` | Clientes o empresas compradoras |
| `products` | Productos del catálogo de muebles |
| `quotations` | Cotizaciones comerciales |
| `quotation_items` | Conceptos o líneas de una cotización |
| `orders` | Pedidos generados desde cotizaciones |
| `order_items` | Snapshot de conceptos y precios del pedido |
| `users` | Usuarios autenticados de Breeze |

Relaciones principales:

- `Client` tiene muchas `Quotation`.
- `Product` tiene muchas `QuotationItem` y muchas `OrderItem`.
- `Quotation` pertenece a `Client` y `User`, tiene muchas `QuotationItem` y puede tener un `Order`.
- `QuotationItem` pertenece a `Quotation` y `Product`.
- `Order` pertenece a `Quotation`, `Client` y `User`, y tiene muchas `OrderItem`.
- `OrderItem` pertenece a `Order` y `Product`.

## Módulos Funcionales

### Clientes

- Controlador: `app/Http/Controllers/ClienteController.php`.
- Modelo: `app/Models/Client.php`.
- Vistas: `resources/views/clientes`.
- Campos: nombre, correo, teléfono, RFC y dirección.
- Funciones: listar, crear, editar, actualizar y eliminar.
- Regla actual: no se elimina un cliente si tiene cotizaciones registradas.

### Catálogo

- Controlador: `app/Http/Controllers/CatalogoController.php`.
- Modelo activo: `app/Models/Product.php`.
- Vistas: `resources/views/catalogo`.
- Campos: SKU, nombre, material, descripción, precio unitario, stock y activo.
- Funciones: listar, crear, editar, actualizar y eliminar productos.
- Regla actual: no se elimina un producto si fue usado en cotizaciones o pedidos.

### Cotizaciones

- Controlador: `app/Http/Controllers/CotizacionController.php`.
- Modelos: `Quotation` y `QuotationItem`.
- Vistas: `resources/views/cotizaciones`.
- Estados: `borrador`, `enviada`, `aceptada`, `convertida`, `rechazada`, `vencida`.
- Funciones: listar, crear, ver, editar, actualizar, eliminar y convertir a pedido.
- Regla actual: una cotización con pedido asociado no se puede eliminar.
- Conversión actual: solo se permite convertir si el estado es `aceptada` y no existe pedido previo.

Fórmula usada por el controlador para totales:

```text
subtotal_linea = max((cantidad * precio_unitario) - descuento_linea, 0)
subtotal = suma de subtotales de línea
base = max(subtotal - descuento_global, 0)
iva = base * 0.16
total = base + iva
```

### Pedidos

- Controlador: `app/Http/Controllers/PedidoController.php`.
- Modelos: `Order` y `OrderItem`.
- Vistas: `resources/views/pedidos`.
- Funciones: listar y ver detalle.
- Creación: se generan desde `cotizaciones.convertir`.
- Regla de negocio: el pedido copia productos, cantidades, descuentos y precios pactados desde la cotización para conservar un snapshot aunque el catálogo cambie después.

### Perfil Y Autenticación

- Breeze provee login, registro, recuperación de contraseña, confirmación de contraseña, verificación de email y logout.
- `ProfileController` permite editar información de perfil, actualizar contraseña y eliminar cuenta.

## Servicios De Dominio

| Servicio | Uso actual |
| --- | --- |
| `CotizacionTotals` | Calcula totales con nombres de campos usados por vistas de cotización |
| `QuotationService` | Servicio disponible para recalcular totales, agregar/quitar líneas, aplicar descuentos, cambiar estados y marcar vencidas |
| `ConversionService` | Servicio disponible para convertir una cotización a pedido dentro de una transacción |

Nota técnica: `CotizacionController` actualmente contiene parte de la lógica de cálculo y conversión directamente. Si el proyecto crece, conviene consolidar esa lógica en `QuotationService` y `ConversionService` para evitar duplicación.

## Flujo Manual Recomendado

1. Registrar un usuario o ejecutar `php artisan db:seed` y entrar con `test@example.com` / `password`.
2. Crear uno o más clientes desde `Clientes`.
3. Crear productos activos desde `Catálogo`.
4. Crear una cotización asociando cliente y productos.
5. Cambiar la cotización a estado `aceptada`.
6. Entrar al detalle de la cotización y usar `Convertir a pedido`.
7. Revisar el pedido generado desde `Pedidos`.

## Frontend

- El layout autenticado está en `resources/views/layouts/app.blade.php`.
- La navegación está en `resources/views/layouts/navigation.blade.php`.
- Los assets se cargan con `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- Alpine se inicializa en `resources/js/app.js`.
- Axios se configura globalmente en `resources/js/bootstrap.js`.
- Tailwind escanea `resources/views/**/*.blade.php`, vistas cacheadas y vistas de paginación de Laravel.

## Testing Y Calidad

Ejecutar toda la suite:

```bash
composer test
```

Ejecutar una prueba puntual:

```bash
php artisan test tests/Feature/ProfileTest.php
php artisan test --filter=test_new_users_can_register
```

Revisar estilo PHP:

```bash
vendor/bin/pint --test
```

Corregir estilo PHP:

```bash
vendor/bin/pint
```

Compilar assets:

```bash
npm run build
```

## Observaciones Y Pendientes Detectados

- `DatabaseSeeder` solo crea un usuario de prueba; todavía no hay seeders de clientes, productos, cotizaciones o pedidos.
- Las pruebas actuales cubren principalmente Breeze, perfil y ejemplos base; faltan pruebas Feature para clientes, catálogo, cotizaciones, conversión y pedidos.
- `app/Models/Catalogo.php` parece un modelo legado apuntando a una tabla `catalogo`; el CRUD actual usa `Product` y la tabla `products`.
- El botón `+ Agregar línea` en la vista de creación de cotizaciones todavía no agrega líneas dinámicamente.
- El campo `notas` aparece en vistas de cotización, pero no está persistido en la tabla `quotations` ni validado por el controlador.
- La vista de creación de cotización conserva valores por defecto hardcodeados para folio y fechas, aunque el controlador construye una plantilla dinámica.
- `ConversionService` valida convertibilidad usando `Quotation::isConvertible()`, pero la ruta actual de conversión implementa la conversión inline en `CotizacionController`.
- No hay decremento de stock al convertir una cotización en pedido.
- El módulo de pedidos no tiene edición, cancelación ni cambio de estado desde la interfaz.

## Convenciones Del Proyecto

- Las rutas, vistas y copy de dominio están en español: `clientes`, `catalogo`, `cotizaciones`, `pedidos`.
- Los modelos y tablas principales están en inglés: `Client`, `Product`, `Quotation`, `Order`.
- Mantener esa convención mixta mientras no se haga una refactorización completa.
- En Laravel 12 el bootstrap principal está en `bootstrap/app.php`; no existe `app/Http/Kernel.php`.
