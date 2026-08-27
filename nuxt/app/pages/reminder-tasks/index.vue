<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>রিমাইন্ডার টাস্ক</h1>
        <p class="page-subtitle">শিক্ষার্থী, অভিভাবক ও কর্মকর্তা-কর্মচারীদের জন্য স্মরণী বার্তা ও অটোমেটেড নোটিফিকেশন পরিচালনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate"><icon name="plus" /> নতুন টাস্ক তৈরি</button>
        <button class="btn btn-outline" @click="load"><icon name="refresh" /> রিফ্রেশ</button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid" v-if="stats">
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats.pending || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মুলতুবি টাস্ক</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats.sent || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">পাঠানো সম্পন্ন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="calendar" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats.scheduled || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">নির্ধারিত সময়সূচী</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="repeat" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (stats.recurring || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">পুনরাবৃত্তিমূলক টাস্ক</span>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="filters.search" placeholder="টাস্কের নাম বা বার্তা খুঁজুন..." @keyup.enter="load" />
        <button v-if="filters.search" class="clear-search-btn" @click="filters.search = ''; load()">×</button>
      </div>
      <select v-model="filters.type" class="form-select" @change="load">
        <option value="">সব মাধ্যম (All Channels)</option>
        <option value="sms">SMS</option>
        <option value="email">ইমেইল</option>
        <option value="push">পুশ নোটিফিকেশন</option>
        <option value="whatsapp">হোয়াটসঅ্যাপ</option>
      </select>
      <select v-model="filters.status" class="form-select" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="pending">মুলতুবি</option>
        <option value="sent">পাঠানো</option>
        <option value="failed">ব্যর্থ</option>
        <option value="scheduled">নির্ধারিত</option>
      </select>
      <select v-model="filters.priority" class="form-select" @change="load">
        <option value="">সব প্রাধান্য</option>
        <option value="low">নিম্ন</option>
        <option value="medium">মধ্যম</option>
        <option value="high">উচ্চ</option>
        <option value="urgent">জরুরি</option>
      </select>
      <div class="pagination-info" v-if="pagination?.total">
        মোট <span class="highlight">{{ pagination.total.toLocaleString('bn-BD') }}</span> টি টাস্ক
      </div>
    </div>

    <div v-if="loading" class="loading-state card"><div class="spinner" /><p>টাস্ক লোড হচ্ছে...</p></div>

    <div v-else-if="!tasks.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="clipboard-list" /></div>
      <h3>কোনো রিমাইন্ডার টাস্ক পাওয়া যায়নি</h3>
      <p>নতুন টাস্ক তৈরি করে অটোমেটেড বার্তা প্রেরণ শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate"><icon name="plus" /> প্রথম টাস্ক তৈরি করুন</button>
    </div>

    <!-- Table Card -->
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিরোনাম</th>
              <th>মাধ্যম</th>
              <th>প্রাধান্য</th>
              <th>অবস্থা</th>
              <th>নির্ধারিত সময়</th>
              <th>পুনরাবৃত্তি</th>
              <th>কর্তা / কর্মকর্তা</th>
              <th class="text-right">কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="task in tasks" :key="task.id" class="table-row">
              <td>
                <span class="task-title">{{ task.title_bn || task.title }}</span>
              </td>
              <td>
                <span class="type-tag">{{ formatType(task.type) }}</span>
              </td>
              <td>
                <span class="priority-badge" :class="'priority-' + task.priority">
                  {{ formatPriority(task.priority) }}
                </span>
              </td>
              <td>
                <span class="status-pill" :class="statusBadge(task.status)">
                  <span class="status-dot" />
                  {{ formatStatus(task.status) }}
                </span>
              </td>
              <td class="dimmed-date">
                {{ task.scheduled_for ? formatDate(task.scheduled_for) : '—' }}
              </td>
              <td>
                <span v-if="task.is_recurring" class="recurring-tag"><icon name="repeat" /> {{ task.recurring_interval || 'নিয়মিত' }}</span>
                <span v-else class="text-muted">—</span>
              </td>
              <td>
                <div v-if="task.created_by_user" class="user-cell">
                  <div class="user-avatar" :style="{ backgroundColor: userColor(task.created_by_user.name_bn || task.created_by_user.email) }">
                    {{ initials(task.created_by_user.name_bn || task.created_by_user.email) }}
                  </div>
                  <div class="user-info">
                    <span class="user-name">{{ task.created_by_user.name_bn || task.created_by_user.name }}</span>
                  </div>
                </div>
                <span v-else class="dimmed-date">সিস্টেম</span>
              </td>
              <td class="text-right">
                <div class="row-actions">
                  <NuxtLink :to="`/reminder-tasks/${task.id}`" class="action-btn view-btn" title="বিস্তারিত দেখুন">
                    <icon name="eye" />
                  </NuxtLink>
                  <button class="action-btn toggle-btn" @click="toggleTask(task)" :title="task.is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন'">
                    <icon :name="task.is_active ? 'pause' : 'play'" />
                  </button>
                  <button class="action-btn delete-btn" @click="deleteTask(task.id)" title="মুছুন">
                    <icon name="delete" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination && pagination.last_page > 1" class="pagination-footer">
        <button class="btn btn-outline btn-sm" :disabled="filters.page <= 1" @click="goPage(filters.page - 1)">পূর্ববর্তী</button>
        <span class="page-indicator">পৃষ্ঠা {{ filters.page.toLocaleString('bn-BD') }} / {{ pagination.last_page.toLocaleString('bn-BD') }}</span>
        <button class="btn btn-outline btn-sm" :disabled="filters.page >= pagination.last_page" @click="goPage(filters.page + 1)">পরবর্তী</button>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="closeCreate">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingTask ? 'টাস্ক সম্পাদনা' : 'নতুন রিমাইন্ডার টাস্ক তৈরি' }}</h3>
            <p>শিরোনাম, মাধ্যম, সময়সূচী ও বিবরণ নির্ধারণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="closeCreate">×</button>
        </div>

        <form @submit.prevent="saveTask" class="modal-form">
          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">টাস্কের শিরোনাম (বাংলা) *</label>
              <input v-model="form.title_bn" type="text" class="form-input" required placeholder="যেমন: পরীক্ষার ফি জমা স্মরণী" />
            </div>
            <div class="form-group">
              <label class="form-label">পাঠানোর মাধ্যম *</label>
              <select v-model="form.type" class="form-select" required>
                <option value="sms">SMS</option>
                <option value="email">ইমেইল</option>
                <option value="push">পুশ নোটিফিকেশন</option>
                <option value="whatsapp">হোয়াটসঅ্যাপ</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">প্রাধান্য</label>
              <select v-model="form.priority" class="form-select">
                <option value="low">নিম্ন</option>
                <option value="medium">মধ্যম</option>
                <option value="high">উচ্চ</option>
                <option value="urgent">জরুরি</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">নির্ধারিত সময়</label>
              <input v-model="form.scheduled_for" type="datetime-local" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">কত ঘণ্টা আগে স্মরণ করাবে?</label>
              <input v-model.number="form.reminder_before_hours" type="number" class="form-input" min="1" placeholder="24" />
            </div>
            <div class="form-group wide">
              <label class="form-label">বার্তার বিবরণ (বাংলা)</label>
              <textarea v-model="form.description_bn" class="form-textarea" rows="3" placeholder="রিমাইন্ডার বার্তার মূল অংশ..."></textarea>
            </div>
            <div class="form-group wide">
              <div class="checkboxes-row">
                <label class="custom-checkbox">
                  <input type="checkbox" v-model="form.is_recurring" />
                  <span class="checkbox-text">পুনরাবৃত্তিমূলক টাস্ক (Recurring)</span>
                </label>
                <label class="custom-checkbox">
                  <input type="checkbox" v-model="form.is_active" />
                  <span class="checkbox-text">সক্রিয় রাখুন</span>
                </label>
              </div>
            </div>
            <div class="form-group wide" v-if="form.is_recurring">
              <label class="form-label">পুনরাবৃত্তির ব্যবধান</label>
              <select v-model="form.recurring_interval" class="form-select">
                <option value="daily">প্রতিদিন (Daily)</option>
                <option value="weekly">প্রতি সপ্তাহে (Weekly)</option>
                <option value="monthly">প্রতি মাসে (Monthly)</option>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="closeCreate">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingTask ? 'আপডেট করুন' : 'টাস্ক তৈরি করুন') }}
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

const tasks = ref<any[]>([])
const loading = ref(false)
const pagination = ref<any>(null)
const stats = ref<any>({})
const showCreate = ref(false)
const editingTask = ref<any>(null)
const saving = ref(false)

const filters = reactive({
  search: '',
  type: '',
  status: '',
  priority: '',
  page: 1,
  per_page: 15,
})

const form = reactive({
  title_bn: '',
  title: '',
  type: 'sms',
  priority: 'medium',
  scheduled_for: '',
  reminder_before_hours: 24,
  description_bn: '',
  is_recurring: false,
  recurring_interval: 'daily',
  is_active: true,
})

async function load() {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: String(filters.page),
      per_page: String(filters.per_page),
      ...(filters.search && { search: filters.search }),
      ...(filters.type && { type: filters.type }),
      ...(filters.status && { status: filters.status }),
      ...(filters.priority && { priority: filters.priority }),
    })
    const [res, statsRes] = await Promise.all([
      api.get('/reminder-tasks?' + params.toString()),
      api.get('/reminder-tasks/stats').catch(() => ({ data: {} })),
    ])
    tasks.value = res.data?.data || []
    pagination.value = res.data?.meta || null
    stats.value = statsRes.data || {}
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingTask.value = null
  Object.assign(form, {
    title_bn: '',
    title: '',
    type: 'sms',
    priority: 'medium',
    scheduled_for: '',
    reminder_before_hours: 24,
    description_bn: '',
    is_recurring: false,
    recurring_interval: 'daily',
    is_active: true,
  })
  showCreate.value = true
}

function closeCreate() {
  showCreate.value = false
  editingTask.value = null
}

async function saveTask() {
  saving.value = true
  try {
    if (editingTask.value) {
      await api.put('/reminder-tasks/' + editingTask.value.id, form)
    } else {
      await api.post('/reminder-tasks', form)
    }
    closeCreate()
    await load()
  } catch (e: any) {
    alert('সংরক্ষণে ত্রুটি: ' + (e.response?.data?.message || 'অজানা ত্রুটি'))
  } finally {
    saving.value = false
  }
}

function toggleTask(task: any) {
  const newActive = !task.is_active
  api.put('/reminder-tasks/' + task.id, { ...task, is_active: newActive }).then(() => {
    task.is_active = newActive
  })
}

async function deleteTask(id: number) {
  if (!confirm('এই রিমাইন্ডার টাস্ক মুছে ফেলতে চান?')) return
  try {
    await api.delete('/reminder-tasks/' + id)
    await load()
  } catch (e) {
    console.error(e)
  }
}

function goPage(p: number) {
  filters.page = p
  load()
}

function formatType(t: string) {
  const map: Record<string, string> = { sms: 'SMS', email: 'ইমেইল', push: 'পুশ', whatsapp: 'হোয়াটসঅ্যাপ' }
  return map[t] || t
}

function formatPriority(p: string) {
  const map: Record<string, string> = { low: 'নিম্ন', medium: 'মধ্যম', high: 'উচ্চ', urgent: 'জরুরি' }
  return map[p] || p
}

function formatStatus(s: string) {
  const map: Record<string, string> = { pending: 'মুলতুবি', sent: 'পাঠানো', failed: 'ব্যর্থ', scheduled: 'নির্ধারিত' }
  return map[s] || s
}

function statusBadge(s: string) {
  if (s === 'sent') return 'badge-approved'
  if (s === 'failed') return 'badge-rejected'
  return 'badge-pending'
}

function formatDate(date: string) {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
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
  for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
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

.clear-search-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0 0.2rem;
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

.table-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.table-responsive { overflow-x: auto; }

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

.table-row { transition: background 0.15s ease; }
.table-row:hover { background: rgba(20, 80, 50, 0.025); }

.task-title { font-weight: 600; color: var(--color-text); }

.type-tag {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  background: rgba(20, 80, 50, 0.07);
  color: var(--color-primary);
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
}

.priority-badge {
  display: inline-block;
  padding: 0.15rem 0.55rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 700;
}

.priority-low { background: rgba(107, 114, 128, 0.12); color: #4b5563; }
.priority-medium { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.priority-high { background: rgba(245, 158, 11, 0.12); color: #b45309; }
.priority-urgent { background: rgba(239, 68, 68, 0.12); color: #dc2626; }

.recurring-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.8rem;
  color: #7c3aed;
  font-weight: 500;
}

.user-cell { display: flex; align-items: center; gap: 0.6rem; }
.user-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  flex-shrink: 0;
}

.user-name { font-size: 0.86rem; font-weight: 600; }
.dimmed-date { font-size: 0.82rem; color: var(--color-text-light); }

.row-actions { display: flex; align-items: center; gap: 0.35rem; justify-content: flex-end; }

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

.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); transform: translateY(-1px); }
.action-btn.view-btn:hover { color: var(--color-primary); border-color: var(--color-primary); background: rgba(20, 80, 50, 0.08); }
.action-btn.toggle-btn:hover { color: #2563eb; border-color: #2563eb; background: rgba(59, 130, 246, 0.1); }
.action-btn.delete-btn:hover { color: #dc2626; border-color: #dc2626; background: rgba(239, 68, 68, 0.1); }

.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.95rem 1.25rem;
  background: rgba(0, 0, 0, 0.01);
  border-top: 1px solid var(--color-border-light);
}

.page-indicator { font-size: 0.85rem; color: var(--color-text-light); font-weight: 500; }

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

.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }
.btn-ghost { background: transparent; color: var(--color-text); }
.btn-ghost:hover { background: rgba(0, 0, 0, 0.05); }
.btn-sm { padding: 0.4rem 0.85rem; font-size: 0.8rem; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.checkboxes-row { display: flex; gap: 1.5rem; padding: 0.5rem 0; }
.custom-checkbox { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 500; cursor: pointer; }
.custom-checkbox input { accent-color: var(--color-primary); width: 16px; height: 16px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

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
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>