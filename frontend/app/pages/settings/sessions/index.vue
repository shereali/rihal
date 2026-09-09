<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <h1>একাডেমিক সেশন</h1>
        <p class="page-subtitle">শিক্ষাবর্ষ, সেশন ও পরীক্ষা সেশন তৈরি ও ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="showForm = !showForm">
          <Icon name="plus" class="btn-icon" /> নতুন সেশন
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>সেশন তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!sessions.length" class="empty-card">
      <div class="empty-icon"><Icon name="calendar" /></div>
      <h3>এখনও কোনো সেশন নেই</h3>
      <p>একাডেমিক সেশন তৈরি করে শুরু করুন</p>
    </div>

    <div v-else class="sessions-table">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>সেশনের নাম</th>
              <th>শুরুর তারিখ</th>
              <th>শেষ তারিখ</th>
              <th>অবস্থা</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in sessions" :key="s.id">
              <td class="session-name">
                <strong>{{ s.session_bn || s.session_name }}</strong>
                <span class="text-muted">{{ s.session_name }}</span>
              </td>
              <td class="text-center">{{ formatDate(s.start_date) }}</td>
              <td class="text-center">{{ formatDate(s.end_date) }}</td>
              <td>
                <span class="status-badge" :class="statusClass(s.status)">
                  {{ statusLabel(s.status) }}
                </span>
                <span v-if="s.is_active" class="active-mark">সক্রিয়</span>
              </td>
              <td class="text-center">
                <NuxtLink :to="`/settings/sessions/${s.id}`" class="btn btn-ghost btn-sm">
                  <Icon name="eye" class="btn-icon" /> দেখুন
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination" v-if="meta">
        <span class="text-muted">{{ (meta.current_page - 1) * meta.per_page + 1 }}–{{ Math.min(meta.current_page * meta.per_page, meta.total) }} / {{ meta.total }}</span>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" v-if="showForm" @click.self="showForm = false">
      <div class="modal card">
        <div class="modal-header">
          <h2>{{ isEditing ? 'সেশন সম্পাদনা' : 'নতুন সেশন তৈরি' }}</h2>
          <button class="close-btn" @click="showForm = false">×</button>
        </div>
        <form @submit.prevent="saveSession" class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label>সেশনের নাম (ইংরেজি) *</label>
              <input v-model="form.session_name" class="form-control" placeholder="2024-2025" />
            </div>
            <div class="form-group">
              <label>সেশনের নাম (বাংলা)</label>
              <input v-model="form.session_bn" class="form-control" placeholder="২০২৪-২০২৫ শিক্ষাবর্ষ" />
            </div>
            <div class="form-group">
              <label>শুরুর তারিখ *</label>
              <input v-model="form.start_date" type="date" class="form-control" />
            </div>
            <div class="form-group">
              <label>শেষ তারিখ *</label>
              <input v-model="form.end_date" type="date" class="form-control" />
            </div>
            <div class="form-group">
              <label>অবস্থা</label>
              <select v-model="form.status" class="form-control">
                <option value="active">সক্রিয়</option>
                <option value="inactive">নিষ্ক্রিয়</option>
                <option value="upcoming">আগামী</option>
                <option value="completed">সমাপ্ত</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (isEditing ? 'আপডেট করুন' : 'তৈরি করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const api = useApiClient()
const sessions = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const isEditing = ref(false)
const currentId = ref<number | null>(null)
const error = ref('')

const form = reactive({
  session_name: '',
  session_bn: '',
  start_date: '',
  end_date: '',
  status: 'active',
})

const meta = ref<any>(null)

async function load() {
  loading.value = true
  try {
    const res = await api.get('/settings/sessions?per_page=50')
    sessions.value = res.data?.data?.data || []
    meta.value = res.data?.data?.meta
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function saveSession() {
  saving.value = true
  error.value = ''
  try {
    if (isEditing.value && currentId.value) {
      await api.put(`/settings/sessions/${currentId.value}`, {
        session_name: form.session_name,
        session_bn: form.session_bn,
        start_date: form.start_date,
        end_date: form.end_date,
        status: form.status,
      })
    } else {
      await api.post('/settings/sessions', {
        session_name: form.session_name,
        session_bn: form.session_bn,
        start_date: form.start_date,
        end_date: form.end_date,
        status: form.status,
      })
    }
    showForm.value = false
    resetForm()
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

function resetForm() {
  form.session_name = ''
  form.session_bn = ''
  form.start_date = ''
  form.end_date = ''
  form.status = 'active'
  currentId.value = null
  isEditing.value = false
}

function formatDate(d: string | undefined) {
  if (!d) return '-'
  try { return new Date(d).toLocaleDateString('bn-BD', { day: '2-digit', month: 'short', year: 'numeric' }) } catch { return d }
}

function statusClass(s: string) {
  if (s === 'active') return 'status-active'
  if (s === 'completed') return 'status-completed'
  if (s === 'upcoming') return 'status-upcoming'
  return 'status-inactive'
}

function statusLabel(s: string) {
  const map: Record<string, string> = { active: 'সক্রিয়', inactive: 'নিষ্ক্রিয়', upcoming: 'আগামী', completed: 'সমাপ্ত' }
  return map[s] || s
}

onMounted(load)
</script>

<style scoped>
.page-wrapper { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.5rem; }
.btn { padding: 0.55rem 1.1rem; border-radius: 10px; font-family: var(--font-bn); font-weight: 600; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; border: 1px solid var(--color-border); background: white; color: var(--color-text); transition: all 0.15s ease; }
.btn:hover { background: var(--color-bg-muted); }
.btn-primary { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.btn-primary:hover { background: var(--color-primary-dark); }
.btn-ghost { background: transparent; border-color: var(--color-border-light); color: var(--color-text); }
.btn-icon { width: 15px; height: 15px; }
.btn-sm { padding: 0.35rem 0.7rem; font-size: 0.75rem; }
.loading-overlay { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem; gap: 1rem; }
.spinner { width: 36px; height: 36px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-card { background: white; border: 1px solid var(--color-border-light); border-radius: 14px; padding: 3rem; text-align: center; }
.empty-icon { width: 56px; height: 56px; color: var(--color-primary-400); margin: 0 auto 1rem; }
.empty-card h3 { font-family: var(--font-bn); font-size: 1.1rem; color: var(--color-text); margin: 0 0 0.3rem; }
.empty-card p { font-family: var(--font-bn); font-size: 0.82rem; color: var(--color-text-muted); margin: 0; }
.sessions-table { background: white; border: 1px solid var(--color-border-light); border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-family: var(--font-bn); font-size: 0.82rem; }
.data-table th { background: var(--color-bg-muted); padding: 0.7rem 1rem; text-align: left; font-weight: 600; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-light); white-space: nowrap; }
.data-table td { padding: 0.6rem 1rem; border-bottom: 1px solid var(--color-border-light); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: 0; }
.data-table tr:hover td { background: var(--color-bg-muted); }
.text-center { text-align: center; }
.text-muted { color: var(--color-text-muted); }
.session-name { font-weight: 600; color: var(--color-text); }
.session-name .text-muted { margin-left: 0.5rem; font-weight: 400; font-size: 0.75rem; }
.status-badge { display: inline-flex; align-items: center; padding: 0.15rem 0.5rem; border-radius: 99px; font-size: 0.65rem; font-weight: 600; }
.status-active { background: #e6f4ec; color: #19724a; }
.status-inactive { background: #fde8e8; color: #a03030; }
.status-upcoming { background: #e6f0ff; color: #2563eb; }
.status-completed { background: var(--color-bg-muted); color: var(--color-text-muted); }
.active-mark { font-size: 0.65rem; color: var(--color-primary); margin-left: 0.4rem; font-weight: 600; }
.pagination { padding: 0.7rem 1rem; background: var(--color-bg-muted); display: flex; align-items: center; font-size: 0.78rem; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.2rem; border-bottom: 1px solid var(--color-border-light); }
.modal-header h2 { font-family: var(--font-bn); font-size: 1.1rem; margin: 0; }
.close-btn { border: 0; background: transparent; font-size: 1.5rem; color: var(--color-text-muted); cursor: pointer; }
.modal-body { padding: 1.2rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group label { font-family: var(--font-bn); font-size: 0.78rem; font-weight: 600; color: var(--color-text); }
.form-control { padding: 0.55rem 0.7rem; border: 1px solid var(--color-border); border-radius: 8px; font-family: var(--font-bn); font-size: 0.82rem; outline: none; transition: border 0.15s; }
.form-control:focus { border-color: var(--color-primary); }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 1rem; padding-top: 0.8rem; border-top: 1px solid var(--color-border-light); }
</style>