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
        <button class="back-btn" @click="goBack"><icon name="arrow-left" /> বাংলাদেশ</button>
        <div class="sticky-actions">
          <button class="btn btn-outline btn-sm" @click="editTask"><icon name="edit" /> সম্পাদনা</button>
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
              <dd>{{ task.is_recurring ? 'হ্যাঁ — ' + task.recurring_interval : 'না' }}</dd>
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

          <div v-if="task.description_bn" class="detail-section">
            <h3>বিবরণ</h3>
            <p class="detail-text">{{ task.description_bn }}</p>
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
                <span class="history-label">এখনো পাঠানো হয়নি</span>
                <span class="history-time">নির্ধারিত: {{ task.scheduled_for ?? '—' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delivery channels -->
      <div v-if="task.delivery_channels?.length" class="channels-grid card">
        <div class="card-body">
          <h3 class="section-title">পাঠানোর মাধ্যম</h3>
          <div class="channels-list">
            <div v-for="channel in task.delivery_channels" :key="channel" class="channel-item">
              <div class="channel-icon" :class="'channel-' + channel"><icon :name="channelIcon(channel)" /></div>
              <span>{{ channelLabel(channel) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const task = ref<any>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const id = route.params.id as string
    const res = await api.get(`/reminder-tasks/${id}`)
    task.value = res.data || {}
  } catch (err: any) {
    console.error('Failed to load task:', err)
    task.value = null
  } finally {
    loading.value = false
  }
}

function goBack() { navigateTo('/reminder-tasks') }

function editTask() { navigateTo(`/reminder-tasks/${route.params.id}/edit`) }

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
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colors[Math.abs(hash) % colors.length]
}
function channelIcon(c: string) {
  const m: Record<string, string> = { sms: 'message', email: 'mail', push: 'notification', whatsapp: 'whatsapp' }
  return m[c] || 'share'
}
function channelLabel(c: string) {
  const m: Record<string, string> = { sms: 'SMS বার্তা', email: 'ইমেইল', push: 'পুশ নোটিফিকেশন', whatsapp: 'হোয়াটসঅ্যাপ' }
  return m[c] || c
}

onMounted(() => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
  load()
})
</script>

<style scoped>
.page-wrapper { padding: 1.5rem; }
.breadcrumb { margin-bottom: 1rem; font-size: 0.875rem; }
.breadcrumb a { color: var(--color-primary); text-decoration: none; }
.breadcrumb .sep { margin: 0 0.5rem; color: var(--color-text-muted); }
.breadcrumb .current { color: var(--color-text-muted); }
.loading-state, .empty-state { text-align: center; padding: 3rem 1rem; }
.loading-state .spinner { width: 40px; height: 40px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state .empty-icon { font-size: 3rem; margin-bottom: 1rem; color: var(--color-text-muted); }
.detail-screen { display: flex; flex-direction: column; gap: 1rem; }
.sticky-header { position: sticky; top: 0; background: var(--color-bg); padding: 0.75rem 0; border-bottom: 1px solid var(--color-border-light); display: flex; justify-content: space-between; align-items: center; z-index: 10; }
.back-btn { display: flex; align-items: center; gap: 0.375rem; background: none; border: none; cursor: pointer; font-size: 0.875rem; color: var(--color-text-muted); }
.back-btn:hover { color: var(--color-primary); }
.sticky-actions { display: flex; gap: 0.5rem; }
.detail-content { background: var(--color-bg-card); border-radius: var(--radius-lg); border: 1px solid var(--color-border-light); }
.card-body { padding: 1.5rem; }
.detail-topbar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; }
.badge-lg { font-size: 0.875rem; padding: 0.375rem 0.75rem; border-radius: var(--radius-sm); }
.detail-title { font-size: 1.5rem; margin: 0 0 1.5rem; font-weight: 700; }
.detail-grid { display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 1.5rem; margin-bottom: 1rem; }
.detail-item { margin-bottom: 0.5rem; }
.detail-item dt { font-size: 0.8125rem; color: var(--color-text-muted); font-weight: 500; }
.detail-item dd { font-size: 0.875rem; margin: 0; }
.detail-item dd.bigger { font-size: 1rem; font-weight: 500; }
.inline-user { display: flex; align-items: center; gap: 0.5rem; }
.user-avatar { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.75rem; font-weight: 600; }
.detail-section { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light); }
.detail-section h3 { font-size: 1rem; margin: 0 0 0.75rem; color: var(--color-text-muted); font-weight: 500; }
.detail-text { font-size: 0.9375rem; white-space: pre-wrap; margin: 0; line-height: 1.6; }
.detail-section.muted { background: var(--color-bg); border-radius: var(--radius-sm); padding: 1rem; }
.history-item { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem 0; }
.history-item:not(:last-child) { border-bottom: 1px solid var(--color-border-light); }
.history-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 0.35rem; flex-shrink: 0; }
.history-dot.sent { background: var(--color-success); }
.history-dot.pending { background: var(--color-warning); }
.history-info { display: flex; flex-direction: column; }
.history-label { font-size: 0.875rem; font-weight: 500; }
.history-time { font-size: 0.8125rem; color: var(--color-text-muted); }
.channels-grid { margin-top: 1rem; }
.channels-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem; }
.channel-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--color-bg); border-radius: var(--radius-sm); border: 1px solid var(--color-border-light); }
.channel-item .channel-icon { background: var(--color-bg-card); min-width: 36px; height: 36px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
.channel-sms .channel-icon { color: var(--color-success); }
.channel-email .channel-icon { color: var(--color-info); }
.channel-push .channel-icon { color: var(--color-warning); }
.channel-whatsapp .channel-icon { color: var(--color-danger); }
</style>