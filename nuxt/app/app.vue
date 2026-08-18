<template>
  <div class="app">
    <NuxtPage />
  </div>
</template>

<script setup lang="ts">
// Root app component — Rihal
// Nuxt handles the HTML document structure (nuxt.config.ts → app.template)

import { useAuthStore } from '~/stores/auth'

// Hydrate auth state from localStorage on the client as soon as the app mounts.
// This must run client-side only (localStorage is unavailable during SSR).
onMounted(() => {
  if (import.meta.client) {
    const store = useAuthStore()
    store.restoreAuth()
  }
})
</script>

<style>
.app {
  min-height: 100vh;
}
</style>
