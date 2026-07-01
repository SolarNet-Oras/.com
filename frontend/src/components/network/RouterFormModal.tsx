import { useState, useEffect } from 'react';
import { type Router, type CreateRouterData } from '@/services/routerService';
import { X } from 'lucide-react';

interface RouterFormModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSave: (data: CreateRouterData) => Promise<void>;
  router?: Router | null;
}

export function RouterFormModal({ isOpen, onClose, onSave, router }: RouterFormModalProps) {
  const [formData, setFormData] = useState<CreateRouterData>({
    name: '',
    host: '',
    port: 8728,
    username: 'admin',
    password: '',
    location: '',
    notes: '',
    dhcp_pool_name: '',
    is_active: true,
  });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (router) {
      setFormData({
        name: router.name,
        host: router.host,
        port: router.port,
        username: router.username,
        password: '', // Don't populate password for security
        location: router.location || '',
        notes: router.notes || '',
        dhcp_pool_name: router.dhcp_pool_name || '',
        is_active: router.is_active,
      });
    } else {
      setFormData({
        name: '',
        host: '',
        port: 8728,
        username: 'admin',
        password: '',
        location: '',
        notes: '',
        dhcp_pool_name: '',
        is_active: true,
      });
    }
    setError(null);
  }, [router, isOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSaving(true);

    try {
      // If editing and password is empty, remove it from the update
      const dataToSave = { ...formData };
      if (router && !dataToSave.password) {
        delete dataToSave.password;
      }

      await onSave(dataToSave);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to save router');
    } finally {
      setSaving(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="bg-card rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between p-6 border-b border-border">
          <h2 className="text-xl font-bold text-foreground">
            {router ? 'Edit Router' : 'Add New Router'}
          </h2>
          <button
            onClick={onClose}
            className="p-2 hover:bg-secondary rounded transition-colors"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {error && (
            <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
              <p className="text-sm text-red-800 dark:text-red-200">{error}</p>
            </div>
          )}

          <div className="grid grid-cols-2 gap-4">
            <div className="col-span-2">
              <label className="block text-sm font-medium text-foreground mb-1">
                Router Name <span className="text-red-600">*</span>
              </label>
              <input
                type="text"
                required
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Main Router"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-foreground mb-1">
                Host / IP Address <span className="text-red-600">*</span>
              </label>
              <input
                type="text"
                required
                value={formData.host}
                onChange={(e) => setFormData({ ...formData, host: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="192.168.1.1"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-foreground mb-1">
                API Port <span className="text-red-600">*</span>
              </label>
              <input
                type="number"
                required
                value={formData.port}
                onChange={(e) => setFormData({ ...formData, port: parseInt(e.target.value) })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="8728"
              />
              <p className="text-xs text-muted-foreground mt-1">Default: 8728 (plain) or 8729 (SSL)</p>
            </div>

            <div>
              <label className="block text-sm font-medium text-foreground mb-1">
                Username <span className="text-red-600">*</span>
              </label>
              <input
                type="text"
                required
                value={formData.username}
                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="admin"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-foreground mb-1">
                Password {!router && <span className="text-red-600">*</span>}
              </label>
              <input
                type="password"
                required={!router}
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder={router ? 'Leave blank to keep current' : 'Enter password'}
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-foreground mb-1">
                Location
              </label>
              <input
                type="text"
                value={formData.location}
                onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Building A, Floor 2"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-foreground mb-1">
                DHCP Pool Name
              </label>
              <input
                type="text"
                value={formData.dhcp_pool_name}
                onChange={(e) => setFormData({ ...formData, dhcp_pool_name: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="dhcp-pool-1"
              />
            </div>

            <div className="col-span-2">
              <label className="block text-sm font-medium text-foreground mb-1">
                Notes
              </label>
              <textarea
                value={formData.notes}
                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                rows={3}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Additional notes about this router..."
              />
            </div>

            <div className="col-span-2">
              <label className="flex items-center space-x-2">
                <input
                  type="checkbox"
                  checked={formData.is_active}
                  onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                  className="rounded border-input text-primary focus:ring-primary"
                />
                <span className="text-sm font-medium text-foreground">Router is active</span>
              </label>
            </div>
          </div>

          <div className="flex justify-end space-x-3 pt-4 border-t border-border">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 border border-border rounded-md text-foreground hover:bg-secondary transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={saving}
              className="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity disabled:opacity-50"
            >
              {saving ? 'Saving...' : router ? 'Update Router' : 'Add Router'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
