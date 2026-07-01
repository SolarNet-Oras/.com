import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '@/hooks/useAuth';

const DashboardPage: React.FC = () => {
  const navigate = useNavigate();
  const { user, logout } = useAuth();

  const handleLogout = async (): Promise<void> => {
    await logout();
    navigate('/login');
  };

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="bg-card border-b border-border">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div>
              <h1 className="text-xl font-bold text-foreground">
                ISP Billing System
              </h1>
            </div>
            <div className="flex items-center gap-4">
              <span className="text-sm text-muted-foreground">
                {user.email}
              </span>
              <button
                onClick={handleLogout}
                className="px-4 py-2 text-sm bg-secondary text-secondary-foreground rounded-md hover:opacity-90 transition-opacity"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="space-y-6">
          {/* Welcome Section */}
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h2 className="text-2xl font-bold text-foreground mb-2">
              Welcome, {user.name}! 👋
            </h2>
            <p className="text-muted-foreground">
              You're now logged into the ISP Billing & Network Management System
            </p>
          </div>

          {/* User Info */}
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h3 className="text-lg font-semibold text-foreground mb-4">
              Your Account Information
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p className="text-sm font-medium text-muted-foreground">Name</p>
                <p className="text-base text-foreground">{user.name}</p>
              </div>
              <div>
                <p className="text-sm font-medium text-muted-foreground">Email</p>
                <p className="text-base text-foreground">{user.email}</p>
              </div>
              {user.phone && (
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Phone</p>
                  <p className="text-base text-foreground">{user.phone}</p>
                </div>
              )}
              <div>
                <p className="text-sm font-medium text-muted-foreground">Status</p>
                <p className="text-base text-foreground">
                  <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Active
                  </span>
                </p>
              </div>
            </div>
          </div>

          {/* Roles */}
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h3 className="text-lg font-semibold text-foreground mb-4">
              Assigned Roles
            </h3>
            <div className="flex flex-wrap gap-2">
              {user.roles && user.roles.length > 0 ? (
                user.roles.map((role) => (
                  <span
                    key={role.id}
                    className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary border border-primary/20"
                  >
                    {role.display_name}
                  </span>
                ))
              ) : (
                <p className="text-muted-foreground">No roles assigned</p>
              )}
            </div>
          </div>

          {/* Permissions */}
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h3 className="text-lg font-semibold text-foreground mb-4">
              Your Permissions
            </h3>
            <div className="flex flex-wrap gap-2">
              {user.permissions && user.permissions.length > 0 ? (
                user.permissions.slice(0, 10).map((permission) => (
                  <span
                    key={permission}
                    className="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-secondary text-secondary-foreground"
                  >
                    {permission}
                  </span>
                ))
              ) : (
                <p className="text-muted-foreground">No permissions assigned</p>
              )}
              {user.permissions && user.permissions.length > 10 && (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-secondary text-secondary-foreground">
                  +{user.permissions.length - 10} more
                </span>
              )}
            </div>
          </div>

          {/* Phase Info */}
          <div className="bg-secondary border border-border rounded-lg p-6">
            <h3 className="text-lg font-semibold text-foreground mb-2">
              ✅ Phase 2 Complete!
            </h3>
            <p className="text-sm text-muted-foreground mb-4">
              Authentication & RBAC system is now fully operational.
            </p>
            <ul className="text-sm text-muted-foreground space-y-1">
              <li>✅ JWT Authentication</li>
              <li>✅ Role-Based Access Control</li>
              <li>✅ 8 Roles with 54 Permissions</li>
              <li>✅ Secure Token Storage</li>
              <li>✅ User Management APIs</li>
            </ul>
          </div>
        </div>
      </main>
    </div>
  );
};

export default DashboardPage;
