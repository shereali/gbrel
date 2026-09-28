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
docker compose up -d --build --remove-orphans

echo "=== Waiting for Database Service ==="
sleep 3
docker compose ps -a
docker compose logs --tail=30 db

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
fi

# Reload Caddy using the correct container or host service
if docker ps --format '{{.Names}}' | grep -q "^caddy-caddy-1$"; then
    echo "Reloading Caddy gateway (caddy-caddy-1)..."
    docker exec caddy-caddy-1 caddy reload --config /etc/caddy/Caddyfile 2>&1 || docker restart caddy-caddy-1 2>&1 || true
elif docker ps --format '{{.Names}}' | grep -q "^caddy$"; then
    echo "Reloading Caddy gateway (caddy)..."
    docker exec caddy caddy reload --config /etc/caddy/Caddyfile 2>&1 || docker restart caddy 2>&1 || true
elif command -v caddy >/dev/null 2>&1; then
    echo "Reloading Caddy on host..."
    caddy reload 2>/dev/null || systemctl reload caddy 2>/dev/null || true
fi

echo "=== Verifying Backend Health ==="
docker compose exec -T backend curl -sS -i http://127.0.0.1:8000/up || echo "Warning: Internal backend /up healthcheck failed"
docker compose exec -T backend curl -sS -i http://127.0.0.1:8000/api/health || echo "Warning: Internal /api/health check failed"

echo "=== Deploy Finished Successfully ==="
