#!/usr/bin/env bash
# One-shot deploy / update script for Solarnet ISP Billing
# Run from /app/deploy on the production VPS

set -euo pipefail

cd "$(dirname "$0")"

if [ ! -f .env ]; then
  echo "ERROR: .env missing. Copy .env.production.example to .env and edit it first."
  exit 1
fi

COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env"

echo "==> Pulling latest images"
$COMPOSE pull postgres redis caddy || true

echo "==> Building app images"
$COMPOSE build --pull

echo "==> Starting infrastructure (postgres, redis)"
$COMPOSE up -d postgres redis

echo "==> Waiting for postgres to be healthy"
until $COMPOSE ps postgres | grep -q "healthy"; do sleep 2; done

echo "==> Running database migrations"
$COMPOSE run --rm backend php artisan migrate --force

echo "==> Seeding (idempotent - safe to re-run)"
$COMPOSE run --rm backend php artisan db:seed --force || true

echo "==> Optimising Laravel (cache config/routes/views)"
$COMPOSE run --rm backend php artisan config:cache
$COMPOSE run --rm backend php artisan route:cache
$COMPOSE run --rm backend php artisan view:cache
$COMPOSE run --rm backend php artisan storage:link || true

echo "==> Starting all services"
$COMPOSE up -d

echo "==> Cleaning up dangling images"
docker image prune -f

echo ""
echo "Deploy complete. Live at: https://$(grep '^DOMAIN=' .env | cut -d= -f2)"
echo "Tail logs:  docker compose -f docker-compose.prod.yml logs -f"
