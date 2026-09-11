<template>
  <div class="auth-layout">
    <div class="auth-pattern" aria-hidden="true" />
    <div class="auth-layout-inner">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
// Auth layout — clean standalone screen with no sidebar/topbar
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  width: 100%;
  background: 
    radial-gradient(circle at 10% 10%, rgba(24, 102, 64, 0.45) 0%, transparent 45%),
    radial-gradient(circle at 90% 90%, rgba(13, 59, 36, 0.7) 0%, transparent 50%),
    radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.04) 0%, transparent 60%),
    var(--color-primary-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: calc(1.5rem + env(safe-area-inset-top)) 1.25rem calc(1.5rem + env(safe-area-inset-bottom));
  position: relative;
  overflow: hidden;
}

/* Domain-earned texture: Islamic geometric star lattice (girih-style) */
.auth-pattern {
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.16;
  background-image:
    linear-gradient(30deg, rgba(212, 175, 55, 0.55) 12%, transparent 12.5%, transparent 87%, rgba(212, 175, 55, 0.55) 87.5%, rgba(212, 175, 55, 0.55)),
    linear-gradient(150deg, rgba(212, 175, 55, 0.55) 12%, transparent 12.5%, transparent 87%, rgba(212, 175, 55, 0.55) 87.5%, rgba(212, 175, 55, 0.55)),
    linear-gradient(30deg, rgba(212, 175, 55, 0.55) 12%, transparent 12.5%, transparent 87%, rgba(212, 175, 55, 0.55) 87.5%, rgba(212, 175, 55, 0.55)),
    linear-gradient(150deg, rgba(212, 175, 55, 0.55) 12%, transparent 12.5%, transparent 87%, rgba(212, 175, 55, 0.55) 87.5%, rgba(212, 175, 55, 0.55)),
    linear-gradient(60deg, rgba(212, 175, 55, 0.55) 25%, transparent 25.5%, transparent 75%, rgba(212, 175, 55, 0.55) 75%, rgba(212, 175, 55, 0.55)),
    linear-gradient(60deg, rgba(212, 175, 55, 0.55) 25%, transparent 25.5%, transparent 75%, rgba(212, 175, 55, 0.55) 75%, rgba(212, 175, 55, 0.55));
  background-size: 80px 140px;
  background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
}

.auth-layout-inner {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1120px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (prefers-reduced-motion: reduce) {
  .auth-pattern {
    display: none;
  }
}
</style>
