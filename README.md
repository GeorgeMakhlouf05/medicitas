# MediCitas – Taller Prueba y Depuración

Sistema de gestión de citas médicas de la clínica **MediCitas**, desarrollado con Laravel como parte del *Taller Prueba y Depuración* (Universidad Autónoma de Bucaramanga – UNAB).

## Objetivo

Implementar un módulo web (Blade) y una API JSON para gestionar citas médicas, y una suite de pruebas automatizadas que verifique: el filtrado por estado, la persistencia en base de datos, el envío de correo al médico y la detección del problema de consultas N+1.

## Tecnologías

- Laravel 13 y PHP 8.5
- SQLite (desarrollo) y SQLite en memoria (pruebas)
- Laravel Sanctum (tokens para la API)
- Laravel Breeze, pila Blade (inicio de sesión y registro)
- Tailwind CSS y Vite
- PHPUnit

## Requisitos previos

- PHP 8.5 con la extensión `pdo_sqlite`
- Composer
- Node.js y npm
- Git

## Instalación

```bash
git clone https://github.com/GeorgeMakhlouf05/medicitas
cd medicitas
composer install
copy .env.example .env        # en Linux/macOS: cp .env.example .env
php artisan key:generate
```

Cree el archivo vacío de la base de datos si no existe:

```powershell
New-Item database\database.sqlite -ItemType File    # Linux/macOS: touch database/database.sqlite
```

Ejecute las migraciones y compile los recursos del frontend:

```bash
php artisan migrate
npm install
npm run build
```

### Notas de entorno

- `.env.example` usa `DB_CONNECTION=sqlite` (archivo `database/database.sqlite`).
- `.env.example` usa `MAIL_MAILER=log`: los correos **no se envían realmente**, se escriben en `storage/logs/laravel.log`.
- Si edita clases de Tailwind en las vistas Blade, vuelva a ejecutar `npm run build` (o use `npm run dev`).

## Datos de prueba (seeders)

```bash
php artisan db:seed
```

El seeder `CitaSeeder` es idempotente: si ya existen citas, no crea nada. Crea 1 paciente, 3 médicos y 12 citas (por médico: 2 confirmadas, 1 pendiente y 1 cancelada).

> **Solo datos de demostración locales.** No use estas credenciales en ningún entorno real.

| Rol      | Correo                       | Contraseña |
|----------|------------------------------|------------|
| Paciente | paciente@medicitas.test      | password   |
| Médico   | laura.gomez@medicitas.test   | password   |
| Médico   | carlos.rivera@medicitas.test | password   |
| Médico   | ana.torres@medicitas.test    | password   |

## Ejecutar la aplicación

```bash
php artisan serve
```

Abra `http://127.0.0.1:8000/login`, inicie sesión y use el enlace **Citas** del menú:

- `/citas`: listado de citas (admite `?estado=pendiente|confirmada|cancelada`).
- `/citas/crear`: formulario para agendar una cita.

## Pruebas automatizadas

```bash
php artisan test
```

Pruebas individuales:

```bash
php artisan test --filter=test_muestra_citas_confirmadas
php artisan test --filter=test_agendar_cita_y_envio_correo
php artisan test --filter=test_detecta_problema_n_mas_1
```

Resultado de la suite completa en el equipo de desarrollo: **28 pruebas aprobadas (101 aserciones)**. Son 25 pruebas que trae Laravel Breeze y las 3 de `CitaApiTest`.

### Base de datos de desarrollo vs. base de datos de pruebas

| Entorno    | Base de datos                         | Configuración                                  |
|------------|---------------------------------------|------------------------------------------------|
| Desarrollo | Archivo `database/database.sqlite`    | `.env` (`DB_CONNECTION=sqlite`)                |
| Pruebas    | SQLite en memoria (`:memory:`)        | `phpunit.xml`                                  |

Las pruebas nunca tocan el archivo de desarrollo: cada ejecución crea y descarta su propia base de datos en memoria (`RefreshDatabase`). Además, `phpunit.xml` usa `MAIL_MAILER=array`.

### Descripción de las tres pruebas (`tests/Feature/CitaApiTest.php`)

1. **`test_muestra_citas_confirmadas`**: crea 3 citas confirmadas, 2 pendientes y 2 canceladas; consulta `GET /api/citas` y verifica que responde 200, devuelve exactamente las 3 confirmadas y ninguna de los otros estados.
2. **`test_agendar_cita_y_envio_correo`**: con `Mail::fake()` y un paciente autenticado con Sanctum, hace `POST /api/citas`; verifica el código 201, el JSON, la fila en la base de datos con las llaves foráneas correctas y que se envió un correo dirigido al médico (y no al paciente).
3. **`test_detecta_problema_n_mas_1`**: cuenta las consultas SQL de `GET /api/citas` con 3 y luego con 10 citas; verifica que el número de consultas no crece, y que hay 1 consulta sobre `citas` y 2 sobre `users` (pacientes y médicos).

## API

Rutas en `routes/api.php`:

| Método | Ruta         | Autenticación  | Descripción                                                   |
|--------|--------------|----------------|---------------------------------------------------------------|
| GET    | `/api/citas` | No             | Lista citas. Sin `?estado` devuelve solo las **confirmadas**. |
| POST   | `/api/citas` | Token Sanctum  | Agenda una cita para el usuario autenticado.                  |

Respuestas: `GET` con `?estado` inválido → 422. `POST` sin token → 401. `POST` con datos inválidos → 422.

### Obtener un token de Sanctum para pruebas

```bash
php artisan tinker
>>> \App\Models\User::where('email','paciente@medicitas.test')->first()->createToken('prueba')->plainTextToken
```

No publique ni guarde el token en el repositorio.

### Ejemplos

Listar citas pendientes:

```bash
curl -H "Accept: application/json" "http://127.0.0.1:8000/api/citas?estado=pendiente"
```

Agendar una cita (el paciente es siempre el usuario del token; `estado` no se acepta, siempre inicia en `pendiente`):

```bash
curl -X POST http://127.0.0.1:8000/api/citas \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{"doctor_id":2,"especialidad":"Cardiología","fecha_hora":"2026-12-15 10:00:00","motivo":"Control de rutina anual"}'
```

Respuesta (201):

```json
{
  "data": {
    "id": 17,
    "patient_id": 1,
    "doctor_id": 2,
    "especialidad": "Cardiología",
    "fecha_hora": "2026-12-15T10:00:00+00:00",
    "estado": "pendiente",
    "motivo": "Control de rutina anual",
    "patient": { "id": 1, "name": "Paciente Demo" },
    "doctor": { "id": 2, "name": "Dra. Laura Gómez", "email": "laura.gomez@medicitas.test" }
  }
}
```

Reglas de validación (`StoreCitaRequest`): `doctor_id` obligatorio, existente y distinto del propio usuario; `especialidad` máximo 100 caracteres; `fecha_hora` fecha posterior a la actual; `motivo` entre 5 y 1000 caracteres.

## Correo de notificación

Al agendar una cita (por la web o por la API) se envía un correo al médico asignado con el asunto **"Nueva cita médica agendada"** (`app/Mail/CitaAgendada.php`, vista `resources/views/emails/cita-agendada.blade.php`).

- Con `MAIL_MAILER=log` el correo queda en `storage/logs/laravel.log`.
- Se comprobó que una petición inválida (422) no genera correo y que una válida genera exactamente uno, dirigido al médico.
- Para enviar correos reales, configure `MAIL_MAILER` y las variables `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` en su `.env`.

## El problema N+1 y la carga anticipada (eager loading)

El problema N+1 ocurre cuando se carga una lista de N citas y, al mostrar el paciente y el médico de cada una, Eloquent ejecuta una consulta adicional por cada cita (1 consulta de citas + N de pacientes + N de médicos).

En `Api\CitaController@index` se evita con carga anticipada:

```php
Cita::with(['patient', 'doctor'])->estado($estado)->orderBy('fecha_hora')->get();
```

Así el número de consultas es constante (1 de citas y 2 de usuarios) sin importar cuántas citas haya. `CitaResource` incluye siempre paciente y médico (no usa `whenLoaded`), de modo que olvidar `with()` provocaría consultas N+1 reales que `test_detecta_problema_n_mas_1` detecta. En la suite actual esta prueba pasa con la carga anticipada activa.

## Trazabilidad

| Requisito | Implementación | Verificación |
|-----------|----------------|--------------|
| RF 1.1 Modelos `User` y `Cita` | `app/Models/User.php`, `app/Models/Cita.php`, `database/migrations/2026_10_27_190348_create_citas_table.php`, `database/factories/CitaFactory.php`, `database/seeders/CitaSeeder.php` | Migración y seeder ejecutados; tests de `CitaApiTest` |
| RF 2 Interfaz Blade (listar y agendar) | `app/Http/Controllers/CitaWebController.php`, `resources/views/citas/index.blade.php`, `resources/views/citas/create.blade.php`, rutas en `routes/web.php` | Manual en el navegador (`/citas`, `/citas/crear`) |
| RF 3 Rutas API equivalentes | `routes/api.php`, `app/Http/Controllers/Api/CitaController.php`, `app/Http/Resources/CitaResource.php`, `app/Http/Requests/StoreCitaRequest.php` | `php artisan route:list`; `CitaApiTest` |
| RT 1 Pruebas en base de datos en memoria | `phpunit.xml` (`sqlite` y `:memory:`) | `php artisan test` |
| RT 2 Clase de pruebas con 3 métodos | `tests/Feature/CitaApiTest.php` | `php artisan test --filter=<nombre>` |
| Correo al médico | `app/Actions/AgendarCita.php`, `app/Mail/CitaAgendada.php`, `resources/views/emails/cita-agendada.blade.php` | `test_agendar_cita_y_envio_correo`; `storage/logs/laravel.log` |
| Entregable: README | `README.md` | Este documento |
| Entregable: repositorio público | Enlace enviado por la plataforma TEMA | Ver la entrega en TEMA |

## Limitaciones conocidas

- `GET /api/citas` es público (no requiere token).
- Como el enunciado no define roles, cualquier usuario autenticado ve todas las citas y cualquier otro usuario puede elegirse como médico.
- La API devuelve por defecto solo las citas confirmadas; el listado web muestra todas.
- Los valores permitidos de `estado` se validan en el código, no con una restricción en la base de datos.
- Si falla el envío del correo, el error se registra en el log pero no se muestra al paciente; la cita queda guardada.
- La zona horaria de la aplicación es UTC, salvo que se defina `APP_TIMEZONE`.
