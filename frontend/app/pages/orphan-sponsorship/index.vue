<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">কল্যাণ ও তহবিল</span>
        <h1>এতিম ও শিশু স্পনসরশিপ</h1>
        <p class="page-subtitle">অসহায় ও এতিম শিক্ষার্থীদের সহায়তা তহবিল ও স্পনসর ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" :disabled="exporting" @click="downloadExport">
          <icon name="download" /> {{ exporting ? 'তৈরি হচ্ছে...' : 'CSV রিপোর্ট' }}
        </button>
        <NuxtLink to="/orphan-sponsorship/create" class="btn btn-primary">
          <icon name="plus" /> নতুন এতিম শিক্ষার্থী
        </NuxtLink>
        <button class="btn btn-outline" @click="loadOrphans(currentPage)">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_orphans || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট নিবন্ধিত এতিম</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="heart" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_sponsored || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">স্পনসর প্রাপ্ত</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="alert-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_pending || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">স্পনসরের অপেক্ষায়</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="cash-multiple" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.total_sponsored_amount) }} ৳</span>
          <span class="stat-label">মোট সহায়তা বিতরণ</span>
        </div>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="শিক্ষার্থীর নাম বা অভিভাবক খুঁজুন..." @input="debouncedLoad" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadOrphans(1)">×</button>
      </div>
      <select v-model="filterStatus" class="form-select" @change="loadOrphans(1)">
        <option value="">সকল স্ট্যাটাস (All Status)</option>
        <option value="pending">অপেক্ষমান</option>
        <option value="sponsored">স্পনসর প্রাপ্ত</option>
        <option value="completed">সম্পূর্ণ</option>
        <option value="closed">বন্ধ</option>
      </select>
      <div class="pagination-info" v-if="orphans?.total">
        মোট <span class="highlight">{{ (orphans?.total || 0).toLocaleString('bn-BD') }}</span> জন শিক্ষার্থী
      </div>
    </div>

    <!-- Orphans Table -->
    <div v-if="loading" class="loading-state card"><div class="spinner" /><p>এতিম তালিকা লোড হচ্ছে...</p></div>
    <div v-else-if="!(orphans?.data || []).length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="heart" /></div>
      <h3>কোনো এতিম শিক্ষার্থী নিবন্ধিত নেই</h3>
      <p>নতুন এতিম শিক্ষার্থী যোগ করে স্পনসরশিপের ব্যবস্থা করুন</p>
      <NuxtLink to="/orphan-sponsorship/create" class="btn btn-primary"><icon name="plus" /> নতুন এতিম শিক্ষার্থী যোগ করুন</NuxtLink>
    </div>
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিক্ষার্থীর নাম</th>
              <th>অভিভাবক</th>
              <th>মোবাইল</th>
              <th>মাসিক সহায়তা</th>
              <th>মোট প্রাপ্ত</th>
              <th>স্পনসর</th>
              <th class="text-center">অবস্থা</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="orphan in (orphans?.data || [])" :key="orphan.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(orphan.name_bn || orphan.name_en || 'এ') }">
                    {{ (orphan.name_bn || orphan.name_en || 'এ').charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ orphan.name_bn }}</strong>
                    <div class="sub-text" v-if="orphan.name_en">{{ orphan.name_en }}</div>
                  </div>
                </div>
              </td>
              <td>{{ orphan.guardian_name_bn || orphan.guardian_name || '—' }}</td>
              <td class="mono-font">{{ orphan.guardian_phone || '—' }}</td>
              <td><strong>{{ formatCurrency(orphan.monthly_amount) }} ৳</strong></td>
              <td><strong class="text-success">{{ formatCurrency(orphan.total_sponsored) }} ৳</strong></td>
              <td>
                <span class="badge-outline" v-if="orphan.sponsors && orphan.sponsors.length">
                  {{ orphan.sponsors[0].name_bn || orphan.sponsors[0].name_en }}
                </span>
                <span class="text-muted" v-else>কেউ নেই</span>
              </td>
              <td class="text-center">
                <span class="status-pill" :class="statusClass(orphan.sponsorship_status)">
                  <span class="status-dot" />
                  {{ statusLabel(orphan.sponsorship_status) }}
                </span>
              </td>
              <td class="text-right">
                <NuxtLink :to="`/orphan-sponsorship/${orphan.id}`" class="action-btn" title="বিস্তারিত">
                  <icon name="eye" />
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="orphans?.last_page > 1" class="pagination-footer">
        <button class="btn btn-sm btn-outline" :disabled="currentPage <= 1" @click="loadOrphans(currentPage - 1)">
          পূর্ববর্তী
        </button>
        <span class="page-indicator">{{ currentPage.toLocaleString('bn-BD') }} / {{ (orphans.last_page || 1).toLocaleString('bn-BD') }}</span>
        <button class="btn btn-sm btn-outline" :disabled="currentPage >= orphans.last_page" @click="loadOrphans(currentPage + 1)">
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
const orphans = ref<any>(null)
const summary = ref<any>(null)
const search = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
let debounceTimer: any

async function loadOrphans(page = 1) {
  loading.value = true
  currentPage.value = page
  try {
    const params = new URLSearchParams()
    params.set('per_page', '50')
    params.set('page', String(page))
    if (filterStatus.value) params.set('sponsorship_status', filterStatus.value)
    if (search.value) params.set('search', search.value)

    const r = await api.get(`/orphans?${params.toString()}`)
    orphans.value = r.data?.data
  } catch (e: any) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadSummary() {
  try {
    const r = await api.get('/orphans/summary')
    summary.value = r.data?.data
  } catch (e) {
    console.error(e)
  }
}

function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadOrphans(1), 300)
}

async function downloadExport() {
  exporting.value = true
  try {
    const res = await api.get('/reports/orphans.csv', { responseType: 'blob' })
    const blob = res.data as Blob
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `orphans_${new Date().toISOString().slice(0, 10)}.csv`
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
  if (s === 'sponsored') return 'badge-approved'
  if (s === 'closed') return 'badge-rejected'
  return 'badge-pending'
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    pending: 'অপেক্ষমান',
    sponsored: 'স্পনসর প্রাপ্ত',
    completed: 'সম্পূর্ণ',
    closed: 'বন্ধ',
  }
  return map[s] || s || 'অপেক্ষমান'
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

onMounted(() => {
  loadOrphans()
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
.mono-font { font-family: monospace; font-size: 0.84rem; }
.badge-outline { display: inline-block; padding: 0.15rem 0.5rem; border: 1px solid var(--color-border); border-radius: 4px; font-size: 0.75rem; }
.text-success { color: #15803d; }

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
