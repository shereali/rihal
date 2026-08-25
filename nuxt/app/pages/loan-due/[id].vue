<template>
  <div class="loan-detail-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/loan-due" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1 v-if="loan">ঋণ: {{ loan.title_bn }}</h1>
        <p v-else class="text-muted">ঋণ লোড হচ্ছে...</p>
      </div>
      <span v-if="loan" class="badge" :class="statusClass(loan.status)">
        {{ statusLabel(loan.status) }}
      </span>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div v-if="loan" class="detail-grid">
      <!-- Loan Info -->
      <div class="card">
        <h3>ঋণের তথ্য</h3>
        <dl class="info-list">
          <div><dt>শিরোনাম (বাংলা)</dt><dd>{{ loan.title_bn }}</dd></div>
          <div v-if="loan.title_en"><dt>শিরোনাম (ইংরেজি)</dt><dd>{{ loan.title_en }}</dd></div>
          <div><dt>ধরণ</dt><dd>{{ loan.loan_type || 'সাধারণ' }}</dd></div>
          <div><dt>প্রতিষ্ঠানকর্তা</dt><dd>{{ loan.user?.name_bn || loan.user?.name_en || loan.user_id || '-' }}</dd></div>
          <div><dt>মূল পরিমাণ (৳)</dt><dd>{{ loan.principal_amount ? Number(loan.principal_amount).toLocaleString('bn-BD') : 0 }}</dd></div>
          <div><dt>সুদের হার (%)</dt><dd>{{ loan.interest_rate || 0 }}</dd></div>
          <div><dt>সুদের ধরন</dt><dd>{{ loan.interest_type === 'flat' ? 'ফ্ল্যাট' : 'হ্রাসমান ব্যালেন্স' }}</dd></div>
          <div><dt>কিস্তির সংখ্যা</dt><dd>{{ loan.installment_count || 1 }}</dd></div>
          <div><dt>প্রতি কিস্তি (৳)</dt><dd>{{ money(loan.monthly_installment) }}</dd></div>
          <div><dt>শুরুর তারিখ</dt><dd>{{ loan.start_date || '-' }}</dd></div>
          <div><dt>শেষ তারিখ</dt><dd>{{ loan.due_date || '-' }}</dd></div>
          <div><dt>প্রদত্ত (৳)</dt><dd>{{ loan.total_paid ? Number(loan.total_paid).toLocaleString('bn-BD') : 0 }}</dd></div>
          <div><dt>বকেয়া (৳)</dt><dd>{{ loan.remaining_amount ? Number(loan.remaining_amount).toLocaleString('bn-BD') : 0 }}</dd></div>
          <div><dt>মোট কিস্তি (৳)</dt><dd>{{ loan.total_interest ? Number(loan.total_interest).toLocaleString('bn-BD') : 0 }}</dd></div>
          <div><dt>মোট বকেয়া (৳)</dt><dd>{{ loan.total_due ? Number(loan.total_due).toLocaleString('bn-BD') : 0 }}</dd></div>
          <div><dt>অনুমোদন অবস্থা</dt><dd>{{ loan.approval_status || 'pending' }}</dd></div>
          <div v-if="loan.notes"><dt>নোট</dt><dd>{{ loan.notes }}</dd></div>
          <div><dt>তৈরির তারিখ</dt><dd>{{ formatDate(loan.created_at) }}</dd></div>
        </dl>
      </div>

      <div class="card schedule-card">
        <h3>কিস্তির সূচি</h3>
        <div class="schedule-summary"><span>মোট {{ installments.length }} কিস্তি</span><strong>প্রতি কিস্তি ৳{{ money(loan.monthly_installment) }}</strong></div>
        <div v-if="!installments.length" class="empty-state"><p>কিস্তির সূচি নেই</p></div>
        <div v-else class="table-scroll">
          <table class="table table-hover">
            <thead><tr><th>#</th><th>ডিউ তারিখ</th><th>মূলধন</th><th>সুদ</th><th>কিস্তি</th><th>পরিশোধ</th><th>অবস্থা</th></tr></thead>
            <tbody><tr v-for="row in installments" :key="row.id">
              <td>{{ row.installment_number }}</td><td>{{ row.due_date }}</td><td>{{ money(row.principal_amount) }}</td><td>{{ money(row.interest_amount) }}</td><td>{{ money(row.installment_amount) }}</td><td>{{ money(row.paid_amount) }}</td><td><span class="badge" :class="installmentClass(row.status)">{{ installmentLabel(row.status) }}</span></td>
            </tr></tbody>
          </table>
        </div>
      </div>

      <!-- Record Payment -->
      <div class="card">
        <h3>প্রদান রেকর্ড করুন</h3>
        <form @submit.prevent="recordPayment" class="payment-form">
          <div class="form-row">
            <div class="form-group">
              <label>পরিমাণ (৳) *</label>
              <input v-model.number="payment.amount" type="number" min="1" step="0.01" placeholder="0" :disabled="loading" />
            </div>
            <div class="form-group">
              <label>তারিখ</label>
              <input v-model="payment.payment_date" type="date" :disabled="loading" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>পদ্ধতি</label>
              <select v-model="payment.payment_method" :disabled="loading">
                <option value="নগদ">নগদ</option>
                <option value="ব্যাংক">ব্যাংক</option>
                <option value="মুদ্রা">মুদ্রা</option>
                <option value="চেক">চেক</option>
              </select>
            </div>
            <div class="form-group">
              <label>রেফারেন্স</label>
              <input v-model="payment.reference" type="text" placeholder="ট্রানজ্যাকশন আইডি" :disabled="loading" />
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="loading || !payment.amount">
              <span v-if="loading" class="spinner"></span>
              <span v-else>প্রদান রেকর্ড</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Payment History -->
      <div class="card">
        <h3>প্রদান ইতিবোধ্য</h3>
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(payments || []).length === 0" class="empty-state"><p>কোনো প্রদান নেই</p></div>
        <table v-else class="table table-hover">
          <thead><tr><th>তারিখ</th><th>পরিমাণ (৳)</th><th>পদ্ধতি</th><th>রেফারেন্স</th><th>প্রদানকারী</th></tr></thead>
          <tbody>
            <tr v-for="p in payments" :key="p.id">
              <td>{{ p.payment_date }}</td>
              <td>{{ p.amount ? Number(p.amount).toLocaleString('bn-BD') : 0 }}</td>
              <td>{{ p.payment_method || '-' }}</td>
              <td>{{ p.reference || '-' }}</td>
              <td>{{ p.collector?.name_bn || p.collected_by_user_id || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(false)
const loan = ref<any>(null)
const payments = ref<any[]>([])
const installments = ref<any[]>([])
const error = ref('')
const success = ref('')

const payment = ref({
  amount: null,
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: 'নগদ',
  reference: '',
})

async function loadLoan() {
  loading.value = true
  try {
    const id = route.params.id
    const r = await api.get(`/loans/${id}`)
    loan.value = r.data?.data
    payments.value = loan.value?.payments || []
    installments.value = loan.value?.installments || []
  } catch (e: any) {
    error.value = 'ঋণ লোড করা যায়নি'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function recordPayment() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const r = await api.post(`/loans/${loan.value.id}/payments`, { ...payment.value })
    loan.value = r.data?.data
    payments.value = loan.value?.payments || []
    installments.value = loan.value?.installments || []
    success.value = 'প্রদান সফল!'
    payment.value = { amount: null, payment_date: new Date().toISOString().split('T')[0], payment_method: 'নগদ', reference: '' }
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'প্রদান রেকর্ড করা যায়নি'
  } finally {
    loading.value = false
  }
}

function money(value: unknown): string {
  return Number(value || 0).toLocaleString('bn-BD', { maximumFractionDigits: 2 })
}
function installmentClass(status: string): string {
  return status === 'paid' ? 'badge-success' : status === 'overdue' ? 'badge-danger' : status === 'partial' ? 'badge-warning' : 'badge-outline'
}
function installmentLabel(status: string): string {
  return ({ paid: 'পরিশোধিত', overdue: 'বিলম্বিত', partial: 'আংশিক', pending: 'অপেক্ষমান' } as Record<string, string>)[status] || status
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('bn-BD', { year: 'numeric', month: 'short', day: 'numeric' })
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

onMounted(loadLoan)
</script>

<style scoped>
.loan-detail-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { margin: 0; font-size: 1.25rem; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.text-muted { color: var(--color-text-light); }
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1.25rem; }
.schedule-card { grid-column: 1 / -1; }
.schedule-summary { display:flex;justify-content:space-between;gap:1rem;padding:.75rem 0;color:var(--color-text-light); }
.table-scroll { overflow-x:auto; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card h3 { margin: 0 0 1rem; font-size: 1.05rem; font-family: 'Noto Sans Bengali', sans-serif; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-body { padding: 1.25rem; }
.card > h3 { padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.info-list { display: grid; grid-template-columns: 140px 1fr; gap: 0.5rem 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.info-list dt { color: var(--color-text-light); font-size: 0.85rem; }
.info-list dd { margin: 0; font-size: 0.95rem; }
.badge { padding: 0.2rem 0.6rem; border-radius: 10px; font-size: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; white-space: nowrap; }
.badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.badge-danger { background: rgba(220, 38, 38, 0.15); color: #dc2626; }
.badge-secondary { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.badge-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text-light); }
.payment-form .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 0.75rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group input, .form-group select {
  padding: 0.55rem 0.8rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 0.9rem;
  font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg);
}
.form-actions { margin-top: 0.5rem; }
.btn { padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; display: inline-flex; align-items: center; gap: 0.35rem; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.spinner { width: 14px; height: 14px; border: 2px solid var(--color-text-on-primary); border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.spinner { width: 20px; height: 20px; }
.empty-state { padding: 1.5rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.table { width: 100%; border-collapse: collapse; font-family: 'Noto Sans Bengali', sans-serif; }
.table th, .table td { padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border-light); }
.table th { font-weight: 600; font-size: 0.8rem; color: var(--color-text-light); }
.alert { padding: 0.6rem 0.9rem; border-radius: 8px; margin-bottom: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: #fde2e2; color: var(--color-error); }
.alert-success { background: #dcfce8; color: #16a34a; }
</style>
