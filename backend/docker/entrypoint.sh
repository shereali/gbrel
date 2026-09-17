#!/bin/sh
set -e

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache resources/views 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache resources/views 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    until php -r "
        \$pdo = new PDO(
            'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
            getenv('DB_USERNAME') ?: 'root',
            getenv('DB_PASSWORD') ?: ''
        );
        \$pdo->query('SELECT 1');
    " 2>/dev/null; do
        sleep 2
    done
    echo "MySQL is up."
fi

php artisan migrate --force || exit 1
php artisan db:seed --force || true
php artisan storage:link || true
mkdir -p storage/app/public/properties && chmod -R 775 storage/app/public 2>/dev/null || true

php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true

exec "$@"
