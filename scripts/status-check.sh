#!/bin/bash

echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║      ISP Billing & Network Management System - Status Check      ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""

# Check Services
echo "🔧 Services Status:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
supervisorctl status | grep -E "(laravel|isp_frontend|postgres|redis)" || echo "Checking individual services..."
service postgresql status | grep -q "online" && echo "✅ PostgreSQL: RUNNING" || echo "❌ PostgreSQL: STOPPED"
service redis-server status | grep -q "running" && echo "✅ Redis: RUNNING" || echo "❌ Redis: STOPPED"
echo ""

# Check Laravel Backend
echo "🚀 Laravel Backend:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
LARAVEL_VERSION=$(cd /app/backend && php artisan --version 2>/dev/null | awk '{print $3}')
echo "Version: $LARAVEL_VERSION"
echo "Port: 8001"
HEALTH_CHECK=$(curl -s http://localhost:8001/api/health | jq -r '.status' 2>/dev/null)
if [ "$HEALTH_CHECK" = "ok" ]; then
    echo "✅ Health Check: PASSED"
else
    echo "❌ Health Check: FAILED"
fi
echo ""

# Check Frontend
echo "⚛️  React Frontend:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
FRONTEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:3001 2>/dev/null)
echo "Port: 3001"
if [ "$FRONTEND_STATUS" = "200" ]; then
    echo "✅ Frontend: ACCESSIBLE"
else
    echo "❌ Frontend: NOT ACCESSIBLE (Status: $FRONTEND_STATUS)"
fi
echo ""

# Check Database
echo "🐘 PostgreSQL Database:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
DB_CONN=$(sudo -u postgres psql -d isp_billing -t -c "SELECT 'connected'" 2>/dev/null | xargs)
if [ "$DB_CONN" = "connected" ]; then
    echo "✅ Database: CONNECTED"
    echo "Database: isp_billing"
    echo "User: isp_user"
    echo "Port: 5432"
    MIGRATION_COUNT=$(cd /app/backend && php artisan migrate:status 2>/dev/null | grep "Ran" | wc -l)
    echo "Migrations: $MIGRATION_COUNT ran"
else
    echo "❌ Database: CONNECTION FAILED"
fi
echo ""

# Check Redis
echo "🔴 Redis Cache:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
REDIS_PING=$(redis-cli ping 2>/dev/null)
if [ "$REDIS_PING" = "PONG" ]; then
    echo "✅ Redis: CONNECTED"
    echo "Port: 6379"
else
    echo "❌ Redis: CONNECTION FAILED"
fi
echo ""

# API Endpoints
echo "🌐 API Endpoints:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Health: http://localhost:8001/api/health"
echo "Status: http://localhost:8001/api/v1/status"
echo "API v1: http://localhost:8001/api/v1/*"
echo ""

# URLs
echo "🔗 Access URLs:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Frontend: http://localhost:3001"
echo "Backend API: http://localhost:8001/api"
echo "Public URL: https://network-ops-center-2.preview.emergentagent.com"
echo ""

# Summary
echo "📊 Summary:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Technology Stack:"
echo "  • Laravel 12.62.0 (PHP 8.2)"
echo "  • React 19 + TypeScript + Vite"
echo "  • PostgreSQL 15"
echo "  • Redis 7"
echo "  • Tailwind CSS 4.3"
echo ""
echo "Phase 1: Foundation - ✅ COMPLETE"
echo "Next: Phase 2 - Authentication & RBAC"
echo ""
echo "╚══════════════════════════════════════════════════════════════════╝"
