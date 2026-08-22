<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>রিমাইন্ডার টাস্ক</h1>
        <p>শিক্ষার্থী, অভিভাবক ও কর্মকর্তার জন্য স্মরণী বার্তা ও অটোমেটেড টাস্ক তৈরি ও ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary btn-sm" @click="openCreate"><icon name="plus" /> নতুন টাস্ক</button>
        <button class="btn btn-outline btn-sm" @click="load"><icon name="refresh" /> রিফ্রেশ</button>
      </div>
    </div>

    <div class="stats-row" v-if="stats">
      <div class="stat-card green"><div class="stat-icon"><icon name="check" /></div><div class="stat-content"><span class="stat-value">{{ stats.pending }}</span><span class="stat-label">মুলতুবি</span></div></div>
      <div class="stat-card blue"><div class="stat-icon"><icon name="send" /></div><div class="stat-content"><span class="stat-value">{{ stats.sent }}</span><span class="stat-label">পাঠানো</span></div></div>
      <div class="stat-card amber"><div class="stat-icon"><icon name="clock" /></div><div class="stat-content"><span class="stat-value">{{ stats.scheduled }}</span><span class="stat-label">নির্ধারিত</span></div></div>
      <div class="stat-card gold"><div class="stat-icon"><icon name="repeat" /></div><div class="stat-content"><span class="stat-value">{{ stats.recurring }}</span><span class="stat-label">পুনরাবৃত্তি</span></div></div>
    </div>

    <div class="toolbar card">
      <div class="search-box"><icon name="search" /><input v-model="filters.search" placeholder="টাস্ক খুঁজুন..." @keyup.enter="load" /></div>
      <select v-model="filters.type" class="form-control compact" @change="load">
        <option value="">সব ধরন</option>
        <option value="sms">SMS</option>
        <option value="email">ইমেইল</option>
        <option value="push">পুশ নোটিফিকেশন</option>
        <option value="whatsapp">হোয়াটসঅ্যাপ</option>
      </select>
      <select v-model="filters.status" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="pending">মুলতুবি</option>
        <option value="sent">পাঠানো</option>
        <option value="failed">ব্যর্থ</option>
        <option value="scheduled">নির্ধারিত</option>
      </select>
      <select v-model="filters.priority" class="form-control compact" @change="load">
        <option value="">সব প্রাধান্য</option>
        <option value="low">নিম্ন</option>
        <option value="medium">মধ্যম</option>
        <option value="high">উচ্চ</option>
        <option value="urgent">জরুরি</option>
      </select>
      <div class="pagination-info">{{ pagination?.meta?.total ?? 0 }}টি টাস্ক</div>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>

    <div v-else-if="!tasks.length" class="empty-state">
      <div class="empty-icon"><icon name="clipboard-list" /></div>
      <h3>কোনো রিমাইন্ডার টাস্ক নেই</h3>
      <p>এখনও কোনো টাস্ক তৈরি করা হয়নি। প্রথম টাস্ক তৈরি করতে নিচের বাটনে ক্লিক করুন।</p>
      <button class="btn btn-primary" @click="openCreate">নতুন টাস্ক তৈরি করুন</button>
    </div>

    <div v-else class="card">
      <div class="card-body scrollable-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>শিরোনাম</th>
              <th>ধরন</th>
              <th>প্রাধান্য</th>
              <th>অবস্থা</th>
              <th>নির্ধারিত সময়</th>
              <th>পাঠানো</th>
              <th>পুনরাবৃত্তি</th>
              <th>কর্তা</th>
              <th>কর্মকর্তা</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="task in tasks" :key="task.id">
              <td><span class="task-title" :class="['priority-' + task.priority]">{{ task.title_bn || task.title }}</span></td>
              <td><span class="badge badge-sm" :class="typeBadge(task.type)">{{ task.type }}</span></td>
              <td><span class="badge badge-sm" :class="priorityBadge(task.priority)">{{ task.priority }}</span></td>
              <td><span class="badge badge-sm status-badge" :class="statusBadge(task.status)">{{ task.status }}</span></td>
              <td><span class="dimmed">{{ task.scheduled_for ?? '—' }}</span></td>
              <td><span class="dimmed">{{ task.sent_at ?? '—' }}</span></td>
              <td><icon v-if="task.is_recurring" name="repeat" class="icon-sm" /><span v-else class="dimmed">না</span></td>
              <td><span class="dimmed">{{ task.created_by ?? 'সিস্টেম' }}</span></td>
              <td>
                <div v-if="task.created_by_user" class="user-cell">
                  <div class="user-avatar" :style="{ backgroundColor: userColor(task.created_by_user.name_bn || task.created_by_user.email) }">{{ initials(task.created_by_user.name_bn || task.created_by_user.email) }}</div>
                  <span>{{ task.created_by_user.name_bn || task.created_by_user.email }}</span>
                </div>
                <span v-else class="dimmed">—</span>
              </td>
              <td>
                <div class="row-actions">
                  <NuxtLink :to="`/reminder-tasks/${task.id}`" class="btn btn-ghost btn-sm" title="বিস্তারিত"><icon name="eye" /></NuxtLink>
                  <button class="btn btn-ghost btn-sm" @click="toggleTask(task)" title="সক্রিয়/নিষ্ক্রিয়"><icon :name="task.is_active ? 'pause' : 'play'" /></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="pagination?.meta?.last_page" class="card-footer pagination">
        <span v-if="pagination.meta.current_page > 1"><button class="btn btn-outline btn-sm" @click="goPage(pagination.meta.current_page - 1)">পূর্বে</button></span>
        <span>পৃষ্ঠা {{ pagination.meta.current_page }} / {{ pagination.meta.last_page }}</span>
        <span v-if="pagination.meta.current_page < pagination.meta.last_page"><button class="btn btn-outline btn-sm" @click="goPage(pagination.meta.current_page + 1)">পরে</button></span>
      </div>
    </div>
  </div>

  <!-- Create/Edit Modal -->
  <div v-if="showCreate" class="modal-overlay" @click.self="closeCreate">
    <div class="modal" :class="{ 'modal-sm': false }">
      <div class="modal-header">
        <h3>নতুন রিমাইন্ডার টাস্ক</h3>
        <button class="btn btn-icon" @click="closeCreate"><icon name="close" /></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="saveTask">
          <div class="form-group">
            <label class="form-label">শিরোনাম (বাংলা) *</label>
            <input v-model="form.title_bn" type="text" class="form-control" required placeholder="যেমন: ফি সংগ্রহের স্মরণী" />
          </div>
          <div class="form-group">
            <label class="form-label">শিরোনাম (ইংরেজি)</label>
            <input v-model="form.title" type="text" class="form-control" placeholder="Payment reminder" />
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">ধরন *</label>
              <select v-model="form.type" class="form-control" required>
                <option value="">নির্বাচন করুন</option>
                <option value="sms">SMS</option>
                <option value="email">ইমেইল</option>
                <option value="push">পুশ নোটিফিকেশন</option>
                <option value="whatsapp">হোয়াটসঅ্যাপ</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">প্রাধান্য</label>
              <select v-model="form.priority" class="form-control">
                <option value="low">নিম্ন</option>
                <option value="medium" selected>মধ্যম</option>
                <option value="high">উচ্চ</option>
                <option value="urgent">জরুরি</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">বিবরণ (বাংলা)</label>
            <textarea v-model="form.description_bn" class="form-control" rows="3" placeholder="টাস্কের বিবরণ..."></textarea>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group form-check">
              <label class="checkbox-label"><input type="checkbox" v-model="form.is_recurring" /> পুনরাবৃত্তি টাস্ক</label>
            </div>
            <div class="form-group form-check">
              <label class="checkbox-label"><input type="checkbox" v-model="form.is_active" checked /> সক্রিয়</label>
            </div>
          </div>
          <div class="form-group" v-if="form.is_recurring">
            <label class="form-label">পুনরাবৃত্তি সময়কাল</label>
            <select v-model="form.recurring_interval" class="form-control">
              <option value="daily">দৈনিক</option>
              <option value="weekly">সাপ্তাহিক</option>
              <option value="monthly">মাসিক</option>
              <option value="yearly">বার্ষিক</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">পাঠানোর মাধ্যম</label>
            <div class="checkbox-list">
              <label class="checkbox-label"><input type="checkbox" v-model="form.delivery_channels" value="sms" /> SMS</label>
              <label class="checkbox-label"><input type="checkbox" v-model="form.delivery_channels" value="email" /> ইমেইল</label>
              <label class="checkbox-label"><input type="checkbox" v-model="form.delivery_channels" value="push" /> পুশ নোটিফিকেশন</label>
              <label class="checkbox-label"><input type="checkbox" v-model="form.delivery_channels" value="whatsapp" /> হোয়াটসঅ্যাপ</label>
            </div>
          </div>
          <div class="form-group" v-if="editingTask">
            <label class="form-label">ফিল্টার (কার জন্য)</label>
            <select v-model="form.target_type" class="form-control">
              <option value="all">সব শিক্ষার্থী</option>
              <option value="group">নির্বাচিত গ্রুপ</option>
              <option value="single">নির্বাচিত একজন</option>
              <option value="class">একটি শ্রেণি</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">অগ্রিম নোটিফিকেশন (ঘণ্টা আগে)</label>
            <input v-model="form.reminder_before_hours" type="number" class="form-control compact" min="0" max="720" placeholder="যেমন: 24" />
          </div>
          <div class="form-group">
            <label class="form-label">নোট</label>
            <input v-model="form.note" type="text" class="form-control" placeholder="অভ্যন্তরীণ নোট (শুধু প্রশাসন দেখতে পাবে)" />
          </div>
          <div class="form-actions">
            <button type="button" class="btn btn-outline" @click="closeCreate">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে...' : 'সংরক্ষণ করুন' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Detail Panel -->
  <div v-if="detailTask" class="detail-panel">
    <div class="card">
      <div class="card-body">
        <div class="detail-header">
          <div>
            <span class="badge badge-sm" :class="typeBadge(detailTask.type)">{{ detailTask.type }}</span>
            <span class="badge badge-sm" :class="priorityBadge(detailTask.priority)">{{ detailTask.priority }}</span>
          </div>
          <div class="detail-actions">
            <button class="btn btn-outline btn-sm" @click="editTask"><icon name="edit" /> সম্পাদনা</button>
            <button class="btn btn-ghost btn-sm" @click="closeDetail"><icon name="close" /> বন্ধ</button>
          </div>
        </div>
        <h2 class="detail-title">{{ detailTask.title_bn || detailTask.title }}</h2>
        <dl class="detail-grid">
          <div class="detail-row"><dt>অবস্থা</dt><dd><span class="badge badge-sm status-badge" :class="statusBadge(detailTask.status)">{{ detailTask.status }}</span></dd></div>
          <div class="detail-row"><dt>পাঠানোর মাধ্যম</dt><dd>{{ detailTask.type }}</dd></div>
          <div class="detail-row"><dt>নির্ধারিত সময়</dt><dd>{{ detailTask.scheduled_for ?? '—' }}</dd></div>
          <div class="detail-row"><dt>পাঠানো সময়</dt><dd>{{ detailTask.sent_at ?? 'এখনো পাঠানো হয়নি' }}</dd></div>
          <div class="detail-row"><dt>পুনরাবৃত্তি</dt><dd>{{ detailTask.is_recurring ? 'হ্যাঁ (' + detailTask.recurring_interval + ')' : 'না' }}</dd></div>
          <div class="detail-row"><dt>কর্তা</dt><dd>{{ detailTask.created_by ?? 'সিস্টেম' }}</dd></div>
          <div class="detail-row" v-if="detailTask.created_by_user"><dt>কর্মকর্তা</dt><dd>{{ detailTask.created_by_user.name_bn || detailTask.created_by_user.email }}</dd></div>
        </dl>
        <div v-if="detailTask.description_bn" class="detail-section">
          <h4>বিবরণ</h4>
          <p class="detail-text">{{ detailTask.description_bn }}</p>
        </div>
        <div v-if="detailTask.note" class="detail-section">
          <h4>অভ্যন্তরীণ নোট</h4>
          <p class="detail-text">{{ detailTask.note }}</p>
        </div>
        <div v-if="detailTask.target_type" class="detail-section">
          <h4>লক্ষ্য শ্রেণী</h4>
          <p class="detail-text">{{ detailTask.target_type === 'all' ? 'সব শিক্ষার্থী' : detailTask.target_type }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const route = useRoute()

const tasks = ref([])
const loading = ref(false)
const pagination = ref<any>(null)
const stats = ref<any>({})
const showCreate = ref(false)
const editingTask = ref<any>(null)
const saving = ref(false)
const detailTask = ref<any>(null)
const showDetail = ref(false)

const filters = reactive({
  search: '',
  type: '',
  status: '',
  priority: '',
  page: 1,
  perPage: 15,
})

const form = reactive({
  title_bn: '',
  title: '',
  type: 'sms',
  priority: 'medium',
  status: 'pending',
  description_bn: '',
  is_recurring: false,
  is_active: true,
  recurring_interval: 'daily',
  delivery_channels: [] as string[],
  target_type: 'all',
  reminder_before_hours: 24,
  note: '',
  scheduled_for: '',
})

function typeBadge(type: string) {
  const map: Record<string, string> = { sms: 'badge-success', email: 'badge-info', push: 'badge-warning', whatsapp: 'badge-danger' }
  return map[type] || 'badge-default'
}
function priorityBadge(p: string) {
  const map: Record<string, string> = { low: 'badge-default', medium: 'badge-info', high: 'badge-warning', urgent: 'badge-danger' }
  return map[p] || 'badge-default'
}
function statusBadge(s: string) {
  const map: Record<string, string> = { pending: 'badge-warning', sent: 'badge-success', failed: 'badge-danger', scheduled: 'badge-info', draft: 'badge-default' }
  return map[s] || 'badge-default'
}
function initials(name: string) {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
}
function userColor(name: string) {
  const colors = ['#145032', '#d4af37', '#5c8eb7', '#e8573a', '#3a9e8f', '#8b5cf6', '#f59e0b']
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colors[Math.abs(hash) % colors.length]
}

async function load() {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: String(filters.page),
      per_page: String(filters.perPage),
      ...(filters.search && { search: filters.search }),
      ...(filters.type && { type: filters.type }),
      ...(filters.status && { status: filters.status }),
      ...(filters.priority && { priority: filters.priority }),
    })
    const res = await api.get(`/reminder-tasks?${params}`)
    tasks.value = res.data?.data || []
    pagination.value = res.data?.meta || null
    const overview = await api.get('/reminder-tasks/stats')
    stats.value = overview.data || {}
  } catch (err: any) {
    console.error('Failed to load reminder tasks:', err)
    tasks.value = []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingTask.value = null
  Object.assign(form, {
    title_bn: '', title: '', type: 'sms', priority: 'medium',
    description_bn: '', is_recurring: false, is_active: true,
    delivery_channels: [], target_type: 'all', reminder_before_hours: 24, note: '',
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
      await api.put(`/reminder-tasks/${editingTask.value.id}`, form)
    } else {
      await api.post('/reminder-tasks', form)
    }
    closeCreate()
    load()
  } catch (err: any) {
    alert('সংরক্ষণে ত্রুটি: ' + (err.response?.data?.message || 'অজানা ত্রুটি'))
  } finally {
    saving.value = false
  }
}

function toggleTask(task: any) {
  const newStatus = task.is_active ? 'inactive' : 'active'
  api.put(`/reminder-tasks/${task.id}`, { ...task, is_active: !task.is_active }).then(() => load())
}

function editTask() {
  if (!detailTask.value) return
  editingTask.value = detailTask.value
  Object.assign(form, {
    title_bn: detailTask.value.title_bn || '',
    title: detailTask.value.title || '',
    type: detailTask.value.type || 'sms',
    priority: detailTask.value.priority || 'medium',
    status: detailTask.value.status || 'pending',
    description_bn: detailTask.value.description_bn || '',
    is_recurring: Boolean(detailTask.value.is_recurring),
    is_active: Boolean(detailTask.value.is_active),
    recurring_interval: detailTask.value.recurring_interval || 'daily',
    delivery_channels: detailTask.value.delivery_channels || [],
    target_type: detailTask.value.target_type || 'all',
    reminder_before_hours: detailTask.value.reminder_before_hours || 24,
    note: detailTask.value.note || '',
    scheduled_for: detailTask.value.scheduled_for || '',
  })
  showCreate.value = true
  showDetail.value = false
}

function goPage(page: number) {
  filters.page = page
  load()
}

onMounted(() => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
})

// Read route param for detail view
const taskId = computed(() => route.params.id as string || '')
if (taskId.value) {
  load()
  // Will load detail after tasks load
}
</script>

<style scoped>
.page-wrapper { padding: 1.5rem; }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem; }
.stat-card { background: var(--color-bg-card); border-radius: var(--radius-md); padding: 1rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid var(--color-border-light); }
.stat-icon { width: 40px; height: 40px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
.stat-content { display: flex; flex-direction: column; }
.stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; }
.stat-label { font-size: 0.875rem; color: var(--color-text-muted); }
.toolbar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.toolbar .search-box { position: relative; flex: 0 0 220px; }
.toolbar .search-box input { padding-left: 2rem; }
.toolbar .pagination-info { margin-left: auto; font-size: 0.875rem; color: var(--color-text-muted); }
.task-title { font-weight: 500; }
.task-title.priority-urgent { color: var(--color-error); }
.task-title.priority-high { color: var(--color-warning); }
.badge-sm { font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: var(--radius-sm); }
.status-badge { font-weight: 500; }
.row-actions { display: flex; gap: 0.25rem; }
.data-table th, .data-table td { padding: 0.625rem 0.75rem; text-align: left; }
.data-table th { font-size: 0.8125rem; color: var(--color-text-muted); font-weight: 500; }
.data-table td { font-size: 0.875rem; border-top: 1px solid var(--color-border-light); }
.user-cell { display: flex; align-items: center; gap: 0.5rem; }
.user-avatar { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.75rem; font-weight: 600; }
.icon-sm { color: var(--color-text-muted); }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: var(--color-bg); border-radius: var(--radius-lg); width: 90%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-body { padding: 1.25rem; }
.checkbox-list { display: flex; flex-wrap: wrap; gap: 1rem; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--color-border-light); }
.detail-panel { position: fixed; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.detail-panel .card { width: 90%; max-width: 480px; max-height: 90vh; overflow-y: auto; }
.detail-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
.detail-title { font-size: 1.25rem; margin: 0 0 1rem; }
.detail-grid { display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 1rem; margin-bottom: 1rem; }
.detail-row dt { font-size: 0.875rem; color: var(--color-text-muted); }
.detail-row dd { font-size: 0.875rem; margin: 0; }
.detail-section { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border-light); }
.detail-section h4 { font-size: 0.875rem; color: var(--color-text-muted); margin: 0 0 0.5rem; }
.detail-text { font-size: 0.875rem; white-space: pre-wrap; margin: 0; }
.loading-state, .empty-state { text-align: center; padding: 3rem 1rem; }
.loading-state .spinner { width: 40px; height: 40px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state .empty-icon { font-size: 3rem; margin-bottom: 1rem; color: var(--color-text-muted); }
.scrollable-body { max-height: 400px; overflow-y: auto; }
</style>