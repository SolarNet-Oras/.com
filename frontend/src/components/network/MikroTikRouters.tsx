import { useState, useEffect } from 'react';
import { routerService, type Router, type CreateRouterData } from '@/services/routerService';
import { RouterList } from './RouterList';
import { RouterFormModal } from './RouterFormModal';
import { Plus } from 'lucide-react';

export function MikroTikRouters() {
  const [routers, setRouters] = useState<Router[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingRouter, setEditingRouter] = useState<Router | null>(null);

  const loadRouters = async () => {
    try {
      setLoading(true);
      const data = await routerService.getAll();
      setRouters(data);
    } catch (error) {
      console.error('Failed to load routers:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadRouters();
  }, []);

  const handleAdd = () => {
    setEditingRouter(null);
    setIsModalOpen(true);
  };

  const handleEdit = (router: Router) => {
    setEditingRouter(router);
    setIsModalOpen(true);
  };

  const handleDelete = async (id: string) => {
    if (!confirm('Are you sure you want to delete this router?')) {
      return;
    }

    try {
      await routerService.delete(id);
      await loadRouters();
    } catch (error) {
      console.error('Failed to delete router:', error);
      alert('Failed to delete router');
    }
  };

  const handleSave = async (data: CreateRouterData) => {
    try {
      if (editingRouter) {
        await routerService.update(editingRouter.id, data);
      } else {
        await routerService.create(data);
      }
      setIsModalOpen(false);
      await loadRouters();
    } catch (error) {
      console.error('Failed to save router:', error);
      throw error;
    }
  };

  const handleTestConnection = async (id: string) => {
    await loadRouters(); // Refresh to get updated status
  };

  const handleSync = async (id: string) => {
    await loadRouters(); // Refresh after sync
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center p-12">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold text-foreground">MikroTik Routers</h2>
          <p className="text-sm text-muted-foreground mt-1">
            {routers.length} {routers.length === 1 ? 'router' : 'routers'} configured
          </p>
        </div>
        <button
          onClick={handleAdd}
          className="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity"
        >
          <Plus className="h-4 w-4 mr-2" />
          Add Router
        </button>
      </div>

      <RouterList
        routers={routers}
        onEdit={handleEdit}
        onDelete={handleDelete}
        onTestConnection={handleTestConnection}
        onSync={handleSync}
      />

      <RouterFormModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSave={handleSave}
        router={editingRouter}
      />
    </div>
  );
}
