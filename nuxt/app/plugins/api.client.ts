import { apiClient } from '../utils/api';

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
  }

  return {
    provide: {
      api: apiClient,
    },
  };
});
