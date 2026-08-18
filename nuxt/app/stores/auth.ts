import { defineStore } from 'pinia'

interface AuthState {
  user: any | null
  token: string | null
  loading: boolean
  initialized: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    loading: false,
    initialized: false,
  }),

  actions: {
    setAuth(authData: { user: any; token: string }) {
      this.user = authData.user
      this.token = authData.token
      if (import.meta.client) {
        localStorage.setItem('rihal_token', authData.token)
        localStorage.setItem('rihal_user', JSON.stringify(authData.user))
      }
    },

    restoreAuth() {
      if (import.meta.client) {
        const savedToken = localStorage.getItem('rihal_token')
        const savedUser = localStorage.getItem('rihal_user')

        if (savedToken && savedUser) {
          try {
            this.user = JSON.parse(savedUser)
            this.token = savedToken
            this.initialized = true
          } catch {
            this.clearAuth()
          }
        } else {
          this.initialized = true
        }
      } else {
        this.initialized = true
      }
    },

    clearAuth() {
      this.user = null
      this.token = null
      if (import.meta.client) {
        localStorage.removeItem('rihal_token')
        localStorage.removeItem('rihal_user')
      }
    },

    setLoading(value: boolean) {
      this.loading = value
    },
  },
})
