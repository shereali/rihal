<template>
  <div class="default-layout">
    <AppSidebar v-if="showSidebar" />

    <div class="layout-main" :class="{ 'with-sidebar': showSidebar }">
      <AppTopBar />

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
}
</style>
