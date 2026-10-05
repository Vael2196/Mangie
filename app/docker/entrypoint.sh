#!/bin/sh

set -eu

cd /var/www/html

if [ "${APP_ENV:-}" = "production" ] \
    && { [ -z "${APP_KEY:-}" ] \
        || [ "${APP_KEY}" = "CHANGE_ME" ]; }; then
    echo "APP_KEY must be configured before Mangie can start." >&2
    exit 1
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data storage bootstrap/cache

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    attempt=1
    max_attempts="${DB_WAIT_MAX_ATTEMPTS:-60}"

    echo "Waiting for MySQL at ${DB_HOST:-shared-mysql}:${DB_PORT:-3306}..."

    until php -r '
        try {
            new PDO(
                sprintf(
                    "mysql:host=%s;port=%s;dbname=%s",
                    getenv("DB_HOST"),
                    getenv("DB_PORT") ?: "3306",
                    getenv("DB_DATABASE")
                ),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD"),
                [PDO::ATTR_TIMEOUT => 2]
            );
        } catch (Throwable $exception) {
            exit(1);
        }
    '; do
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "MySQL did not become ready after ${max_attempts} attempts." >&2
            exit 1
        fi

        attempt=$((attempt + 1))
        sleep 2
    done
fi

php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${CONTAINER_ROLE:-}" = "web" ] \
    && [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
