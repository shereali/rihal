<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">হিসাব ও অর্থায়ন</span>
        <h1>শিক্ষার্থী ফি সংগ্রহ ও ব্যবস্থাপনা</h1>
        <p class="page-subtitle">শিক্ষার্থীদের মাসিক বেতন, ভর্তি ফি, পরীক্ষা ফি ও বকেয়া আদায় ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/fees/collect" class="btn btn-primary">
          <icon name="plus" /> নতুন ফি সংগ্রহ
        </NuxtLink>
        <button class="btn btn-outline" @click="loadPayments">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" v-if="paymentsList.length">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="document-text" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ paymentsList.length.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট ফি চালান</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="cash-multiple" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(totalCollected) }} ৳</span>
          <span class="stat-label">মোট আদায়কৃত ফি</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="cash-minus" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(totalBalance) }} ৳</span>
          <span class="stat-label">মোট বকেয়া ফি</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ fullyPaidCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">সম্পূর্ণ পরিশোধিত</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="শিক্ষার্থীর নাম বা রোল খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="filter.status" class="form-select" @change="loadPayments">
        <option value="">সব অবস্থা (All Status)</option>
        <option value="0">বকেয়া ফি</option>
        <option value="1">সম্পূর্ণ পরিশোধিত</option>
      </select>
      <div class="pagination-info" v-if="filteredPayments.length">
        মোট <span class="highlight">{{ filteredPayments.length.toLocaleString('bn-BD') }}</span> টি রেকর্ড
      </div>
    </div>

    <!-- Payments Table -->
    <div v-if="loading" class="loading-state card"><div class="spinner" /><p>ফি তালিকা লোড হচ্ছে...</p></div>
    <div v-else-if="!filteredPayments.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="money" /></div>
      <h3>কোনো ফি রেকর্ড পাওয়া যায়নি</h3>
      <p>নতুন ফি সংগ্রহ করে রশিদ তৈরি করুন</p>
      <NuxtLink to="/fees/collect" class="btn btn-primary"><icon name="plus" /> ফি সংগ্রহ করুন</NuxtLink>
    </div>
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিক্ষার্থী</th>
              <th>ফি কাঠামো / বিবরণ</th>
              <th>মোট প্রদেয়</th>
              <th>আদায়কৃত</th>
              <th>বকেয়া</th>
              <th>পরিশোধের শেষ তারিখ</th>
              <th class="text-center">অবস্থা</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in filteredPayments" :key="p.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(p.student?.user?.name_bn || p.student?.name_bn || p.student?.user?.name_en || p.student?.name_en || 'শ') }">
                    {{ (p.student?.user?.name_bn || p.student?.name_bn || p.student?.user?.name_en || p.student?.name_en || 'শ').charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ p.student?.user?.name_bn || p.student?.name_bn || p.student?.user?.name_en || p.student?.name_en || '—' }}</strong>
                    <div class="sub-text" v-if="p.student?.admission_number">রোল: {{ p.student.admission_number }}</div>
                  </div>
                </div>
              </td>
              <td><span class="fund-tag">{{ p.fee_structure?.name_bn || 'সাধারণ মাসিক বেতন' }}</span></td>
              <td><strong>{{ formatCurrency(p.total_amount) }} ৳</strong></td>
              <td><strong class="text-success">{{ formatCurrency(p.paid_amount) }} ৳</strong></td>
              <td><strong :class="Number(p.balance) > 0 ? 'text-danger' : 'text-muted'">{{ formatCurrency(p.balance) }} ৳</strong></td>
              <td>{{ formatDate(p.due_date) }}</td>
              <td class="text-center">
                <span class="status-pill" :class="p.is_fully_paid ? 'badge-approved' : 'badge-pending'">
                  <span class="status-dot" />
                  {{ p.is_fully_paid ? 'পরিশোধিত' : 'বকেয়া' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const payments = ref<any>(null)
const filter = ref({ status: '' })
const search = ref('')

async function loadPayments() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.set('per_page', '100')
    if (filter.value.status !== '') params.set('is_fully_paid', filter.value.status)
    const res = await api.get(`/finance/fee-payments?${params.toString()}`)
    payments.value = res.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

const paymentsList = computed(() => payments.value?.data?.data || payments.value?.data || [])

const filteredPayments = computed(() => {
  if (!search.value) return paymentsList.value
  return paymentsList.value.filter((p: any) => {
    const name = (p.student?.user?.name_bn || p.student?.name_bn || '') + ' ' + (p.student?.user?.name_en || p.student?.name_en || '') + ' ' + (p.student?.admission_number || '')
    return name.toLowerCase().includes(search.value.toLowerCase())
  })
})

const totalCollected = computed(() => paymentsList.value.reduce((acc: number, p: any) => acc + (Number(p.paid_amount) || 0), 0))
const totalBalance = computed(() => paymentsList.value.reduce((acc: number, p: any) => acc + (Number(p.balance) || 0), 0))
const fullyPaidCount = computed(() => paymentsList.value.filter((p: any) => p.is_fully_paid).length)

function formatCurrency(val: any) {
  if (!val) return '০'
  return Number(val).toLocaleString('bn-BD')
}

function formatDate(date: string | null | undefined): string {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch { return date }
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

onMounted(loadPayments)
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
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.76rem; color: var(--color-text-light); }
.fund-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); border-radius: 6px; font-size: 0.78rem; font-weight: 600; }
.text-success { color: #15803d; }
.text-danger { color: #dc2626; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }

.empty-icon-wrap { width: 60px; height: 60px; border-radius: 16px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem; }
.empty-state { padding: 3rem 1.5rem; text-align: center; }
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>
