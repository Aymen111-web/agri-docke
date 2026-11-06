#!/bin/sh
set -e

# Ensure Laravel storage and cache directories exist with correct permissions
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create public storage symlink if missing
if [ ! -L /var/www/html/public/storage ]; then
    echo "[Entrypoint] Creating public storage symlink..."
    php artisan storage:link --force || true
fi

# Ensure an application key exists
if [ -z "$APP_KEY" ]; then
    echo "[Entrypoint] WARNING: APP_KEY not provided. Generating a key..."
    export APP_KEY=$(php artisan key:generate --show)
fi

# Wait for database connection if DB_HOST is configured
if [ -n "$DB_HOST" ]; then
    echo "[Entrypoint] Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    php -r '
        $host = getenv("DB_HOST");
        $port = getenv("DB_PORT") ?: "3306";
        $user = getenv("DB_USERNAME") ?: "root";
        $pass = getenv("DB_PASSWORD") ?: "";
        $db   = getenv("DB_DATABASE") ?: "b2b_agri_tech";
        $maxAttempts = 30;
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            try {
                $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 2,
                ]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS \`{$db}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                echo "[Entrypoint] Successfully connected to MySQL and verified database \"{$db}\".\n";
                exit(0);
            } catch (Exception $e) {
                $attempt++;
                echo "[Entrypoint] Attempt {$attempt}/{$maxAttempts}: Database not ready yet (" . $e->getMessage() . "). Retrying in 2s...\n";
                sleep(2);
            }
        }
        echo "[Entrypoint] ERROR: Could not connect to database after {$maxAttempts} attempts.\n";
        exit(1);
    '
fi

# Run database migrations if configured
if [ "$AUTORUN_MIGRATIONS" = "true" ]; then
    echo "[Entrypoint] Running database migrations..."
    php artisan migrate --force
fi

# Run database seeders if configured
if [ "$AUTORUN_SEEDERS" = "true" ]; then
    echo "[Entrypoint] Running database seeders..."
    php artisan db:seed --force
fi

# Cache config, routes, and views if running in production
if [ "$APP_ENV" = "production" ]; then
    echo "[Entrypoint] Optimizing Laravel for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Execute the specified container command (defaults to php-fpm)
exec "$@"

