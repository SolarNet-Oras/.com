import { useState, useEffect } from 'react';
import { servicePlanService, type ServicePlan, type CreateServicePlanData } from '@/services/servicePlanService';
import { ServicePlansTable } from './ServicePlansTable';
import { ServicePlanFormModal } from './ServicePlanFormModal';
import { Plus } from 'lucide-react';

export function ServicePlansList() {
  const [plans, setPlans] = useState<ServicePlan[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingPlan, setEditingPlan] = useState<ServicePlan | null>(null);

  const loadPlans = async () => {
    try {
      setLoading(true);
      const data = await servicePlanService.getAll();
      setPlans(data);
    } catch (error) {
      console.error('Failed to load service plans:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadPlans();
  }, []);

  const handleAdd = () => {
    setEditingPlan(null);
    setIsModalOpen(true);
  };

  const handleEdit = (plan: ServicePlan) => {
    setEditingPlan(plan);
    setIsModalOpen(true);
  };

  const handleDelete = async (id: string, name: string) => {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) {
      return;
    }

    try {
      await servicePlanService.delete(id);
      await loadPlans();
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to delete service plan';
      alert(message);
    }
  };

  const handleSave = async (data: CreateServicePlanData) => {
    try {
      if (editingPlan) {
        await servicePlanService.update(editingPlan.id, data);
      } else {
        await servicePlanService.create(data);
      }
      setIsModalOpen(false);
      await loadPlans();
    } catch (error) {
      console.error('Failed to save service plan:', error);
      throw error;
    }
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
          <h2 className="text-2xl font-bold text-foreground">Available Plans</h2>
          <p className="text-sm text-muted-foreground mt-1">
            {plans.length} {plans.length === 1 ? 'plan' : 'plans'} configured
          </p>
        </div>
        <button
          onClick={handleAdd}
          className="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity"
        >
          <Plus className="h-4 w-4 mr-2" />
          Add Plan
        </button>
      </div>

      <ServicePlansTable
        plans={plans}
        onEdit={handleEdit}
        onDelete={handleDelete}
      />

      <ServicePlanFormModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSave={handleSave}
        plan={editingPlan}
      />
    </div>
  );
}
