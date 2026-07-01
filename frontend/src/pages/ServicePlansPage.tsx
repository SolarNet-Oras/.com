import { DashboardLayout } from '@/components/layout/DashboardLayout';
import { ServicePlansList } from '@/components/service-plans/ServicePlansList';

export function ServicePlansPage() {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Service Plans</h1>
          <p className="text-muted-foreground mt-2">
            Manage bandwidth plans and pricing for your customers
          </p>
        </div>

        <ServicePlansList />
      </div>
    </DashboardLayout>
  );
}
