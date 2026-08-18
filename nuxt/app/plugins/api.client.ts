import { apiClient } from '../utils/api'

export default defineNuxtPlugin(({ vueApp }) => {
  // Base URL from runtime config
  const config = useRuntimeConfig();
  apiClient.defaults.baseURL = config.public.apiBase || 'http://localhost:8000/api/v1';

  // ─── Auth interceptor (client-only) ──────────────────────────────────────────
  if (import.meta.client) {
    const savedToken = localStorage.getItem('rihal_token');
    if (savedToken) {
      apiClient.defaults.headers.Authorization = `Bearer ${savedToken}`;
    }

    const authStore = useAuthStore();
    if (authStore) {
      if (authStore.token) {
        apiClient.defaults.headers.Authorization = `Bearer ${authStore.token}`;
      }
      authStore.$subscribe((mutation, state) => {
        if (state.token) {
          apiClient.defaults.headers.Authorization = `Bearer ${state.token}`;
        } else {
          delete apiClient.defaults.headers.Authorization;
        }
      });
    }

    // ─── Response interceptor: handle expired / invalid tokens ──────────────────
    // On 401, clear local auth and send the user back to login (once per response).
    apiClient.interceptors.response.use(
      (response) => response,
      (error) => {
        const status = error?.response?.status;
        if (status === 401) {
          const store = useAuthStore();
          // Avoid redirect loops: only act if we actually had a (now stale) token.
          if (store?.token) {
            store.clearAuth()
            if (window.location.pathname !== '/login') {
              navigateTo('/login')
            }
          }
        }
        return Promise.reject(error)
      }
    )
  }

  return {
    provide: {
      api: apiClient,
    },
  };
});
