<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/transport">পরিবহন</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <NuxtLink to="/transport/routes">রুট তালিকা</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <span>{{ routeData?.route_name_bn || 'রুট বিবরণী' }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">যাতায়াত ব্যবস্থাপনা</span>
        <h1>রুটের বিবরণী</h1>
        <p>{{ routeData?.route_name_bn }} রুটের সম্পূর্ণ তথ্য — দূরত্ব, সময়, ভাড়া এবং বরাদ্দকৃত বাস</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/transport/routes" class="btn btn-ghost">
          <icon name="arrow-left" /> রুট তালিকায় ফিরে যান
        </NuxtLink>
        <button class="btn btn-primary btn-sm" @click="openEditModal" v-if="routeData">
          <icon name="pencil" /> তথ্য সম্পাদনা
        </button>
      </div>
    </div>

    <!-- Edit Route Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
          <div class="modal-card">
            <div class="modal-header">
              <h3>রুট তথ্য সম্পাদনা</h3>
              <button class="modal-close" @click="showEdit = false">×</button>
            </div>
            <form @submit.prevent="saveEdit">
              <div class="modal-body">
                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">রুটের নাম (বাংলায়) *</label>
                    <input v-model="editForm.route_name_bn" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">রুটের নাম (ইংরেজি)</label>
                    <input v-model="editForm.route_name_en" class="form-control" />
                  </div>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">শুরুর স্থান</label>
                    <input v-model="editForm.start_point" class="form-control" placeholder="যেমন: ক্যাম্পাস গেট" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">গন্তব্য স্থান</label>
                    <input v-model="editForm.end_point" class="form-control" placeholder="যেমন: শিবগঞ্জ মোড়" />
                  </div>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">দূরত্ব (কি.মি.)</label>
                    <input v-model.number="editForm.distance_km" type="number" step="0.1" min="0" class="form-control" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">ভাড়া (টাকা)</label>
                    <input v-model.number="editForm.fare" type="number" min="0" class="form-control" />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-check-label">
                    <input type="checkbox" v-model="editForm.is_active" /> সক্রিয় রুট
                  </label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-ghost" @click="showEdit = false">বাতিল</button>
                <button type="submit" class="btn btn-primary" :disabled="saving || !editForm.route_name_bn">
                  <icon v-if="saving" name="loader" />
                  {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </ClientOnly>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!routeData" class="empty-card">
      <div class="empty-icon"><icon name="bus" /></div>
      <h3>রুট পাওয়া যায়নি</h3>
      <NuxtLink to="/transport/routes" class="btn btn-primary">রুট তালিকায় ফিরে যান</NuxtLink>
    </div>
    <div v-else class="detail-layout">
      <div class="card route-detail-card">
        <div class="route-header">
          <div class="route-identification">
            <h2 class="route-title">{{ routeData.route_name_bn }}</h2>
            <span v-if="routeData.route_name_en" class="route-en-name">{{ routeData.route_name_en }}</span>
          </div>
          <span class="status-badge" :class="routeData.is_active ? 'active' : 'inactive'">
            {{ routeData.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>শুরু</label>
            <p>{{ routeData.start_point || '-' }}</p>
          </div>
          <div class="info-block">
            <label>গন্তব্য</label>
            <p>{{ routeData.end_point || '-' }}</p>
          </div>
          <div class="info-block">
            <label>দূরত্ব</label>
            <p>{{ routeData.distance_km ? routeData.distance_km + ' কি.মি.' : '-' }}</p>
          </div>
          <div class="info-block">
            <label>ভাড়া</label>
            <p>{{ routeData.fare ? formatCurrency(routeData.fare) : '-' }}</p>
          </div>
          <div class="info-block wide">
            <label>শুরুর সময়</label>
            <p>{{ routeData.start_time ? formatTime(routeData.start_time) : '-' }}</p>
          </div>
          <div class="info-block wide">
            <label>শেষ সময়</label>
            <p>{{ routeData.end_time ? formatTime(routeData.end_time) : '-' }}</p>
          </div>
        </div>

        <div v-if="routeData.buses?.length" class="buses-section">
          <div class="buses-header">
            <h3>এই রুটে বরাদ্দকৃত বাস</h3>
            <NuxtLink to="/transport/buses" class="btn btn-ghost btn-sm">সব বাস দেখুন</NuxtLink>
          </div>
          <div class="buses-list">
            <div v-for="bus in routeData.buses" :key="bus.id" class="bus-row">
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
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(true)
const routeData = ref<any>(null)
const showEdit = ref(false)
const saving = ref(false)

const editForm = reactive({
  route_name_bn: '',
  route_name_en: '',
  start_point: '',
  end_point: '',
  distance_km: 0,
  fare: 0,
  is_active: true,
})

function openEditModal() {
  if (!routeData.value) return
  const r = routeData.value
  editForm.route_name_bn = r.route_name_bn || ''
  editForm.route_name_en = r.route_name_en || ''
  editForm.start_point = r.start_point || ''
  editForm.end_point = r.end_point || ''
  editForm.distance_km = r.distance_km ? Number(r.distance_km) : 0
  editForm.fare = r.fare ? Number(r.fare) : 0
  editForm.is_active = r.is_active ?? true
  showEdit.value = true
}

async function saveEdit() {
  if (!editForm.route_name_bn.trim()) return
  saving.value = true
  try {
    const res = await api.put(`/transport/routes/${route.params.id}`, editForm)
    routeData.value = res.data?.data || { ...routeData.value, ...editForm }
    showEdit.value = false
    alert('রুটের তথ্য সফলভাবে আপডেট করা হয়েছে!')
  } catch (err: any) {
    console.error('Update route error:', err)
    alert(err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে')
  } finally {
    saving.value = false
  }
}

function load() {
  loading.value = true
  const id = Number(route.params.id)
  api.get(`/transport/routes/${id}`)
    .then(r => { routeData.value = r.data?.data })
    .catch(() => { routeData.value = null })
    .finally(() => { loading.value = false })
}

function formatCurrency(v: number) {
  return v ? 'টাকা ' + Number(v).toLocaleString('bn-BD') : ''
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
.page-header-row { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin-bottom:1.4rem; flex-wrap:wrap }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn) }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.5rem var(--font-bn) }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn) }
.header-actions { display:flex; gap:.5rem }
.loading-state { text-align:center; padding:3rem 0; display:flex; justify-content:center; gap:.5rem }
.empty-card { text-align:center; padding:3rem 0; display:flex; flex-direction:column; align-items:center; gap:.7rem }
.empty-icon { width:56px; height:56px; color:var(--color-text-muted); margin-bottom:.3rem }
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

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.modal-card { background: var(--color-bg-card, #fff); border-radius: 12px; width: 100%; max-width: 550px; overflow: hidden; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-muted); }
.modal-body { padding: 1.5rem; max-height: 75vh; overflow-y: auto; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 0.75rem; }
.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.85rem; font-weight: 600; }
.form-control { padding: 0.55rem 0.85rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); font-size: 0.9rem; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
</style>