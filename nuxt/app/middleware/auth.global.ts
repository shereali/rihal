// ─── Global auth middleware: redirect to /login if not authenticated ──────────────────
import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware((to, from) => {
  // Don't block public pages
  const publicPages = ['/login', '/register']
  if (publicPages.includes(to.path)) return true

  // Only redirect on client side — SSR doesn't have localStorage
  // During SSR, the page will render, then redirect client-side after hydration
  if (import.meta.client) {
    const store = useAuthStore()

    // If not initialized yet, allow render (the store will hydrate from localStorage)
    if (!store.initialized) return true

    // If initialized and no token, redirect to login
    if (!store.token) {
      return navigateTo('/login')
    }
  }

  return true
})
