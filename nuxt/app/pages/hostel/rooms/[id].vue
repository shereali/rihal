<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/hostel">হোস্টেল কক্ষ</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <span>{{ room?.room_number }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">আবাসিক ব্যবস্থাপনা</span>
        <h1>কক্ষের বিবরণী</h1>
        <p>{{ room?.room_number }} কক্ষের তথ্য, ধারণক্ষমতা ও বর্তমান অবস্থা</p>
      </div>
      <NuxtLink to="/hostel" class="btn btn-ghost">
        <icon name="arrow-left" /> কক্ষ তালিকায় ফিরে যান
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!room" class="empty-card">
      <div class="empty-icon"><icon name="building" /></div>
      <h3>কক্ষ পাওয়া যায়নি</h3>
      <NuxtLink to="/hostel" class="btn btn-primary">কক্ষ তালিকায় ফিরে যান</NuxtLink>
    </div>
    <div v-else class="detail-layout">
      <div class="card detail-card">
        <div class="card-body">
          <div class="room-header">
            <div class="room-identifier">
              <h2>{{ room.room_number }}</h2>
              <span v-if="room.block">{{ room.block }} ব্লক</span>
              <span class="room-floor" v-if="room.floor">তলা {{ room.floor }}</span>
            </div>
            <span class="status-badge" :class="room.is_available ? 'available' : 'occupied'">
              {{ room.is_available ? 'খালি' : (room.current_occupancy >= room.capacity ? 'পূর্ণ' : 'আংশিক') }}
            </span>
          </div>

          <div class="occupancy-bar">
            <div class="occupancy-track">
              <div class="occupancy-fill" :style="{ width: occupancyPercent + '%' }" />
            </div>
            <span class="occupancy-label">
              {{ room.current_occupancy || 0 }} / {{ room.capacity || 1 }} জন বসবাস করছে
            </span>
          </div>

          <div class="info-grid">
            <div class="info-block">
              <label>ধারণক্ষমতা</label>
              <p>{{ room.capacity || 'নেই' }} জন</p>
            </div>
            <div class="info-block">
              <label>মাসিক ভাড়া</label>
              <p>{{ room.monthly_rent ? formatCurrency(room.monthly_rent) : 'নির্ধারিত নয়' }}</p>
            </div>
            <div class="info-block" v-if="room.amenities?.length">
              <label>সুবিধা</label>
              <div class="amenities-tags">
                <span v-for="a in room.amenities" :key="a" class="amenity-tag">{{ a }}</span>
              </div>
            </div>
            <div class="info-block" v-else>
              <label>সুবিধা</label>
              <p class="text-muted">নির্ধারিত নয়</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="room.warden" class="card">
        <div class="card-header"><h3>ওয়ার্ডেন</h3></div>
        <div class="card-body">
          <div class="warden-info">
            <h3>{{ room.warden?.name_bn || room.warden?.name_en }}</h3>
            <p class="text-muted">{{ room.warden?.email || 'ইমেইল নেই' }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>কক্ষের ছাত্রছাত্রী</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="students" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো ছাত্রছাত্রী বরাদ্দ নেই</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>সংশ্লিষ্ট কক্ষ</h3></div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="clock" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো রেকর্ড নেই</p>
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
const room = ref<any>(null)

function load() {
  loading.value = true
  const id = Number(route.params.id)
  api.get(`/hostel-rooms/${id}`)
    .then(r => { room.value = r.data?.data })
    .catch(() => { room.value = null })
    .finally(() => { loading.value = false })
}

function formatCurrency(v: number) {
  return v ? 'টাকা ' + v.toLocaleString('bn-BD') : ''
}

function occupancyPercent() {
  if (!room.value || !room.value.capacity) return 0
  return Math.min(100, Math.round((Number(room.value.current_occupancy || 0) / Number(room.value.capacity)) * 100))
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
.detail-layout { display:flex; flex-direction:column; gap:.7rem }
.room-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.2rem; padding-bottom:1rem; border-bottom:1px solid var(--color-border-light) }
.room-identifier h2 { margin:0; font:700 1.3rem var(--font-bn); color:var(--color-primary) }
.room-identifier span { display:block; font:.82rem var(--font-bn); color:var(--color-text-light) }
.room-floor { margin-left:.5rem }
.status-badge { padding:.25rem .7rem; border-radius:99px; font:.72rem var(--font-bn); font-weight:600; white-space:nowrap }
.status-badge.available { background:#e6f4ec; color:#19724a }
.status-badge.occupied { background:#fff0e4; color:#a05c35 }
.occupancy-bar { margin-bottom:1.2rem }
.occupancy-track { height:10px; background:#e9ecef; border-radius:5px; overflow:hidden; margin-bottom:.5rem }
.occupancy-fill { height:100%; background:linear-gradient(90deg,var(--color-primary-500),var(--color-primary-400)); transition:width .4s }
.occupancy-label { font:.78rem var(--font-bn); color:var(--color-text-muted) }
.info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.8rem }
.info-block { display:flex; flex-direction:column; gap:.2rem }
.info-block label { font:600 .72rem var(--color-text-muted); font-family:var(--font-bn) }
.info-block p { margin:0; font:.88rem var(--font-bn) }
.text-muted { color:var(--color-text-muted); font-family:var(--font-bn) }
.amenities-tags { display:flex; flex-wrap:wrap; gap:.35rem }
.amenity-tag { padding:.2rem .55rem; background:#f0f4f8; border-radius:99px; font:.72rem var(--font-bn); color:var(--color-text) }
.warden-info h3 { margin:0; font:700 .95rem var(--font-bn) }
.card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.card-header { padding:.85rem 1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.card-header h3 { margin:0; font:700 1rem var(--font-bn); color:var(--color-text) }
.card-body { padding:1.1rem }
.empty-slate { display:flex; flex-direction:column; align-items:center; gap:.5rem; padding:1.5rem 0; text-align:center }
.empty-icon-slate { color:var(--color-text-muted); width:38px; height:38px }
@media(max-width:650px){ .info-grid { grid-template-columns:1fr } .page-header-row { flex-direction:column; align-items:flex-start } }
</style>