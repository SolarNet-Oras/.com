import api from './api';

export const reportService = {
  getRevenueReport: async (startDate?: string, endDate?: string): Promise<any> => {
    const response = await api.get('/api/v1/reports/revenue', {
      params: { start_date: startDate, end_date: endDate },
    });
    return response.data;
  },

  getCustomerGrowth: async (startDate?: string, endDate?: string): Promise<any> => {
    const response = await api.get('/api/v1/reports/customer-growth', {
      params: { start_date: startDate, end_date: endDate },
    });
    return response.data;
  },

  getPaymentMethods: async (startDate?: string, endDate?: string): Promise<any> => {
    const response = await api.get('/api/v1/reports/payment-methods', {
      params: { start_date: startDate, end_date: endDate },
    });
    return response.data;
  },

  getServicePlanPopularity: async (): Promise<any> => {
    const response = await api.get('/api/v1/reports/service-plans');
    return response.data;
  },

  getTicketsOverview: async (startDate?: string, endDate?: string): Promise<any> => {
    const response = await api.get('/api/v1/reports/tickets', {
      params: { start_date: startDate, end_date: endDate },
    });
    return response.data;
  },
};

export default reportService;
