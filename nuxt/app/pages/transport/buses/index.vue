<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">যাতায়াত ব্যবস্থাপনা</span>
        <h1>বাসের তথ্য</h1>
        <p>হোস্টেল/মাদ্রাসা যাতায়াতের যানবাহন, চালক ও তাদের ডকুমেন্ট</p>
      </div>
      <button class="btn btn-primary" @click="showForm = !showForm">
        <icon name="plus" /> নতুন বাস
      </button>
    </div>

    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" />
        <input v-model="search" placeholder="বাস নম্বর বা রুট খুঁজুন..." @keyup.enter="load" />
      </div>
      <select v-model="routeFilter" class="form-control compact" @change="load">
        <option value="">সব রুট</option>
        <option v-for="r in routeOptions" :key="r.id" :value="r.id">{{ r.route_name_bn }}</option>
      </select>
      <select v-model="statusFilter" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="active">সক্রিয়</option>
        <option value="inactive">নিষ্ক্রিয়</option>
      </select>
      <button class="btn btn-outline btn-sm" @click="load">
        <icon name="refresh" /> রিফ্রেশ
      </button>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createBus">
      <div class="form-heading">
        <div>
          <h2>নতুন যানবাহন যোগ করুন</h2>
          <p>বাসের তথ্য, রুট, চালক ও ডকুমেন্ট পূরণ করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-grid">
        <div class="form-group wide">
          <label>বাস নম্বর *</label>
          <input v-model="form.bus_number" class="form-control" required placeholder="যেমন: মাদ্রাসা-১২" />
        </div>
        <div class="form-group">
          <label>রুট</label>
          <select v-model="form.route_id" class="form-control">
            <option value="">নির্বাচন করুন</option>
            <option v-for="r in routeOptions" :key="r.id" :value="r.id">{{ r.route_name_bn }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>চালক</label>
          <select v-model="form.driver_id" class="form-control">
            <option value="">নির্বাচন করুন</option>
            <option v-for="d in driverOptions" :key="d.id" :value="d.id">{{ d.name_bn || d.name_en }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>ধারণক্ষমতা</label>
          <input v-model.number="form.capacity" type="number" class="form-control" min="1" placeholder="উদাহরণ: ৪০" />
        </div>
        <div class="form-group">
          <label>যানবাহনের ধরণ</label>
          <input v-model="form.vehicle_type" class="form-control" placeholder="যেমন: মিনিবাস/ডবল ডেক" />
        </div>
        <div class="form-group">
          <label>রেজিস্ট্রেশন নম্বর</label>
          <input v-model="form.registration_number" class="form-control" placeholder="যেমন: ঢা-বি-১২৩৪" />
        </div>
        <div class="form-group">
          <label>বীমা শেষ তারিখ</label>
          <input v-model="form.insurance_expiry" type="date" class="form-control" />
        </div>
        <div class="form-group">
          <label>ফিটনেস শেষ তারিখ</label>
          <input v-model="form.fitness_expiry" type="date" class="form-control" />
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'বাস যোগ করুন' }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!buses.length" class="empty-card">
      <div class="empty-icon"><icon name="bus" /></div>
      <h3>এখনও কোনো বাস নেই</h3>
      <p>পরিবহন রুট তৈরি করে বাস যোগ করুন</p>
    </div>

    <div v-else class="buses-table">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>বাস নম্বর</th>
              <th>রুট</th>
              <th>চালক</th>
              <th>ধরণ</th>
              <th>ক্ষমতা</th>
              <th>বর্তমান ভিড়</th>
              <th>অবস্থা</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="bus in buses" :key="bus.id">
              <td class="bus-number-cell">
                <span class="bus-number-badge">{{ bus.bus_number }}</span>
              </td>
              <td>{{ bus.route?.route_name_bn || '-' }}</td>
              <td>{{ bus.driver?.name_bn || bus.driver?.name_en || '-' }}</td>
              <td class="text-muted">{{ bus.vehicle_type || '-' }}</td>
              <td class="text-center">{{ bus.capacity || '-' }}</td>
              <td class="text-center">
                <span class="occupancy-pill">{{ bus.current_occupancy || 0 }}/{{ bus.capacity || 0 }}</span>
                <div v-if="bus.capacity" class="mini-occupancy-bar">
                  <div class="mini-occupancy-fill" :style="{ width: bus.capacity ? ((bus.current_occupancy||0)/bus.capacity*100)+'%' : '0' }" />
                </div>
              </td>
              <td>
                <span class="status-badge" :class="bus.is_active ? 'active' : 'inactive'">
                  {{ bus.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                </span>
              </td>
              <td class="text-center">
                <NuxtLink :to="`/transport/buses/${bus.id}`" class="btn btn-outline btn-sm">
                  <icon name="eye" /> বিস্তারিত
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const buses = ref<any[]>([])
const routeOptions = ref<any[]>([])
const driverOptions = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const search = ref('')
const routeFilter = ref('')
const statusFilter = ref('')

interface BusForm {
  bus_number: string; route_id: string; driver_id: string
  capacity: number; vehicle_type: string; registration_number: string
  insurance_expiry: string; fitness_expiry: string; is_active: boolean
}

const form = reactive<BusForm>({
  bus_number: '', route_id: '', driver_id: '', capacity: 0,
  vehicle_type: '', registration_number: '', insurance_expiry: '', fitness_expiry: '',
  is_active: true,
})

async function load() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (routeFilter.value) q.set('route_id', routeFilter.value)
    if (statusFilter.value) q.set('is_active', statusFilter.value === 'active' ? 'true' : 'false')
    const [routesRes, driversRes, busesRes] = await Promise.all([
      api.get('/transport/routes'),
      api.get('/hr/staff?per_page=50'),
      api.get(`/transport/buses?${q}`),
    ])
    routeOptions.value = routesRes.data?.data?.data || routesRes.data?.data || []
    driverOptions.value = (driversRes.data?.data?.data || driversRes.data?.data || []).filter((d: any) => d.is_active !== false)
    buses.value = busesRes.data?.data?.data || busesRes.data?.data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function createBus() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/transport/buses', {
      ...form,
      capacity: form.capacity || undefined,
      is_active: form.is_active !== false,
    })
    showForm.value = false
    form.bus_number = ''; form.route_id = ''; form.driver_id = ''
    form.capacity = 0; form.vehicle_type = ''; form.registration_number = ''
    form.insurance_expiry = ''; form.fitness_expiry = ''; form.is_active = true
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'বাস যোগ করা যায়নি'
  } finally { saving.value = false }
}

onMounted(load)
</script>

<style scoped>
.module-page { max-width: 1200px; margin: 0 auto; padding-bottom: 2rem }
.page-header-row { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin-bottom:1.4rem }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn) }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.65rem var(--font-bn) }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn) }
.toolbar { display:flex; gap:.7rem; padding:.7rem; margin-bottom:1rem; flex-wrap:wrap }
.search-box { display:flex; align-items:center; gap:.5rem; flex:1; padding:0 .75rem; background:var(--color-bg-muted); border-radius:10px; min-width:180px }
.search-box input { width:100%; padding:.65rem 0; border:0; outline:0; background:transparent; font:.86rem var(--font-bn) }
.form-control.compact { width:140px; padding:.62rem .7rem }
.create-panel { padding:1.2rem; margin-bottom:1rem; border:1px solid var(--color-primary-100) }
.form-heading { display:flex; justify-content:space-between; margin-bottom:1rem }
.form-heading h2 { font:700 1rem var(--font-bn) }
.close-btn { border:0; background:transparent; font-size:1.5rem; color:var(--color-text-muted); cursor:pointer }
.form-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:.7rem }
.form-group label { display:block; margin-bottom:.3rem; font:600 .78rem var(--font-bn) }
.form-group.wide { grid-column:span 2 }
.form-actions { display:flex; gap:.6rem; margin-top:1rem }
.buses-table { background:#fff; border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.table-responsive { overflow-x:auto }
.table { width:100%; border-collapse:collapse; font:.82rem var(--font-bn) }
.table th { background:rgba(0,0,0,0.03); padding:.7rem 1rem; text-align:left; font:600 .75rem var(--font-bn); color:var(--color-text-muted); border-bottom:1px solid var(--color-border-light); white-space:nowrap }
.table td { padding:.6rem 1rem; border-bottom:1px solid var(--color-border-light); vertical-align:middle }
.table tr:last-child td { border-bottom:0 }
.table tr:hover td { background:#fafbfc }
.text-center { text-align:center }
.text-muted { color:var(--color-text-muted) }
.bus-number-cell { font-weight:600; color:var(--color-text) }
.bus-number-badge { display:inline-flex; align-items:center; padding:.15rem .5rem; background:var(--color-primary-100); border-radius:99px; font:600 .75rem var(--font-bn); color:var(--color-primary) }
.occupancy-pill { font:.65rem var(--font-bn); color:var(--color-text-muted); margin-bottom:.2rem; display:block }
.mini-occupancy-bar { height:4px; background:#e9ecef; border-radius:2px; margin-top:.2rem }
.mini-occupancy-fill { height:100%; background:var(--color-primary); border-radius:2px; }
.status-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.15rem .5rem; border-radius:99px; font:.65rem var(--font-bn); font-weight:600 }
.status-badge.active { background:#e6f4ec; color:#19724a }
.status-badge.inactive { background:#fde8e8; color:#a03030 }
@media(max-width:700px){ .page-header-row { align-items:flex-start; flex-direction:column } .toolbar { flex-wrap:wrap } .search-box { min-width:100% } .form-grid { grid-template-columns:1fr } .form-group.wide { grid-column:auto } }
</style>