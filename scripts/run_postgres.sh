#!/bin/bash
# Supervisor wrapper: waits until bootstrap has installed postgres + initialized data dir, then runs it.
while [ ! -x /usr/lib/postgresql/15/bin/postgres ] || ! id postgres >/dev/null 2>&1 || [ ! -f /app/data/postgres/PG_VERSION ]; do
    sleep 2
done
exec setpriv --reuid postgres --regid postgres --init-groups -- \
    /usr/lib/postgresql/15/bin/postgres -D /app/data/postgres -c listen_addresses='127.0.0.1' -c port=5432
