<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink to="/leave-applications">ছুটির আবেদন</NuxtLink>
      <span class="sep">/</span>
      <span class="current">{{ leave?.title_bn || leave?.title || 'আবেদন বিস্তারিত' }}</span>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>

    <div v-else-if="!leave" class="empty-state">
      <div class="empty-icon"><icon name="alert-circle" /></div>
      <h3>আবেদন পাওয়া যায়নি</h3>
      <NuxtLink to="/leave-applications" class="btn btn-primary">আবেদন তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-screen">
      <div class="sticky-header">
        <button class="back-btn" @click="goBack"><icon name="arrow-left" /> বাংলাদেশ</button>
        <div class="sticky-actions">
          <button v-if="leave.status === 'pending'" class="btn btn-outline btn-sm" @click="editLeave"><icon name="edit" /> সম্পাদনা</button>
        </div>
      </div>

      <div class="detail-content card">
        <div class="card-body">
          <div class="detail-topbar">
            <span class="badge badge-lg status-badge" :class="statusBadge(leave.status)">{{ leave.status }}</span>
            <span class="badge badge-lg">{{ leave.leave_type }}</span>
            <span v-if="leave.is_urgent" class="badge badge-lg badge-danger">জরুরি</span>
          </div>

          <h1 class="detail-title">{{ leave.title_bn || leave.title }}</h1>

          <dl class="detail-grid">
            <div class="detail-item">
              <dt>কর্মকর্তা</dt>
              <dd>
                <div class="inline-user">
                  <div class="user-avatar" :style="{ backgroundColor: userColor(leave.user_name_bn) }">{{ initials(leave.user_name_bn) }}</div>
                  <span>{{ leave.user_name_bn }} ({{ leave.user_email }})</span>
                </div>
              </dd>
            </div>
            <div class="detail-item">
              <dt>ছুটির ধরন</dt><dd>{{ leave.leave_type }}</dd>
            </div>
            <div class="detail-item">
              <dt>শুরু</dt><dd class="bigger">{{ leave.start_date }}</dd>
            </div>
            <div class="detail-item">
              <dt>শেষ</dt><dd class="bigger">{{ leave.end_date }}</dd>
            </div>
            <div class="detail-item">
              <dt>মোট দিন</dt><dd>{{ leave.days_count }} দিন</dd>
            </div>
            <div class="detail-item">
              <dt>জরুরি</dt><dd>{{ leave.is_urgent ? 'হ্যাঁ' : 'না' }}</dd>
            </div>
            <div class="detail-item">
              <dt>অবস্থা</dt><dd><span class="badge status-badge" :class="statusBadge(leave.status)">{{ leave.status }}</span></dd>
            </div>
            <div class="detail-item" v-if="leave.approved_by_name">
              <dt>অনুমোদনকারী</dt><dd>{{ leave.approved_by_name }}</dd>
            </div>
            <div class="detail-item" v-if="leave.approved_at">
              <dt>অনুমোদন সময়</dt><dd>{{ leave.approved_at }}</dd>
            </div>
            <div class="detail-item">
              <dt>জমা দেওয়া</dt><dd>{{ leave.created_at }}</dd>
            </div>
          </dl>

          <div v-if="leave.description_bn" class="detail-section">
            <h3>বিবরণ</h3>
            <p class="detail-text">{{ leave.description_bn }}</p>
          </div>

          <div v-if="leave.notes" class="detail-section muted">
            <h3>নোট</h3>
            <p class="detail-text">{{ leave.notes }}</p>
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

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const route = useRoute()
const router = useRouter()

const leave = ref<any>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const id = route.params.id as string
    const res = await api.get(`/leave-applications/${id}`)
    leave.value = res.data || {}
  } catch (err: any) {
    console.error('Failed to load leave:', err)
    leave.value = null
  } finally {
    loading.value = false
  }
}

function goBack() { router.push('/leave-applications') }

function editLeave() { router.push(`/leave-applications/${route.params.id}/edit`) }

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
</style>