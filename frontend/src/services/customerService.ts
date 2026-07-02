import api from './api';
import type { Customer, PaginatedResponse } from '../types/api';

export const customerService = {
  /**
   * Get all customers with optional filters
   */
  getCustomers: async (params?: {
    search?: string;
    status?: string;
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Customer>> => {
    const response = await api.get('/customers', { params });
    return response.data;
  },

  /**
   * Get a single customer by ID
   */
  getCustomer: async (id: string): Promise<Customer> => {
    const response = await api.get(`/customers/${id}`);
    return response.data;
  },
};

export default customerService;
