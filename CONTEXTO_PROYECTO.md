# Contexto Del Proyecto

## Resumen
- Proyecto Laravel 12 para una aplicación de gestión relacionada con muebles.
- Incluye autenticación con Laravel Breeze y pantallas internas para `clientes`, `catalogo`, `cotizaciones` y `pedidos`.
- La interfaz principal usa Blade, Tailwind CSS, Alpine.js y Vite.
- El idioma funcional de las pantallas de dominio es español: `Clientes`, `Catálogo`, `Cotizaciones`, `Pedidos`, `Nuevo cliente`, `Nuevo producto`.

## Stack Técnico
- PHP `^8.2`.
- Laravel Framework `^12.0`.
- Laravel Breeze para autenticación.
- Blade como motor de vistas.
- Tailwind CSS para estilos.
- Alpine.js para interacciones simples del frontend.
- Axios está configurado globalmente en `resources/js/bootstrap.js`.
- Vite compila `resources/css/app.css` y `resources/js/app.js`.

## Estructura Principal
- `routes/web.php` contiene las rutas web principales.
- `routes/auth.php` contiene las rutas de autenticación de Breeze.
- `bootstrap/app.php` configura el arranque de Laravel 12; no existe `app/Http/Kernel.php`.
- `resources/views/layouts/app.blade.php` es el layout principal autenticado.
- `resources/views/layouts/navigation.blade.php` contiene la navegación hacia Dashboard, Clientes, Catálogo, Cotizaciones y Pedidos.
- `app/Services/CotizacionTotals.php` contiene el cálculo temporal de subtotal, descuento global, IVA y total para cotizaciones/pedidos.
- `app/Http/Controllers/ProfileController.php` maneja edición de perfil y eliminación de cuenta.
- `app/Models/User.php` y `app/Models/Catalogo.php` existen actualmente; `Catalogo` es un modelo mínimo y aún no representa CRUD completo.

## Rutas Actuales
- `/` muestra la vista pública `welcome`.
- `/dashboard` requiere usuario autenticado y verificado.
- `/profile` permite editar perfil, actualizar datos y eliminar cuenta.
- `/clientes` está registrado como resource route bajo middleware `auth`.
- `/catalogo` está registrado como resource route bajo middleware `auth`.
- `/cotizaciones` está registrado como resource route bajo middleware `auth`.
- `/cotizaciones/{cotizacion}/convertir` es una acción temporal para simular conversión a pedido.
- `/pedidos` está registrado como resource route solo para `index` y `show` bajo middleware `auth`.
- Las rutas de login, registro, recuperación de contraseña, verificación de email y logout vienen de Breeze en `routes/auth.php`.

## Estado De Clientes
- `ClienteController` existe, pero la lógica CRUD real aún no está implementada.
- `index()` devuelve `resources/views/clientes/index.blade.php`.
- `create()` devuelve `resources/views/clientes/create.blade.php`.
- `edit()` devuelve `resources/views/clientes/edit.blade.php`.
- `store()` y `update()` están vacíos.
- `destroy()` solo redirige a `clientes.index`.
- No existe modelo `Cliente` ni migración propia para clientes.
- Las vistas de clientes contienen datos estáticos o valores de prueba.

## Estado De Catálogo
- `CatalogoController` existe, pero la lógica CRUD real aún no está implementada.
- `index()` devuelve `resources/views/catalogo/index.blade.php`.
- `create()` devuelve `resources/views/catalogo/create.blade.php`.
- `store()`, `show()`, `edit()`, `update()` y `destroy()` están vacíos.
- Existe `App\Models\Catalogo` como modelo mínimo con `$table = 'catalogo'` y campos fillable básicos.
- No existe migración propia para productos del catálogo.
- `resources/views/catalogo/edit.blade.php` contiene un formulario prototipo de edición conectado a `catalogo.update`.
- La vista de listado de catálogo muestra columnas esperadas como SKU, nombre, material, descripción, precio unitario, stock y activo.

## Estado De Cotizaciones
- `CotizacionController` existe como controlador temporal sin persistencia real.
- `index()`, `create()`, `show()` y `edit()` devuelven vistas Blade con datos estáticos calculados.
- `store()`, `update()`, `destroy()` y `convertir()` redirigen con mensajes temporales.
- No existe modelo `Cotizacion`, `CotizacionLinea` ni migraciones asociadas.
- `resources/views/cotizaciones/index.blade.php` muestra folio, cliente, fechas, estado, total y acciones.
- `resources/views/cotizaciones/create.blade.php` y `edit.blade.php` muestran datos generales, conceptos, descuentos, IVA y total.
- `resources/views/cotizaciones/show.blade.php` muestra detalle, flujo de estados y botón `Convertir a pedido` solo cuando el estado es `Aceptada`.
- Estados visuales usados: `Borrador`, `Enviada`, `Aceptada`, `Convertida`, `Rechazada`, `Vencida`.

## Estado De Pedidos
- `PedidoController` existe como controlador temporal sin persistencia real.
- `index()` y `show()` devuelven vistas Blade con datos estáticos calculados.
- No existe modelo `Pedido`, `PedidoLinea` ni migraciones asociadas.
- `resources/views/pedidos/index.blade.php` muestra pedidos generados desde cotizaciones aceptadas.
- `resources/views/pedidos/show.blade.php` muestra cotización origen, conceptos copiados y snapshot de precios pactados.
- La conversión final pendiente debe crear el pedido dentro de una transacción, copiar líneas/precios y marcar la cotización como `Convertida`.

## Base De Datos
- `.env.example` usa SQLite por defecto.
- Existe `database/database.sqlite` para desarrollo local.
- Las migraciones actuales solo crean tablas base de Laravel: usuarios, password resets, sessions, cache y jobs.
- `DatabaseSeeder` solo crea un usuario de prueba con email `test@example.com`.
- Aún no hay migraciones, factories ni seeders para clientes, productos, cotizaciones o pedidos.

## Testing
- `phpunit.xml` fuerza pruebas con SQLite en memoria usando `DB_DATABASE=:memory:`.
- Las pruebas usan cache, session y mail en modo array.
- La cola se ejecuta en modo `sync` durante pruebas.
- Las pruebas actuales cubren principalmente autenticación, perfil y ejemplos base de Laravel.

## Comandos Útiles
- `composer setup`: instala dependencias, crea `.env`, genera app key, migra, instala npm packages y compila assets.
- `composer dev`: levanta servidor Laravel, worker de cola, logs con Pail y Vite en paralelo.
- `composer test`: limpia configuración y ejecuta `php artisan test`.
- `php artisan test tests/Feature/ProfileTest.php`: ejecuta una prueba por archivo.
- `php artisan test --filter=test_new_users_can_register`: ejecuta una prueba enfocada por nombre.
- `vendor/bin/pint --test`: revisa estilo PHP.
- `vendor/bin/pint`: corrige estilo PHP.
- `npm run dev`: levanta Vite.
- `npm run build`: compila assets de producción.

## Frontend Y Estilos
- `resources/css/app.css` carga las directivas base de Tailwind.
- Tailwind escanea vistas Blade en `resources/views`, vistas cacheadas y vistas de paginación de Laravel.
- Si se agregan clases dinámicas fuera de esas rutas, hay que actualizar `tailwind.config.js`.
- Alpine inicia en `resources/js/app.js`.
- La navegación desktop y móvil incluye rutas reales para `Clientes`, `Catálogo`, `Cotizaciones` y `Pedidos`.

## Pendientes Técnicos Detectados
- Crear modelos, migraciones, factories y seeders para clientes.
- Crear modelos, migraciones, factories y seeders para productos o catálogo.
- Crear modelos, migraciones, factories y seeders para cotizaciones, líneas de cotización, pedidos y líneas de pedido.
- Implementar validación y persistencia en `ClienteController`.
- Implementar validación y persistencia en `CatalogoController`.
- Implementar validación, persistencia y transacciones reales en `CotizacionController`.
- Reemplazar `PedidoController` temporal por lectura desde base de datos.
- Reemplazar datos estáticos de las vistas por datos de base de datos.
- Agregar pruebas Feature para el flujo cotización aceptada -> pedido generado cuando existan modelos.

## Nota Para Continuar Desarrollo
- Antes de asumir que existe CRUD real, revisar controladores, modelos y migraciones.
- Mantener nombres de rutas y textos de dominio en español.
- Para cambios funcionales, acompañar con pruebas Feature cuando sea posible.
