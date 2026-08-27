<template>
  <section class="kpi-grid">
    <article v-for="stat in kpis" :key="stat.label" class="kpi-card slide-up-fade" :class="[`tone-${stat.tone}`]" @click="stat.onClick && stat.onClick()">
      <div class="kpi-topline">
        <span class="kpi-icon"><Icon :name="stat.icon" /></span>
        <span class="kpi-trend" v-if="stat.trend">{{ stat.trend }}</span>
      </div>
      <div class="kpi-value">{{ stat.value }}</div>
      <div class="kpi-label">{{ stat.label }}</div>
      <div class="kpi-shine" />
    </article>
  </section>
</template>

<script setup lang="ts">
defineProps<{
  kpis: {
    label: string
    value: string | number
    icon: string
    tone: string
    trend?: string
    onClick?: () => void
  }[]
}>()
</script>

<style scoped>
.kpi-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .9rem; margin-bottom: 2rem; }
.kpi-card { position: relative; overflow: hidden; min-height: 152px; padding: 1rem; border: 1px solid rgba(255,255,255,.35); border-radius: 17px; color: #fff; box-shadow: var(--shadow-md); transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
.kpi-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); }
.tone-green { background: linear-gradient(145deg,#176b45,#0e452d); }
.tone-blue { background: linear-gradient(145deg,#17658f,#103b60); }
.tone-purple { background: linear-gradient(145deg,#6e4da5,#41316f); }
.tone-amber { background: linear-gradient(145deg,#bd7620,#825012); }
.tone-teal { background: linear-gradient(145deg,#087d78,#07524f); }
.tone-gold { background: linear-gradient(145deg,#b7902e,#775b16); }
.kpi-topline { display: flex; justify-content: space-between; align-items: center; }
.kpi-icon { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 11px; background: rgba(255,255,255,.15); color: rgba(255,255,255,.95); }
.kpi-trend { padding: .2rem .45rem; border-radius: 999px; color: rgba(255,255,255,.8); background: rgba(255,255,255,.12); font: .7rem var(--font-bn); }
.kpi-value { margin-top: 1.15rem; font: 800 1.42rem var(--font-sans); letter-spacing: -.03em; }
.kpi-label { color: rgba(255,255,255,.8); font: .78rem var(--font-bn); }
.kpi-shine { position: absolute; width: 130px; height: 130px; right: -45px; bottom: -75px; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; }

@media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 560px) { 
  .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; }
  .kpi-card { min-height: 128px; padding: .75rem; }
  .kpi-value { margin-top: .75rem; font-size: 1.12rem; }
  .kpi-label { font-size: .7rem; }
}

/* Staggered animation classes applied per item */
.slide-up-fade { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
.kpi-card:nth-child(1) { animation-delay: 0.05s; }
.kpi-card:nth-child(2) { animation-delay: 0.1s; }
.kpi-card:nth-child(3) { animation-delay: 0.15s; }
.kpi-card:nth-child(4) { animation-delay: 0.2s; }
.kpi-card:nth-child(5) { animation-delay: 0.25s; }
.kpi-card:nth-child(6) { animation-delay: 0.3s; }
.kpi-card:nth-child(7) { animation-delay: 0.35s; }

@keyframes slideUpFade {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
