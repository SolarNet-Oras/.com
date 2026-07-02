#!/bin/bash
# Supervisor wrapper: waits until PHP 8.4 is installed, then serves the Laravel app on port 8001.
while [ ! -x /usr/bin/php8.4 ] || ! /usr/bin/php8.4 -m 2>/dev/null | grep -q pdo_pgsql; do
    sleep 2
done
cd /app/backend
exec /usr/bin/php8.4 -S 0.0.0.0:8001 -t /app/backend/public
