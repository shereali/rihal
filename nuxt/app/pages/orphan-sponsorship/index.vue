<template>
  <div class="orphan-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/dashboard" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>অর্ফান স্পন্সরশিপ</h1>
        <p class="subtitle">অপ্রাপ্তবয় শিশু ও তাদের স্পন্সরদের ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/orphan-sponsorship/create" class="btn btn-primary btn-sm"><icon name="plus" /> নতুন অর্ফান</NuxtLink>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon"><icon name="child" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_orphans || 0 }}</p>
          <p class="stat-label">মোট অর্ফান</p>
        </div>
      </div>
      <div class="stat-card stat-success">
        <div class="stat-icon"><icon name="heart" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_sponsored || 0 }}</p>
          <p class="stat-label">স্পন্সরড</p>
        </div>
      </div>
      <div class="stat-card stat-warning">
        <div class="stat-icon"><icon name="alert" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_pending || 0 }}</p>
          <p class="stat-label">অধীন রয়েছে</p>
        </div>
      </div>
      <div class="stat-card stat-info">
        <div class="stat-icon"><icon name="cash" /></div>
        <div class="stat-info">
          <p class="stat-value">৳{{ summary?.total_sponsored_amount ? Number(summary.total_sponsored_amount).toLocaleString('bn-BD') : 0 }}</p>
          <p class="stat-label">মোট স্পন্সরশিপ</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <input v-model="search" type="text" placeholder="নাম দিয়ে সার্চ..." @input="debouncedLoad" class="search-input" />
      <select v-model="filterStatus" @change="loadOrphans" class="filter-select">
        <option value="">সকল স্ট্যাটাস</option>
        <option value="pending">অপেক্ষমান</option>
        <option value="sponsored">স্পন্সরড</option>
        <option value="completed">সম্পূর্ণ</option>
        <option value="closed">বন্ধ</option>
      </select>
    </div>

    <!-- Orphans Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(orphans?.data || []).length === 0" class="empty-state"><p>কোনো অর্ফান নেই</p></div>
        <table v-else class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>নাম (বাংলা)</th>
              <th>নাম (ইংরেজি)</th>
              <th>বয়স</th>
              <th>লিঙ্গ</th>
              <th>শ্রেণি</th>
              <th>স্পন্সর</th>
              <th>মাসিক (৳)</th>
              <th>মোট স্পন্সরড (৳)</th>
              <th>স্ট্যাটাস</th>
              <th>ক্রিয়া</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="orphan in (orphans?.data || [])" :key="orphan.id">
              <td>{{ orphan.id }}</td>
              <td>{{ orphan.name_bn }}</td>
              <td>{{ orphan.name_en || '-' }}</td>
              <td>{{ orphan.birth_date ? calculateAge(orphan.birth_date) : '-' }}</td>
              <td>{{ genderLabel(orphan.gender) }}</td>
              <td>{{ orphan.class_id || '-' }}</td>
              <td>{{ orphan.sponsor?.name_bn || orphan.sponsor?.name_en || 'অভাজন' }}</td>
              <td>{{ orphan.monthly_amount ? Number(orphan.monthly_amount).toLocaleString('bn-BD') : 0 }}</td>
              <td>{{ orphan.total_sponsored ? Number(orphan.total_sponsored).toLocaleString('bn-BD') : 0 }}</td>
              <td>
                <span class="badge" :class="statusClass(orphan.sponsorship_status)">
                  {{ statusLabel(orphan.sponsorship_status) }}
                </span>
              </td>
              <td>
                <NuxtLink :to="`/orphan-sponsorship/${orphan.id}`" class="btn btn-sm btn-outline"><icon name="eye" /></NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="orphans?.last_page > 1" class="pagination">
          <button class="btn btn-sm btn-outline" :disabled="!orphans.prev_page_url" @click="loadPage(orphans.current_page - 1)">পূর্ব</button>
          <span class="page-info">পৃষ্ঠা {{ orphans.current_page }} / {{ orphans.last_page }}</span>
          <button class="btn btn-sm btn-outline" :disabled="!orphans.next_page_url" @click="loadPage(orphans.current_page + 1)">পরবর্তী</button>
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
    if (filterStatus.value) params.set('status', filterStatus.value)
    if (search.value) params.set('search', search.value)

    const r = await api.get(`/orphans?${params.toString()}`)
    orphans.value = r.data
  } catch (e: any) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadSummary() {
  try {
    const r = await api.get('/orphans-summary')
    summary.value = r.data
  } catch (e: any) {
    console.error(e)
  }
}

function debouncedLoad() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadOrphans(1)
  }, 500)
}

function loadPage(page: number) {
  loadOrphans(page)
}

function calculateAge(birthDate: string): string {
  if (!birthDate) return '-'
  const age = Math.floor((Date.now() - new Date(birthDate).getTime()) / (365.25 * 24 * 60 * 60 * 1000))
  return `${age} বছর`
}

function genderLabel(g: string): string {
  switch (g) {
    case 'male': return 'পুরুষ'
    case 'female': return 'মহিলা'
    default: return 'অন্যান্য'
  }
}

function statusClass(status: string): string {
  switch (status) {
    case 'completed': return 'badge-success'
    case 'sponsored': return 'badge-outline'
    case 'closed': return 'badge-secondary'
    default: return 'badge-warning'
  }
}

function statusLabel(status: string): string {
  switch (status) {
    case 'completed': return 'সম্পূর্ণ'
    case 'sponsored': return 'স্পন্সরড'
    case 'closed': return 'বন্ধ'
    default: return 'অপেক্ষমান'
  }
}

onMounted(() => {
  loadOrphans()
  loadSummary()
})
</script>

<style scoped>
.orphan-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.subtitle { color: var(--color-text-light); font-size: 0.9rem; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.header-actions .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }
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
.stat-success .stat-icon { color: #16a34a; }
.stat-warning .stat-icon { color: #d97706; }
.stat-info .stat-icon { color: #2563eb; }
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
.badge { padding: 0.2rem 0.6rem; border-radius: 10px; font-size: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; white-space: nowrap; }
.badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.badge-warning { background: rgba(234, 179, 8, 0.15); color: #d97706; }
.badge-secondary { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.badge-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text-light); }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.page-info { font-size: 0.85rem; color: var(--color-text-light); }
</style>
