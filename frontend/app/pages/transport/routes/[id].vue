<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/transport">পরিবহন রুট</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <span>{{ route?.route_name_bn }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">যাতায়াত ব্যবস্থাপনা</span>
        <h1>রুটের বিবরণী</h1>
        <p>{{ route?.route_name_bn }} রুটের সম্পূর্ণ তথ্য — দূরত্ব, সময়, ভাড়া এবং বরাদ্দকৃত বাস</p>
      </div>
      <NuxtLink to="/transport" class="btn btn-ghost">
        <icon name="arrow-left" /> রুট তালিকায় ফিরে যান
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!route" class="empty-card">
      <div class="empty-icon"><icon name="bus" /></div>
      <h3>রুট পাওয়া যায়নি</h3>
      <NuxtLink to="/transport" class="btn btn-primary">রুট তালিকায় ফিরে যান</NuxtLink>
    </div>
    <div v-else class="detail-layout">
      <div class="card route-detail-card">
        <div class="route-header">
          <div class="route-identification">
            <h2 class="route-title">{{ route.route_name_bn }}</h2>
            <span v-if="route.route_name_en" class="route-en-name">{{ route.route_name_en }}</span>
          </div>
          <span class="status-badge" :class="route.is_active ? 'active' : 'inactive'">
            {{ route.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>শুরু</label>
            <p>{{ route.start_point || '-' }}</p>
          </div>
          <div class="info-block">
            <label>গন্তব্য</label>
            <p>{{ route.end_point || '-' }}</p>
          </div>
          <div class="info-block">
            <label>দূরত্ব</label>
            <p>{{ route.distance_km ? route.distance_km.toFixed(1) + ' কি.মি.' : '-' }}</p>
          </div>
          <div class="info-block">
            <label>ভাড়া</label>
            <p>{{ route.fare ? formatCurrency(route.fare) : '-' }}</p>
          </div>
          <div class="info-block wide">
            <label>শুরুর সময়</label>
            <p>{{ route.start_time ? formatTime(route.start_time) : '-' }}</p>
          </div>
          <div class="info-block wide">
            <label>শেষ সময়</label>
            <p>{{ route.end_time ? formatTime(route.end_time) : '-' }}</p>
          </div>
        </div>

        <div v-if="route.buses?.length" class="buses-section">
          <div class="buses-header">
            <h3>এই রুটে বরাদ্দকৃত বাস</h3>
            <NuxtLink to="/transport/buses" class="btn btn-ghost btn-sm">সব বাস দেখুন</NuxtLink>
          </div>
          <div class="buses-list">
            <div v-for="bus in route.buses" :key="bus.id" class="bus-row">
              <div class="bus-number">
                <div class="bus-badge">{{ bus.bus_number }}</div>
              </div>
              <div class="bus-info">
                <span class="bus-capacity">
                  {{ bus.capacity }} জনের, বর্তমান {{ bus.current_occupancy || 0 }} জন
                </span>
                <span v-if="bus.driver?.name_bn || bus.driver?.name_en" class="bus-driver">
                  চালক: {{ bus.driver?.name_bn || bus.driver?.name_en }}
                </span>
              </div>
              <NuxtLink :to="`/transport/buses/${bus.id}`" class="view-link">বিস্তারিত</NuxtLink>
            </div>
          </div>
        </div>
        <div v-else class="buses-empty">
          <icon name="bus" class="empty-icon-inline" />
          <p>এই রুটে এখনও কোনো বাস বরাদ্দ করা হয়নি</p>
          <NuxtLink to="/transport/buses?route_id={{ route.id }}" class="btn btn-outline btn-sm">বাস যোগ করুন</NuxtLink>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>রুটের শিক্ষার্থী বরাদ্দ</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="assignment" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো শিক্ষার্থী এই রুটে বরাদ্দ নেই</p>
            <NuxtLink to="/transport/assignments?route_id={{ route.id }}" class="btn btn-outline btn-sm">শিক্ষার্থী বরাদ্দ করুন</NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(true)
const routeData = ref<any>(null)

function load() {
  loading.value = true
  const id = Number(route.params.id)
  api.get(`/transport/routes/${id}`)
    .then(r => { routeData.value = r.data?.data })
    .catch(() => { routeData.value = null })
    .finally(() => { loading.value = false })
}

function formatCurrency(v: number) {
  return v ? 'টাকা ' + v.toLocaleString('bn-BD') : ''
}

function formatTime(v: string) {
  if (!v) return '-'
  try { return new Date(v).toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit' }) } catch { return v }
}

onMounted(load)
</script>

<style scoped>
.module-page { max-width: 1000px; margin: 0 auto; padding-bottom: 2rem }
.breadcrumb { display:flex; align-items:center; gap:.4rem; margin-bottom:.7rem; font:.82rem var(--font-bn); color:var(--color-text-muted) }
.breadcrumb-sep { color:var(--color-text-muted) }
.page-header-row { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin-bottom:1.4rem }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn) }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.5rem var(--font-bn) }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn) }
.header-actions { display:flex; gap:.5rem }
.loading-state { text-align:center; padding:3rem 0; display:flex; justify-content:center; gap:.5rem }
.empty-card { text-align:center; padding:3rem 0; display:flex; flex-direction:column; align-items:center; gap:.7rem }
.empty-icon { width:56px; height:56px; color:var(--color-text-muted); margin-bottom:.3rem }
.empty-icon-inline { width:32px; height:32px; color:var(--color-text-muted); margin-right:.4rem; vertical-align:middle }
.detail-layout { display:flex; flex-direction:column; gap:.7rem }
.route-detail-card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.route-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; padding:1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.route-title { margin:0; font:700 1.3rem var(--font-bn); color:var(--color-primary) }
.route-en-name { display:block; font:.8rem var(--font-bn); color:var(--color-text-muted); margin-top:.2rem }
.status-badge { padding:.2rem .6rem; border-radius:99px; font:.7rem var(--font-bn); font-weight:600; white-space:nowrap }
.status-badge.active { background:#e6f4ec; color:#19724a }
.status-badge.inactive { background:#fde8e8; color:#a03030 }
.info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.8rem; padding:1.1rem; }
.info-block { display:flex; flex-direction:column; gap:.2rem }
.info-block.wide { grid-column:span 2 }
.info-block label { font:600 .72rem var(--color-text-muted); font-family:var(--font-bn) }
.info-block p { margin:0; font:.88rem var(--font-bn) }
.buses-section { padding:1.1rem; border-top:1px solid var(--color-border-light); background:#fafbfc }
.buses-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:.8rem }
.buses-header h3 { margin:0; font:700 .95rem var(--font-bn) }
.buses-list { display:flex; flex-direction:column; gap:.5rem }
.bus-row { display:flex; align-items:center; gap:.8rem; padding:0.6rem 0.5rem; border-bottom:1px solid var(--color-border-light); }
.bus-row:last-child { border-bottom:0 }
.bus-badge { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:var(--color-primary-100); color:var(--color-primary); font:700 .85rem var(--font-bn) }
.bus-info { flex:1; font:.8rem var(--font-bn); color:var(--color-text-light) }
.bus-driver { display:block; margin-top:.2rem; color:var(--color-text) }
.view-link { font:600 .78rem var(--color-primary); text-decoration:none }
.buses-empty { padding:1.5rem; text-align:center; font:.85rem var(--font-bn); color:var(--color-text-muted) }
.card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.card-header { padding:.85rem 1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.card-header h3 { margin:0; font:700 1rem var(--font-bn); color:var(--color-text) }
.card-body { padding:1.1rem }
.empty-slate { display:flex; flex-direction:column; align-items:center; gap:.5rem; padding:1.5rem 0; text-align:center }
.empty-icon-slate { color:var(--color-text-muted); width:38px; height:38px }
@media(max-width:650px){ .info-grid { grid-template-columns:1fr } .info-block.wide { grid-column:auto } .page-header-row { flex-direction:column; align-items:flex-start } }
</style>