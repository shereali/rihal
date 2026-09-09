<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">যাতায়াত ব্যবস্থাপনা</span>
        <h1>শিক্ষার্থী যানবাহন বরাদ্দ</h1>
        <p>শিক্ষার্থীদের বাস ও পথ বরাদ্দ, পিকআপ ও ড্রপঅফ স্থল সংগঠন</p>
      </div>
      <button class="btn btn-primary" @click="showForm = !showForm">
        <icon name="plus" /> নতুন বরাদ্দ
      </button>
    </div>

    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" />
        <input v-model="search" placeholder="শিক্ষার্থীর নাম খুঁজুন..." @keyup.enter="load" />
      </div>
      <select v-model="statusFilter" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="active">সক্রিয়</option>
        <option value="inactive">নিষ্ক্রিয়</option>
        <option value="paused">অস্থায়ীভাবে বন্ধ</option>
      </select>
      <button class="btn btn-outline btn-sm" @click="load">
        <icon name="refresh" /> রিফ্রেশ
      </button>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createAssignment">
      <div class="form-heading">
        <div>
          <h2>নতুন যানবাহন বরাদ্দ করুন</h2>
          <p>শিক্ষার্থী নির্বাচন করে বাস, পথ ও পিকআপ স্থল নির্ধারণ করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-grid">
        <div class="form-group">
          <label>শিক্ষার্থী</label>
          <select v-model="form.student_id" class="form-control" :disabled="!studentOptions.length">
            <option value="">শিক্ষার্থী নির্বাচন করুন</option>
            <option v-for="s in studentOptions" :key="s.id" :value="s.id">
              {{ s.name_bn || s.name_en }} — {{ s.enrollment?.class?.name_bn || 'সব শ্রেণি' }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>পথ</label>
          <select v-model="form.route_id" class="form-control">
            <option value="">পথ নির্বাচন করুন</option>
            <option v-for="r in routeOptions" :key="r.id" :value="r.id">{{ r.route_name_bn }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>বাস</label>
          <select v-model="form.bus_id" class="form-control">
            <option value="">বাস নির্বাচন করুন</option>
            <option v-for="b in busOptions" :key="b.id" :value="b.id">{{ b.bus_number }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>পিকআপ স্থল</label>
          <input v-model="form.pickup_point" class="form-control" placeholder="যেমন: বাজার পুকুরের পাশ" />
        </div>
        <div class="form-group">
          <label>ড্রপঅফ স্থল</label>
          <input v-model="form.drop_point" class="form-control" placeholder="যেমন: মাদ্রাসার গेट" />
        </div>
        <div class="form-group">
          <label>পিকআপ সময়</label>
          <input v-model="form.pickup_time" type="time" class="form-control" />
        </div>
        <div class="form-group">
          <label>ড্রপঅফ সময়</label>
          <input v-model="form.drop_time" type="time" class="form-control" />
        </div>
        <div class="form-group">
          <label>ভাড়া (টাকা)</label>
          <input v-model.number="form.fare_amount" type="number" class="form-control" min="0" step="0.01" />
        </div>
        <div class="form-group">
          <label>অবস্থা</label>
          <select v-model="form.status" class="form-control">
            <option value="active">সক্রিয়</option>
            <option value="inactive">নিষ্ক্রিয়</option>
            <option value="paused">অস্থায়ীভাবে বন্ধ</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'বরাদ্দ করুন' }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!assignments.length" class="empty-card">
      <div class="empty-icon"><icon name="assignment" /></div>
      <h3>এখনও কোনো যানবাহন বরাদ্দ নেই</h3>
      <p>শিক্ষার্থীদের জন্য পথ ও বাস বরাদ্দ করুন</p>
    </div>

    <div v-else class="assignments-table">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>শিক্ষার্থী</th>
              <th>পিকআপ ভূমিকা</th>
              <th>পথ</th>
              <th>বাস</th>
              <th>পিকআপ স্থল</th>
              <th>ড্রপঅফ স্থল</th>
              <th>ভাড়া</th>
              <th>অবস্থা</th>
              <th>কর্ম</th>
              <th>পিকআপ সময়</th>
              <th>ড্রপঅফ সময়</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in assignments" :key="a.id">
              <td class="student-cell">
                <strong>{{ a.student_display_name }}</strong>
                <span class="text-muted">{{ a.student_class }}</span>
              </td>
              <td class="text-center">
                <span class="pickup-badge">
                  <icon name="map-marker" /> {{ a.pickup_point || '-' }}
                </span>
              </td>
              <td class="text-center">
                <span :class="{ 'route-highlight': a.route?.id === activeRouteId }" v-if="a.route">
                  {{ a.route.route_name_bn || a.route.route_name_en || '-' }}
                </span>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="bus-cell" v-if="a.bus">
                <span class="bus-badge">{{ a.bus.bus_number }}</span>
              </td>
              <td class="text-center">{{ a.pickup_point || '-' }}</td>
              <td class="text-center">{{ a.drop_point || '-' }}</td>
              <td class="text-center">{{ a.fare_amount ? 'টাকা ' + formatTk(a.fare_amount) : '-' }}</td>
              <td>
                <span class="status-badge" :class="statusClass(a.status)">
                  {{ statusLabel(a.status) }}
                </span>
              </td>
              <td>
                <button class="btn btn-ghost btn-sm" @click="undoAssignment(a.id)">
                  <icon name="undo" /> পূর্বাবস্থায়
                </button>
              </td>
              <td class="text-center">{{ a.pickup_time ? formatTime(a.pickup_time) : '-' }}</td>
              <td class="text-center">{{ a.drop_time ? formatTime(a.drop_time) : '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const studentOptions = ref<any[]>([])
const routeOptions = ref<any[]>([])
const busOptions = ref<any[]>([])
const assignments = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const search = ref('')
const statusFilter = ref('')

async function load() {
  loading.value = true
  try {
    const [students, routes, buses, ass] = await Promise.all([
      api.get('/students?per_page=50').catch(() => ({ data: { data: [] } })),
      api.get('/transport/routes').catch(() => ({ data: { data: [] } })),
      api.get('/transport/buses').catch(() => ({ data: { data: [] } })),
      api.get(`/transport/assignments?search=${search.value || ''}`),
    ])
    studentOptions.value = (students.data?.data?.data || students.data?.data || []).map((s: any) => ({
      id: s.id, name_bn: s.name_bn, name_en: s.name_en,
      enrollment: s.enrollment,
      class: s.enrollment?.class,
    }))
    routeOptions.value = routes.data?.data?.data || routes.data?.data || []
    busOptions.value = (buses.data?.data?.data || buses.data?.data || []).filter((b: any) => b.is_active !== false)
    assignments.value = (ass.data?.data?.data || ass.data?.data || []).map((a: any) => ({
      ...a,
      student_display_name: a.student?.name_bn || a.student?.user?.name_bn || a.student?.name_en || a.student?.user?.name_en || 'শিক্ষার্থী',
      student_class: a.student?.enrollment?.class?.name_bn || a.student?.class?.name_bn || '—',
    }))
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function createAssignment() {
  saving.value = true
  error.value = ''
  try {
    const payload: any = {
      student_id: parseInt(form.student_id),
      bus_id: parseInt(form.bus_id),
      route_id: parseInt(form.route_id),
      pickup_point: form.pickup_point,
      drop_point: form.drop_point,
      pickup_time: form.pickup_time,
      dropoff_time: form.dropoff_time,
      monthly_fee: form.fare_amount || 0,
      status: form.status,
    }
    await api.post('/transport/assignments', payload)
    showForm.value = false
    form.student_id = ''
    form.route_id = ''
    form.bus_id = ''
    form.pickup_point = ''
    form.drop_point = ''
    form.pickup_time = ''
    form.drop_time = ''
    form.fare_amount = 0
    form.status = 'active'
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'বরাদ্দ তৈরি করা যায়নি'
  } finally { saving.value = false }
}

async function undoAssignment(id: number) {
  if (!confirm('এই শিক্ষার্থীর যানবাহন বরাদ্দ বাতিল করতে চান?')) return
  try {
    await api.delete(`/transport/assignments/${id}`)
    await load()
  } catch (e) { console.error(e) }
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    active: 'সক্রিয়',
    inactive: 'নিষ্ক্রিয়',
    paused: 'অস্থায়ীভাবে বন্ধ',
  }
  return map[s] || s || '-'
}

function statusClass(s: string) {
  if (s === 'active') return 'status-active'
  if (s === 'paused') return 'status-paused'
  return 'status-inactive'
}

function formatTk(v: number) {
  return v ? '৳ ' + v.toLocaleString('bn-BD') : ''
}

function formatTime(v: string) {
  return v ? new Date(v).toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit' }) : '-'
}

onMounted(load)
</script>

<style scoped>
.module-page { max-width: 1400px; margin: 0 auto; padding-bottom: 2rem }
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
.form-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.7rem }
.form-group label { display:block; margin-bottom:.3rem; font:600 .78rem var(--font-bn) }
.form-actions { display:flex; gap:.6rem; margin-top:1rem }
.assignments-table { background:#fff; border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.table-responsive { overflow-x:auto }
.table { width:100%; border-collapse:collapse; font:.82rem var(--font-bn) }
.table th { background:rgba(0,0,0,0.03); padding:.7rem 1rem; text-align:left; font:600 .75rem var(--font-bn); color:var(--color-text-muted); border-bottom:1px solid var(--color-border-light); white-space:nowrap }
.table td { padding:.6rem 1rem; border-bottom:1px solid var(--color-border-light); vertical-align:middle }
.table tr:last-child td { border-bottom:0 }
.table tr:hover td { background:#fafbfc }
.text-center { text-align:center }
.text-muted { color:var(--color-text-muted) }
.student-cell { font-weight:600; color:var(--color-text) }
.pickup-badge { display:inline-flex; align-items:center; gap:.25rem; font:.7rem var(--font-bn); color:var(--color-text-muted); padding:.2rem .5rem; background:#f0f4f8; border-radius:99px }
.bus-cell { text-align:center }
.bus-badge { display:inline-flex; align-items:center; padding:.15rem .5rem; background:var(--color-primary-100); border-radius:99px; font:600 .75rem var(--font-bn); color:var(--color-primary) }
.route-highlight { color:var(--color-primary); font-weight:600 }
.status-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.15rem .5rem; border-radius:99px; font:.65rem var(--font-bn); font-weight:600 }
.status-active { background:#e6f4ec; color:#19724a }
.status-inactive { background:#fde8e8; color:#a03030 }
.status-paused { background:#fef3e2; color:#a07035 }
@media(max-width:700px){ .page-header-row { align-items:flex-start; flex-direction:column } .toolbar { flex-wrap:wrap } .search-box { min-width:100% } .form-grid { grid-template-columns:1fr } }
</style>