<template>
  <div class="activity-page">
    <div class="page-header">
      <div class="header-left">
        <h1>গতিবিধি লগ</h1>
        <p class="text-muted">{{ logs?.data?.meta?.total || 0 }}টি রেকর্ড</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state">
          <div class="spinner" />
          <p>লগ লোড হচ্ছে...</p>
        </div>

        <div v-else-if="(logs?.data?.data || []).length === 0" class="empty-state">
          <p>কোনো গতিবিধি লগ নেই</p>
        </div>

        <div v-else class="filters-row">
          <div class="form-row">
            <div class="form-group">
              <label>একশন টাইপ</label>
              <select v-model="filters.action" @change="applyFilters">
                <option value="">সব একশন</option>
                <option value="created">তৈরি (created)</option>
                <option value="updated">আপডেট (updated)</option>
                <option value="deleted">মুছে ফেলা (deleted)</option>
                <option value="login">লগইন (login)</option>
                <option value="logout">লগআউট (logout)</option>
                <option value="view">দেখা (view)</option>
              </select>
            </div>
            <div class="form-group">
              <label>ব্যবহারকারী</label>
              <select v-model="filters.userId" @change="applyFilters">
                <option value="">সব ব্যবহারকারী</option>
                <option v-for="u in allUsers" :key="u.id" :value="u.id">
                  {{ u.name_bn || u.name_en || u.email }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>শুরুর তারিখ</label>
              <input v-model="filters.from" type="date" @change="applyFilters" />
            </div>
            <div class="form-group">
              <label>শেষ তারিখ</label>
              <input v-model="filters.to" type="date" @change="applyFilters" />
            </div>
            <button class="btn btn-outline btn-sm" @click="resetFilters">
              <icon name="cancel" /> রিসেট
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>সময়</th>
                <th>একশন</th>
                <th>ব্যবহারকারী</th>
                <th>এনটিটি</th>
                <th>আইপি</th>
                <th>বর্ণনা</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(log, idx) in logs?.data?.data || []" :key="log.id">
                <td class="text-muted text-sm">{{ (logs.data.current_page - 1) * logs.data.per_page + idx + 1 }}</td>
                <td>
                  <span class="timestamp">{{ formatDate(log.created_at) }}</span>
                  <span class="text-muted text-xs">{{ formatTime(log.created_at) }}</span>
                </td>
                <td>
                  <span class="action-badge" :class="actionClass(log.action)">
                    {{ formatAction(log.action) }}
                  </span>
                </td>
                <td>
                  <div v-if="log.user" class="user-cell">
                    <span class="user-name">{{ log.user_name || 'অজ্ঞাত' }}</span>
                    <span class="user-type" :class="userTypeClass(log.user_type)">
                      {{ formatUserType(log.user_type) }}
                    </span>
                  </div>
                  <span v-else class="text-muted">অজ্ঞাত</span>
                </td>
                <td>
                  <span class="entity-type">{{ log.entity_type }}</span>
                  <span class="text-muted text-xs">#{{ log.entity_id || '-' }}</span>
                </td>
                <td class="text-muted text-sm">{{ log.ip_address || '-' }}</td>
                <td>
                  <p class="desc-text">{{ log.description || '-' }}</p>
                  <button
                    v-if="log.description"
                    class="btn-link"
                    @click="toggleDesc(log.id)"
                  >
                    {{ expandedDescs.has(log.id) ? 'সংক্ষেপে' : 'বিস্তারিত' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="logs?.data?.meta && logs.data.meta.total > logs.data.per_page" class="pagination-wrapper">
          <div class="pagination-info">
            {{ logs.data.meta.from }} – {{ logs.data.meta.to }} / {{ logs.data.meta.total }}টি
          </div>
          <div class="pagination">
            <button
              v-for="page in totalPages"
              :key="page"
              :class="['page-btn', { active: page === logs?.data?.current_page }]"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const logs = ref<any>(null)
const totalPages = ref(1)
const expandedDescs = ref(new Map<number, boolean>())

const filters = ref({
  action: '',
  userId: '',
  from: '',
  to: '',
})

const allUsers = ref<any[]>([])

async function loadUsers() {
  try {
    const res = await api.get('/users?per_page=100')
    allUsers.value = res.data?.data?.data || res.data?.data || []
  } catch {}
}

async function loadLogs(page = 1) {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: '20',
    })
    if (filters.value.action) params.set('action', filters.value.action)
    if (filters.value.userId) params.set('user_id', filters.value.userId)
    if (filters.value.from) params.set('from', filters.value.from + ' 00:00:00')
    if (filters.value.to) params.set('to', filters.value.to + ' 23:59:59')

    const res = await api.get(`/activity-log?${params}`)
    logs.value = res.data
    totalPages.value = res.data.meta?.last_page || 1
  } catch (error) {
    console.error('Failed to load activity logs:', error)
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  loadLogs(1)
}

function resetFilters() {
  filters.value = { action: '', userId: '', from: '', to: '' }
  loadLogs(1)
}

const goToPage = (page: number) => loadLogs(page)

function formatDate(date: string | null | undefined): string {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatTime(date: string | null | undefined): string {
  if (!date) return '-'
  return new Date(date).toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit' })
}

function formatAction(action: string): string {
  const map: Record<string, string> = {
    created: 'তৈরি',
    updated: 'আপডেট',
    deleted: 'মুছে ফেলা',
    login: 'লগইন',
    logout: 'লগআউট',
    view: 'দেখা',
  }
  return map[action] || action
}

function formatUserType(type: string | null | undefined): string {
  if (!type) return 'অজ্ঞাত'
  const map: Record<string, string> = {
    admin: 'অ্যাডমিন',
    super_admin: 'সুপার অ্যাডমিন',
    teacher: 'শিক্ষক',
    student: 'ছাত্র',
    guardian: 'অভিভাবক',
  }
  return map[type] || type
}

function actionClass(action: string): string {
  const cls: Record<string, string> = {
    created: 'action-created',
    updated: 'action-updated',
    deleted: 'action-deleted',
    login: 'action-login',
    logout: 'action-logout',
    view: 'action-view',
  }
  return cls[action] || 'action-other'
}

function userTypeClass(type: string | null | undefined): string {
  if (!type) return ''
  const cls: Record<string, string> = {
    super_admin: 'type-super-admin',
    admin: 'type-admin',
    teacher: 'type-teacher',
    student: 'type-student',
    guardian: 'type-guardian',
  }
  return cls[type] || ''
}

function toggleDesc(id: number) {
  const current = expandedDescs.value.has(id)
  if (current) {
    expandedDescs.value.delete(id)
  } else {
    expandedDescs.value.set(id, true)
  }
  expandedDescs.value = new Map(expandedDescs.value)
}

onMounted(() => {
  loadUsers()
  loadLogs()
})
</script>

<style scoped>
.activity-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }

.filters-row { margin-bottom: 1rem; padding: 0 0.5rem; }
.form-row { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group label { font-size: 0.75rem; font-weight: 500; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.3px; }
.form-group select, .form-group input { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); background: var(--color-bg); color: var(--color-text); font-size: 0.875rem; }
.form-group select:focus, .form-group input:focus { border-color: var(--color-primary); outline: none; }

.table-responsive { overflow-x: auto; margin-bottom: 1rem; }
.table { width: 100%; border-collapse: collapse; }
.table th { padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); border-bottom: 2px solid var(--color-border); background: var(--color-bg-card); }
.table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--color-border-light); vertical-align: middle; }
.table tr:hover td { background: var(--color-bg-hover); }
.table .text-muted { color: var(--color-text-muted); }
.table .text-sm { font-size: 0.8125rem; }
.table .text-xs { font-size: 0.6875rem; }

.timestamp { font-weight: 600; color: var(--color-text); }
.action-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.action-created { background: #e8f5e9; color: #2e7d32; }
.action-updated { background: #e3f2fd; color: #1565c0; }
.action-deleted { background: #fce4e4; color: #c62828; }
.action-login { background: #e8f5e9; color: #388e3c; }
.action-logout { background: #f5f5f5; color: #616161; }
.action-view { background: #fff3e0; color: #e65100; }
.action-other { background: #f5f5f5; color: #616161; }

.user-cell { display: flex; flex-direction: column; gap: 0.15rem; }
.user-name { font-weight: 500; color: var(--color-text); }
.user-type { font-size: 0.6875rem; padding: 0.1rem 0.4rem; border-radius: 999px; font-weight: 500; display: inline-block; }
.type-super-admin { background: #fce4e4; color: #c62828; }
.type-admin { background: #fff3e0; color: #e65100; }
.type-teacher { background: #e3f2fd; color: #1565c0; }
.type-student { background: #e8f5e9; color: #2e7d32; }
.type-guardian { background: #f3e5f5; color: #7b1fa2; }

.entity-type { font-size: 0.8125rem; font-weight: 500; color: var(--color-text); }
.btn-link { background: none; border: none; color: var(--color-primary); cursor: pointer; font-size: 0.75rem; text-decoration: underline; padding: 0; }

.pagination-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-top: 1px solid var(--color-border-light); margin-top: 0.5rem; }
.pagination-info { font-size: 0.8125rem; color: var(--color-text-muted); }
.pagination { display: flex; gap: 0.5rem; }
.page-btn { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; }
.page-btn:hover:not(.active) { background: var(--color-bg-hover); border-color: var(--color-border-dark); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }

.loading-state { padding: 3rem; text-align: center; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state { padding: 3rem; text-align: center; color: var(--color-text-muted); }
</style>
