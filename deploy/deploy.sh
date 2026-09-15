#!/bin/sh
set -e

DEPLOY_DIR="/var/www/attashil"
LOG_DIR="/var/log/attashil"
LOG_FILE="$LOG_DIR/deploy.log"
SENTINEL="$LOG_DIR/deploy.done"
STATUS_FILE="$LOG_DIR/deploy.status"

mkdir -p "$LOG_DIR"

# Ensure sentinel is ALWAYS written on exit so CI/CD runner never hangs
trap 'echo "done" > "$SENTINEL"' EXIT

echo "=== $(date -u '+%Y-%m-%d %H:%M:%S UTC') deploy start ===" >> "$LOG_FILE"

cd "$DEPLOY_DIR"

# Configure git safe directory to avoid dubious ownership block
git config --global --add safe.directory "$DEPLOY_DIR" || true

# If git credentials exist, attempt pull, otherwise source was synced by CI/CD runner
git fetch origin master >> "$LOG_FILE" 2>&1 || true
git reset --hard origin/master >> "$LOG_FILE" 2>&1 || true

echo "Rebuilding and starting production containers..." >> "$LOG_FILE"
if docker compose -f docker-compose.prod.yml build --no-cache frontend >> "$LOG_FILE" 2>&1 && \
   docker compose -f docker-compose.prod.yml up -d >> "$LOG_FILE" 2>&1; then
    # Clear cached optimization and run migrations
    docker compose -f docker-compose.prod.yml exec -T backend php artisan optimize:clear >> "$LOG_FILE" 2>&1 || true
    docker compose -f docker-compose.prod.yml exec -T backend php artisan migrate --force >> "$LOG_FILE" 2>&1 || true
    STATUS=$(docker compose -f docker-compose.prod.yml ps --format '{{.Service}}={{.Status}}' | tr '\n' ' ')
    echo "deploy finished — $STATUS" >> "$LOG_FILE"
    echo "OK: $STATUS" > "$STATUS_FILE"
else
    echo "deploy FAILED" >> "$LOG_FILE"
    echo "FAILED: see $LOG_FILE" > "$STATUS_FILE"
    exit 1
fi

echo "=== deploy done ===" >> "$LOG_FILE"
exit 0
