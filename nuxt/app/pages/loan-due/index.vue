<template>
  <div class="loans-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/dashboard" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>লোন ও ডিউ</h1>
        <p class="subtitle">ঋণ, কিস্তি ও বাকিয়া ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline btn-sm" :disabled="exporting" @click="downloadExport"><icon name="document" /> {{ exporting ? 'তৈরি হচ্ছে...' : 'CSV রিপোর্ট' }}</button>
        <NuxtLink to="/loan-due/create" class="btn btn-primary btn-sm"><icon name="plus" /> নতুন ঋণ</NuxtLink>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon"><icon name="document" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_loans || 0 }}</p>
          <p class="stat-label">মোট ঋণ</p>
        </div>
      </div>
      <div class="stat-card stat-warning">
        <div class="stat-icon"><icon name="cash" /></div>
        <div class="stat-info">
          <p class="stat-value">৳{{ summary?.total_outstanding ? Number(summary.total_outstanding).toLocaleString('bn-BD') : 0 }}</p>
          <p class="stat-label">বকেয়া পরিমাণ</p>
        </div>
      </div>
      <div class="stat-card stat-danger">
        <div class="stat-icon"><icon name="alert" /></div>
        <div class="stat-info">
          <p class="stat-value">৳{{ summary?.total_overdue ? Number(summary.total_overdue).toLocaleString('bn-BD') : 0 }}</p>
          <p class="stat-label">বিলম্বিত</p>
        </div>
      </div>
      <div class="stat-card stat-success">
        <div class="stat-icon"><icon name="cash" /></div>
        <div class="stat-info">
          <p class="stat-value">৳{{ summary?.total_paid ? Number(summary.total_paid).toLocaleString('bn-BD') : 0 }}</p>
          <p class="stat-label">মোট প্রদান</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <input v-model="search" type="text" placeholder="সার্চ করুন..." @input="debouncedLoad" class="search-input" />
      <select v-model="filterStatus" @change="loadLoans" class="filter-select">
        <option value="">সকল স্ট্যাটাস</option>
        <option value="active">সক্রিয়</option>
        <option value="paid">পূর্ণ প্রদান</option>
        <option value="overdue">বিলম্বিত</option>
        <option value="closed">বন্ধ</option>
      </select>
      <select v-if="false" v-model="filterType" @change="loadLoans" class="filter-select">
        <option value="">সকল ধরণ</option>
      </select>
    </div>

    <!-- Loans Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(loans?.data || []).length === 0" class="empty-state"><p>কোনো ঋণ নেই</p></div>
        <table v-else class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>শিরোনাম</th>
              <th>ধরণ</th>
              <th>প্রতিষ্ঠানকর্তা</th>
              <th>মূল (৳)</th>
              <th>প্রদত্ত (৳)</th>
              <th>বকেয়া (৳)</th>
              <th>স্ট্যাটাস</th>
              <th>ক্রিয়া</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="loan in (loans?.data || [])" :key="loan.id">
              <td>{{ loan.id }}</td>
              <td>
                <p class="font-medium">{{ loan.title_bn }}</p>
                <p v-if="loan.title_en" class="text-muted text-sm">{{ loan.title_en }}</p>
              </td>
              <td>{{ loan.loan_type || 'সাধারণ' }}</td>
              <td>{{ loan.user?.name_bn || loan.user?.name_en || loan.user_id || '-' }}</td>
              <td>{{ loan.principal_amount ? Number(loan.principal_amount).toLocaleString('bn-BD') : 0 }}</td>
              <td>{{ loan.total_paid ? Number(loan.total_paid).toLocaleString('bn-BD') : 0 }}</td>
              <td>{{ loan.remaining_amount ? Number(loan.remaining_amount).toLocaleString('bn-BD') : 0 }}</td>
              <td>
                <span class="badge" :class="statusClass(loan.status)">
                  {{ statusLabel(loan.status) }}
                </span>
              </td>
              <td>
                <NuxtLink :to="`/loan-due/${loan.id}`" class="btn btn-sm btn-outline"><icon name="eye" /></NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="loans?.last_page > 1" class="pagination">
          <button class="btn btn-sm btn-outline" :disabled="!loans.prev_page_url" @click="loadPage(loans.current_page - 1)">পূর্ব</button>
          <span class="page-info">পৃষ্ঠা {{ loans.current_page }} / {{ loans.last_page }}</span>
          <button class="btn btn-sm btn-outline" :disabled="!loans.next_page_url" @click="loadPage(loans.current_page + 1)">পরবর্তী</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const exporting = ref(false)
const loans = ref<any>(null)
const summary = ref<any>(null)
const search = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
let debounceTimer: any

async function loadLoans(page = 1) {
  loading.value = true
  currentPage.value = page
  try {
    const params = new URLSearchParams()
    params.set('per_page', '50')
    params.set('page', String(page))
    if (filterStatus.value) params.set('status', filterStatus.value)
    if (search.value) params.set('search', search.value)

    const r = await api.get(`/loans?${params.toString()}`)
    loans.value = r.data?.data
  } catch (e: any) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadSummary() {
  try {
    const r = await api.get('/loans-summary')
    summary.value = r.data?.data
  } catch (e: any) {
    console.error(e)
  }
}

async function downloadExport() {
  exporting.value = true
  try {
    const response = await api.get('/reports/loans.csv', { responseType: 'blob' })
    const url = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.download = `loans-${new Date().toISOString().slice(0, 10)}.csv`
    link.click()
    URL.revokeObjectURL(url)
  } finally {
    exporting.value = false
  }
}

function debouncedLoad() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadLoans(1)
  }, 500)
}

function loadPage(page: number) {
  loadLoans(page)
}

function statusClass(status: string): string {
  switch (status) {
    case 'paid': return 'badge-success'
    case 'overdue': return 'badge-danger'
    case 'closed': return 'badge-secondary'
    default: return 'badge-outline'
  }
}

function statusLabel(status: string): string {
  switch (status) {
    case 'paid': return 'পূর্ণ'
    case 'overdue': return 'বিলম্বিত'
    case 'closed': return 'বন্ধ'
    default: return 'সক্রিয়'
  }
}

onMounted(() => {
  loadLoans()
  loadSummary()
})
</script>

<style scoped>
.loans-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.subtitle { color: var(--color-text-light); font-size: 0.9rem; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.header-actions { display: flex; gap: 0.5rem; }
.btn { padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; }
.btn-sm { padding: 0.35rem 0.8rem; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 0.7rem; }
.stat-icon { width: 36px; height: 36px; flex-shrink: 0; color: var(--color-primary); display: flex; align-items: center; justify-content: center; }
.stat-info p { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.stat-value { font-size: 1.3rem; font-weight: 700; color: var(--color-text); }
.stat-label { font-size: 0.8rem; color: var(--color-text-light); }
.stat-warning .stat-icon { color: #d97706; }
.stat-danger .stat-icon { color: #dc2626; }
.stat-success .stat-icon { color: #16a34a; }
.filters-bar { display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.search-input { padding: 0.5rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 0.9rem; font-family: 'Noto Sans Bengali', sans-serif; width: 220px; }
.filter-select { padding: 0.5rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 0.9rem; font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg); cursor: pointer; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card-body { padding: 1rem; }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.spinner { width: 24px; height: 24px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { padding: 1.5rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.table { width: 100%; border-collapse: collapse; font-family: 'Noto Sans Bengali', sans-serif; }
.table th, .table td { padding: 0.6rem 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border-light); }
.table th { font-weight: 600; font-size: 0.8rem; color: var(--color-text-light); }
.font-medium { font-weight: 500; }
.text-sm { font-size: 0.8rem; }
.badge { padding: 0.2rem 0.6rem; border-radius: 10px; font-size: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; white-space: nowrap; }
.badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.badge-danger { background: rgba(220, 38, 38, 0.15); color: #dc2626; }
.badge-secondary { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.badge-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text-light); }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.page-info { font-size: 0.85rem; color: var(--color-text-light); }
</style>
