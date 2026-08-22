<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/properties">সম্পত্তি ও সম্পদ</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <span>{{ property?.property_name_bn }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">সম্পদ ও অবকাঠামো</span>
        <h1>সম্পত্তির বিবরণী</h1>
        <p>{{ property?.property_name_bn }} — ধরণ, অবস্থা, মূল্য ও সংশ্লিষ্ট তথ্য</p>
      </div>
      <NuxtLink to="/properties" class="btn btn-ghost">
        <icon name="arrow-left" /> সম্পত্তি তালিকায় ফিরে যান
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!property" class="empty-card">
      <div class="empty-icon"><icon name="building" /></div>
      <h3>সম্পত্তি পাওয়া যায়নি</h3>
      <NuxtLink to="/properties" class="btn btn-primary">সম্পত্তি তালিকায় ফিরে যান</NuxtLink>
    </div>
    <div v-else class="detail-layout">
      <div class="card detail-card">
        <div class="property-header">
          <div class="property-identification">
            <h2 class="property-title">{{ property.property_name_bn }}</h2>
            <span v-if="property.property_name_en" class="property-en">{{ property.property_name_en }}</span>
          </div>
          <span class="status-badge" :class="property.status">
            {{ property.status === 'owned' ? 'নিজস্ব' : property.status === 'rented' ? 'ভাড়া' : property.status === 'under_maintenance' ? 'রক্ষণাবেক্ষণে' : 'অন্যান্য' }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>ধরণ</label>
            <p>{{ property.property_type || 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block">
            <label>অবস্থা</label>
            <p>{{ statusLabel(property.status) }}</p>
          </div>
          <div class="info-block">
            <label>পরিবর্তন তারিখ</label>
            <p>{{ property.updated_at ? formatDate(property.updated_at) : '-' }}</p>
          </div>
          <div class="info-block wide">
            <label>বর্তমান মূল্য</label>
            <p>{{ property.current_market_value ? formatCurrency(property.current_market_value) + ' টাকা' : 'নির্ধারিত নয়' }}</p>
          </div>
        </div>

        <div class="address-section" v-if="property.location_address_bn || property.location_address_en">
          <div class="address-label">ঠিকানা</div>
          <div class="address-text">{{ property.location_address_bn || property.location_address_en }}</div>
        </div>
        <div v-else class="address-section">
          <div class="address-label">ঠিকানা</div>
          <p class="text-muted">ঠিকানা নির্ধারিত নয়</p>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>ডকুমেন্ট</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="document" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো ডকুমেন্ট আপলোড নেই</p>
            <button class="btn btn-outline btn-sm">আপলোড করুন</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>রক্ষণাবেক্ষণ</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="tools" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো রক্ষণাবেক্ষণ রেকর্ড নেই</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>ভিজিটর</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="users" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো ভিজিটর রেকর্ড নেই</p>
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
const property = ref<any>(null)

function load() {
  loading.value = true
  const id = Number(route.params.id)
  api.get(`/properties/${id}`)
    .then(r => { property.value = r.data?.data })
    .catch(() => { property.value = null })
    .finally(() => { loading.value = false })
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    owned: 'নিজস্ব সম্পত্তি',
    rented: 'ভাড়ায় প্রাপ্ত',
    under_maintenance: 'বর্তমানে রক্ষণাবেক্ষণে',
    completed: 'সম্পন্ন',
    donated: 'দানকৃত',
  }
  return map[s] || (s || 'অজানা')
}

function formatDate(v: string) {
  return v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
}

function formatCurrency(v: number) {
  return v ? v.toLocaleString('bn-BD', { minimumFractionDigits: 0 }) : ''
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
.empty-icon-slate { width:34px; height:34px; color:var(--color-text-muted); margin-bottom:.3rem }
.empty-icon-slate.muted { color:var(--color-text-muted) }
.detail-layout { display:flex; flex-direction:column; gap:.7rem }
.detail-card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.property-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; padding:1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.property-title { margin:0; font:700 1.3rem var(--font-bn); color:var(--color-primary) }
.property-en { display:block; font:.8rem var(--font-bn); color:var(--color-text-muted); margin-top:.2rem }
.status-badge { padding:.2rem .6rem; border-radius:99px; font:.7rem var(--font-bn); font-weight:600; white-space:nowrap }
.status-badge.owned { background:#e6f4ec; color:#19724a }
.status-badge.rented { background:#fff0e4; color:#a05c35 }
.status-badge.under_maintenance { background:#fef3e2; color:#a07035 }
.info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.8rem; padding:1.1rem }
.info-block { display:flex; flex-direction:column; gap:.2rem }
.info-block.wide { grid-column:span 2 }
.info-block label { font:600 .72rem var(--color-text-muted); font-family:var(--font-bn) }
.info-block p { margin:0; font:.88rem var(--font-bn) }
.text-muted { color:var(--color-text-muted); font-family:var(--font-bn) }
.address-section { padding:1.1rem; border-top:1px solid var(--color-border-light); background:#fafbfc }
.address-label { font:600 .78rem var(--font-bn); color:var(--color-text-muted); margin-bottom:.3rem }
.address-text { font:.88rem var(--font-bn); line-height:1.5 }
.card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.card-header { padding:.85rem 1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.card-header h3 { margin:0; font:700 1rem var(--font-bn); color:var(--color-text) }
.card-body { padding:1.1rem }
.empty-slate { display:flex; flex-direction:column; align-items:center; gap:.4rem; padding:1.5rem 0; text-align:center; font:.85rem var(--font-bn); color:var(--color-text-muted) }
@media(max-width:650px){ .info-grid { grid-template-columns:1fr } .info-block.wide { grid-column:auto } .page-header-row { flex-direction:column; align-items:flex-start } }
</style>