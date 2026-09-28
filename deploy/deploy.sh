#!/bin/sh
set -e

DEPLOY_DIR="${DEPLOY_DIR:-/var/www/gbrel}"
if [ ! -d "$DEPLOY_DIR" ] && [ -d "/var/www/gangchil" ]; then
    DEPLOY_DIR="/var/www/gangchil"
fi

echo "=== Deploying in $DEPLOY_DIR ==="
cd "$DEPLOY_DIR"

echo "=== Pulling latest commits ==="
git fetch origin main
git reset --hard origin/main

echo "=== Building and starting Docker services ==="
docker compose up -d --build

echo "=== Running database migrations ==="
docker compose exec -T backend php artisan migrate --force || echo "Warning: Migration failed, continuing..."

echo "=== Running database seeders ==="
docker compose exec -T backend php artisan db:seed --force || echo "Warning: Seeding failed, continuing..."

echo "=== Clearing caches ==="
docker compose exec -T backend php artisan optimize:clear || true
docker compose exec -T backend php artisan route:clear || true
docker compose exec -T backend php artisan config:clear || true

echo "=== Container Status ==="
docker compose ps

echo "=== Syncing Caddy Configuration ==="
if [ -d "/opt/caddy/conf.d" ]; then
    cp "$DEPLOY_DIR/deploy/gbrel.caddy" /opt/caddy/conf.d/gbrel.caddy 2>/dev/null || true
    docker exec caddy caddy reload --config /etc/caddy/Caddyfile 2>/dev/null || caddy reload 2>/dev/null || true
fi

echo "=== Verifying Backend Health ==="
docker compose exec -T backend curl -sS -i http://127.0.0.1:8000/up || echo "Warning: Internal backend healthcheck failed"

echo "=== Deploy Finished Successfully ==="
