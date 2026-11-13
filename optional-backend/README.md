# OPTIONAL Backend

OPTIONAL Backend is a Laravel REST API for the OPTIONAL product catalog. It provides authentication, product management, and admin-protected routes for the platform.

## Project and group

- Frontend repository: [KuyaMen/optional-frontend](https://github.com/KuyaMen/optional-frontend)
- Backend repository: [KuyaMen/optional-backend](https://github.com/KuyaMen/optional-backend)
- Group name: OPTIONAL
- Members:
  - Kristian Jay Eugenio — [@KuyaMen](https://github.com/KuyaMen)
  - Jave Balbuena — [@jebzkybalbuena-debug](https://github.com/jebzkybalbuena-debug)
  - Grezz Quivon Mahinay — [@grasyaa](https://github.com/grezzxso)
  - Eula Jane Justiniane — [@eulzjustin](https://github.com/eulzjustin)
  - Cristine Pagobo — [@cristinayy](https://github.com/cristinayy)
  - Glicerio Lamento — [@Glicerio2000](https://github.com/Glicerio2000)

## Prerequisites and installation

Requirements:

- PHP 8.3+
- Composer
- SQLite or another configured Laravel database

Steps:

1. Install PHP dependencies with `composer install`.
2. Copy `.env.example` to `.env` and run `php artisan key:generate`.
3. For SQLite, create `database/database.sqlite` if it does not exist.
4. Configure the database in `.env`.
5. Run migrations and product seed data with `php artisan migrate --seed`.
6. Start the backend with `php artisan serve`.
7. To provision an administrator, set `ADMIN_FIRSTNAME`, `ADMIN_LASTNAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` in `.env`, then run `php artisan db:seed --class=AdminUserSeeder`.

Never commit `.env` or publish administrator credentials. Public registration creates regular users and rejects role or permission fields.

## Configuring environment variables

- Copy `.env.example` to `.env` before running the project.
- Never commit `.env` files or publish real credentials.
- Configure your local database and Laravel app settings in `.env`.
- Public registration creates regular users and rejects role or permission fields.

## Running the frontend and backend locally

Backend:

```bash
php artisan serve
```

Frontend:

```bash
cd ../optional-frontend
npm install
npm run dev
```

## Authentication endpoints and product API routes

The API uses Laravel Sanctum bearer tokens.

| Request | Access |
| --- | --- |
| `POST /api/register` | Public |
| `POST /api/login` | Public |
| `POST /api/logout` | Authenticated |
| `GET /api/products` | Authenticated users and admins |
| `GET /api/products/{id}` | Authenticated users and admins |
| `POST /api/products` | Admin only |
| `PUT /api/products/{id}` | Admin only |
| `PATCH /api/products/{id}` | Admin only |
| `DELETE /api/products/{id}` | Admin only |

Guests receive `401 Unauthorized`; signed-in users attempting admin actions receive `403 Forbidden`.

## Role and permission rules

- Public registration creates regular users only.
- Role and permission fields are rejected during registration.
- Only trusted operators can create administrator accounts with `AdminUserSeeder`.
- The backend enforces these rules independently of frontend controls.

## Known limitations

- Password reset and email verification are not implemented.
- Administrator accounts must be provisioned by a trusted operator with `AdminUserSeeder`.
- The frontend and backend are maintained in separate repositories.

## Verification

Run the test suite with:

```bash
php artisan test
```

The project uses an in-memory SQLite database and a test-only encryption key configured in `phpunit.xml`.
