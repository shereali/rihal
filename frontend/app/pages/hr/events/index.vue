<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>আয়োজন ও অনুষ্ঠান ব্যবস্থাপনা</h1>
        <p>মাদ্রাসার আয়োজন, সম্মেলন, সেমিনার ও অনুষ্ঠান পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন আয়োজন
        </button>
        <button class="btn btn-outline" @click="load">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
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
    </div>

    <!-- Create / Edit Event Panel -->
    <form v-if="showForm" class="create-panel card" @submit.prevent="saveEvent">
      <div class="form-heading">
        <div>
          <h2>{{ editingId ? 'আয়োজন সম্পাদনা' : 'নতুন আয়োজন তৈরি করুন' }}</h2>
          <p>আয়োজনের নাম, সময়, অবস্থান ও বিবরণ পূরণ করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-grid">
        <div class="form-group wide">
          <label>আয়োজনের নাম (বাংলা) *</label>
          <input v-model="form.name_bn" class="form-control" required placeholder="যেমন: বার্ষিক সাধারণ সভা ২০২৬" />
        </div>
        <div class="form-group">
          <label>আয়োজনের নাম (ইংরেজি)</label>
          <input v-model="form.name_en" class="form-control" placeholder="e.g. Annual General Meeting 2026" />
        </div>
        <div class="form-group">
          <label>শুরুর তারিখ *</label>
          <input v-model="form.start_date" type="date" class="form-control" required />
        </div>
        <div class="form-group">
          <label>শেষ তারিখ / সময়</label>
          <input v-model="form.end_date" type="date" class="form-control" />
        </div>
        <div class="form-group">
          <label>অবস্থান / ভেন্যু</label>
          <input v-model="form.location_bn" class="form-control" placeholder="যেমন: মাদ্রাসার মূল মিলনায়তন" />
        </div>
        <div class="form-group">
          <label>অবস্থা</label>
          <select v-model="form.status" class="form-control">
            <option value="upcoming">আসন্ন</option>
            <option value="ongoing">চলছে</option>
            <option value="completed">সম্পন্ন</option>
            <option value="cancelled">বাতিল</option>
          </select>
        </div>
        <div class="form-group wide">
          <label>বিবরণ</label>
          <textarea v-model="form.description_bn" class="form-control" rows="2" placeholder="আয়োজনের সংক্ষিপ্ত বর্ণনা..."></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'আয়োজন তৈরি করুন') }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>আয়োজন তালিকা লোড হচ্ছে...</p></div>
    <div v-else-if="!events.length" class="empty-card">
      <div class="empty-icon"><icon name="calendar" /></div>
      <h3>এখনও কোনো আয়োজন নেই</h3>
      <p>নতুন আয়োজন তৈরি করে আয়োজন তালিকা শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate">প্রথম আয়োজন তৈরি করুন</button>
    </div>

    <div v-else class="events-grid">
      <article v-for="event in events" :key="event.id" class="event-card">
        <div class="event-date-block">
          <span class="event-month">{{ formatMonth(event.start_date) }}</span>
          <span class="event-day">{{ formatDay(event.start_date) }}</span>
        </div>
        <div class="event-info">
          <h3 class="event-title">{{ event.name_bn || event.name_en || event.title_bn || event.title }}</h3>
          <p v-if="event.description_bn" class="event-description">{{ event.description_bn }}</p>
          <p v-else class="text-muted">কোনো বিবরণ নেই</p>
          <div class="event-meta-row">
            <span v-if="event.location_bn"><icon name="building" /> {{ event.location_bn }}</span>
            <span v-if="event.start_date"><icon name="calendar" /> {{ formatDate(event.start_date) }}</span>
            <span v-if="event.end_date"><icon name="clock" /> শেষ: {{ formatDate(event.end_date) }}</span>
          </div>
        </div>
        <div class="event-footer">
          <span class="event-status" :class="statusClass(event.status)">
            {{ statusLabel(event.status) }}
          </span>
          <div class="action-buttons">
            <button class="btn-icon" @click="openEdit(event)" title="সম্পাদনা">
              <icon name="pencil" />
            </button>
            <button class="btn-icon text-danger" @click="deleteEvent(event.id)" title="মুছুন">
              <icon name="delete" />
            </button>
          </div>
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
const editingId = ref<number | null>(null)
const error = ref('')
const search = ref('')
const statusFilter = ref('')

const form = reactive({
  name_bn: '',
  name_en: '',
  start_date: '',
  end_date: '',
  location_bn: '',
  description_bn: '',
  status: 'upcoming',
})

async function load() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (statusFilter.value) q.set('status', statusFilter.value)
    const r = await api.get(`/hr/events?${q.toString()}`)
    events.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  error.value = ''
  form.name_bn = ''
  form.name_en = ''
  form.start_date = new Date().toISOString().split('T')[0]
  form.end_date = ''
  form.location_bn = ''
  form.description_bn = ''
  form.status = 'upcoming'
  showForm.value = true
}

function openEdit(e: any) {
  editingId.value = e.id
  error.value = ''
  form.name_bn = e.name_bn || e.title_bn || ''
  form.name_en = e.name_en || e.title || ''
  form.start_date = e.start_date || e.date || ''
  form.end_date = e.end_date || ''
  form.location_bn = e.location_bn || e.location || ''
  form.description_bn = e.description_bn || ''
  form.status = e.status || 'upcoming'
  showForm.value = true
}

async function saveEvent() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/hr/events/${editingId.value}`, form)
    } else {
      await api.post('/hr/events', form)
    }
    showForm.value = false
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'আয়োজন সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function deleteEvent(id: number) {
  if (!confirm('এই আয়োজন মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/hr/events/${id}`)
    await load()
  } catch (e) {
    console.error(e)
  }
}

function formatMonth(dateStr: string) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('bn-BD', { month: 'short' })
  } catch {
    return ''
  }
}

function formatDay(dateStr: string) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('bn-BD', { day: 'numeric' })
  } catch {
    return ''
  }
}

function formatDate(dateStr: string) {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return dateStr
  }
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    upcoming: 'আসন্ন',
    ongoing: 'চলছে',
    completed: 'সম্পন্ন',
    cancelled: 'বাতিল',
  }
  return map[s] || s || 'আসন্ন'
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
.module-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--color-primary);
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.toolbar {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  padding: 0.85rem 1.25rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.45rem 0.75rem;
  flex: 1;
  min-width: 200px;
}

.search-box input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 0.9rem;
  color: var(--color-text);
}

.search-box input:focus { outline: none; }

.form-control.compact {
  padding: 0.45rem 0.75rem;
  font-size: 0.85rem;
  min-width: 150px;
}

.create-panel {
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border: 1px solid var(--color-primary-light);
}

.form-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--color-border-light);
}

.form-heading h2 { font-size: 1.15rem; margin: 0; }
.form-heading p { font-size: 0.8rem; color: var(--color-text-light); margin: 0.2rem 0 0; }

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-light);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
}

.form-group.wide { grid-column: 1 / -1; }
.form-group label { display: block; font-size: 0.82rem; font-weight: 500; margin-bottom: 0.35rem; }

.form-control {
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.9rem;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
  justify-content: flex-end;
}

.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

.event-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: var(--shadow-sm);
  transition: transform 0.15s ease;
}

.event-card:hover {
  transform: translateY(-2px);
}

.event-date-block {
  position: absolute;
  top: 1.25rem;
  right: 1.25rem;
  background: rgba(20, 80, 50, 0.08);
  border-radius: 8px;
  padding: 0.4rem 0.65rem;
  text-align: center;
  min-width: 48px;
}

.event-month {
  display: block;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--color-primary);
}

.event-day {
  display: block;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--color-text);
}

.event-info {
  margin-right: 55px;
  margin-bottom: 1rem;
}

.event-title {
  font-size: 1.05rem;
  margin: 0 0 0.4rem;
}

.event-description {
  font-size: 0.82rem;
  color: var(--color-text-light);
  line-height: 1.4;
  margin: 0 0 0.75rem;
}

.event-meta-row {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  font-size: 0.78rem;
  color: var(--color-text-light);
}

.event-meta-row span {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.event-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 0.75rem;
  border-top: 1px solid var(--color-border-light);
}

.event-status {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
}

.event-status.status-upcoming { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.event-status.status-ongoing { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.event-status.status-completed { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.event-status.status-cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

.action-buttons { display: flex; gap: 0.35rem; }
.btn-icon { background: transparent; border: none; cursor: pointer; padding: 0.3rem; border-radius: 4px; color: var(--color-text-light); }
.btn-icon:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); }
.btn-icon.text-danger:hover { color: var(--color-error); background: rgba(220, 38, 38, 0.1); }

.btn {
  padding: 0.55rem 1.1rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
}

.btn-primary { background: var(--color-primary); color: #fff; }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-ghost { background: transparent; color: var(--color-text); }

.card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
}

.empty-card, .loading-state {
  padding: 3rem;
  text-align: center;
  color: var(--color-text-light);
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
}

.empty-icon { font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0.5rem; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 0.75rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 0.65rem 0.9rem; border-radius: var(--radius-sm); margin-bottom: 1rem; font-size: 0.85rem; }
.alert-error { background: #fce4e4; color: var(--color-error); }
</style>