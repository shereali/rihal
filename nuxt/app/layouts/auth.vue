<template>
  <div class="auth-layout">
    <slot />
  </div>
</template>

<script setup lang="ts">
// Auth layout — clean standalone screen with no sidebar/topbar
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  width: 100%;
  background: var(--color-bg);
}
</style>
