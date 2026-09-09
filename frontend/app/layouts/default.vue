<template>
  <div class="default-layout">
    <AppSidebar v-if="showSidebar" :open="sidebarOpen" @close="sidebarOpen = false" />

    <div class="layout-main" :class="{ 'with-sidebar': showSidebar && sidebarOpen, 'sidebar-collapsed': showSidebar && !sidebarOpen }">
      <AppTopBar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <main class="layout-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppSidebar from '~/components/layout/AppSidebar.vue'
import AppTopBar from '~/components/layout/AppTopBar.vue'

const showSidebar = ref(true)
const sidebarOpen = ref(true)

onMounted(() => {
  if (window.innerWidth <= 768) {
    sidebarOpen.value = false
  }
})
</script>

<style>
.default-layout {
  min-height: 100vh;
  background: var(--color-bg);
}

.layout-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  margin-inline-start: 0;
  transition: margin-inline-start var(--transition-normal);
}

.layout-main.with-sidebar {
  margin-inline-start: var(--sidebar-width);
}

.layout-main.with-sidebar .topbar {
  inset-inline-start: var(--sidebar-width);
}

.layout-main.sidebar-collapsed {
  margin-inline-start: 80px;
}

.layout-main.sidebar-collapsed .topbar {
  inset-inline-start: 80px;
}

.layout-content {
  flex: 1;
  padding: 1.5rem;
  padding-top: calc(1.5rem + var(--header-height) + env(safe-area-inset-top));
  min-width: 0;
}

@media (max-width: 768px) {
  .layout-main {
    margin-inline-start: 0 !important;
  }
  .layout-main.with-sidebar .topbar,
  .layout-main.sidebar-collapsed .topbar {
    inset-inline-start: 0;
  }
  .layout-content {
    padding: .875rem;
    padding-top: calc(.875rem + var(--header-height) + env(safe-area-inset-top));
    overflow-x: hidden;
  }
}
</style>
