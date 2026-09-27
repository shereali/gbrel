#!/bin/sh
set -e

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache resources/views 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache resources/views 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ "$DB_CONNECTION" = "mysql" ]; then
    DB_HOST="${DB_HOST:-gbrel-db-1}"
    DB_PORT="${DB_PORT:-3306}"
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
    until php -r "
        \$pdo = new PDO(
            'mysql:host=' . (getenv('DB_HOST') ?: 'gbrel-db-1') . ';port=' . (getenv('DB_PORT') ?: '3306'),
            getenv('DB_USERNAME') ?: 'root',
            getenv('DB_PASSWORD') ?: ''
        );
        \$pdo->query('SELECT 1');
    " 2>/dev/null; do
        sleep 2
    done
    echo "MySQL is up."

    # Ensure database exists with utf8mb4 collation before migrations run
    php -r "
        try {
            \$pdo = new PDO(
                'mysql:host=' . (getenv('DB_HOST') ?: 'gbrel-db-1') . ';port=' . (getenv('DB_PORT') ?: '3306'),
                getenv('DB_USERNAME') ?: 'root',
                getenv('DB_PASSWORD') ?: ''
            );
            \$db = getenv('DB_DATABASE') ?: 'gbrel';
            \$pdo->exec('CREATE DATABASE IF NOT EXISTS \`' . \$db . '\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (\Throwable \$e) {}
    " 2>/dev/null || true
fi

# Run only new migrations safely. Existing tables and records are never dropped or wiped.
php artisan migrate --force || exit 1

# Seed baseline data safely: insert once if missing, update if existing. User data is never deleted.
php artisan db:seed --force || true
php artisan storage:link || true
mkdir -p storage/app/public/properties && chmod -R 775 storage/app/public 2>/dev/null || true

php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true

exec "$@"
