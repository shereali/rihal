// ─── Auth Composable ───────────────────────────────────────────────────────────
import { apiClient } from '~/utils/api'
import { useAuthStore } from '~/stores/auth'
import type { User, AuthResponse, LoginCredentials, RegisterData } from '~/types'

export function useAuth() {
  const store = useAuthStore()

  const isAuthenticated = computed(() => store.token !== null)
  const currentUser = computed(() => store.user)
  const isLoading = computed(() => store.initialized === false)

  async function register(data: RegisterData): Promise<AuthResponse> {
    store.setLoading(true)
    try {
      const response = await apiClient.post('/auth/register', data)
      const { user, token } = response.data.data
      store.setAuth({ user: user as User, token })
      // NOTE: we deliberately do NOT auto-redirect here — the register page
      // shows a success message and sends the user to /login.
      return { user, token }
    } finally {
      store.setLoading(false)
    }
  }

  async function login(data: LoginCredentials): Promise<AuthResponse> {
    store.setLoading(true)
    try {
      const response = await apiClient.post('/auth/login', data)
      const { user, token } = response.data.data
      store.setAuth({ user: user as User, token })
      // Redirect is handled by the calling page to allow success messages
      return { user, token }
    } finally {
      store.setLoading(false)
    }
  }

  async function logout(): Promise<void> {
    try {
      await apiClient.post('/auth/logout')
    } catch {
      // proceed with local logout
    } finally {
      store.clearAuth()
      try { navigateTo('/login') } catch { /* SSR-safe */ }
    }
  }

  async function fetchMe(): Promise<User | null> {
    try {
      const response = await apiClient.get('/auth/user')
      store.setAuth({ user: response.data.data as User, token: store.token ?? null })
      return response.data.data as User
    } catch {
      store.clearAuth()
      return null
    }
  }

  async function restoreSession(): Promise<boolean> {
    if (!import.meta.client) return false
    // Hydrate token/user from localStorage first (synchronous).
    store.restoreAuth()
    if (!store.token) return false
    // Re-validate the token against the server and refresh user data.
    try {
      const user = await fetchMe()
      return user !== null
    } catch {
      store.clearAuth()
      return false
    }
  }

  return {
    isAuthenticated,
    currentUser,
    isLoading,
    register,
    login,
    logout,
    fetchMe,
    restoreSession,
  }
}
