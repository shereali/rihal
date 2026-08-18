// ─── API Composable ────────────────────────────────────────────────────────────────
import apiClient from '~/utils/api';
import type {
  PaginatedResponse,
  ApiError,
  Tenant,
  User,
} from '~/types';

export function useApi() {
  async function request<T>(
    endpoint: string,
    options: Record<string, any> = {},
  ): Promise<T> {
    const response = await apiClient(endpoint, options);
    if (import.meta.server && !response.data) {
      throw createError({
        statusCode: 500,
        statusMessage: 'API returned empty response',
      });
    }
    return (response?.data as any) ?? response;
  }

  async function get<T>(endpoint: string, params?: Record<string, any>): Promise<T> {
    const response = await apiClient.get(endpoint, { params });
    return response.data as T;
  }

  async function post<T>(endpoint: string, data?: any, params?: Record<string, any>): Promise<T> {
    const response = await apiClient.post(endpoint, data, { params });
    return response.data as T;
  }

  async function put<T>(endpoint: string, data?: any, params?: Record<string, any>): Promise<T> {
    const response = await apiClient.put(endpoint, data, { params });
    return response.data as T;
  }

  async function patch<T>(endpoint: string, data?: any, params?: Record<string, any>): Promise<T> {
    const response = await apiClient.patch(endpoint, data, { params });
    return response.data as T;
  }

  async function del<T>(endpoint: string, params?: Record<string, any>): Promise<T> {
    const response = await apiClient.delete(endpoint, { params });
    return response.data as T;
  }

  async function getTenant(id: number): Promise<Tenant> {
    const response = await get<PaginatedResponse<Tenant>>(`/tenants/${id}`);
    return response.data as Tenant;
  }

  async function listTenants(params?: Record<string, any>): Promise<PaginatedResponse<Tenant>> {
    const response = await get<PaginatedResponse<Tenant>>('/tenants', params);
    return response;
  }

  async function createUser(data: any): Promise<User> {
    const response = await post<PaginatedResponse<User>>('/users', data);
    return response.data as User;
  }

  async function listUsers(params?: Record<string, any>): Promise<PaginatedResponse<User>> {
    const response = await get<PaginatedResponse<User>>('/users', params);
    return response;
  }

  async function getCurrentUser(): Promise<User> {
    const response = await get<PaginatedResponse<User>>('/users/me');
    return response.data as User;
  }

  async function updateUser(id: number, data: any): Promise<User> {
    const response = await put<PaginatedResponse<User>>(`/users/${id}`, data);
    return response.data as User;
  }

  async function deleteUser(id: number): Promise<void> {
    await del(`/users/${id}`);
  }

  async function getMe(): Promise<User> {
    const response = await get<PaginatedResponse<User>>('/auth/me');
    return response.data as User;
  }

  return {
    request,
    get,
    post,
    put,
    patch,
    del,
    getTenant,
    listTenants,
    createUser,
    listUsers,
    getCurrentUser,
    updateUser,
    deleteUser,
    getMe,
  };
}
