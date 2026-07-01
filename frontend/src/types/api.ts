/**
 * API Type Definitions
 * 
 * Centralized type definitions for API requests and responses
 */

import { AxiosError, AxiosResponse } from 'axios';

// ============================================================================
// Base Types
// ============================================================================

export interface ApiResponse<T = unknown> {
  data: T;
  message?: string;
  status: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

// ============================================================================
// Authentication Types
// ============================================================================

export interface LoginRequest {
  email: string;
  password: string;
  remember?: boolean;
}

export interface LoginResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface RegisterResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

export interface RefreshTokenResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
}

export interface PasswordResetRequest {
  email: string;
}

export interface PasswordResetResponse {
  message: string;
}

// ============================================================================
// User Types
// ============================================================================

export interface User {
  id: string;
  name: string;
  email: string;
  email_verified_at: string | null;
  role: string;
  permissions: string[];
  created_at: string;
  updated_at: string;
}

export interface UpdateUserRequest {
  name?: string;
  email?: string;
  password?: string;
  password_confirmation?: string;
}

// ============================================================================
// Customer Types (ISP Subscribers)
// ============================================================================

export interface Customer {
  id: string;
  account_number: string;
  full_name: string;
  address: string;
  gps_coordinates: {
    latitude: number;
    longitude: number;
  } | null;
  contact_number: string;
  email: string;
  installation_date: string;
  status: 'active' | 'suspended' | 'expired' | 'pending';
  router_id: string | null;
  service_plan_id: string | null;
  service_plan?: {
    id: string;
    name: string;
    download_speed: number;
    upload_speed: number;
    price: number;
  };
  monthly_fee: number;
  mac_address: string | null;
  ip_address: string | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateCustomerRequest {
  account_number: string;
  full_name: string;
  address: string;
  gps_coordinates?: {
    latitude: number;
    longitude: number;
  };
  contact_number: string;
  email: string;
  installation_date: string;
  router_id?: string;
  service_plan_id: string;
  monthly_fee: number;
  mac_address?: string;
  ip_address?: string;
  notes?: string;
}

// ============================================================================
// Router Types (MikroTik)
// ============================================================================

export interface Router {
  id: string;
  name: string;
  ip_address: string;
  username: string;
  port: number;
  status: 'online' | 'offline' | 'error';
  router_os_version: string | null;
  cpu_usage: number | null;
  memory_usage: number | null;
  uptime: string | null;
  last_check: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateRouterRequest {
  name: string;
  ip_address: string;
  username: string;
  password: string;
  port?: number;
}

export interface TestRouterConnectionResponse {
  success: boolean;
  message: string;
  router_os_version?: string;
  uptime?: string;
}

// ============================================================================
// Service Plan Types
// ============================================================================

export interface ServicePlan {
  id: string;
  name: string;
  monthly_price: number;
  download_speed: number;
  upload_speed: number;
  burst_download: number | null;
  burst_upload: number | null;
  burst_time: number | null;
  data_limit: number | null;
  priority: number;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface CreateServicePlanRequest {
  name: string;
  monthly_price: number;
  download_speed: number;
  upload_speed: number;
  burst_download?: number;
  burst_upload?: number;
  burst_time?: number;
  data_limit?: number;
  priority?: number;
}

// ============================================================================
// DHCP Lease Types
// ============================================================================

export interface DhcpLease {
  id: string;
  ip_address: string;
  mac_address: string;
  hostname: string | null;
  router_id: string;
  customer_id: string | null;
  status: 'bound' | 'expired' | 'released';
  lease_time: string;
  vlan: string | null;
  created_at: string;
  updated_at: string;
}

// ============================================================================
// Invoice & Payment Types
// ============================================================================

export interface Invoice {
  id: string;
  customer_id: string;
  invoice_number: string;
  amount: number;
  due_date: string;
  status: 'pending' | 'paid' | 'overdue' | 'cancelled';
  created_at: string;
  updated_at: string;
}

export interface Payment {
  id: string;
  invoice_id: string;
  customer_id: string;
  amount: number;
  payment_method: 'cash' | 'gcash' | 'maya' | 'bank_transfer' | 'voucher';
  reference_number: string | null;
  status: 'completed' | 'pending' | 'failed';
  paid_at: string;
  created_at: string;
  updated_at: string;
}

// ============================================================================
// Dashboard Metrics Types
// ============================================================================

export interface DashboardMetrics {
  active_subscribers: number;
  expired_subscribers: number;
  suspended_subscribers: number;
  online_users: number;
  offline_users: number;
  today_revenue: number;
  monthly_revenue: number;
  pending_tickets: number;
  router_status: {
    online: number;
    offline: number;
    error: number;
  };
}

// ============================================================================
// Axios Type Extensions
// ============================================================================

export type ApiAxiosResponse<T = unknown> = AxiosResponse<ApiResponse<T>>;
export type ApiAxiosError = AxiosError<ApiError>;
