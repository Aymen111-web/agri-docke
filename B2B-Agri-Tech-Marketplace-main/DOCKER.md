# Docker Setup & Operations Guide — B2B Agri-Tech Marketplace

This guide covers building, running, operating, and maintaining the containerized **B2B Agri-Tech Marketplace** using Docker and Docker Compose on Windows 11 (WSL 2 / Ubuntu) and Linux production environments.

---

## 1. Architecture Overview

The application is containerized using four dedicated, isolated services connected via an internal bridge network (`agri_network`):

```
                       +---------------------------------------+
                       |              Browser / Client         |
                       +---------------------------------------+
                                           |
                                  Port 8080 (HTTP)
                                           v
                       +---------------------------------------+
                       |      Reverse Proxy (agri_nginx)       |
                       |             Nginx:alpine              |
                       +---------------------------------------+
                           /              |               \
                location /api/    location /storage/   location /
                          |               |                |
                       FastCGI      Direct Disk         HTTP Proxy
                      (Port 9000)      Volume          (Port 80)
                          |               |                |
                          v               |                v
        +----------------------------+    |    +----------------------------+
        |   Backend (agri_backend)   |<---+    |  Frontend (agri_frontend)  |
        |   PHP 8.3-FPM (Laravel 13) |         |      Vue 3 + Nginx SPA     |
        +----------------------------+         +----------------------------+
                      |
                 MySQL (Port 3306)
                      v
        +----------------------------+
        |    Database (agri_mysql)   |
        |         MySQL 8.0          |
        |  Named Volume: mysql_data  |
        +----------------------------+
```

### Components
1. **`agri_nginx` (Ingress Reverse Proxy)**:
   - Single point of entry on port `8080` (configurable via `APP_PORT`).
   - Routes `/api/*`, `/up`, and `/sanctum/*` directly to Laravel's FastCGI server (`backend:9000`).
   - Serves uploaded media from `/storage/*` directly from the shared persistent volume with aggressive browser caching.
   - Proxies all frontend single-page application requests (`/`, `/marketplace`, etc.) to `frontend:80`.
   - Sets request size limit to `64M` (`client_max_body_size`) to support produce photos and verification documents.
   - Eliminates CORS issues because frontend and API share the same origin.

2. **`agri_backend` (Laravel 13 REST API)**:
   - Official `php:8.3-fpm-alpine` base image.
   - Extensions: `pdo_mysql`, `mbstring`, `bcmath`, `opcache`, `zip`, `intl`, `gd`, `pcntl`, `posix`, `exif`.
   - Composer production dependencies pre-installed and optimized.
   - Automatic database connectivity wait loop on startup.
   - Automatic execution of database migrations (`AUTORUN_MIGRATIONS=true`).
   - Automatic storage symlinking and permissions (`storage/`, `bootstrap/cache`).
   - PHP-FPM listening internally on port `9000`.

3. **`agri_frontend` (Vue 3 Single Page Application)**:
   - Multi-stage build (`node:22-alpine` builder -> `nginx:alpine` runtime).
   - Clean dependency installation using lockfile (`npm ci`).
   - Pre-configured with relative `/api` base URL (`VITE_API_BASE_URL=/api`).
   - HTML5 History Mode SPA routing fallback (`try_files $uri $uri/ /index.html`).

4. **`agri_mysql` (MySQL 8.0 Database)**:
   - Official `mysql:8.0` image.
   - Persistent named Docker volume (`agri_mysql_data`).
   - Built-in container health check (`mysqladmin ping`).
   - Private to the internal Docker network; not exposed publicly to host.

---

## 2. Quick Start

### Step 1: Copy Environment File
Ensure a valid `.env` file exists before starting:
```bash
# From the project directory:
cp .env.docker.example .env
```

### Step 2: Build & Start All Services
```bash
docker compose up -d --build
```

### Step 3: Access the Application
- **Frontend Application (Vue 3)**: [http://localhost:8080](http://localhost:8080)
- **Backend Health Check**: [http://localhost:8080/up](http://localhost:8080/up)
- **Backend API Endpoints**: [http://localhost:8080/api/...](http://localhost:8080/api/listings)

---

## 3. Developer Commands Reference

### Service Management

| Action | Command |
|---|---|
| Start all services (background) | `docker compose up -d` |
| Rebuild images and start | `docker compose up -d --build` |
| View status of services | `docker compose ps` |
| Stop all running services | `docker compose stop` |
| Stop and remove containers & networks | `docker compose down` |
| Stop and remove containers + volumes (⚠️ clears DB) | `docker compose down -v` |
| View unified real-time logs | `docker compose logs -f` |
| View logs for a specific service | `docker compose logs -f backend` |

---

### Laravel Artisan & Composer Commands

Run commands inside the running backend container without SSH:

```bash
# Execute any artisan command:
docker compose exec backend php artisan <command>

# Examples:
docker compose exec backend php artisan route:list
docker compose exec backend php artisan config:clear
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan about

# Interactive Tinker shell:
docker compose exec backend php artisan tinker

# Run Composer commands:
docker compose exec backend composer update --dry-run
docker compose exec backend composer dump-autoload
```

---

### Database Operations (Migrations & Seeders)

```bash
# Run pending migrations:
docker compose exec backend php artisan migrate --force

# Check migration status:
docker compose exec backend php artisan migrate:status

# Rollback last migration batch:
docker compose exec backend php artisan migrate:rollback

# Fresh migration (drops all tables and re-runs all migrations):
docker compose exec backend php artisan migrate:fresh --force

# Seed database with sample admin, farmers, buyers, categories & listings:
docker compose exec backend php artisan db:seed --force
```

> **Seeded Test Credentials:**
> - **Admin User**: Phone: `+251921283801` | Password: `123456`
> - **Farmer User**: Phone: `+251718280155` | Password: `123456`
> - **Buyer User**: Phone: `+251911223344` | Password: `123456`

---

### Database Backup & Restore

#### Backup MySQL to Host:
```bash
# Export full SQL dump to a host file:
docker compose exec -T mysql mysqldump -u root -proot_secret b2b_agri_tech > backup_$(date +%Y%m%d_%H%M%S).sql
```

#### Restore MySQL from Host Dump:
```bash
# Import SQL dump back into container:
docker compose exec -i mysql mysql -u root -proot_secret b2b_agri_tech < backup_file.sql
```

#### Access MySQL CLI Directly:
```bash
docker compose exec mysql mysql -u agri_user -pagri_secret b2b_agri_tech
```

---

### Shell Access to Containers

```bash
# Backend container shell (sh/bash):
docker compose exec backend sh

# Nginx container shell:
docker compose exec nginx sh

# Frontend container shell:
docker compose exec frontend sh
```

---

## 4. Environment Variables Breakdown

| Variable | Description | Default |
|---|---|---|
| `APP_PORT` | Host port mapped to Nginx reverse proxy | `8080` |
| `APP_URL` | Base application URL | `http://localhost:8080` |
| `APP_ENV` | Application environment (`local`, `production`) | `local` |
| `APP_KEY` | Laravel 32-byte encryption key | Provided in example |
| `DB_HOST` | Database hostname inside Docker network | `mysql` |
| `DB_PORT` | Database port inside Docker network | `3306` |
| `DB_DATABASE` | Name of MySQL database | `b2b_agri_tech` |
| `DB_USERNAME` | Non-root application database username | `agri_user` |
| `DB_PASSWORD` | Application database password | `agri_secret` |
| `DB_ROOT_PASSWORD` | MySQL root administrative password | `root_secret` |
| `AUTORUN_MIGRATIONS` | Automatically run migrations on container boot | `true` |
| `AUTORUN_SEEDERS` | Automatically run seeders on container boot | `false` |
| `VITE_API_BASE_URL` | Base API path for Vue frontend client | `/api` |
| `CHAPA_SECRET_KEY` | Secret key from Chapa dashboard | `CHASECK_TEST-...` |
| `CHAPA_WEBHOOK_SECRET`| Webhook verification secret from Chapa | Configured secret |
| `CHAPA_CALLBACK_URL` | Webhook URL reached by Chapa | `http://<host>:8080/api/payments/webhook` |
| `CHAPA_RETURN_URL` | Front-end success redirect page | `http://<host>:8080/payment/success` |
| `SMS_ETHIOPIA_API_KEY`| API key for SMS OTP delivery | Optional for mock OTP |

---

## 5. Persistent Storage & Volumes

The application stores persistent state in two Docker named volumes:

1. **`agri_mysql_data`** (`/var/lib/mysql` in `mysql`):
   - Stores all MySQL database files, user accounts, tables, and transactional records.
   - Data persists across container restarts, stops, and rebuilds.

2. **`agri_storage_data`** (`/var/www/html/storage` in `backend` and `nginx`):
   - Stores produce listings photos (`storage/app/public/produce-photos`), buyer verification documents, and application logs.
   - Shared with `agri_nginx` to allow high-speed direct file serving without passing through PHP-FPM.

To inspect existing volumes:
```bash
docker volume ls | grep agri
```

