#!/bin/sh
set -e

DEPLOY_DIR="${DEPLOY_DIR:-/var/www/gbrel}"
if [ ! -d "$DEPLOY_DIR" ] && [ -d "/var/www/gangchil" ]; then
    DEPLOY_DIR="/var/www/gangchil"
fi

LOG_DIR="${LOG_DIR:-/var/log/gbrel}"
if [ "$DEPLOY_DIR" = "/var/www/gangchil" ] && [ ! -d "/var/log/gbrel" ]; then
    LOG_DIR="/var/log/gangchil"
fi
LOG_FILE="$LOG_DIR/deploy.log"
SENTINEL="$LOG_DIR/deploy.done"
STATUS_FILE="$LOG_DIR/deploy.status"

mkdir -p "$LOG_DIR"

echo "=== $(date -u '+%Y-%m-%d %H:%M:%S UTC') deploy start ===" >> "$LOG_FILE"

cd "$DEPLOY_DIR"

# Pull latest commits
git fetch origin main >> "$LOG_FILE" 2>&1 || true
git reset --hard origin/main >> "$LOG_FILE" 2>&1 || true

if docker compose up -d --build >> "$LOG_FILE" 2>&1; then
    STATUS=$(docker compose ps --format '{{.Service}}={{.Status}}' | tr '\n' ' ')
    echo "deploy finished — $STATUS" >> "$LOG_FILE"
    echo "OK: $STATUS" > "$STATUS_FILE"
else
    echo "deploy FAILED" >> "$LOG_FILE"
    echo "FAILED: see $LOG_FILE" > "$STATUS_FILE"
    exit 1
fi

echo "=== deploy done ===" >> "$LOG_FILE"
echo "done" > "$SENTINEL"
exit 0
