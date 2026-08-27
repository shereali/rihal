<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">আর্থিক হিসাব</span>
        <h1>লোন ও কর্জে হাসানা ব্যবস্থাপনা</h1>
        <p class="page-subtitle">ঋণ গ্রহণ, কিস্তি পরিশোধ, বিলম্বিত হিসাব ও বকেয়া আদায় পর্যবেক্ষণ</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" :disabled="exporting" @click="downloadExport">
          <icon name="download" /> {{ exporting ? 'তৈরি হচ্ছে...' : 'CSV রিপোর্ট' }}
        </button>
        <NuxtLink to="/loan-due/create" class="btn btn-primary">
          <icon name="plus" /> নতুন ঋণ অনুমোদন
        </NuxtLink>
        <button class="btn btn-outline" @click="loadLoans(currentPage)">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="document-text" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_loans || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট ঋণ হিসাব</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="cash-multiple" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.total_outstanding) }} ৳</span>
          <span class="stat-label">মোট বকেয়া স্থিতি</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap red"><icon name="alert-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.total_overdue) }} ৳</span>
          <span class="stat-label">মেয়াদোত্তীর্ণ / বিলম্বিত</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.total_paid) }} ৳</span>
          <span class="stat-label">মোট আদায়কৃত কিস্তি</span>
        </div>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ঋণের শিরোনাম বা গ্রহীতার নাম খুঁজুন..." @input="debouncedLoad" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadLoans(1)">×</button>
      </div>
      <select v-model="filterStatus" class="form-select" @change="loadLoans(1)">
        <option value="">সকল স্ট্যাটাস (All Status)</option>
        <option value="active">সক্রিয় ঋণ</option>
        <option value="paid">সম্পূর্ণ পরিশোধিত</option>
        <option value="overdue">মেয়াদোত্তীর্ণ / বকেয়া</option>
        <option value="closed">বন্ধ হিসাব</option>
      </select>
      <div class="pagination-info" v-if="loans?.total">
        মোট <span class="highlight">{{ (loans?.total || 0).toLocaleString('bn-BD') }}</span> টি রেকর্ড
      </div>
    </div>

    <!-- Loans Table -->
    <div v-if="loading" class="loading-state card"><div class="spinner" /><p>ঋণ তালিকা লোড হচ্ছে...</p></div>
    <div v-else-if="!(loans?.data || []).length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="money" /></div>
      <h3>কোনো ঋণ বা লোন রেকর্ড নেই</h3>
      <p>নতুন কর্জে হাসানা বা ঋণ এন্ট্রি করে কিস্তি হিসাব শুরু করুন</p>
      <NuxtLink to="/loan-due/create" class="btn btn-primary"><icon name="plus" /> নতুন ঋণ অনুমোদন</NuxtLink>
    </div>
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিরোনাম</th>
              <th>ধরণ</th>
              <th>ঋণগ্রহীতা</th>
              <th>মূলধন</th>
              <th>পরিশোধিত</th>
              <th>অবশিষ্ট বকেয়া</th>
              <th class="text-center">অবস্থা</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="loan in (loans?.data || [])" :key="loan.id">
              <td>
                <div class="user-cell">
                  <div>
                    <strong>{{ loan.title_bn }}</strong>
                    <div class="sub-text" v-if="loan.title_en">{{ loan.title_en }}</div>
                  </div>
                </div>
              </td>
              <td><span class="type-tag">{{ loan.loan_type || 'কর্জে হাসানা' }}</span></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(loan.user?.name_bn || loan.user?.name_en || 'গ') }">
                    {{ (loan.user?.name_bn || loan.user?.name_en || 'গ').charAt(0) }}
                  </div>
                  <span>{{ loan.user?.name_bn || loan.user?.name_en || 'কর্মী' }}</span>
                </div>
              </td>
              <td><strong>{{ formatCurrency(loan.principal_amount) }} ৳</strong></td>
              <td><strong class="text-success">{{ formatCurrency(loan.total_paid) }} ৳</strong></td>
              <td><strong :class="Number(loan.remaining_amount) > 0 ? 'text-danger' : 'text-muted'">{{ formatCurrency(loan.remaining_amount) }} ৳</strong></td>
              <td class="text-center">
                <span class="status-pill" :class="statusClass(loan.status)">
                  <span class="status-dot" />
                  {{ statusLabel(loan.status) }}
                </span>
              </td>
              <td class="text-right">
                <NuxtLink :to="`/loan-due/${loan.id}`" class="action-btn" title="কিস্তি ও বিবরণ">
                  <icon name="eye" />
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="loans?.last_page > 1" class="pagination-footer">
        <button class="btn btn-sm btn-outline" :disabled="currentPage <= 1" @click="loadLoans(currentPage - 1)">
          পূর্ববর্তী
        </button>
        <span class="page-indicator">{{ currentPage.toLocaleString('bn-BD') }} / {{ (loans.last_page || 1).toLocaleString('bn-BD') }}</span>
        <button class="btn btn-sm btn-outline" :disabled="currentPage >= loans.last_page" @click="loadLoans(currentPage + 1)">
          পরবর্তী
        </button>
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
    const r = await api.get('/loans/summary')
    summary.value = r.data?.data
  } catch (e) {
    console.error(e)
  }
}

function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadLoans(1), 300)
}

async function downloadExport() {
  exporting.value = true
  try {
    const res = await api.get('/reports/loans.csv', { responseType: 'blob' })
    const blob = res.data as Blob
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `loans_${new Date().toISOString().slice(0, 10)}.csv`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    console.error(e)
  } finally {
    exporting.value = false
  }
}

function formatCurrency(val: any) {
  if (!val) return '০'
  return Number(val).toLocaleString('bn-BD')
}

function statusClass(s: string) {
  if (s === 'paid') return 'badge-approved'
  if (s === 'overdue') return 'badge-rejected'
  return 'badge-pending'
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    active: 'চলমান',
    paid: 'পরিশোধিত',
    overdue: 'মেয়াদোত্তীর্ণ',
    closed: 'বন্ধ',
  }
  return map[s] || s || 'সক্রিয়'
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

onMounted(() => {
  loadLoans()
  loadSummary()
})
</script>

<style scoped>
.page-wrapper { max-width: 1320px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.eyebrow { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--color-primary); letter-spacing: 0.08em; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.clear-search-btn { background: none; border: none; font-size: 1.1rem; color: var(--color-text-light); cursor: pointer; padding: 0 0.2rem; }
.pagination-info { margin-left: auto; font-size: 0.85rem; color: var(--color-text-light); }
.pagination-info .highlight { font-weight: 700; color: var(--color-primary); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 30px; height: 30px; border-radius: 50%; color: #fff; font-size: 0.82rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.76rem; color: var(--color-text-light); }
.type-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(0, 0, 0, 0.05); border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.text-success { color: #15803d; }
.text-danger { color: #dc2626; }

.pagination-footer { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 1rem; border-top: 1px solid var(--color-border-light); }
.page-indicator { font-size: 0.85rem; font-weight: 600; color: var(--color-text-light); }

.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; text-decoration: none; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); transform: translateY(-1px); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-sm { padding: 0.45rem 0.85rem; font-size: 0.82rem; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }

.empty-icon-wrap { width: 60px; height: 60px; border-radius: 16px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem; }
.empty-state { padding: 3rem 1.5rem; text-align: center; }
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>
