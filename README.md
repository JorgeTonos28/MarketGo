# MarketGo MVP

MarketGo es una plataforma Laravel para planificar compras inteligentes en supermercados y otros establecimientos. Este MVP incluye un panel de usuario completo, datos de demostración y un área administrativa reservada para cuentas con rol de administrador.

## Funcionalidades principales

- **Autenticación básica** con usuarios demo ya configurados (`admin@marketgo.test` y `demo@marketgo.test`, contraseña `password`).
- **Dashboard personal** con métricas de listas activas, presupuesto estimado, consumo reciente y gastos por establecimiento.
- **Gestión de listas inteligentes**: crea listas, selecciona productos del catálogo o agrégalos manualmente, asigna supermercados por ítem, define pasillos y calcula el orden automáticamente.
- **Interacción en la lista**: marca productos como agregados al carrito y revisa un resumen con pendientes vs. items en el carrito.
- **Módulo de establecimientos** para registrar supermercados, ferreterías u otras tiendas con sus pasillos.
- **Panel administrativo** (solo administradores) con la visión general creada previamente.

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- Base de datos compatible con Laravel (SQLite, MySQL, PostgreSQL, etc.)

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build # o npm run dev para entorno local
```

Configura la conexión a la base de datos en `.env` y luego ejecuta las migraciones con datos de demo:

```bash
php artisan migrate --seed
```

## Usuarios de demostración

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administrador | `admin@marketgo.test` | `password` |
| Usuario | `demo@marketgo.test` | `password` |

## Ejecución

Arranca el servidor de desarrollo:

```bash
php artisan serve
```

Visita `http://localhost:8000` para acceder al login. Tras iniciar sesión podrás navegar por el dashboard, gestionar listas y crear establecimientos. Si ingresas con la cuenta de administrador tendrás acceso adicional al panel `/admin`.

## Tests

```bash
php artisan test
```
