<template>
  <div class="finance-page">
    <div class="page-header">
      <div class="header-left">
        <h1>আর্থিক ব্যবস্থাপনা</h1>
        <p class="text-muted">ফান্ড, দান, ব্যয় ও আর্থিক রিকর্ড</p>
      </div>
    </div>

    <div class="stats-row">
      <div class="stat-card stat-revenue">
        <div class="stat-icon"><icon :name="mdiCashMultiple" /></div>
        <div class="stat-info"><p class="stat-value">৳{{ summary?.total_donations || 0 }}</p><p class="stat-label">মোট দান</p></div>
      </div>
      <div class="stat-card stat-expense">
        <div class="stat-icon"><icon :name="mdiCashMinus" /></div>
        <div class="stat-info"><p class="stat-value">৳{{ summary?.total_expenses || 0 }}</p><p class="stat-label">মোট ব্যয়</p></div>
      </div>
      <div class="stat-card stat-balance">
        <div class="stat-icon"><icon :name="mdi-cash-plus" /></div>
        <div class="stat-info"><p class="stat-value">৳{{ summary?.net_balance || 0 }}</p><p class="stat-label">স্যুট ব্যালেন্স</p></div>
      </div>
      <div class="stat-card stat-donations">
        <div class="stat-icon"><icon :name="mdi-heart-multiple" /></div>
        <div class="stat-info"><p class="stat-value">৳{{ summary?.total_fee_collected || 0 }}</p><p class="stat-label">ফি আয়</p></div>
      </div>
    </div>

    <div class="tabs">
      <button v-for="tab in tabs" :key="tab.id" :class="['tab-btn', { active: activeTab === tab.id }]" @click="activeTab = tab.id">{{ tab.label }}</button>
    </div>

    <div class="tab-content">
      <!-- Funds -->
      <div v-show="activeTab === 'funds'" class="table-section">
        <div class="table-actions"><NuxtLink to="/finance" class="btn btn-outline btn-sm"><icon :name="mdiRefresh" /> রিফ্রেশ</NuxtLink></div>
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr><th>#</th><th>ফান্ডের নাম</th><th>ধরণ</th><th>লক্ষ্য</th><th>সংগ্রহিত</th><th>অবস্থা</th><th>ক্রিয়া</th></tr>
            </thead>
            <tbody>
              <tr v-for="fund in funds?.data || []" :key="fund.id">
                <td>{{ fund.id }}</td>
                <td><p class="font-weight-medium">{{ fund.name_bn }}</p><p class="text-muted text-sm" v-if="fund.name_en">{{ fund.name_en }}</p></td>
                <td><span class="badge badge-outline">{{ fund.type || 'রাশনির্দিষ্ট' }}</span></td>
                <td>৳{{ fund.target_amount || 0 }}</td>
                <td>৳{{ fund.collected_amount || 0 }}</td>
                <td><span class="badge" :class="fund.is_active ? 'badge-success' : 'badge-secondary'">{{ fund.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span></td>
                <td><NuxtLink :to="`/finance/funds/${fund.id}`" class="btn btn-sm btn-outline"><icon :name="mdiEye" /></NuxtLink></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Donations -->
      <div v-show="activeTab === 'donations'" class="table-section">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr><th>#</th><th>দাতা</th><th>ফান্ড</th><th>পরিমাণ</th><th>পদ্ধতি</th><th>তারিখ</th><th>মন্তব্য</th><th>ক্রিয়া</th></tr>
            </thead>
            <tbody>
              <tr v-for="donation in donations?.data || []" :key="donation.id">
                <td>{{ donation.id }}</td>
                <td>{{ donation.donor?.name_bn || donation.donor?.name_en || 'অজানা' }}</td>
                <td>{{ donation.fund?.name_bn || '-' }}</td>
                <td>৳{{ donation.amount }}</td>
                <td><span class="badge badge-outline">{{ donation.method || 'নগদ' }}</span></td>
                <td>{{ formatDate(donation.donation_date) }}</td>
                <td><p class="text-muted text-sm">{{ donation.notes || '-' }}</p></td>
                <td><NuxtLink :to="`/finance/donations/${donation.id}`" class="btn btn-sm btn-outline"><icon :name="mdiEye" /></NuxtLink></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Expenses -->
      <div v-show="activeTab === 'expenses'" class="table-section">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr><th>#</th><th>ব্যয়ের বিষয়</th><th>বিভাগ</th><th>পরিমাণ</th><th>তারিখ</th><th>পদ্ধতি</th><th>প্রদাতা</th><th>ক্রিয়া</th></tr>
            </thead>
            <tbody>
              <tr v-for="expense in expenses?.data || []" :key="expense.id">
                <td>{{ expense.id }}</td>
                <td><p class="font-weight-medium">{{ expense.title_bn }}</p><p class="text-muted text-sm" v-if="expense.notes">{{ truncate(expense.notes, 40) }}</p></td>
                <td><span class="badge badge-outline">{{ expense.category || 'অন্যান্য' }}</span></td>
                <td>৳{{ expense.amount }}</td>
                <td>{{ formatDate(expense.expense_date) }}</td>
                <td><span class="badge badge-outline">{{ expense.method || 'নগদ' }}</span></td>
                <td>{{ expense.vendor?.name_bn || expense.payee_name || '-' }}</td>
                <td><NuxtLink :to="`/finance/expenses/${expense.id}`" class="btn btn-sm btn-outline"><icon :name="mdiEye" /></NuxtLink></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()

const activeTab = ref('funds')
const funds = ref<any>(null)
const donations = ref<any>(null)
const expenses = ref<any>(null)
const summary = ref<any>(null)

const tabs = [
  { id: 'funds', label: 'ফান্ড সমূহ' },
  { id: 'donations', label: 'দানসমূহ' },
  { id: 'expenses', label: 'ব্যয় সমূহ' },
]

async function loadFinance() {
  try {
    const [fundsRes, donationsRes, expensesRes, summaryRes] = await Promise.all([
      api.get('/finance/funds').catch(() => ({ data: { data: [], meta: { total: 0 } } })),
      api.get('/finance/donations').catch(() => ({ data: { data: [], meta: { total: 0 } } })),
      api.get('/finance/expenses').catch(() => ({ data: { data: [], meta: { total: 0 } } })),
      api.get('/finance/summary').catch(() => ({ data: { data: {} } })),
    ])
    funds.value = fundsRes.data
    donations.value = donationsRes.data
    expenses.value = expensesRes.data
    summary.value = summaryRes.data.data || {}
  } catch (error) { console.error('Failed to load finance data:', error) }
}

const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day:'numeric', month:'short', year:'numeric' }) : '-'
const truncate = (text: string | null | undefined, maxLen: number) => text ? (text.length > maxLen ? text.substring(0, maxLen) + '...' : text) : '-'

// Auth guard — redirect if not authenticated
if (!isAuthenticated.value && !authLoading.value) {
  navigateTo('/login')
} else {
  onMounted(() => { if (isAuthenticated.value) loadFinance() })
}
</script>
