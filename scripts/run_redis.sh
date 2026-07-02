#!/bin/bash
# Supervisor wrapper: waits until redis is installed, then runs it with persistent data in /app/data/redis.
while ! command -v redis-server >/dev/null 2>&1; do
    sleep 2
done
mkdir -p /app/data/redis
exec redis-server --bind 127.0.0.1 --port 6379 --dir /app/data/redis --appendonly no --save ""
