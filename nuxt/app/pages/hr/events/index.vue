<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>আয়োজন</h1>
        <p>মাদ্রাসার আয়োজন, সম্মেলন, সেমিনার ও অনুষ্ঠান পরিচালনা করুন</p>
      </div>
      <button class="btn btn-primary" @click="showForm = !showForm">
        <icon name="plus" /> নতুন আয়োজন
      </button>
    </div>

    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" />
        <input v-model="search" placeholder="আয়োজনের নাম খুঁজুন..." @keyup.enter="load" />
      </div>
      <select v-model="statusFilter" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="upcoming">আসন্ন</option>
        <option value="ongoing">চলছে</option>
        <option value="completed">সম্পন্ন</option>
        <option value="cancelled">বাতিল</option>
      </select>
      <button class="btn btn-outline btn-sm" @click="load">
        <icon name="refresh" /> রিফ্রেশ
      </button>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createEvent">
      <div class="form-heading">
        <div>
          <h2>নতুন আয়োজন তৈরি করুন</h2>
          <p>আয়োজনের নাম, সময় ও অবস্থান পূরণ করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-grid">
        <div class="form-group wide">
          <label>আয়োজনের নাম *</label>
          <input v-model="form.name_bn" class="form-control" required placeholder="যেমন: বার্ষিক সাধারণ সভা ২০২৬" />
        </div>
        <div class="form-group">
          <label>শুরুর তারিখ *</label>
          <input v-model="form.start_date" type="date" class="form-control" required />
        </div>
        <div class="form-group">
          <label>শেষ তারিখ/সময়</label>
          <input v-model="form.end_date" type="datetime-local" class="form-control" />
        </div>
        <div class="form-group">
          <label>অবস্থান</label>
          <input v-model="form.location_bn" class="form-control" placeholder="যেমন: মাদ্রাসার মেলা পর্দা" />
        </div>
        <div class="form-group wide">
          <label>বিবরণ</label>
          <textarea v-model="form.description_bn" class="form-control" rows="2" placeholder="আয়োজনের সংক্ষিপ্ত বর্ণনা..."></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'আয়োজন তৈরি করুন' }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!events.length" class="empty-card">
      <div class="empty-icon"><icon name="calendar" /></div>
      <h3>এখনও কোনো আয়োজন নেই</h3>
      <p>নতুন আয়োজন তৈরি করে আয়োজন তালিকা শুরু করুন</p>
    </div>

    <div v-else class="events-grid">
      <article v-for="event in events" :key="event.id" class="event-card">
        <div class="event-date-block">
          <span class="event-month">{{ formatMonth(event.start_date) }}</span>
          <span class="event-day">{{ formatDay(event.start_date) }}</span>
        </div>
        <div class="event-info">
          <h3 class="event-title">{{ event.name_bn || event.name_en }}</h3>
          <p v-if="event.description_bn" class="event-description">{{ event.description_bn }}</p>
          <p v-else class="text-muted">কোনো বিবরণ নেই</p>
          <div class="event-meta-row">
            <span v-if="event.location_bn"><icon name="building" /> {{ event.location_bn }}</span>
            <span v-if="event.start_date"><icon name="calendar" /> {{ formatDate(event.start_date) }}</span>
            <span v-if="event.end_date"><icon name="clock" /> {{ formatDate(event.end_date) }}</span>
          </div>
        </div>
        <div class="event-footer">
          <span class="event-status" :class="statusClass(event.status)">
            {{ statusLabel(event.status) }}
          </span>
          <NuxtLink :to="`/hr/events/${event.id}`" class="view-link">নাম তালিকা</NuxtLink>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const events = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const search = ref('')
const statusFilter = ref('')

interface EventForm {
  name_bn: string; name_en: string; start_date: string; end_date: string
  location_bn: string; description_bn: string
}

const form = reactive<EventForm>({
  name_bn: '', name_en: '', start_date: '', end_date: '',
  location_bn: '', description_bn: '',
})

async function load() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (statusFilter.value) q.set('status', statusFilter.value)
    const r = await api.get(`/hr/events?${q}`)
    events.value = r.data?.data?.data || r.data?.data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function createEvent() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/hr/events', form)
    showForm.value = false
    form.name_bn = ''; form.name_en = ''; form.start_date = ''
    form.end_date = ''; form.location_bn = ''; form.description_bn = ''
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'আয়োজন তৈরি করা যায়নি'
  } finally { saving.value = false }
}

function formatDate(v: string) {
  return v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
}

function formatMonth(v: string) {
  return v ? new Date(v).toLocaleDateString('bn-BD', { month: 'long' }) : ''
}

function formatDay(v: string) {
  return v ? new Date(v).getDate().toString() : ''
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    upcoming: 'আসন্ন',
    ongoing: 'চলছে',
    completed: 'সম্পন্ন',
    cancelled: 'বাতিল',
  }
  return map[s] || s || '-'
}

function statusClass(s: string) {
  if (s === 'ongoing') return 'status-ongoing'
  if (s === 'completed') return 'status-completed'
  if (s === 'cancelled') return 'status-cancelled'
  return 'status-upcoming'
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
.form-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:.7rem }
.form-group label { display:block; margin-bottom:.3rem; font:600 .78rem var(--font-bn) }
.form-group.wide { grid-column:span 2 }
.form-actions { display:flex; gap:.6rem; margin-top:1rem }
.events-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem }
.event-card { display:flex; flex-direction:column; gap:.6rem; padding:1.1rem; background:#fff; border:1px solid var(--color-border-light); border-radius:17px; box-shadow:var(--shadow-sm); transition:.2s }
.event-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-md) }
.event-date-block { display:flex; flex-direction:column; align-items:center; justify-content:center; width:50px; height:52px; border-radius:12px; background:var(--color-primary-100); margin-bottom:.5rem }
.event-month { font:.6rem var(--font-bn); color:var(--color-primary); text-transform:uppercase; font-weight:700 }
.event-day { font:700 1.5rem var(--font-bn); color:var(--color-primary); line-height:1 }
.event-info { flex:1; }
.event-title { margin:0; font:700 .95rem var(--font-bn); color:var(--color-text) }
.event-description { margin:0; font:.8rem var(--font-bn); color:var(--color-text-light); line-height:1.5 }
.event-meta-row { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.4rem; font:.72rem var(--font-bn); color:var(--color-text-muted) }
.event-meta-row span { display:inline-flex; align-items:center; gap:.3rem }
.event-footer { display:flex; justify-content:space-between; align-items:center; padding-top:.4rem; border-top:1px solid var(--color-border-light) }
.event-status { font:.68rem var(--font-bn); padding:.2rem .55rem; border-radius:99px }
.status-upcoming { background:#e6f4ec; color:#19724a }
.status-ongoing { background:#e3f2fa; color:#1a5276 }
.status-completed { background:#f0f0f0; color:#666 }
.status-cancelled { background:#fde8e8; color:#a03030 }
.view-link { font:600 .75rem var(--color-primary); text-decoration:none; display:inline-flex; align-items:center; gap:.3rem }
@media(max-width:1050px){ .events-grid { grid-template-columns:repeat(2,minmax(0,1fr)) } }
@media(max-width:700px){ .page-header-row { align-items:flex-start; flex-direction:column } .toolbar { flex-wrap:wrap } .search-box { min-width:100% } .form-grid { grid-template-columns:1fr } .form-group.wide { grid-column:auto } .events-grid { grid-template-columns:1fr } }
</style>