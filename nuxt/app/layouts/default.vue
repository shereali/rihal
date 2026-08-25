<template>
  <div class="default-layout">
    <AppSidebar v-if="showSidebar" :open="sidebarOpen" @close="sidebarOpen = false" />

    <div class="layout-main" :class="{ 'with-sidebar': showSidebar }">
      <AppTopBar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <main class="layout-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AppSidebar from '~/components/layout/AppSidebar.vue'
import AppTopBar from '~/components/layout/AppTopBar.vue'

const showSidebar = ref(true)
const sidebarOpen = ref(false)
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
  margin-left: 0;
  transition: margin-left var(--transition-normal);
}

.layout-main.with-sidebar {
  margin-left: var(--sidebar-width);
}

.layout-content {
  flex: 1;
  padding: 1.5rem;
  padding-top: calc(1.5rem + var(--header-height));
  min-width: 0;
}

@media (max-width: 768px) {
  .layout-main.with-sidebar { margin-left: 0; min-width: 0; }
  .layout-content {
    padding: .875rem;
    padding-top: calc(.875rem + var(--header-height));
    overflow-x: hidden;
  }
}
</style>
