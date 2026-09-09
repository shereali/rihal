<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink to="/leave-applications">ছুটির আবেদন</NuxtLink>
      <icon name="chevron-down" class="breadcrumb-sep rotate-270" />
      <span>{{ leave?.title_bn || leave?.title || 'আবেদন বিস্তারিত' }}</span>
    </div>

    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>আবেদন লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!leave" class="empty-state card">
      <div class="empty-icon-wrap">
        <icon name="alert-circle" />
      </div>
      <h3>ছুটির আবেদন পাওয়া যায়নি</h3>
      <p>এই আইডির কোনো ছুটির আবেদন বিদ্যমান নেই বা মুছে ফেলা হয়েছে</p>
      <NuxtLink to="/leave-applications" class="btn btn-primary">
        <icon name="arrow-left" /> আবেদন তালিকায় ফিরে যান
      </NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <!-- Top Action Bar -->
      <div class="detail-header-card card">
        <div class="header-left">
          <NuxtLink to="/leave-applications" class="back-link">
            <icon name="arrow-left" /> ফিরে যান
          </NuxtLink>
          <div class="title-status-group">
            <h1>{{ leave.title_bn || leave.title }}</h1>
            <div class="badges-row">
              <span class="status-pill" :class="statusBadge(leave.status)">
                <span class="status-dot" />
                {{ statusText(leave.status) }}
              </span>
              <span class="type-tag">{{ leave.leave_type || 'সাধারণ' }}</span>
              <span v-if="leave.is_urgent" class="urgent-pill">
                <icon name="alert-circle" /> জরুরি
              </span>
            </div>
          </div>
        </div>

        <div class="header-actions" v-if="leave.status === 'pending'">
          <button class="btn btn-success" @click="approveLeave" :disabled="actionLoading">
            <icon name="check" /> অনুমোদন করুন
          </button>
          <button class="btn btn-danger" @click="rejectLeave" :disabled="actionLoading">
            <icon name="close" /> প্রত্যাখ্যান করুন
          </button>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="detail-grid-layout">
        <!-- Applicant & Metadata Card -->
        <div class="card info-card">
          <div class="card-header">
            <h3>আবেদনকারীর তথ্য</h3>
          </div>
          <div class="card-body">
            <div class="applicant-profile">
              <div class="user-avatar-large" :style="{ backgroundColor: userColor(leave.user_name_bn || leave.user_name || 'U') }">
                {{ initials(leave.user_name_bn || leave.user_name) }}
              </div>
              <div class="applicant-details">
                <h4>{{ leave.user_name_bn || leave.user_name }}</h4>
                <p class="text-muted" v-if="leave.user_email">{{ leave.user_email }}</p>
              </div>
            </div>

            <div class="info-items-grid">
              <div class="info-item">
                <span class="info-label">ছুটির ধরন</span>
                <span class="info-value">{{ leave.leave_type || 'সাধারণ' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">মোট সময়কাল</span>
                <span class="info-value highlight">{{ (leave.days_count || 1).toLocaleString('bn-BD') }} দিন</span>
              </div>
              <div class="info-item">
                <span class="info-label">শুরুর তারিখ</span>
                <span class="info-value">{{ formatDate(leave.start_date) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">শেষ তারিখ</span>
                <span class="info-value">{{ formatDate(leave.end_date) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">জরুরি আবেদন?</span>
                <span class="info-value">{{ leave.is_urgent ? 'হ্যাঁ, জরুরি' : 'না' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">আবেদনের সময়</span>
                <span class="info-value">{{ formatDate(leave.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reason & Description Card -->
        <div class="card description-card">
          <div class="card-header">
            <h3>ছুটির কারণ ও বিবরণ</h3>
          </div>
          <div class="card-body">
            <p class="description-text">{{ leave.description_bn || leave.description || 'কোনো বিবরণ উল্লেখ নেই' }}</p>

            <div v-if="leave.notes" class="notes-box">
              <h5>অভ্যন্তরীণ নোট:</h5>
              <p>{{ leave.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Approval Status Card -->
        <div class="card approval-card" v-if="leave.approved_by_name || leave.approved_at || leave.status !== 'pending'">
          <div class="card-header">
            <h3>অনুমোদন সংক্রান্ত তথ্য</h3>
          </div>
          <div class="card-body">
            <div class="info-items-grid">
              <div class="info-item" v-if="leave.approved_by_name">
                <span class="info-label">সিদ্ধান্ত গ্রহণকারী</span>
                <span class="info-value">{{ leave.approved_by_name }}</span>
              </div>
              <div class="info-item" v-if="leave.approved_at">
                <span class="info-label">অনুমোদন / সিদ্ধান্তের তারিখ</span>
                <span class="info-value">{{ formatDate(leave.approved_at) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">বর্তমান অবস্থা</span>
                <span class="status-pill" :class="statusBadge(leave.status)">
                  <span class="status-dot" />
                  {{ statusText(leave.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRoute, useRouter } from 'vue-router'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const route = useRoute()
const router = useRouter()

const leave = ref<any>(null)
const loading = ref(true)
const actionLoading = ref(false)

async function load() {
  loading.value = true
  try {
    const id = route.params.id as string
    const res = await api.get(`/leave-applications/${id}`)
    leave.value = res.data?.data || res.data || {}
  } catch (err: any) {
    console.error('Failed to load leave:', err)
    leave.value = null
  } finally {
    loading.value = false
  }
}

async function approveLeave() {
  if (!confirm('আপনি কি এই ছুটির আবেদন অনুমোদন করতে চান?')) return
  actionLoading.value = true
  try {
    const id = route.params.id as string
    await api.put(`/leave-applications/${id}`, { status: 'approved' })
    await load()
  } catch (e) {
    console.error(e)
  } finally {
    actionLoading.value = false
  }
}

async function rejectLeave() {
  if (!confirm('আপনি কি এই ছুটির আবেদন প্রত্যাখ্যান করতে চান?')) return
  actionLoading.value = true
  try {
    const id = route.params.id as string
    await api.put(`/leave-applications/${id}`, { status: 'rejected' })
    await load()
  } catch (e) {
    console.error(e)
  } finally {
    actionLoading.value = false
  }
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
  max-width: 1100px;
  margin: 0 auto;
  padding: 1.75rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--color-text-light);
  margin-bottom: 1.25rem;
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.rotate-270 {
  transform: rotate(270deg);
}

/* Detail Header Card */
.detail-header-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.75rem;
  margin-bottom: 1.5rem;
  border-radius: 14px;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  flex-wrap: wrap;
  gap: 1.25rem;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-primary);
  text-decoration: none;
  margin-bottom: 0.6rem;
}

.back-link:hover {
  text-decoration: underline;
}

.title-status-group h1 {
  font-size: 1.5rem;
  font-weight: 800;
  margin: 0 0 0.6rem;
  color: var(--color-text);
}

.badges-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

/* Grid Layout */
.detail-grid-layout {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 1.25rem;
}

.card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light);
  background: rgba(0, 0, 0, 0.015);
}

.card-header h3 {
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text);
}

.card-body {
  padding: 1.5rem;
}

/* Applicant Profile */
.applicant-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
  margin-bottom: 1.25rem;
}

.user-avatar-large {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.15rem;
  font-weight: 700;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.applicant-details h4 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 0.2rem;
}

.applicant-details p {
  font-size: 0.82rem;
  margin: 0;
}

/* Info Items Grid */
.info-items-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
}

.info-label {
  font-size: 0.76rem;
  color: var(--color-text-light);
  font-weight: 500;
  margin-bottom: 0.2rem;
}

.info-value {
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--color-text);
}

.info-value.highlight {
  color: var(--color-primary);
  font-size: 1.05rem;
}

/* Description Text */
.description-text {
  font-size: 0.95rem;
  line-height: 1.6;
  color: var(--color-text);
  margin: 0 0 1.25rem;
  white-space: pre-wrap;
}

.notes-box {
  background: var(--color-bg);
  border: 1px solid var(--color-border-light);
  border-radius: 8px;
  padding: 0.85rem 1rem;
}

.notes-box h5 {
  font-size: 0.82rem;
  font-weight: 600;
  margin: 0 0 0.35rem;
  color: var(--color-text-light);
}

.notes-box p {
  font-size: 0.88rem;
  margin: 0;
  color: var(--color-text);
}

/* Status Pills & Tags */
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

.badge-pending { background: rgba(245, 158, 11, 0.12); color: #b45309; }
.badge-pending .status-dot { background: #f59e0b; }
.badge-approved { background: rgba(16, 185, 129, 0.12); color: #059669; }
.badge-approved .status-dot { background: #10b981; }
.badge-rejected { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
.badge-rejected .status-dot { background: #ef4444; }
.badge-cancelled { background: rgba(107, 114, 128, 0.12); color: #4b5563; }
.badge-cancelled .status-dot { background: #6b7280; }

.type-tag {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
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

/* Buttons */
.btn {
  padding: 0.55rem 1.15rem;
  border-radius: 8px;
  font-size: 0.86rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  transition: all 0.2s ease;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #145032 0%, #1a6b43 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25);
}

.btn-success {
  background: #059669;
  color: #fff;
  box-shadow: 0 3px 10px rgba(5, 150, 105, 0.25);
}

.btn-success:hover {
  background: #047857;
  transform: translateY(-1px);
}

.btn-danger {
  background: #dc2626;
  color: #fff;
  box-shadow: 0 3px 10px rgba(220, 38, 38, 0.25);
}

.btn-danger:hover {
  background: #b91c1c;
  transform: translateY(-1px);
}

.empty-state, .loading-state {
  padding: 3.5rem 1.5rem;
  text-align: center;
  color: var(--color-text-light);
}

.empty-icon-wrap {
  font-size: 2.5rem;
  color: var(--color-primary);
  margin-bottom: 0.75rem;
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