import axios from 'axios'

const apiClient = axios.create({
  timeout: 15_000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  validateStatus: (status) => status >= 200 && status < 500,
  maxRedirects: 5,
  withCredentials: false,
})

const baseURL = 'http://localhost:8000/api/v1'

/**
 * Runtime-config–aware composable returning the configured API client.
 */
export function useApiClient() {
  return {
    get: <T = any>(url: string, config?: any) => apiClient.get<T>(url, { baseURL, ...config }).then(res => res),
    post: <T = any>(url: string, data?: any, config?: any) => apiClient.post<T>(url, data, { baseURL, ...config }).then(res => res),
    put: <T = any>(url: string, data?: any, config?: any) => apiClient.put<T>(url, data, { baseURL, ...config }).then(res => res),
    patch: <T = any>(url: string, data?: any, config?: any) => apiClient.patch<T>(url, data, { baseURL, ...config }).then(res => res),
    delete: <T = any>(url: string, config?: any) => apiClient.delete<T>(url, { baseURL, ...config }).then(res => res),
  }
}

export { apiClient }
export default apiClient
