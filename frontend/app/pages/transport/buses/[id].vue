<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/transport">পরিবহন</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <NuxtLink :to="`/transport/buses`">বাস তালিকা</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <span>{{ bus?.bus_number }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">যাতায়াত ব্যবস্থাপনা</span>
        <h1>যানবাহনের বিবরণী</h1>
        <p>{{ bus?.bus_number }} — ধরণ, রুট, চালক, ক্ষমতা ও ডকুমেন্ট</p>
      </div>
      <NuxtLink to="/transport/buses" class="btn btn-ghost">
        <icon name="arrow-left" /> বাস তালিকায় ফিরে যান
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!bus" class="empty-card">
      <div class="empty-icon"><icon name="bus" /></div>
      <h3>বাস পাওয়া যায়নি</h3>
      <NuxtLink to="/transport/buses" class="btn btn-primary">বাস তালিকায় ফিরে যান</NuxtLink>
    </div>
    <div v-else class="detail-layout">
      <div class="card bus-detail-card">
        <div class="bus-header">
          <div class="bus-identification">
            <h2 class="bus-title">{{ bus.bus_number }}</h2>
            <span v-if="bus.vehicle_type" class="bus-type">{{ bus.vehicle_type }}</span>
          </div>
          <span class="status-badge" :class="bus.is_active ? 'active' : 'inactive'">
            {{ bus.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>ধরণ</label>
            <p>{{ bus.vehicle_type || 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block">
            <label>রেজিস্ট্রেশন</label>
            <p>{{ bus.registration_number || '-' }}</p>
          </div>
          <div class="info-block">
            <label>ধারণক্ষমতা</label>
            <p>{{ bus.capacity || '-' }} জন</p>
          </div>
          <div class="info-block">
            <label>বর্তমান ভিড়</label>
            <p>{{ bus.current_occupancy || 0 }} জন</p>
          </div>
          <div class="info-block wide">
            <label>চালক</label>
            <p>{{ bus.driver?.name_bn || bus.driver?.name_en || '-' }}</p>
          </div>
          <div class="info-block wide">
            <label>নির্ধারিত পথ</label>
            <p>{{ bus.route?.route_name_bn || bus.route?.route_name_en || '-' }}</p>
          </div>
        </div>

        <div class="insurance-section" v-if="bus.insurance_expiry || bus.fitness_expiry">
          <div class="doc-row">
            <div class="doc-cell">
              <span class="doc-label">বীমা শেষ তারিখ</span>
              <p class="doc-value">{{ bus.insurance_expiry || '-' }}</p>
            </div>
            <div class="doc-cell">
              <span class="doc-label">ফিটনেস শেষ তারিখ</span>
              <p class="doc-value">{{ bus.fitness_expiry || '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>তথ্যপত্র</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="document" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো ছবি বা ডকুমেন্ট আপলোড নেই</p>
            <button class="btn btn-outline btn-sm">আপলোড করুন</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>এই বাসে বরাদ্দকৃত শিক্ষার্থী</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="assignment" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো শিক্ষার্থী বরাদ্দ নেই</p>
            <NuxtLink to="/transport/assignments" class="btn btn-outline btn-sm">শিক্ষার্থী বরাদ্দ করুন</NuxtLink>
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
const bus = ref<any>(null)

function load() {
  loading.value = true
  const id = Number(route.params.id)
  api.get(`/transport/buses/${id}`)
    .then(r => { bus.value = r.data?.data })
    .catch(() => { bus.value = null })
    .finally(() => { loading.value = false })
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
.empty-icon-slate { width:34px; height:34px; color:var(--color-text-muted) }
.detail-layout { display:flex; flex-direction:column; gap:.7rem }
.bus-detail-card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.bus-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; padding:1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.bus-title { margin:0; font:700 1.3rem var(--font-bn); color:var(--color-primary) }
.bus-type { display:block; font:.8rem var(--font-bn); color:var(--color-text-muted); margin-top:.2rem }
.status-badge { padding:.2rem .6rem; border-radius:99px; font:.7rem var(--font-bn); font-weight:600; white-space:nowrap }
.status-badge.active { background:#e6f4ec; color:#19724a }
.status-badge.inactive { background:#fde8e8; color:#a03030 }
.info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.8rem; padding:1.1rem }
.info-block { display:flex; flex-direction:column; gap:.2rem }
.info-block.wide { grid-column:span 2 }
.info-block label { font:600 .72rem var(--color-text-muted); font-family:var(--font-bn) }
.info-block p { margin:0; font:.88rem var(--font-bn) }
.backup-dates { padding:1.1rem; border-top:1px solid var(--color-border-light); background:#fafbfc }
.backup-dates-row { display:flex; gap:1.5rem; flex-wrap:wrap }
.backup-dates-row-item { flex:1; min-width:180px; font:.85rem var(--font-bn); color:var(--color-text-light) }
.backup-dates-row-item strong { color:var(--color-text) }
.card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.card-header { padding:.85rem 1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.card-header h3 { margin:0; font:700 1rem var(--font-bn); color:var(--color-text) }
.card-body { padding:1.1rem }
.empty-slate { display:flex; flex-direction:column; align-items:center; gap:.4rem; padding:1.5rem 0; text-align:center; font:.85rem var(--font-bn); color:var(--color-text-muted) }
@media(max-width:650px){ .info-grid { grid-template-columns:1fr } .info-block.wide { grid-column:auto } .page-header-row { flex-direction:column; align-items:flex-start } }
</style>