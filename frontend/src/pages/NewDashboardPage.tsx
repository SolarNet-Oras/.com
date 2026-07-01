import React, { useEffect, useState, useCallback } from 'react';
import { DashboardLayout } from '@/components/layout/DashboardLayout';
import { MetricCard } from '@/components/ui/MetricCard';
import { useAuth } from '@/hooks/useAuth';
import api from '@/services/api';
import { logger } from '@/lib/logger';

interface DashboardMetrics {
  active_subscribers: number;
  expired_subscribers: number;
  suspended_subscribers: number;
  total_subscribers: number;
  online_users: number;
  offline_users: number;
  today_revenue: number;
  monthly_revenue: number;
  pending_payments: number;
  overdue_invoices: number;
  open_tickets: number;
  pending_tickets: number;
  resolved_today: number;
  router_status: {
    online: number;
    offline: number;
    error: number;
    total: number;
  };
  total_users: number;
  active_users: number;
  users_online: number;
}

const NewDashboardPage: React.FC = () => {
  const { user } = useAuth();
  const [metrics, setMetrics] = useState<DashboardMetrics | null>(null);
  const [loading, setLoading] = useState<boolean>(true);

  const fetchMetrics = useCallback(async (): Promise<void> => {
    try {
      const response = await api.get<{ data: DashboardMetrics }>('/dashboard/metrics');
      setMetrics(response.data.data);
    } catch (error) {
      logger.error('Failed to fetch metrics', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchMetrics();
    // Refresh metrics every 30 seconds
    const interval = setInterval(fetchMetrics, 30000);
    return () => clearInterval(interval);
  }, [fetchMetrics]);

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Welcome Section */}
        <div>
          <h1 className="text-3xl font-bold text-foreground mb-2">
            Welcome back, {user?.name}! 👋
          </h1>
          <p className="text-muted-foreground">
            Here's what's happening with your ISP network today.
          </p>
        </div>

        {/* Quick Stats */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <MetricCard
            title="Total Subscribers"
            value={metrics?.total_subscribers || 0}
            change="+12% from last month"
            trend="up"
            icon="👥"
            loading={loading}
          />
          <MetricCard
            title="Active Subscribers"
            value={metrics?.active_subscribers || 0}
            change="Stable"
            trend="stable"
            icon="✅"
            loading={loading}
          />
          <MetricCard
            title="Monthly Revenue"
            value={`₱${metrics?.monthly_revenue.toLocaleString() || '0.00'}`}
            change="+8% from last month"
            trend="up"
            icon="💰"
            loading={loading}
          />
          <MetricCard
            title="Online Users"
            value={metrics?.users_online || 0}
            change={`${metrics?.active_users || 0} active`}
            trend="up"
            icon="🌐"
            loading={loading}
          />
        </div>

        {/* Network Status */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Subscriber Status */}
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h2 className="text-xl font-semibold text-foreground mb-4">
              Subscriber Status
            </h2>
            <div className="space-y-3">
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Active</span>
                <span className="font-semibold text-green-600 dark:text-green-400">
                  {metrics?.active_subscribers || 0}
                </span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Suspended</span>
                <span className="font-semibold text-yellow-600 dark:text-yellow-400">
                  {metrics?.suspended_subscribers || 0}
                </span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Expired</span>
                <span className="font-semibold text-red-600 dark:text-red-400">
                  {metrics?.expired_subscribers || 0}
                </span>
              </div>
              <hr className="border-border" />
              <div className="flex justify-between items-center font-semibold">
                <span className="text-foreground">Total</span>
                <span className="text-foreground">
                  {metrics?.total_subscribers || 0}
                </span>
              </div>
            </div>
          </div>

          {/* Router Status */}
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h2 className="text-xl font-semibold text-foreground mb-4">
              Router Status
            </h2>
            <div className="space-y-3">
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Online</span>
                <span className="font-semibold text-green-600 dark:text-green-400">
                  {metrics?.router_status.online || 0}
                </span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Offline</span>
                <span className="font-semibold text-red-600 dark:text-red-400">
                  {metrics?.router_status.offline || 0}
                </span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Error</span>
                <span className="font-semibold text-yellow-600 dark:text-yellow-400">
                  {metrics?.router_status.error || 0}
                </span>
              </div>
              <hr className="border-border" />
              <div className="flex justify-between items-center font-semibold">
                <span className="text-foreground">Total</span>
                <span className="text-foreground">
                  {metrics?.router_status.total || 0}
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Financial & Support */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <MetricCard
            title="Today's Revenue"
            value={`₱${metrics?.today_revenue.toLocaleString() || '0.00'}`}
            icon="💵"
            loading={loading}
          />
          <MetricCard
            title="Pending Payments"
            value={metrics?.pending_payments || 0}
            icon="⏳"
            loading={loading}
          />
          <MetricCard
            title="Open Tickets"
            value={metrics?.open_tickets || 0}
            change={`${metrics?.resolved_today || 0} resolved today`}
            trend="down"
            icon="🎫"
            loading={loading}
          />
        </div>

        {/* System Info */}
        <div className="bg-secondary border border-border rounded-lg p-6">
          <h2 className="text-lg font-semibold text-foreground mb-4">
            ✅ Phase 3: Dashboard & Core UI - Complete!
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-muted-foreground">
            <div>
              <h3 className="font-semibold text-foreground mb-2">Features Implemented:</h3>
              <ul className="space-y-1">
                <li>✅ Dashboard layout with sidebar</li>
                <li>✅ Real-time metrics display</li>
                <li>✅ Responsive design</li>
                <li>✅ Dark/Light theme toggle</li>
              </ul>
            </div>
            <div>
              <h3 className="font-semibold text-foreground mb-2">Your Access:</h3>
              <ul className="space-y-1">
                <li>👤 User: {user?.name}</li>
                <li>📧 Email: {user?.email}</li>
                <li>🎭 Role: {user?.roles?.[0]?.display_name}</li>
                <li>🔐 Permissions: {user?.permissions?.length || 0}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
};

export default NewDashboardPage;
