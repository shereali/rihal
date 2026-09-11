<template>
  <div class="auth-layout">
    <!-- Atmospheric Divine Illumination (Celestial Glow) -->
    <div class="auth-celestial-glow" aria-hidden="true" />
    <div class="auth-aurora-glow glow-left" aria-hidden="true" />
    <div class="auth-aurora-glow glow-right" aria-hidden="true" />

    <!-- Islamic Monumental Mihrab Arch Silhouette -->
    <div class="auth-mihrab-silhouette" aria-hidden="true">
      <svg viewBox="0 0 1200 900" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
        <!-- Outer Grand Arch Curve -->
        <path d="M200 900V420C200 230 380 90 600 90C820 90 1000 230 1000 420V900" stroke="url(#mihrabGold)" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.25"/>
        <!-- Inner Pointed Ogee Arch -->
        <path d="M300 900V440C300 320 440 200 600 140C760 200 900 320 900 440V900" stroke="url(#mihrabGold)" stroke-width="2" opacity="0.35"/>
        <!-- Apex Keystone 8-Point Star -->
        <path d="M600 115L606 128L620 128L609 137L613 150L600 142L587 150L591 137L580 128L594 128Z" fill="url(#mihrabGold)" opacity="0.6"/>
        <defs>
          <linearGradient id="mihrabGold" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#f5e2a3" stop-opacity="0.8"/>
            <stop offset="50%" stop-color="#d4af37" stop-opacity="0.5"/>
            <stop offset="100%" stop-color="#aa8218" stop-opacity="0.2"/>
          </linearGradient>
        </defs>
      </svg>
    </div>

    <!-- Sacred Islamic Arabesque Girih Geometry (High-precision SVG Tile) -->
    <div class="auth-girih-pattern" aria-hidden="true" />

    <!-- Starlight Dust Particles -->
    <div class="auth-starlight" aria-hidden="true" />

    <div class="auth-layout-inner">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
// Auth layout — shared luxury Islamic architecture background for Login & Register
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  width: 100%;
  background: 
    radial-gradient(circle at 50% -15%, rgba(212, 175, 55, 0.22) 0%, rgba(20, 80, 50, 0.4) 35%, transparent 70%),
    radial-gradient(ellipse at 15% 85%, rgba(24, 102, 64, 0.45) 0%, transparent 55%),
    radial-gradient(ellipse at 85% 85%, rgba(13, 59, 36, 0.5) 0%, transparent 55%),
    linear-gradient(180deg, #041208 0%, #082114 40%, #06190f 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: calc(2rem + env(safe-area-inset-top)) 1.25rem calc(2rem + env(safe-area-inset-bottom));
  position: relative;
  overflow: hidden;
}

/* 1. Divine Celestial Noor Glow */
.auth-celestial-glow {
  position: absolute;
  top: -120px;
  left: 50%;
  transform: translateX(-50%);
  width: 700px;
  height: 400px;
  background: radial-gradient(ellipse, rgba(212, 175, 55, 0.2) 0%, rgba(24, 102, 64, 0.1) 50%, transparent 75%);
  filter: blur(50px);
  pointer-events: none;
  z-index: 0;
}

/* 2. Side Aurora Flares */
.auth-aurora-glow {
  position: absolute;
  width: 450px;
  height: 450px;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(80px);
  z-index: 0;
}

.glow-left {
  bottom: -100px;
  left: -100px;
  background: radial-gradient(circle, rgba(20, 80, 50, 0.5) 0%, transparent 70%);
}

.glow-right {
  bottom: -100px;
  right: -100px;
  background: radial-gradient(circle, rgba(13, 59, 36, 0.6) 0%, transparent 70%);
}

/* 3. Mihrab Silhouette */
.auth-mihrab-silhouette {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  z-index: 0;
  opacity: 0.65;
}

.auth-mihrab-silhouette svg {
  width: 100%;
  height: 100%;
  max-width: 1400px;
}

/* 4. Sacred Islamic Arabesque Girih Geometry (SVG Tile) */
.auth-girih-pattern {
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.16;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg stroke='%23d4af37' stroke-width='0.8' fill='none'%3E%3Cpath d='M50 0 L100 50 L50 100 L0 50 Z'/%3E%3Crect x='14.64' y='14.64' width='70.71' height='70.71' transform='rotate(45 50 50)'/%3E%3Ccircle cx='50' cy='50' r='20' stroke-width='0.5' stroke-dasharray='2 2'/%3E%3Cpath d='M50 20 L55 35 L70 35 L58 45 L62 60 L50 50 L38 60 L42 45 L30 35 L45 35 Z' stroke-width='0.6'/%3E%3Ccircle cx='0' cy='0' r='12'/%3E%3Ccircle cx='100' cy='0' r='12'/%3E%3Ccircle cx='100' cy='100' r='12'/%3E%3Ccircle cx='0' cy='100' r='12'/%3E%3C/g%3E%3C/svg%3E");
  background-size: 80px 80px;
  z-index: 0;
}

/* 5. Starlight Dust */
.auth-starlight {
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.2;
  background-image: 
    radial-gradient(circle at 20% 30%, #f5e2a3 1px, transparent 1px),
    radial-gradient(circle at 80% 25%, #d4af37 1.5px, transparent 1.5px),
    radial-gradient(circle at 40% 75%, #ffffff 1px, transparent 1px),
    radial-gradient(circle at 70% 80%, #f5e2a3 1.2px, transparent 1.2px),
    radial-gradient(circle at 90% 60%, #d4af37 1px, transparent 1px),
    radial-gradient(circle at 10% 65%, #ffffff 1.2px, transparent 1.2px);
  background-size: 240px 240px;
  z-index: 0;
}

/* Content Container */
.auth-layout-inner {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1140px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (prefers-reduced-motion: reduce) {
  .auth-girih-pattern,
  .auth-starlight {
    display: none;
  }
}
</style>
