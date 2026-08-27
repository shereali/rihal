<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink to="/reminder-tasks">রিমাইন্ডার টাস্ক</NuxtLink>
      <span class="sep">/</span>
      <span class="current">{{ task?.title_bn || task?.title || 'টাস্ক বিস্তারিত' }}</span>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>

    <div v-else-if="!task" class="empty-state">
      <div class="empty-icon"><icon name="alert-circle" /></div>
      <h3>টাস্ক পাওয়া যায়নি</h3>
      <p>এই আইডির কোনো রিমাইন্ডার টাস্ক নেই।</p>
      <NuxtLink to="/reminder-tasks" class="btn btn-primary">টাস্ক তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-screen">
      <div class="sticky-header">
        <button class="back-btn" @click="goBack"><icon name="arrow-left" /> ফিরে যান</button>
        <div class="sticky-actions">
          <button class="btn btn-outline btn-sm" @click="openEdit"><icon name="pencil" /> সম্পাদনা</button>
          <button class="btn btn-ghost btn-sm" @click="toggleActive">
            <icon :name="task.is_active ? 'pause' : 'play'" /> {{ task.is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}
          </button>
        </div>
      </div>

      <div class="detail-content card">
        <div class="card-body">
          <div class="detail-topbar">
            <span class="badge badge-lg" :class="typeBadge(task.type)">{{ task.type }}</span>
            <span class="badge badge-lg" :class="priorityBadge(task.priority)">{{ task.priority }}</span>
            <span class="badge badge-lg status-badge" :class="statusBadge(task.status)">{{ task.status }}</span>
          </div>

          <h1 class="detail-title">{{ task.title_bn || task.title }}</h1>

          <dl class="detail-grid">
            <div class="detail-item">
              <dt>অবস্থা</dt>
              <dd><span class="badge status-badge" :class="statusBadge(task.status)">{{ task.status }}</span></dd>
            </div>
            <div class="detail-item">
              <dt>পাঠানোর মাধ্যম</dt>
              <dd>{{ task.type }}</dd>
            </div>
            <div class="detail-item">
              <dt>নির্ধারিত সময়</dt>
              <dd class="bigger">{{ task.scheduled_for ?? '—' }}</dd>
            </div>
            <div class="detail-item">
              <dt>পাঠানো সময়</dt>
              <dd>{{ task.sent_at ?? 'এখনো পাঠানো হয়নি' }}</dd>
            </div>
            <div class="detail-item">
              <dt>পুনরাবৃত্তি</dt>
              <dd>{{ task.is_recurring ? 'হ্যাঁ — ' + (task.recurring_interval || 'নিয়মিত') : 'না' }}</dd>
            </div>
            <div class="detail-item">
              <dt>কর্তা</dt>
              <dd>{{ task.created_by ?? 'সিস্টেম' }}</dd>
            </div>
            <div class="detail-item" v-if="task.created_by_user">
              <dt>কর্মকর্তা</dt>
              <dd>
                <div class="inline-user">
                  <div class="user-avatar" :style="{ backgroundColor: userColor(task.created_by_user.name_bn || task.created_by_user.email) }">{{ initials(task.created_by_user.name_bn || task.created_by_user.email) }}</div>
                  <span>{{ task.created_by_user.name_bn || task.created_by_user.email }}</span>
                </div>
              </dd>
            </div>
            <div class="detail-item">
              <dt>অগ্রিম নোটিফিকেশন</dt>
              <dd>{{ task.reminder_before_hours ?? '24' }} ঘণ্টা আগে</dd>
            </div>
          </dl>

          <div v-if="task.description_bn || task.description" class="detail-section">
            <h3>বিবরণ</h3>
            <p class="detail-text">{{ task.description_bn || task.description }}</p>
          </div>

          <div v-if="task.note" class="detail-section muted">
            <h3>অভ্যন্তরীণ নোট</h3>
            <p class="detail-text">{{ task.note }}</p>
          </div>

          <div class="detail-section">
            <h3>পাঠানোর ইতিহাস</h3>
            <div v-if="task.sent_at" class="history-item">
              <div class="history-dot sent"></div>
              <div class="history-info">
                <span class="history-label">পাঠানো হয়েছে</span>
                <span class="history-time">{{ task.sent_at }}</span>
              </div>
            </div>
            <div v-else class="history-item">
              <div class="history-dot pending"></div>
              <div class="history-info">
                <span class="history-label">অপেক্ষমান</span>
                <span class="history-time">{{ task.scheduled_for ? 'নির্ধারিত: ' + task.scheduled_for : 'তাৎক্ষণিক' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Task Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal card">
        <div class="modal-header">
          <h3>টাস্ক সম্পাদনা</h3>
          <button class="close-btn" @click="showEditModal = false">×</button>
        </div>
        <form @submit.prevent="saveEdit" class="modal-body">
          <div class="form-group">
            <label>শিরোনাম (বাংলা) *</label>
            <input v-model="editForm.title_bn" class="form-control" required />
          </div>
          <div class="form-group">
            <label>পাঠানোর মাধ্যম</label>
            <select v-model="editForm.type" class="form-control">
              <option value="sms">SMS</option>
              <option value="email">ইমেইল</option>
              <option value="push">পুশ নোটিফিকেশন</option>
              <option value="whatsapp">হোয়াটসঅ্যাপ</option>
            </select>
          </div>
          <div class="form-group">
            <label>প্রাধান্য</label>
            <select v-model="editForm.priority" class="form-control">
              <option value="low">নিম্ন</option>
              <option value="medium">মধ্যম</option>
              <option value="high">উচ্চ</option>
              <option value="urgent">জরুরি</option>
            </select>
          </div>
          <div class="form-group">
            <label>নির্ধারিত সময়</label>
            <input v-model="editForm.scheduled_for" type="datetime-local" class="form-control" />
          </div>
          <div class="form-group">
            <label>বিবরণ</label>
            <textarea v-model="editForm.description_bn" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving">সংরক্ষণ করুন</button>
            <button type="button" class="btn btn-ghost" @click="showEditModal = false">বাতিল</button>
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
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()

const task = ref<any>(null)
const loading = ref(true)
const saving = ref(false)
const showEditModal = ref(false)

const editForm = reactive({
  title_bn: '',
  type: 'sms',
  priority: 'medium',
  scheduled_for: '',
  description_bn: '',
})

async function load() {
  loading.value = true
  try {
    const id = route.params.id as string
    const res = await api.get(`/reminder-tasks/${id}`)
    task.value = res.data?.data || res.data || {}
  } catch (err: any) {
    console.error('Failed to load task:', err)
    task.value = null
  } finally {
    loading.value = false
  }
}

function goBack() { router.push('/reminder-tasks') }

function openEdit() {
  if (!task.value) return
  editForm.title_bn = task.value.title_bn || task.value.title || ''
  editForm.type = task.value.type || 'sms'
  editForm.priority = task.value.priority || 'medium'
  editForm.scheduled_for = task.value.scheduled_for ? task.value.scheduled_for.slice(0, 16) : ''
  editForm.description_bn = task.value.description_bn || task.value.description || ''
  showEditModal.value = true
}

async function saveEdit() {
  saving.value = true
  try {
    const id = route.params.id as string
    await api.put(`/reminder-tasks/${id}`, editForm)
    showEditModal.value = false
    await load()
  } catch (e) {
    console.error(e)
  } finally {
    saving.value = false
  }
}

function toggleActive() {
  if (!task.value) return
  const newActive = !task.value.is_active
  api.put(`/reminder-tasks/${task.value.id}`, { ...task.value, is_active: newActive }).then(() => {
    task.value.is_active = newActive
  })
}

function typeBadge(t: string) {
  const m: Record<string, string> = { sms: 'badge-success', email: 'badge-info', push: 'badge-warning', whatsapp: 'badge-danger' }
  return m[t] || 'badge-default'
}
function priorityBadge(p: string) {
  const m: Record<string, string> = { low: 'badge-default', medium: 'badge-info', high: 'badge-warning', urgent: 'badge-danger' }
  return m[p] || 'badge-default'
}
function statusBadge(s: string) {
  const m: Record<string, string> = { pending: 'badge-warning', sent: 'badge-success', failed: 'badge-danger', scheduled: 'badge-info', draft: 'badge-default' }
  return m[s] || 'badge-default'
}
function initials(name: string) {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
}
function userColor(name: string) {
  const colors = ['#145032', '#d4af37', '#5c8eb7', '#e8573a', '#3a9e8f', '#8b5cf6', '#f59e0b']
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
.page-wrapper { padding: 1.5rem; max-width: 1100px; margin: 0 auto; }
.breadcrumb { margin-bottom: 1rem; font-size: 0.875rem; display: flex; align-items: center; }
.breadcrumb a { color: var(--color-primary); text-decoration: none; }
.breadcrumb .sep { margin: 0 0.5rem; color: var(--color-text-muted); }
.breadcrumb .current { color: var(--color-text-muted); }
.loading-state, .empty-state { text-align: center; padding: 3rem 1rem; }
.loading-state .spinner { width: 40px; height: 40px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.sticky-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.back-btn { background: none; border: 1px solid var(--color-border); border-radius: 6px; padding: 0.4rem 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; }
.back-btn:hover { background: var(--color-bg); }
.sticky-actions { display: flex; gap: 0.5rem; }

.detail-content { background: var(--color-bg-card); border-radius: 12px; border: 1px solid var(--color-border-light); }
.card-body { padding: 1.5rem; }
.detail-topbar { display: flex; gap: 0.5rem; margin-bottom: 0.75rem; }
.detail-title { margin: 0 0 1.25rem; font-size: 1.4rem; color: var(--color-text); }

.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.detail-item dt { font-size: 0.78rem; color: var(--color-text-light); margin-bottom: 0.2rem; }
.detail-item dd { margin: 0; font-size: 0.95rem; font-weight: 500; }

.detail-section { border-top: 1px solid var(--color-border-light); padding-top: 1rem; margin-top: 1rem; }
.detail-section h3 { margin: 0 0 0.5rem; font-size: 1rem; }
.detail-text { margin: 0; font-size: 0.9rem; color: var(--color-text); line-height: 1.5; }

.inline-user { display: flex; align-items: center; gap: 0.4rem; }
.user-avatar { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.65rem; font-weight: 700; }

.history-item { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; }
.history-dot { width: 10px; height: 10px; border-radius: 50%; }
.history-dot.sent { background: #10b981; }
.history-dot.pending { background: #f59e0b; }
.history-info { font-size: 0.85rem; display: flex; gap: 0.5rem; }
.history-label { font-weight: 500; }
.history-time { color: var(--color-text-light); }

.badge { padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-info { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.badge-warning { background: rgba(245, 158, 11, 0.15); color: #b45309; }
.badge-danger { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.badge-default { background: rgba(107, 114, 128, 0.15); color: #6b7280; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
.modal { width: 100%; max-width: 480px; background: var(--color-bg-card); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); }
.modal-body { padding: 1.25rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.82rem; font-weight: 500; margin-bottom: 0.35rem; }
.form-control { width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text); font-size: 0.9rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem; }

.btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; }
.btn-primary { background: var(--color-primary); color: #fff; }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-ghost { background: transparent; color: var(--color-text); }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
</style>