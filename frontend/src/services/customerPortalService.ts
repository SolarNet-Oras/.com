import api from './api';
import type { Customer, Invoice, Payment, PaginatedResponse } from '../types/api';

export const customerPortalService = {
  /**
   * Customer login
   */
  login: async (email: string, accountNumber: string): Promise<{
    customer: Customer;
    access_token: string;
    token_type: string;
  }> => {
    const response = await api.post('/api/v1/customer-portal/login', {
      email,
      account_number: accountNumber,
    });
    return response.data.data;
  },

  /**
   * Get customer dashboard data
   */
  getDashboard: async (): Promise<{
    customer: Customer;
    stats: {
      total_invoices: number;
      unpaid_invoices: number;
      total_outstanding: number;
      last_payment: {
        amount: number;
        date: string;
        method: string;
      } | null;
    };
  }> => {
    const response = await api.get('/api/v1/customer-portal/dashboard');
    return response.data;
  },

  /**
   * Get customer invoices
   */
  getInvoices: async (params?: {
    status?: string;
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Invoice>> => {
    const response = await api.get('/api/v1/customer-portal/invoices', { params });
    return response.data;
  },

  /**
   * Get single invoice
   */
  getInvoice: async (id: string): Promise<Invoice> => {
    const response = await api.get(`/api/v1/customer-portal/invoices/${id}`);
    return response.data;
  },

  /**
   * Get customer payments
   */
  getPayments: async (params?: {
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Payment>> => {
    const response = await api.get('/api/v1/customer-portal/payments', { params });
    return response.data;
  },

  /**
   * Update customer profile
   */
  updateProfile: async (data: {
    contact_number?: string;
    address?: string;
    gps_coordinates?: { latitude: number; longitude: number };
  }): Promise<{ customer: Customer }> => {
    const response = await api.put('/api/v1/customer-portal/profile', data);
    return response.data;
  },
};

export default customerPortalService;
