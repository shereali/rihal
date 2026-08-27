<template>
  <div class="page-wrapper">
    <!-- Header Section -->
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>ছুটি ব্যবস্থাপনা</h1>
        <p class="page-subtitle">কর্মকর্তা, শিক্ষক ও কর্মচারীদের ছুটির আবেদন, অনুমোদন ও পরিসংখ্যান পরিচালনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন ছুটির আবেদন
        </button>
        <button class="btn btn-outline" @click="load">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Premium Stats Row -->
    <div class="stats-grid">
      <div class="stat-card stat-pending">
        <div class="stat-icon-wrap amber">
          <icon name="clock" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats?.pending ?? 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মুলতুবি আবেদন</span>
        </div>
      </div>

      <div class="stat-card stat-approved">
        <div class="stat-icon-wrap green">
          <icon name="check-circle" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats?.approved ?? 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">অনুমোদিত ছুটি</span>
        </div>
      </div>

      <div class="stat-card stat-rejected">
        <div class="stat-icon-wrap red">
          <icon name="close-circle" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats?.rejected ?? 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">প্রত্যাখ্যাত</span>
        </div>
      </div>

      <div class="stat-card stat-total">
        <div class="stat-icon-wrap blue">
          <icon name="users" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats?.total ?? 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">সর্বমোট আবেদন</span>
        </div>
      </div>
    </div>

    <!-- Premium Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input
          v-model="filters.search"
          placeholder="কর্মকর্তা বা আবেদনের শিরোনাম খুঁজুন..."
          @keyup.enter="load"
        />
        <button v-if="filters.search" class="clear-search-btn" @click="filters.search = ''; load()">×</button>
      </div>

      <div class="select-wrapper">
        <select v-model="filters.status" class="form-select" @change="load">
          <option value="">সব অবস্থা (All Status)</option>
          <option value="pending">মুলতুবি (Pending)</option>
          <option value="approved">অনুমোদিত (Approved)</option>
          <option value="rejected">প্রত্যাখ্যাত (Rejected)</option>
        </select>
      </div>

      <div class="select-wrapper">
        <select v-model="filters.leave_type" class="form-select" @change="load">
          <option value="">সব ধরনের ছুটি</option>
          <option value="ছুটি">সাধারণ ছুটি</option>
          <option value="রোগ">চিকিৎসা / অসুস্থতাজনিত</option>
          <option value="ব্যক্তিগত">ব্যক্তিগত কারণ</option>
          <option value="মাতৃত্ব">মাতৃত্বকালীন</option>
          <option value="সিহ্ব">হজ্জ / ওমরাহ ছুটি</option>
          <option value="অন্য">অন্যান্য</option>
        </select>
      </div>

      <div class="pagination-info" v-if="pagination?.total">
        মোট <span class="highlight">{{ pagination.total.toLocaleString('bn-BD') }}</span> টি আবেদন
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>ছুটির আবেদনসমূহ লোড হচ্ছে...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!leaves.length" class="empty-state card">
      <div class="empty-icon-wrap">
        <icon name="clipboard-list" />
      </div>
      <h3>কোনো ছুটির আবেদন পাওয়া যায়নি</h3>
      <p>নতুন আবেদন জমা দিয়ে ছুটি ব্যবস্থাপনা শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate">
        <icon name="plus" /> প্রথম ছুটির আবেদন জমা দিন
      </button>
    </div>

    <!-- Premium Table Card -->
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>কর্মকর্তা / কর্মী</th>
              <th>আবেদনের শিরোনাম</th>
              <th>ছুটির ধরন</th>
              <th>শুরু ও শেষ তারিখ</th>
              <th>মোট দিন</th>
              <th>জরুরি</th>
              <th>অবস্থা</th>
              <th>জমা দানের সময়</th>
              <th class="text-right">কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="leave in leaves" :key="leave.id" class="table-row">
              <td>
                <div class="user-cell">
                  <div class="user-avatar" :style="{ backgroundColor: userColor(leave.user_name_bn || leave.user_name || 'U') }">
                    {{ initials(leave.user_name_bn || leave.user_name) }}
                  </div>
                  <div class="user-info">
                    <span class="user-name">{{ leave.user_name_bn || leave.user_name }}</span>
                    <small class="user-email text-muted" v-if="leave.user_email">{{ leave.user_email }}</small>
                  </div>
                </div>
              </td>

              <td>
                <span class="leave-title">{{ leave.title_bn || leave.title }}</span>
              </td>

              <td>
                <span class="type-tag">{{ leave.leave_type || 'সাধারণ' }}</span>
              </td>

              <td>
                <div class="date-range">
                  <span class="date-start">{{ formatDate(leave.start_date) }}</span>
                  <icon name="arrow-right" class="range-arrow" />
                  <span class="date-end">{{ formatDate(leave.end_date) }}</span>
                </div>
              </td>

              <td>
                <span class="days-badge">{{ (leave.days_count || 1).toLocaleString('bn-BD') }} দিন</span>
              </td>

              <td>
                <span v-if="leave.is_urgent" class="urgent-pill">
                  <icon name="alert-circle" /> জরুরি
                </span>
                <span v-else class="text-muted">—</span>
              </td>

              <td>
                <span class="status-pill" :class="statusBadge(leave.status)">
                  <span class="status-dot" />
                  {{ statusText(leave.status) }}
                </span>
              </td>

              <td class="dimmed-date">
                {{ formatDate(leave.created_at) }}
              </td>

              <td class="text-right">
                <div class="row-actions">
                  <NuxtLink :to="`/leave-applications/${leave.id}`" class="action-btn view-btn" title="বিস্তারিত দেখুন">
                    <icon name="eye" />
                  </NuxtLink>
                  <button
                    v-if="leave.status === 'pending'"
                    class="action-btn approve-btn"
                    @click="quickApprove(leave)"
                    title="অনুমোদন করুন"
                  >
                    <icon name="check" />
                  </button>
                  <button
                    v-if="leave.status === 'pending'"
                    class="action-btn reject-btn"
                    @click="quickReject(leave)"
                    title="প্রত্যাখ্যান করুন"
                  >
                    <icon name="close" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="pagination && pagination.last_page > 1" class="pagination-footer">
        <button
          class="btn btn-outline btn-sm"
          :disabled="filters.page <= 1"
          @click="goPage(filters.page - 1)"
        >
          পূর্ববর্তী
        </button>
        <span class="page-indicator">
          পৃষ্ঠা {{ filters.page.toLocaleString('bn-BD') }} / {{ pagination.last_page.toLocaleString('bn-BD') }}
        </span>
        <button
          class="btn btn-outline btn-sm"
          :disabled="filters.page >= pagination.last_page"
          @click="goPage(filters.page + 1)"
        >
          পরবর্তী
        </button>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="closeCreate">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingLeave ? 'ছুটির আবেদন সম্পাদনা' : 'নতুন ছুটির আবেদন তৈরি' }}</h3>
            <p>কর্মকর্তা নির্বাচন করে ছুটির ধরণ, সময়সীমা ও বিবরণ পূরণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="closeCreate">×</button>
        </div>

        <form @submit.prevent="saveLeave" class="modal-form">
          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">কর্মকর্তা / কর্মী নির্বাচন *</label>
              <select v-model="form.user_id" class="form-select" required>
                <option value="0">কর্মকর্তা নির্বাচন করুন</option>
                <option v-for="u in users" :key="u.id" :value="u.id">
                  {{ u.name_bn || u.name }} — {{ u.email }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">ছুটির ধরন *</label>
              <select v-model="form.leave_type" class="form-select" required>
                <option value="ছুটি">সাধারণ ছুটি</option>
                <option value="রোগ">চিকিৎসা / অসুস্থতাজনিত</option>
                <option value="ব্যক্তিগত">ব্যক্তিগত কারণ</option>
                <option value="মাতৃত্ব">মাতৃত্বকালীন</option>
                <option value="সিহ্ব">হজ্জ / ওমরাহ ছুটি</option>
                <option value="অনুপস্থিতি">অনুপস্থিতি</option>
                <option value="অন্য">অন্যান্য</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">আবেদনের শিরোনাম *</label>
              <input
                v-model="form.title_bn"
                type="text"
                class="form-input"
                required
                placeholder="যেমন: চিকিৎসা সংক্রান্ত ছুটি"
              />
            </div>

            <div class="form-group">
              <label class="form-label">শুরুর তারিখ *</label>
              <input v-model="form.start_date" type="date" class="form-input" required />
            </div>

            <div class="form-group">
              <label class="form-label">শেষ তারিখ *</label>
              <input v-model="form.end_date" type="date" class="form-input" required />
            </div>

            <div class="form-group wide">
              <label class="form-label">ছুটির কারণ ও বিস্তারিত বিবরণ *</label>
              <textarea
                v-model="form.description_bn"
                class="form-textarea"
                rows="3"
                required
                placeholder="ছুটির প্রয়োজনীয়তা ও কারণ বিশদভাবে লিখুন..."
              ></textarea>
            </div>

            <div class="form-group wide">
              <div class="checkboxes-row">
                <label class="custom-checkbox">
                  <input type="checkbox" v-model="form.is_urgent" />
                  <span class="checkmark" />
                  <span class="checkbox-text">জরুরি আবেদন (Urgent Application)</span>
                </label>
                <label class="custom-checkbox">
                  <input type="checkbox" v-model="form.is_active" checked />
                  <span class="checkmark" />
                  <span class="checkbox-text">সক্রিয় রেকর্ড</span>
                </label>
              </div>
            </div>

            <div class="form-group wide">
              <label class="form-label">অভ্যন্তরীণ নোট (ঐচ্ছিক)</label>
              <input
                v-model="form.notes"
                type="text"
                class="form-input"
                placeholder="অতিরিক্ত কোনো প্রাসঙ্গিক নোট..."
              />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="closeCreate">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingLeave ? 'আপডেট করুন' : 'আবেদন জমা দিন') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRouter } from 'vue-router'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const router = useRouter()

const leaves = ref<any[]>([])
const loading = ref(false)
const pagination = ref<any>(null)
const stats = ref<any>({})
const users = ref<any[]>([])
const showCreate = ref(false)
const editingLeave = ref<any>(null)
const saving = ref(false)

const filters = reactive({
  search: '',
  status: '',
  leave_type: '',
  page: 1,
  per_page: 15,
})

const form = reactive({
  user_id: 0,
  leave_type: 'ছুটি',
  title_bn: '',
  title: '',
  description_bn: '',
  start_date: '',
  end_date: '',
  notes: '',
  is_urgent: false,
  is_active: true,
})

async function load() {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: String(filters.page),
      per_page: String(filters.per_page),
      ...(filters.search && { search: filters.search }),
      ...(filters.status && { status: filters.status }),
      ...(filters.leave_type && { leave_type: filters.leave_type }),
    })
    const [res, overview, userRes] = await Promise.all([
      api.get('/leave-applications?' + params.toString()),
      api.get('/leave-applications/stats').catch(() => ({ data: {} })),
      api.get('/users?per_page=100').catch(() => ({ data: { data: [] } })),
    ])
    leaves.value = res.data?.data || []
    pagination.value = res.data?.meta || null
    stats.value = overview.data || {}
    users.value = userRes.data?.data || []
  } catch (e: any) {
    console.error('Failed to load leaves:', e)
    leaves.value = []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingLeave.value = null
  Object.assign(form, {
    user_id: users.value[0]?.id || 0,
    leave_type: 'ছুটি',
    title_bn: '',
    title: '',
    description_bn: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    notes: '',
    is_urgent: false,
    is_active: true,
  })
  showCreate.value = true
}

function closeCreate() {
  showCreate.value = false
  editingLeave.value = null
}

async function saveLeave() {
  if (!form.user_id) {
    alert('অনুগ্রহ করে কর্মকর্তা নির্বাচন করুন')
    return
  }
  saving.value = true
  try {
    if (editingLeave.value) {
      await api.put('/leave-applications/' + editingLeave.value.id, form)
    } else {
      await api.post('/leave-applications', form)
    }
    closeCreate()
    await load()
  } catch (e: any) {
    alert('সংরক্ষণে ত্রুটি: ' + (e.response?.data?.message || 'অজানা ত্রুটি'))
  } finally {
    saving.value = false
  }
}

async function quickApprove(leave: any) {
  if (!confirm((leave.user_name_bn || 'কর্মকর্তার') + ' ছুটির আবেদন অনুমোদন করবেন?')) return
  try {
    await api.put('/leave-applications/' + leave.id, { ...leave, status: 'approved' })
    await load()
  } catch (e) {
    console.error(e)
  }
}

async function quickReject(leave: any) {
  if (!confirm((leave.user_name_bn || 'কর্মকর্তার') + ' ছুটির আবেদন প্রত্যাখ্যান করবেন?')) return
  try {
    await api.put('/leave-applications/' + leave.id, { ...leave, status: 'rejected' })
    await load()
  } catch (e) {
    console.error(e)
  }
}

function goPage(p: number) {
  filters.page = p
  load()
}

function statusBadge(s: string) {
  const m: Record<string, string> = {
    pending: 'badge-pending',
    approved: 'badge-approved',
    rejected: 'badge-rejected',
    cancelled: 'badge-cancelled',
  }
  return m[s] || 'badge-pending'
}

function statusText(s: string) {
  const m: Record<string, string> = {
    pending: 'মুলতুবি',
    approved: 'অনুমোদিত',
    rejected: 'প্রত্যাখ্যাত',
    cancelled: 'বাতিল',
  }
  return m[s] || s || 'অজানা'
}

function formatDate(date: string) {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return date
  }
}

function initials(name: string) {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
}

function userColor(name: string) {
  const colors = ['#145032', '#0d7a5f', '#1b6b93', '#b45309', '#0284c7', '#7c3aed', '#db2777']
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colors[Math.abs(hash) % colors.length]
}

onMounted(() => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
  load()
})
</script>

<style scoped>
.page-wrapper {
  max-width: 1320px;
  margin: 0 auto;
  padding: 1.75rem;
}

/* Page Header */
.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-primary);
  letter-spacing: 0.08em;
}

.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0.2rem 0 0.35rem;
  color: var(--color-text);
}

.page-subtitle {
  color: var(--color-text-light);
  font-size: 0.88rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

/* Stats Row */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1.1rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
}

.stat-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}

.stat-icon-wrap.amber { background: rgba(245, 158, 11, 0.12); color: #d97706; }
.stat-icon-wrap.green { background: rgba(16, 185, 129, 0.12); color: #059669; }
.stat-icon-wrap.red   { background: rgba(239, 68, 68, 0.12);  color: #dc2626; }
.stat-icon-wrap.blue  { background: rgba(59, 130, 246, 0.12); color: #2563eb; }

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.6rem;
  font-weight: 800;
  line-height: 1.1;
  color: var(--color-text);
}

.stat-label {
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--color-text-light);
  margin-top: 0.25rem;
}

/* Toolbar */
.toolbar {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.9rem 1.25rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: 12px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 0.5rem 0.85rem;
  flex: 1;
  min-width: 250px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-box:focus-within {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.1);
}

.search-icon {
  color: var(--color-text-light);
  font-size: 1rem;
}

.search-box input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 0.9rem;
  color: var(--color-text);
  outline: none;
}

.clear-search-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0 0.2rem;
}

.select-wrapper {
  position: relative;
}

.form-select {
  padding: 0.55rem 1rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.86rem;
  font-weight: 500;
  cursor: pointer;
  outline: none;
  transition: border-color 0.2s ease;
}

.form-select:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.1);
}

.pagination-info {
  margin-left: auto;
  font-size: 0.85rem;
  color: var(--color-text-light);
}

.pagination-info .highlight {
  font-weight: 700;
  color: var(--color-primary);
}

/* Table Card */
.table-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.table-responsive {
  overflow-x: auto;
}

.premium-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.88rem;
}

.premium-table th {
  padding: 0.95rem 1.1rem;
  background: rgba(0, 0, 0, 0.02);
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--color-text-light);
  border-bottom: 1px solid var(--color-border-light);
  white-space: nowrap;
}

.premium-table td {
  padding: 0.95rem 1.1rem;
  border-bottom: 1px solid var(--color-border-light);
  vertical-align: middle;
}

.table-row {
  transition: background 0.15s ease;
}

.table-row:hover {
  background: rgba(20, 80, 50, 0.025);
}

/* User Cell */
.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
  flex-shrink: 0;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 700;
  color: var(--color-text);
}

.user-email {
  font-size: 0.75rem;
}

.leave-title {
  font-weight: 600;
  color: var(--color-text);
}

.type-tag {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  background: rgba(20, 80, 50, 0.07);
  color: var(--color-primary);
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.82rem;
}

.date-start, .date-end {
  font-weight: 500;
}

.range-arrow {
  color: var(--color-text-light);
  font-size: 0.75rem;
}

.days-badge {
  display: inline-block;
  padding: 0.15rem 0.55rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--color-text);
}

.urgent-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.2rem 0.6rem;
  background: rgba(239, 68, 68, 0.12);
  color: #dc2626;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

/* Status Pills */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.badge-pending {
  background: rgba(245, 158, 11, 0.12);
  color: #b45309;
}
.badge-pending .status-dot { background: #f59e0b; }

.badge-approved {
  background: rgba(16, 185, 129, 0.12);
  color: #059669;
}
.badge-approved .status-dot { background: #10b981; }

.badge-rejected {
  background: rgba(239, 68, 68, 0.12);
  color: #dc2626;
}
.badge-rejected .status-dot { background: #ef4444; }

.badge-cancelled {
  background: rgba(107, 114, 128, 0.12);
  color: #4b5563;
}
.badge-cancelled .status-dot { background: #6b7280; }

.dimmed-date {
  font-size: 0.8rem;
  color: var(--color-text-light);
}

/* Action Buttons */
.row-actions {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  justify-content: flex-end;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid var(--color-border-light);
  background: var(--color-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-text-light);
  transition: all 0.15s ease;
  text-decoration: none;
}

.action-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--color-text);
  transform: translateY(-1px);
}

.action-btn.view-btn:hover {
  color: var(--color-primary);
  border-color: var(--color-primary);
  background: rgba(20, 80, 50, 0.08);
}

.action-btn.approve-btn:hover {
  color: #059669;
  border-color: #059669;
  background: rgba(16, 185, 129, 0.12);
}

.action-btn.reject-btn:hover {
  color: #dc2626;
  border-color: #dc2626;
  background: rgba(239, 68, 68, 0.12);
}

/* Pagination Footer */
.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.95rem 1.25rem;
  background: rgba(0, 0, 0, 0.01);
  border-top: 1px solid var(--color-border-light);
}

.page-indicator {
  font-size: 0.85rem;
  color: var(--color-text-light);
  font-weight: 500;
}

/* Buttons */
.btn {
  padding: 0.6rem 1.15rem;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #145032 0%, #1a6b43 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35);
}

.btn-outline {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.btn-ghost {
  background: transparent;
  color: var(--color-text);
}

.btn-ghost:hover {
  background: rgba(0, 0, 0, 0.05);
}

.btn-sm {
  padding: 0.4rem 0.85rem;
  font-size: 0.8rem;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* Modal Dialog */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1.25rem;
  animation: fadeIn 0.2s ease;
}

.modal-card {
  background: var(--color-bg-card);
  border-radius: 16px;
  width: 100%;
  max-width: 620px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
  border: 1px solid var(--color-border-light);
  animation: scaleUp 0.25s ease;
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes scaleUp { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light);
}

.modal-title-group h3 {
  font-size: 1.2rem;
  font-weight: 800;
  margin: 0 0 0.2rem;
}

.modal-title-group p {
  font-size: 0.82rem;
  color: var(--color-text-light);
  margin: 0;
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-light);
  padding: 0;
  line-height: 1;
}

.modal-close-btn:hover {
  color: var(--color-text);
}

.modal-form {
  padding: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-group.wide {
  grid-column: 1 / -1;
}

.form-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--color-text);
}

.form-input, .form-textarea {
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input:focus, .form-textarea:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.12);
}

.checkboxes-row {
  display: flex;
  gap: 1.5rem;
  padding: 0.5rem 0;
}

.custom-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
}

.custom-checkbox input {
  accent-color: var(--color-primary);
  width: 16px;
  height: 16px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--color-border-light);
}

/* States */
.empty-state, .loading-state {
  padding: 3.5rem 1.5rem;
  text-align: center;
  color: var(--color-text-light);
  border-radius: 14px;
}

.empty-icon-wrap {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1rem;
}

.empty-state h3 {
  font-size: 1.2rem;
  margin: 0 0 0.35rem;
  color: var(--color-text);
}

.empty-state p {
  font-size: 0.88rem;
  margin: 0 0 1.25rem;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 0.85rem;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>