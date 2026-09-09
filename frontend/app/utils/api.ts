import axios, { AxiosError } from 'axios'

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

// Some endpoints intentionally resolve 4xx responses via validateStatus. Normalize
// them back into Axios rejections so every caller's catch/error path is reliable.
apiClient.interceptors.response.use((response) => {
  if (response.status >= 400) {
    return Promise.reject(new AxiosError(
      `Request failed with status code ${response.status}`,
      AxiosError.ERR_BAD_REQUEST,
      response.config,
      response.request,
      response,
    ))
  }
  return response
})

/**
 * Returns the shared client whose baseURL is configured by api.client.ts from
 * Nuxt runtimeConfig. Per-request config may still override it explicitly.
 */
export function useApiClient() {
  return {
    get: <T = any>(url: string, config?: any) => apiClient.get<T>(url, config),
    post: <T = any>(url: string, data?: any, config?: any) => apiClient.post<T>(url, data, config),
    put: <T = any>(url: string, data?: any, config?: any) => apiClient.put<T>(url, data, config),
    patch: <T = any>(url: string, data?: any, config?: any) => apiClient.patch<T>(url, data, config),
    delete: <T = any>(url: string, config?: any) => apiClient.delete<T>(url, config),
  }
}

export { apiClient }
export default apiClient
