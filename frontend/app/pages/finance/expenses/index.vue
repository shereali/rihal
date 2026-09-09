<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> অর্থায়ন ড্যাশবোর্ড</NuxtLink>
        <h1>ব্যয় ও খরচের হিসাব</h1>
        <p class="page-subtitle">প্রাতিষ্ঠানিক যাবতীয় ব্যয়, বিল পরিশোধ ও ভাউচার পরিচালনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="showForm = true">
          <icon name="plus" /> নতুন ব্যয় ভাউচার
        </button>
        <button class="btn btn-outline" @click="loadExpenses(currentPage)">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ব্যয়ের বিবরণ, ভেন্ডর বা নোট খুঁজুন..." @keyup.enter="loadExpenses(1)" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadExpenses(1)">×</button>
      </div>
      <select v-model="categoryFilter" class="form-select" @change="loadExpenses(1)">
        <option value="">সব খাত (All Categories)</option>
        <option value="বেতন">শিক্ষক/কর্মী বেতন</option>
        <option value="ইউটিলিটি">বিদ্যুৎ / গ্যাস / পানি</option>
        <option value="মেরামত">মেরামত ও রক্ষণাবেক্ষণ</option>
        <option value="খাদ্য">হোস্টেল খাদ্য সামগ্রী</option>
        <option value="স্টেশনারি">বই ও স্টেশনারি</option>
        <option value="অন্যান্য">অন্যান্য প্রশাসনিক</option>
      </select>
      <div class="pagination-info" v-if="expenses?.total">
        মোট <span class="highlight">{{ (expenses?.total || 0).toLocaleString('bn-BD') }}</span> টি ব্যয়
      </div>
    </div>

    <!-- Create Expense Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন ব্যয় / ভাউচার এন্ট্রি</h3>
            <p>ব্যয়ের বিবরণ, খাত, ভেন্ডর ও পরিশোধের তথ্য নির্ধারণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showForm = false">×</button>
        </div>
        <form @submit.prevent="saveExpense" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>
          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">ব্যয়ের বিবরণ (বাংলা) *</label>
              <input v-model="form.description_bn" class="form-input" required placeholder="যেমন: মে মাসের বিদ্যুৎ বিল পরিশোধ" />
            </div>
            <div class="form-group">
              <label class="form-label">ব্যয়ের খাত / ক্যাটাগরি *</label>
              <select v-model="form.category" class="form-select" required>
                <option value="বেতন">শিক্ষক/কর্মী বেতন</option>
                <option value="ইউটিলিটি">বিদ্যুৎ / গ্যাস / পানি</option>
                <option value="মেরামত">মেরামত ও রক্ষণাবেক্ষণ</option>
                <option value="খাদ্য">হোস্টেল খাদ্য সামগ্রী</option>
                <option value="স্টেশনারি">বই ও স্টেশনারি</option>
                <option value="অন্যান্য">অন্যান্য প্রশাসনিক</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">পরিমাণ (টাকা) *</label>
              <input v-model.number="form.amount" type="number" min="1" class="form-input" required placeholder="৩০০০" />
            </div>
            <div class="form-group">
              <label class="form-label">প্রাপক / ভেন্ডরের নাম</label>
              <input v-model="form.payee_name" class="form-input" placeholder="যেমন: ডেসকো / স্থানীয় বিক্রেতা" />
            </div>
            <div class="form-group">
              <label class="form-label">পরিশোধ পদ্ধতি *</label>
              <select v-model="form.method" class="form-select" required>
                <option value="নগদ">নগদ (Cash)</option>
                <option value="ব্যাংক">ব্যাংক ট্রান্সফার</option>
                <option value="বিকাশ">বিকাশ / নগদ</option>
                <option value="চেক">চেক</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">লেনদেনের তারিখ *</label>
              <input v-model="form.transaction_date" type="date" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">ভাউচার নম্বর</label>
              <input v-model="form.voucher_number" class="form-input" placeholder="VOUCH-01" />
            </div>
            <div class="form-group wide">
              <label class="form-label">অতিরিক্ত নোট</label>
              <input v-model="form.notes" class="form-input" placeholder="অনুমোদনের রেফারেন্স বা নোট..." />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'ব্যয় সংরক্ষণ করুন' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Expenses Table -->
    <div v-if="loading" class="loading-state card"><div class="spinner" /><p>ব্যয় তালিকা লোড হচ্ছে...</p></div>
    <div v-else-if="!expensesList.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="cash-minus" /></div>
      <h3>কোনো ব্যয়ের রেকর্ড পাওয়া যায়নি</h3>
      <p>নতুন ব্যয় ভাউচার তৈরি করে হিসাব সংরক্ষণ করুন</p>
      <button class="btn btn-primary" @click="showForm = true"><icon name="plus" /> প্রথম ব্যয় যুক্ত করুন</button>
    </div>
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>ব্যয়ের বিবরণ</th>
              <th>খাত / ক্যাটাগরি</th>
              <th>পরিমাণ</th>
              <th>প্রাপক / ভেন্ডর</th>
              <th>পদ্ধতি</th>
              <th>তারিখ</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in expensesList" :key="e.id">
              <td>
                <strong>{{ e.description_bn || e.description || '-' }}</strong>
                <div class="sub-text" v-if="e.notes">{{ e.notes }}</div>
              </td>
              <td><span class="type-tag">{{ e.category || 'অন্যান্য' }}</span></td>
              <td><strong class="text-danger">{{ formatCurrency(e.amount) }} ৳</strong></td>
              <td>{{ e.vendor?.name_bn || e.payee_name || e.vendor || '-' }}</td>
              <td><span class="badge-outline">{{ e.method || 'নগদ' }}</span></td>
              <td>{{ formatDate(e.transaction_date || e.date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="expenses?.last_page > 1" class="pagination-footer">
        <button class="btn btn-sm btn-outline" :disabled="currentPage <= 1" @click="loadExpenses(currentPage - 1)">
          পূর্ববর্তী
        </button>
        <span class="page-indicator">{{ currentPage.toLocaleString('bn-BD') }} / {{ (expenses.last_page || 1).toLocaleString('bn-BD') }}</span>
        <button class="btn btn-sm btn-outline" :disabled="currentPage >= expenses.last_page" @click="loadExpenses(currentPage + 1)">
          পরবর্তী
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const expenses = ref<any>(null)
const currentPage = ref(1)

const search = ref('')
const categoryFilter = ref('')

const form = reactive({
  description_bn: '',
  category: 'অন্যান্য',
  amount: 0,
  payee_name: '',
  method: 'নগদ',
  transaction_date: new Date().toISOString().slice(0, 10),
  voucher_number: '',
  notes: '',
})

const expensesList = computed(() => expenses.value?.data?.data || expenses.value?.data || [])

async function loadExpenses(page = 1) {
  loading.value = true
  currentPage.value = page
  try {
    const q = new URLSearchParams({ page: String(page), per_page: '50' })
    if (search.value) q.set('search', search.value)
    if (categoryFilter.value) q.set('category', categoryFilter.value)
    const r = await api.get(`/finance/expenses?${q.toString()}`)
    expenses.value = r.data
  } catch (e: any) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function saveExpense() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/finance/expenses', form)
    showForm.value = false
    form.description_bn = ''
    form.category = 'অন্যান্য'
    form.amount = 0
    form.payee_name = ''
    form.method = 'নগদ'
    form.transaction_date = new Date().toISOString().slice(0, 10)
    form.voucher_number = ''
    form.notes = ''
    await loadExpenses(1)
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'ব্যয় সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

function formatCurrency(val: any) {
  if (!val) return '০'
  return Number(val).toLocaleString('bn-BD')
}

function formatDate(dateStr: string) {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch { return dateStr }
}

onMounted(() => loadExpenses())
</script>

<style scoped>
.page-wrapper { max-width: 1320px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.clear-search-btn { background: none; border: none; font-size: 1.1rem; color: var(--color-text-light); cursor: pointer; padding: 0 0.2rem; }
.pagination-info { margin-left: auto; font-size: 0.85rem; color: var(--color-text-light); }
.pagination-info .highlight { font-weight: 700; color: var(--color-primary); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.sub-text { font-size: 0.76rem; color: var(--color-text-light); }
.type-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(0, 0, 0, 0.05); border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.badge-outline { display: inline-block; padding: 0.15rem 0.5rem; border: 1px solid var(--color-border); border-radius: 4px; font-size: 0.75rem; }
.text-danger { color: #dc2626; }

.pagination-footer { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 1rem; border-top: 1px solid var(--color-border-light); }
.page-indicator { font-size: 0.85rem; font-weight: 600; color: var(--color-text-light); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-sm { padding: 0.45rem 0.85rem; font-size: 0.82rem; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }
.btn-ghost { background: transparent; color: var(--color-text); }
.btn-ghost:hover { background: rgba(0, 0, 0, 0.05); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.empty-icon-wrap { width: 60px; height: 60px; border-radius: 16px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem; }
.empty-state { padding: 3rem 1.5rem; text-align: center; }
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>
