<template>
  <div class="attendance-show">
    <div class="page-header">
      <NuxtLink to="/attendance" class="btn btn-outline btn-sm">
        <icon name="arrow-left" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink :to="`/attendance/${record?.id}/edit`" class="btn btn-primary btn-sm">
          <icon name="pencil" /> সম্পাদনা
        </NuxtLink>
        <button class="btn btn-outline-danger btn-sm" @click="confirmDelete">
          <icon name="delete" /> মুছুন
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>হাজিরা রেকর্ড লোড হচ্ছে...</p></div>

    <div v-else-if="!record" class="empty-state">
      <p>হাজিরা রেকর্ড পাওয়া যায়নি</p>
      <NuxtLink to="/attendance" class="btn btn-primary">হাজিরার তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <div class="card">
        <div class="card-header">
          <h3>হাজিরা রেকর্ড</h3>
          <span class="status-badge" :class="getStatusClass(record.status)">{{ formatStatus(record.status) }}</span>
        </div>
        <div class="card-body">
          <div class="detail-grid">
            <div class="detail-item">
              <label class="detail-label">ছাত্র</label>
              <p class="detail-value">{{ record.student?.user?.name_bn || record.student?.name_bn }}</p>
              <p class="detail-value text-muted text-sm">{{ record.student?.class?.name_bn || record.student?.class_name }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">তারিখ</label>
              <p class="detail-value">{{ formatDate(record.date) }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">অবস্থা</label>
              <span v-if="record.status === 'present'" class="status-badge status-present"><icon name="check-circle" /> উপস্থিত</span>
              <span v-else-if="record.status === 'absent'" class="status-badge status-absent"><icon name="close-circle" /> অনুপস্থিত</span>
              <span v-else-if="record.status === 'late'" class="status-badge status-late"><icon name="clock-outline" /> দেরি</span>
              <span v-else class="text-muted">{{ record.status }}</span>
            </div>
            <div class="detail-item">
              <label class="detail-label">হাজিরার পদ্ধতি</label>
              <span class="badge badge-outline" :class="getMethodBadge(record.method)">{{ formatMethod(record.method) }}</span>
            </div>
            <div class="detail-item">
              <label class="detail-label">চেক-ইন সময়</label>
              <p class="detail-value">{{ formatDateTime(record.check_in_time) }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">চেক-আউট সময়</label>
              <p class="detail-value">{{ formatDateTime(record.check_out_time) }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">অভিভাবককে জানানো হয়েছে</label>
              <p class="detail-value">{{ record.parent_notified ? 'হ্যাঁ' : 'না' }}</p>
            </div>
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
const record = ref<any>(null)

async function loadRecord() {
  loading.value = true
  try {
    const res = await api.get(`/attendance/${route.params.id}`)
    record.value = res.data.data
  } catch (error) { console.error('Failed to load attendance:', error) }
  finally { loading.value = false }
}

const confirmDelete = () => {
  if (confirm('এই হাজিরা রেকর্ডটি মুছে ফেলতে চান?')) {
    api.delete(`/attendance/${record.value?.id}`).then(() => navigateTo('/attendance'))
  }
}

const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day:'numeric', month:'short', year:'numeric' }) : '-'
const formatDateTime = (t: string | null | undefined) => t ? new Date(t).toLocaleString('bn-BD', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '-'
const formatStatus = (s: string) => ({ present:'উপস্থিত', absent:'অনুপস্থিত', late:'দেরি' }[s] || s)
const getStatusClass = (s: string) => ({ present:'status-present', absent:'status-absent', late:'status-late' }[s] || '')
const formatMethod = (m: string) => ({ fingerprint:'ফিঙ্গারপ্রিন্ট', manual:'ম্যানুয়াল', biometric:'বায়োমেট্রিক', qr:'QR', online:'অনলাইন' }[m] || m)
const getMethodBadge = (m: string) => ({ fingerprint:'badge-primary', manual:'badge-secondary', biometric:'badge-success', qr:'badge-info', online:'badge-warning' }[m] || 'badge-outline')

onMounted(() => { loadRecord() })
</script>

<style scoped>
.attendance-show { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.detail-layout { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 1rem; }
.detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem 1rem; }
.detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
.detail-label { font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; }
.detail-value { font-size: 0.9375rem; color: var(--color-text); margin: 0; }
.status-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); font-size: 0.875rem; }
.status-present { background: rgba(40,167,69,0.12); color: var(--color-success); }
.status-absent { background: rgba(220,53,69,0.12); color: var(--color-error); }
.status-late { background: rgba(255,193,7,0.12); color: var(--color-warning); }
</style>
