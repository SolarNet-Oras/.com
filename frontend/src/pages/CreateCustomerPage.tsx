import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { DashboardLayout } from '@/components/layout/DashboardLayout';
import api from '@/services/api';

interface CustomerFormData {
  account_number: string;
  full_name: string;
  address: string;
  gps_coordinates?: { latitude: number; longitude: number };
  contact_number: string;
  email?: string;
  installation_date: string;
  service_plan_id?: string;
  router_id?: string;
  monthly_fee: string;
  mac_address?: string;
  ip_address?: string;
  vlan?: string;
  status: 'active' | 'suspended' | 'expired' | 'pending';
  onu_information?: string;
  olt_port?: string;
  technician_id?: string;
  notes?: string;
}

const CreateCustomerPage: React.FC = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string>('');
  const [servicePlans, setServicePlans] = useState<ServicePlan[]>([]);
  
  const [formData, setFormData] = useState<CustomerFormData>({
    account_number: `ACC${Date.now().toString().slice(-8)}`,
    full_name: '',
    address: '',
    contact_number: '',
    installation_date: new Date().toISOString().split('T')[0],
    monthly_fee: '0',
    status: 'pending',
  });

  useEffect(() => {
    loadServicePlans();
  }, []);

  const loadServicePlans = async () => {
    try {
      const plans = await servicePlanService.getAll();
      setServicePlans(plans.filter(p => p.is_active));
    } catch (err) {
      console.error('Failed to load service plans:', err);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>): void => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    setError('');
  };

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await api.post('/customers', formData);
      navigate('/customers');
    } catch (err: any) {
      const message = err.response?.data?.message || 'Failed to create customer';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <DashboardLayout>
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-foreground">Add New Customer</h1>
          <p className="text-muted-foreground mt-1">Create a new ISP subscriber account</p>
        </div>

        {/* Error Alert */}
        {error && (
          <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
            <p className="text-sm text-red-800 dark:text-red-200">{error}</p>
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleSubmit} className="bg-card border border-border rounded-lg p-6 space-y-6">
          {/* Basic Information */}
          <div>
            <h2 className="text-xl font-semibold text-foreground mb-4">Basic Information</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Account Number *
                </label>
                <input
                  type="text"
                  name="account_number"
                  value={formData.account_number}
                  onChange={handleChange}
                  required
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Full Name *
                </label>
                <input
                  type="text"
                  name="full_name"
                  value={formData.full_name}
                  onChange={handleChange}
                  required
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-foreground mb-2">
                  Address *
                </label>
                <textarea
                  name="address"
                  value={formData.address}
                  onChange={handleChange}
                  required
                  rows={3}
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Contact Number *
                </label>
                <input
                  type="tel"
                  name="contact_number"
                  value={formData.contact_number}
                  onChange={handleChange}
                  required
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Email
                </label>
                <input
                  type="email"
                  name="email"
                  value={formData.email || ''}
                  onChange={handleChange}
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>
            </div>
          </div>

          {/* Service Information */}
          <div>
            <h2 className="text-xl font-semibold text-foreground mb-4">Service Information</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Installation Date *
                </label>
                <input
                  type="date"
                  name="installation_date"
                  value={formData.installation_date}
                  onChange={handleChange}
                  required
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Monthly Fee (₱) *
                </label>
                <input
                  type="number"
                  name="monthly_fee"
                  value={formData.monthly_fee}
                  onChange={handleChange}
                  required
                  min="0"
                  step="0.01"
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Service Plan
                </label>
                <select
                  name="service_plan_id"
                  value={formData.service_plan_id || ''}
                  onChange={handleChange}
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="">No Plan Assigned</option>
                  {servicePlans.map((plan) => (
                    <option key={plan.id} value={plan.id}>
                      {plan.name} - {plan.download_speed}/{plan.upload_speed} Mbps - ${plan.price}/mo
                    </option>
                  ))}
                </select>
                <p className="text-xs text-muted-foreground mt-1">
                  Select a bandwidth plan for this customer
                </p>
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Status *
                </label>
                <select
                  name="status"
                  value={formData.status}
                  onChange={handleChange}
                  required
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="pending">Pending</option>
                  <option value="active">Active</option>
                  <option value="suspended">Suspended</option>
                  <option value="expired">Expired</option>
                </select>
              </div>
            </div>
          </div>

          {/* Network Information */}
          <div>
            <h2 className="text-xl font-semibold text-foreground mb-4">Network Information</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  MAC Address
                </label>
                <input
                  type="text"
                  name="mac_address"
                  value={formData.mac_address || ''}
                  onChange={handleChange}
                  placeholder="00:00:00:00:00:00"
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  IP Address
                </label>
                <input
                  type="text"
                  name="ip_address"
                  value={formData.ip_address || ''}
                  onChange={handleChange}
                  placeholder="192.168.1.1"
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  VLAN
                </label>
                <input
                  type="text"
                  name="vlan"
                  value={formData.vlan || ''}
                  onChange={handleChange}
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>
            </div>
          </div>

          {/* ONU/OLT Information */}
          <div>
            <h2 className="text-xl font-semibold text-foreground mb-4">ONU/OLT Information (Fiber)</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  ONU Information
                </label>
                <input
                  type="text"
                  name="onu_information"
                  value={formData.onu_information || ''}
                  onChange={handleChange}
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  OLT Port
                </label>
                <input
                  type="text"
                  name="olt_port"
                  value={formData.olt_port || ''}
                  onChange={handleChange}
                  className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>
            </div>
          </div>

          {/* Additional Notes */}
          <div>
            <label className="block text-sm font-medium text-foreground mb-2">
              Notes
            </label>
            <textarea
              name="notes"
              value={formData.notes || ''}
              onChange={handleChange}
              rows={4}
              className="w-full px-4 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Additional notes about this customer..."
            />
          </div>

          {/* Actions */}
          <div className="flex gap-4 pt-4">
            <button
              type="submit"
              disabled={loading}
              className="px-6 py-2 bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Creating...' : 'Create Customer'}
            </button>
            <button
              type="button"
              onClick={() => navigate('/customers')}
              className="px-6 py-2 bg-secondary text-secondary-foreground rounded-md hover:opacity-90 transition-opacity"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </DashboardLayout>
  );
};

export default CreateCustomerPage;
