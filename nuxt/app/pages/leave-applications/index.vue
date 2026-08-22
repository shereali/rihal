<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>ছুটি ব্যবস্থাপনা</h1>
        <p>কর্মকর্তা ও শিক্ষকদের ছুটির আবেদন, অনুমোদন ও প্রত্যাখ্যান ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary btn-sm" @click="openCreate"><icon name="plus" /> নতুন আবেদন</button>
        <button class="btn btn-outline btn-sm" @click="load"><icon name="refresh" /> রিফ্রেশ</button>
      </div>
    </div>

    <div class="stats-row" v-if="stats">
      <div class="stat-card amber"><div class="stat-icon"><icon name="clock" /></div><div class="stat-content"><span class="stat-value">{{ stats.pending ?? 0 }}</span><span class="stat-label">মুলতুবি</span></div></div>
      <div class="stat-card green"><div class="stat-icon"><icon name="check" /></div><div class="stat-content"><span class="stat-value">{{ stats.approved ?? 0 }}</span><span class="stat-label">অনুমোদিত</span></div></div>
      <div class="stat-card red"><div class="stat-icon"><icon name="close" /></div><div class="stat-content"><span class="stat-value">{{ stats.rejected ?? 0 }}</span><span class="stat-label">প্রত্যাখ্যান</span></div></div>
      <div class="stat-card blue"><div class="stat-icon"><icon name="users" /></div><div class="stat-content"><span class="stat-value">{{ stats.total ?? 0 }}</span><span class="stat-label">মোট আবেদন</span></div></div>
    </div>

    <div class="toolbar card">
      <div class="search-box"><icon name="search" /><input v-model="filters.search" placeholder="আবেদন খুঁজুন..." @keyup.enter="load" /></div>
      <select v-model="filters.status" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="pending">মুলতুবি</option>
        <option value="approved">অনুমোদিত</option>
        <option value="rejected">প্রত্যাখ্যান</option>
      </select>
      <select v-model="filters.leave_type" class="form-control compact" @change="load">
        <option value="">সব ধরন</option>
        <option value="ছুটি">ছুটি</option>
        <option value="রোগ">রোগ</option>
        <option value="ব্যক্তিগত">ব্যক্তিগত</option>
        <option value="মাতৃত্ব">মাতৃত্ব</option>
        <option value="সিহ্ব">সিহ্ব</option>
        <option value="অনুপস্থিতি">অনুপস্থিতি</option>
        <option value="অন্য">অন্য</option>
      </select>
      <div class="pagination-info">{{ pagination?.meta?.total ?? 0 }}টি আবেদন</div>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>

    <div v-else-if="!leaves.length" class="empty-state">
      <div class="empty-icon"><icon name="clipboard-list" /></div>
      <h3>কোনো ছুটির আবেদন নেই</h3>
      <button class="btn btn-primary" @click="openCreate">নতুন আবেদন তৈরি করুন</button>
    </div>

    <div v-else class="card">
      <div class="card-body scrollable-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>কর্মকর্তা</th><th>শিরোনাম</th><th>ধরন</th><th>অবস্থা</th>
              <th>শুরু</th><th>শেষ</th><th>দিন</th><th>জরুরি</th><th>জমা দেওয়া</th><th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="leave in leaves" :key="leave.id">
              <td>
                <div v-if="leave.user_name_bn" class="user-cell">
                  <div class="user-avatar" :style="{ backgroundColor: userColor(leave.user_name_bn) }">{{ initials(leave.user_name_bn) }}</div>
                  <span>{{ leave.user_name_bn }}</span>
                </div>
              </td>
              <td><span class="task-title">{{ leave.title_bn || leave.title }}</span></td>
              <td><span class="badge badge-sm">{{ leave.leave_type }}</span></td>
              <td><span class="badge badge-sm status-badge" :class="statusBadge(leave.status)">{{ leave.status }}</span></td>
              <td class="dimmed">{{ leave.start_date }}</td>
              <td class="dimmed">{{ leave.end_date }}</td>
              <td><span class="dimmed">{{ leave.days_count }} দিন</span></td>
              <td><span v-if="leave.is_urgent" class="badge badge-danger">জরুরি</span><span v-else class="dimmed">—</span></td>
              <td class="dimmed">{{ leave.created_at }}</td>
              <td>
                <div class="row-actions">
                  <NuxtLink :to="`/leave-applications/${leave.id}`" class="btn btn-ghost btn-sm" title="বিস্তারিত"><icon name="eye" /></NuxtLink>
                  <button v-if="leave.status === 'pending'" class="btn btn-ghost btn-sm" @click="quickApprove(leave)" title="অনুমোদন"><icon name="check" /></button>
                  <button v-if="leave.status === 'pending'" class="btn btn-ghost btn-sm" @click="quickReject(leave)" title="প্রত্যাখ্যান"><icon name="close" /></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Create Modal -->
  <div v-if="showCreate" class="modal-overlay" @click.self="closeCreate">
    <div class="modal">
      <div class="modal-header">
        <h3>{{ editingLeave ? 'আবেদন সম্পাদনা' : 'নতুন ছুটির আবেদন' }}</h3>
        <button class="btn btn-icon" @click="closeCreate"><icon name="close" /></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="saveLeave">
          <div class="form-group">
            <label class="form-label">কর্মকর্তা *</label>
            <select v-model="form.user_id" class="form-control" required>
              <option value="">নির্বাচন করুন</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name_bn || u.name }} ({{ u.email }})</option>
            </select>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">ছুটির ধরন *</label>
              <select v-model="form.leave_type" class="form-control" required>
                <option value="ছুটি">ছুটি</option>
                <option value="রোগ">রোগ</option>
                <option value="ব্যক্তিগত">ব্যক্তিগত</option>
                <option value="মাতৃত্ব">মাতৃত্ব</option>
                <option value="সিহ্ব">সিহ্ব</option>
                <option value="অনুপস্থিতি">অনুপস্থিতি</option>
                <option value="অন্য">অন্য</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">শিরোনাম (বাংলা) *</label>
              <input v-model="form.title_bn" type="text" class="form-control" required placeholder="যেমন: ছুটির আবেদন" />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">বিবরণ (বাংলা) *</label>
            <textarea v-model="form.description_bn" class="form-control" rows="3" required placeholder="ছুটির কারণ ও বিবরণ..."></textarea>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">শুরুর তারিখ *</label>
              <input v-model="form.start_date" type="date" class="form-control" required />
            </div>
            <div class="form-group">
              <label class="form-label">শেষ তারিখ *</label>
              <input v-model="form.end_date" type="date" class="form-control" required />
            </div>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group form-check">
              <label class="checkbox-label"><input type="checkbox" v-model="form.is_urgent" /> জরুরি আবেদন</label>
            </div>
            <div class="form-group form-check">
              <label class="checkbox-label"><input type="checkbox" v-model="form.is_active" checked /> সক্রিয়</label>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">নোট</label>
            <textarea v-model="form.notes" class="form-control" rows="2" placeholder="অতিরিক্ত নোট..."></textarea>
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
  <div v-if="detailLeave" class="detail-panel">
    <div class="card">
      <div class="card-body">
        <div class="detail-header">
          <div>
            <span class="badge badge-sm" :class="statusBadge(detailLeave.status)">{{ detailLeave.status }}</span>
            <span class="badge badge-sm">{{ detailLeave.leave_type }}</span>
          </div>
          <div class="detail-actions">
            <button v-if="detailLeave.status === 'pending'" class="btn btn-success btn-sm" @click="approveLeave"><icon name="check" /> অনুমোদন</button>
            <button v-if="detailLeave.status === 'pending'" class="btn btn-danger btn-sm" @click="rejectLeave"><icon name="close" /> প্রত্যাখ্যান</button>
            <button class="btn btn-ghost btn-sm" @click="closeDetail"><icon name="close" /> বন্ধ</button>
          </div>
        </div>
        <h2 class="detail-title">{{ detailLeave.title_bn || detailLeave.title }}</h2>
        <dl class="detail-grid">
          <div class="detail-row"><dt>কর্মকর্তা</dt><dd>{{ detailLeave.user_name_bn || detailLeave.user_name }} ({{ detailLeave.user_email }})</dd></div>
          <div class="detail-row"><dt>ছুটির ধরন</dt><dd>{{ detailLeave.leave_type }}</dd></div>
          <div class="detail-row"><dt>শুরু</dt><dd>{{ detailLeave.start_date }}</dd></div>
          <div class="detail-row"><dt>শেষ</dt><dd>{{ detailLeave.end_date }}</dd></div>
          <div class="detail-row"><dt>মোট দিন</dt><dd>{{ detailLeave.days_count }} দিন</dd></div>
          <div class="detail-row"><dt>জরুরি</dt><dd>{{ detailLeave.is_urgent ? 'হ্যাঁ' : 'না' }}</dd></div>
          <div class="detail-row"><dt>অবস্থা</dt><dd><span class="badge status-badge" :class="statusBadge(detailLeave.status)">{{ detailLeave.status }}</span></dd></div>
          <div class="detail-row" v-if="detailLeave.approved_by_name"><dt>অনুমোদনকারী</dt><dd>{{ detailLeave.approved_by_name }}</dd></div>
          <div class="detail-row" v-if="detailLeave.approved_at"><dt>অনুমোদন সময়</dt><dd>{{ detailLeave.approved_at }}</dd></div>
        </dl>
        <div v-if="detailLeave.description_bn" class="detail-section">
          <h3>বিবরণ</h3>
          <p class="detail-text">{{ detailLeave.description_bn }}</p>
        </div>
        <div v-if="detailLeave.notes" class="detail-section muted">
          <h3>নোট</h3>
          <p class="detail-text">{{ detailLeave.notes }}</p>
        </div>
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
const detailLeave = ref<any>(null)
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

function statusBadge(s: string) {
  const m: Record<string, string> = { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger', cancelled: 'badge-default' }
  return m[s] || 'badge-default'
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
      per_page: String(filters.per_page),
      ...(filters.search && { search: filters.search }),
      ...(filters.status && { status: filters.status }),
      ...(filters.leave_type && { leave_type: filters.leave_type }),
    })
    const res = await api.get('/leave-applications?' + params)
    leaves.value = res.data?.data || []
    pagination.value = res.data?.meta || null
    const overview = await api.get('/leave-applications/stats')
    stats.value = overview.data || {}
    const userRes = await api.get('/users')
    users.value = userRes.data?.data || []
  } catch (e: any) {
    console.error('Failed to load:', e)
    leaves.value = []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingLeave.value = null
  Object.assign(form, { user_id: 0, leave_type: 'ছুটি', title_bn: '', title: '', description_bn: '', start_date: '', end_date: '', notes: '', is_urgent: false, is_active: true })
  showCreate.value = true
}
function closeCreate() { showCreate.value = false; editingLeave.value = null }

async function saveLeave() {
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

function quickApprove(leave: any) {
  if (!confirm(leave.user_name_bn + ' এর আবেদন অনুমোদন করবেন?')) return
  api.put('/leave-applications/' + leave.id, { ...leave, status: 'approved' }).then(() => load())
}
function quickReject(leave: any) {
  if (!confirm(leave.user_name_bn + ' এর আবেদন প্রত্যাখ্যান করবেন?')) return
  api.put('/leave-applications/' + leave.id, { ...leave, status: 'rejected' }).then(() => load())
}
function approveLeave() {
  if (!detailLeave.value) return
  api.put('/leave-applications/' + detailLeave.value.id, { ...detailLeave.value, status: 'approved' }).then(() => { detailLeave.value.status = 'approved' })
}
function rejectLeave() {
  if (!detailLeave.value) return
  if (!confirm('এই আবেদনটি প্রত্যাখ্যান করবেন?')) return
  api.put('/leave-applications/' + detailLeave.value.id, { ...detailLeave.value, status: 'rejected' }).then(() => { detailLeave.value.status = 'rejected' })
}
function closeDetail() { detailLeave.value = null }
function goPage(page: number) { filters.page = page; load() }

onMounted(() => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
  load()
})
</script>

<style scoped>
.page-wrapper { padding: 1.5rem; }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem; }
.stat-card { background: var(--color-bg-card); border-radius: var(--radius-md); padding: 1rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid var(--color-border-light); }
.stat-icon { width: 40px; height: 40px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
.stat-content { display: flex; flex-direction: column; }
.stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; }
.stat-label { font-size: 0.875rem; color: var(--color-text-muted); }
.toolbar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.toolbar .search-box { position: relative; flex: 0 0 220px; }
.toolbar .search-box input { padding-left: 2rem; }
.toolbar .pagination-info { margin-left: auto; font-size: 0.875rem; color: var(--color-text-muted); }
.task-title { font-weight: 500; }
.row-actions { display: flex; gap: 0.25rem; }
.data-table th, .data-table td { padding: 0.625rem 0.75rem; text-align: left; }
.data-table th { font-size: 0.8125rem; color: var(--color-text-muted); font-weight: 500; }
.data-table td { font-size: 0.875rem; border-top: 1px solid var(--color-border-light); }
.user-cell { display: flex; align-items: center; gap: 0.5rem; }
.user-avatar { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.75rem; font-weight: 600; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: var(--color-bg); border-radius: var(--radius-lg); width: 90%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-body { padding: 1.25rem; }
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
.detail-section.muted { background: var(--color-bg); border-radius: var(--radius-sm); padding: 1rem; }
.loading-state, .empty-state { text-align: center; padding: 3rem 1rem; }
.loading-state .spinner { width: 40px; height: 40px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state .empty-icon { font-size: 3rem; margin-bottom: 1rem; color: var(--color-text-muted); }
.scrollable-body { max-height: 400px; overflow-y: auto; }
</style>