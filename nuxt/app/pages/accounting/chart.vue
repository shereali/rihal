<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/accounting" class="back-link"><icon name="arrow-left" /> অ্যাকাউন্টিং ড্যাশবোর্ড</NuxtLink>
        <h1>চার্ট অব অ্যাকাউন্টস (Chart of Accounts)</h1>
        <p class="page-subtitle">মাদ্রাসার সমস্ত আর্থিক হিসাবের শ্রেণিবিন্যাস, কোড ও খতিয়ান স্তর</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddAccountModal">
          <icon name="plus" /> নতুন হিসাব খাত যোগ করুন
        </button>
      </div>
    </div>

    <!-- Category Tabs -->
    <div class="acc-tabs-row">
      <button class="acc-tab-btn" :class="{ active: selectedCategory === 'all' }" @click="selectedCategory = 'all'">
        সকল হিসাব (All)
      </button>
      <button class="acc-tab-btn" :class="{ active: selectedCategory === 'asset' }" @click="selectedCategory = 'asset'">
        সম্পদ (Assets)
      </button>
      <button class="acc-tab-btn" :class="{ active: selectedCategory === 'liability' }" @click="selectedCategory = 'liability'">
        দায় (Liabilities)
      </button>
      <button class="acc-tab-btn" :class="{ active: selectedCategory === 'equity' }" @click="selectedCategory = 'equity'">
        ইকুইটি (Equity)
      </button>
      <button class="acc-tab-btn" :class="{ active: selectedCategory === 'revenue' }" @click="selectedCategory = 'revenue'">
        আয় (Revenue)
      </button>
      <button class="acc-tab-btn" :class="{ active: selectedCategory === 'expense' }" @click="selectedCategory = 'expense'">
        ব্যয় (Expenses)
      </button>
    </div>

    <!-- Accounts Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th style="width: 120px;">হিসাব কোড</th>
              <th>হিসাবের নাম (Account Name)</th>
              <th>মূল ক্যাটাগরি</th>
              <th>প্যারেন্ট হেড</th>
              <th class="text-right">বর্তমান ব্যালেন্স (৳)</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="acc in filteredAccounts" :key="acc.id">
              <td><strong class="mono-font">{{ acc.code }}</strong></td>
              <td><strong>{{ acc.name }}</strong></td>
              <td>
                <span class="type-tag" :class="acc.type">{{ categoryLabel(acc.type) }}</span>
              </td>
              <td>{{ acc.parent_head || '—' }}</td>
              <td class="text-right font-bold text-success">৳ {{ acc.balance.toLocaleString('bn-BD') }}</td>
              <td class="text-right">
                <button class="action-btn" title="সম্পাদনা"><icon name="pencil" /></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Account Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন হিসাব খাত তৈরি করুন</h3>
            <p>চার্ট অব অ্যাকাউন্টসে নতুন লেজার বা সাব-হেড যুক্ত করুন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveAccount" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">হিসাবের কোড *</label>
              <input v-model="form.code" class="form-input mono" placeholder="যেমন: 1010" required />
            </div>
            <div class="form-group">
              <label class="form-label">হিসাবের নাম *</label>
              <input v-model="form.name" class="form-input" placeholder="যেমন: ইসলামী ব্যাংক চলতি হিসাব" required />
            </div>
            <div class="form-group">
              <label class="form-label">মূল ক্যাটাগরি *</label>
              <select v-model="form.type" class="form-select" required>
                <option value="asset">সম্পদ (Asset)</option>
                <option value="liability">দায় (Liability)</option>
                <option value="equity">ইকুইটি (Equity)</option>
                <option value="revenue">আয় (Revenue)</option>
                <option value="expense">ব্যয় (Expense)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">প্যারেন্ট হেড / গ্রুপ</label>
              <input v-model="form.parent_head" class="form-input" placeholder="যেমন: ব্যাংক ও নগদ তহবিল" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">হিসাব খাত সংরক্ষণ করুন</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const selectedCategory = ref('all')
const showModal = ref(false)

const accountsList = ref<any[]>([
  { id: 1, code: '1000', name: 'প্রধান ক্যাশ ইন হ্যান্ড (নগদ তহবিল)', type: 'asset', parent_head: 'চলতি সম্পদ', balance: 345000 },
  { id: 2, code: '1010', name: 'ইসলামী ব্যাংক বাংলাদেশ লিঃ (চলতি হিসাব)', type: 'asset', parent_head: 'ব্যাংক ব্যালেন্স', balance: 1850000 },
  { id: 3, code: '1020', name: 'বিকাশ / নগদ মার্চেন্ট অ্যাকাউন্ট', type: 'asset', parent_head: 'ডিজিটাল ওয়ালেট', balance: 75000 },
  { id: 4, code: '2000', name: 'প্রদেয় হিসাব / বিল পাওনাদার', type: 'liability', parent_head: 'চলতি দায়', balance: 120000 },
  { id: 5, code: '3000', name: 'মাদ্রাসার মূলধন ও সঞ্চিতি তহবিল', type: 'equity', parent_head: 'মালিকানা স্বত্ব', balance: 2150000 },
  { id: 6, code: '4000', name: 'শিক্ষার্থী মাসিক বেতন ও ভর্তি ফি', type: 'revenue', parent_head: 'একাডেমিক আয়', balance: 580000 },
  { id: 7, code: '4010', name: 'সাধারণ দান ও আজীবন সদস্য চাঁদা', type: 'revenue', parent_head: 'অনুদান আয়', balance: 270000 },
  { id: 8, code: '5000', name: 'শিক্ষক ও স্টাফ বেতন ভাতা', type: 'expense', parent_head: 'প্রশাসনিক ব্যয়', balance: 380000 },
  { id: 9, code: '5010', name: 'বোর্ডিং খাদ্য ও বাজার খরচ', type: 'expense', parent_head: 'বোর্ডিং ব্যয়', balance: 140000 }
])

const form = reactive({
  code: '',
  name: '',
  type: 'asset',
  parent_head: ''
})

async function loadAccounts() {
  try {
    const res = await api.get('/accounting/chart').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      accountsList.value = fetched.map((a: any) => ({
        id: a.id,
        code: a.code,
        name: a.name,
        type: a.account_type,
        parent_head: a.parent_head || 'সাধারণ খতিয়ান',
        balance: a.current_balance || a.opening_balance || 0
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredAccounts = computed(() => {
  if (selectedCategory.value === 'all') return accountsList.value
  return accountsList.value.filter(a => a.type === selectedCategory.value)
})

function openAddAccountModal() {
  form.code = ''
  form.name = ''
  form.type = 'asset'
  form.parent_head = ''
  showModal.value = true
}

async function saveAccount() {
  try {
    const res = await api.post('/accounting/chart', {
      code: form.code,
      name: form.name,
      account_type: form.type,
      parent_head: form.parent_head
    }).catch(() => null)

    const saved = res?.data?.data
    accountsList.value.push({
      id: saved?.id || Date.now(),
      code: form.code,
      name: form.name,
      type: form.type,
      parent_head: form.parent_head || '—',
      balance: 0
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

onMounted(loadAccounts)

function categoryLabel(t: string) {
  const map: Record<string, string> = {
    asset: 'সম্পদ (Asset)',
    liability: 'দায় (Liability)',
    equity: 'ইকুইটি (Equity)',
    revenue: 'আয় (Revenue)',
    expense: 'ব্যয় (Expense)'
  }
  return map[t] || t
}
</script>

<style scoped>
.page-wrapper { max-width: 1320px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.acc-tabs-row { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.acc-tab-btn { padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-bg); font-size: 0.84rem; font-weight: 600; cursor: pointer; transition: all 0.15s ease; color: var(--color-text); }
.acc-tab-btn.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.text-success { color: #15803d; }

.type-tag { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.type-tag.asset { background: #dcfce7; color: #15803d; }
.type-tag.liability { background: #fee2e2; color: #dc2626; }
.type-tag.equity { background: #f3e8ff; color: #7e22ce; }
.type-tag.revenue { background: #eff6ff; color: #2563eb; }
.type-tag.expense { background: #fffbeb; color: #b45309; }

.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
