import api from './api';
import type { Payment, PaymentStatistics, PaginatedResponse } from '../types/api';

export const paymentService = {
  /**
   * Get all payments with optional filters
   */
  getPayments: async (params?: {
    customer_id?: string;
    invoice_id?: string;
    payment_method?: string;
    from_date?: string;
    to_date?: string;
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Payment>> => {
    const response = await api.get('/payments', { params });
    return response.data;
  },

  /**
   * Get a single payment by ID
   */
  getPayment: async (id: string): Promise<Payment> => {
    const response = await api.get(`/payments/${id}`);
    return response.data;
  },

  /**
   * Get payment statistics
   */
  getStatistics: async (params?: {
    from_date?: string;
    to_date?: string;
  }): Promise<PaymentStatistics> => {
    const response = await api.get('/payments-statistics', { params });
    return response.data;
  },
};

export default paymentService;
