import api, { setAuthToken, clearAuth, getErrorMessage } from './api';
import type { LoginRequest, LoginResponse, RegisterRequest, RegisterResponse, User } from '@/types/api';

/**
 * Authentication Service
 * 
 * Handles all authentication-related API calls
 */

/**
 * Login user
 */
export const login = async (credentials: LoginRequest): Promise<LoginResponse> => {
  try {
    const response = await api.post<{ data: LoginResponse }>('/auth/login', credentials);
    const data = response.data.data;
    
    // Store token
    setAuthToken(data.access_token);
    
    return data;
  } catch (error) {
    throw new Error(getErrorMessage(error));
  }
};

/**
 * Register new user
 */
export const register = async (data: RegisterRequest): Promise<RegisterResponse> => {
  try {
    const response = await api.post<{ data: RegisterResponse }>('/auth/register', data);
    const responseData = response.data.data;
    
    // Store token
    setAuthToken(responseData.access_token);
    
    return responseData;
  } catch (error) {
    throw new Error(getErrorMessage(error));
  }
};

/**
 * Logout user
 */
export const logout = async (): Promise<void> => {
  try {
    await api.post('/auth/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    clearAuth();
  }
};

/**
 * Refresh JWT token
 */
export const refreshToken = async (): Promise<string> => {
  try {
    const response = await api.post<{ data: { access_token: string } }>('/auth/refresh');
    const newToken = response.data.data.access_token;
    
    setAuthToken(newToken);
    
    return newToken;
  } catch (error) {
    clearAuth();
    throw new Error(getErrorMessage(error));
  }
};

/**
 * Get current user
 */
export const getCurrentUser = async (): Promise<User> => {
  try {
    const response = await api.get<{ data: User }>('/auth/me');
    return response.data.data;
  } catch (error) {
    throw new Error(getErrorMessage(error));
  }
};

/**
 * Check if user is authenticated
 */
export const checkAuth = (): boolean => {
  return !!getAuthToken();
};

// Re-export for convenience
import { getAuthToken } from './api';
export { getAuthToken };
