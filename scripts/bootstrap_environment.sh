#!/bin/bash
# Idempotent environment bootstrap for ISP Billing System (Laravel + PostgreSQL + Redis)
# Runs at container boot via supervisor [program:bootstrap]. Safe to re-run.
LOG=/var/log/bootstrap_env.log
exec >> "$LOG" 2>&1
echo "=== BOOTSTRAP START $(date) ==="
export DEBIAN_FRONTEND=noninteractive

# 1. PHP 8.4
if ! command -v php8.4 >/dev/null 2>&1; then
    echo "[bootstrap] Installing PHP 8.4..."
    curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb
    dpkg -i /tmp/debsuryorg-archive-keyring.deb
    echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ bookworm main" > /etc/apt/sources.list.d/php.list
    apt-get update -qq
    apt-get install -y -qq php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl php8.4-bcmath php8.4-gd php8.4-zip php8.4-intl php8.4-redis
fi

# 2. PostgreSQL 15
if [ ! -x /usr/lib/postgresql/15/bin/postgres ]; then
    echo "[bootstrap] Installing PostgreSQL..."
    apt-get update -qq 2>/dev/null
    apt-get install -y -qq postgresql-15 2>/dev/null || apt-get install -y -qq postgresql
fi
# Ensure Debian's default cluster is not holding port 5432 (we use /app/data/postgres)
pg_ctlcluster 15 main stop 2>/dev/null || true

# 3. Redis
if ! command -v redis-server >/dev/null 2>&1; then
    echo "[bootstrap] Installing Redis..."
    apt-get install -y -qq redis-server
    service redis-server stop 2>/dev/null || true
fi

# 4. Persistent data directories inside /app (survive container restarts)
mkdir -p /app/data/redis
chmod 777 /app/data/redis
mkdir -p /app/data/postgres
chown -R postgres:postgres /app/data/postgres
chmod 700 /app/data/postgres

# 5. Initialize PostgreSQL data dir if empty
if [ ! -f /app/data/postgres/PG_VERSION ]; then
    echo "[bootstrap] Initializing PostgreSQL data dir at /app/data/postgres..."
    su postgres -c "/usr/lib/postgresql/15/bin/initdb -D /app/data/postgres -A trust -E UTF8"
fi

# 6. Wait for postgres to accept connections (supervisor run_postgres.sh starts it)
echo "[bootstrap] Waiting for PostgreSQL..."
for i in $(seq 1 90); do
    if su postgres -c "/usr/lib/postgresql/15/bin/pg_isready -h 127.0.0.1 -p 5432" >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

# 7. Create role + database (idempotent)
su postgres -c "psql -h 127.0.0.1 -tAc \"SELECT 1 FROM pg_roles WHERE rolname='isp_user'\"" | grep -q 1 || \
    su postgres -c "psql -h 127.0.0.1 -c \"CREATE USER isp_user WITH PASSWORD 'isp_secure_password'\""
su postgres -c "psql -h 127.0.0.1 -tAc \"SELECT 1 FROM pg_database WHERE datname='isp_billing'\"" | grep -q 1 || \
    su postgres -c "psql -h 127.0.0.1 -c \"CREATE DATABASE isp_billing OWNER isp_user\""

# 8. Migrations (idempotent) + seed only on a fresh database
cd /app/backend
/usr/bin/php8.4 artisan migrate --force
USER_COUNT=$(su postgres -c "psql -h 127.0.0.1 -d isp_billing -tAc 'SELECT COUNT(*) FROM users'" 2>/dev/null | tr -d '[:space:]')
if [ -z "$USER_COUNT" ] || [ "$USER_COUNT" = "0" ]; then
    echo "[bootstrap] Seeding fresh database..."
    /usr/bin/php8.4 artisan db:seed --force
fi

echo "=== BOOTSTRAP COMPLETE $(date) ==="
