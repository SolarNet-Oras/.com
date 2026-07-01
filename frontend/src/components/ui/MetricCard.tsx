import React from 'react';

interface MetricCardProps {
  title: string;
  value: string | number;
  change?: string;
  trend?: 'up' | 'down' | 'stable';
  icon?: string;
  loading?: boolean;
}

export const MetricCard: React.FC<MetricCardProps> = ({
  title,
  value,
  change,
  trend = 'stable',
  icon,
  loading = false,
}) => {
  const getTrendColor = (): string => {
    switch (trend) {
      case 'up':
        return 'text-green-600 dark:text-green-400';
      case 'down':
        return 'text-red-600 dark:text-red-400';
      default:
        return 'text-muted-foreground';
    }
  };

  const getTrendIcon = (): string => {
    switch (trend) {
      case 'up':
        return '↑';
      case 'down':
        return '↓';
      default:
        return '→';
    }
  };

  if (loading) {
    return (
      <div className="bg-card border border-border rounded-lg p-6 shadow-sm animate-pulse">
        <div className="h-4 bg-secondary rounded w-1/2 mb-4"></div>
        <div className="h-8 bg-secondary rounded w-3/4 mb-2"></div>
        <div className="h-3 bg-secondary rounded w-1/4"></div>
      </div>
    );
  }

  return (
    <div className="bg-card border border-border rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <p className="text-sm font-medium text-muted-foreground mb-2">{title}</p>
          <h3 className="text-3xl font-bold text-foreground mb-2">{value}</h3>
          {change && (
            <p className={`text-sm font-medium ${getTrendColor()}`}>
              <span className="mr-1">{getTrendIcon()}</span>
              {change}
            </p>
          )}
        </div>
        {icon && (
          <div className="text-4xl opacity-50">{icon}</div>
        )}
      </div>
    </div>
  );
};
