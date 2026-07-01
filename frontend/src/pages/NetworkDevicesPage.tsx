import { useState } from 'react';
import { DashboardLayout } from '@/components/layout/DashboardLayout';
import { MikroTikRouters } from '@/components/network/MikroTikRouters';

export function NetworkDevicesPage() {
  const [activeTab, setActiveTab] = useState<'mikrotik' | 'olt'>('mikrotik');

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Network Devices</h1>
          <p className="text-muted-foreground mt-2">
            Manage MikroTik routers and OLT devices
          </p>
        </div>

        {/* Tabs */}
        <div className="border-b border-border">
          <div className="flex space-x-8">
            <button
              onClick={() => setActiveTab('mikrotik')}
              className={`pb-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                activeTab === 'mikrotik'
                  ? 'border-primary text-primary'
                  : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
              }`}
            >
              MikroTik Routers
            </button>
            <button
              onClick={() => setActiveTab('olt')}
              className={`pb-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                activeTab === 'olt'
                  ? 'border-primary text-primary'
                  : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
              }`}
              disabled
              title="OLT management will be available in Phase 13"
            >
              OLT Devices
              <span className="ml-2 text-xs bg-muted px-2 py-0.5 rounded">Coming Soon</span>
            </button>
          </div>
        </div>

        {/* Tab Content */}
        <div className="mt-6">
          {activeTab === 'mikrotik' && <MikroTikRouters />}
          {activeTab === 'olt' && (
            <div className="bg-card p-8 rounded-lg border border-border text-center">
              <h3 className="text-lg font-semibold text-foreground mb-2">
                OLT Management Coming Soon
              </h3>
              <p className="text-muted-foreground">
                OLT device management will be available in Phase 13.
                <br />
                Focus on MikroTik integration first.
              </p>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}
