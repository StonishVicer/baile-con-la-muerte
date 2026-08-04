# Baile con la Muerte

Aplicación web para agendar una cita para "bailar con la muerte". El usuario selecciona una fecha y un horario disponible para reservar su cita.

## Requisitos

- **PHP** >= 8.3
- **Composer**
- **Node.js** >= 20 y **npm**
- **PostgreSQL** >= 14 (base de datos requerida por la prueba técnica)
- Extensiones de PHP habilitadas para PostgreSQL (`pgsql` / `pdo_pgsql`)

## Stack

- **Backend:** Laravel 12 con arquitectura Service + Repository
- **Frontend:** Vue 3 + Inertia.js + Tailwind CSS v4 (Vite)
- **Base de datos:** PostgreSQL

## Reglas de negocio implementadas

- El usuario selecciona una fecha.
- Solo se pueden seleccionar días hábiles (lunes a viernes).
- No se pueden seleccionar fechas pasadas.
- El horario disponible es de 09:00 a 19:00 en bloques de una hora.
- Una misma fecha y hora no puede reservarse dos veces.
- El usuario debe ingresar su nombre y correo electrónico.
- Un mismo correo electrónico solo puede tener una cita registrada.
- La aplicación muestra qué horarios están disponibles y cuáles ya están ocupados.
- Los horarios que ya han pasado el día actual quedan bloqueados.

## Instalación

### 1. Clonar el repositorio y entrar en el directorio

```bash
git clone https://github.com/StonishVicer/baile-con-la-muerte.git
cd baile-con-la-muerte
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar el entorno

Copia el archivo de ejemplo y genera la clave de la aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar PostgreSQL

Crea una base de datos en PostgreSQL (por ejemplo `baile_con_la_muerte`) y configura las credenciales en el archivo `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=baile_con_la_muerte
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### 5. Ejecutar las migraciones

```bash
php artisan migrate
```

## Ejecución

Levanta el backend y el frontend (Vite):

```bash
npm run dev
```

El comando inicia ambos procesos. Accede a la aplicación en:

```
http://localhost:8000/appointments
```

Para verificar el estado de las citas registradas:

```bash
php artisan tinker --execute="App\Models\Appointment::all()"
```

## Comandos útiles

| Comando | Descripción |
| --- | --- |
| `npm run dev` | Inicia el servidor de desarrollo (Laravel + Vite) |
| `npm run build` | Compila los assets para producción |
| `composer run lint` | Formatea el código PHP (Pint) |
| `npm run lint` | Corrige errores de ESLint en el frontend |
| `npm run types:check` | Verifica tipos TypeScript (vue-tsc) |

## Estructura relevante

```
app/Http/Controllers/AppointmentController.php   # Controlador de la API
app/Services/AppointmentService.php              # Lógica de negocio y validaciones
app/Repositories/                                # Capa de acceso a datos
database/migrations/...create_appointments_table.php  # Esquema de la tabla
resources/js/pages/Appointments/Index.vue        # Página de reserva
routes/api.php                                   # Endpoints de la API
routes/web.php                                   # Ruta de la página
```

## Endpoints de la API

| Método | Ruta | Descripción |
| --- | --- | --- |
| `GET` | `/api/appointments` | Lista todas las citas |
| `POST` | `/api/appointments` | Crea una cita (con validaciones) |
| `GET` | `/api/available-slots/{date}` | Devuelve los horarios y su disponibilidad para una fecha |
| `GET` | `/api/appointments/{id}` | Muestra una cita específica |

### Ejemplo de reserva

```bash
curl -X POST http://localhost:8000/api/appointments \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Juan Pérez","email":"juan@example.com","date":"2026-08-04","time":"10:00"}'
```

## Notas

- El proyecto incluye funcionalidad de autenticación y dashboard del starter kit de Laravel, pero la página de reserva (`/appointments`) es pública y no requiere iniciar sesión.
- Se usa SSR (Server-Side Rendering) de Inertia para la página de reserva.
