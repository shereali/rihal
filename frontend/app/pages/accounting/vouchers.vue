<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/accounting" class="back-link"><icon name="arrow-left" /> অ্যাকাউন্টিং ড্যাশবোর্ড</NuxtLink>
        <h1>হিসাব ভাউচার ও খতিয়ান ভুক্তি (Voucher Management)</h1>
        <p class="page-subtitle">জার্নাল ভাউচার (JV), পেমেন্ট ভাউচার (PV), রিসিট ভাউচার (RV) ও কন্ট্রা ভাউচার (CV) তৈরি ও অনুমোদন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreateVoucherModal">
          <icon name="plus" /> নতুন ভাউচার তৈরি করুন
        </button>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ভাউচার নং, বিবরণ বা হিসাব খাত খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="voucherTypeFilter" class="form-select">
        <option value="">সকল ভাউচার ধরন</option>
        <option value="PV">পেমেন্ট ভাউচার (Payment - PV)</option>
        <option value="RV">রিসিট ভাউচার (Receipt - RV)</option>
        <option value="JV">জার্নাল ভাউচার (Journal - JV)</option>
        <option value="CV">কন্ট্রা ভাউচার (Contra - CV)</option>
      </select>
      <select v-model="statusFilter" class="form-select">
        <option value="">সকল স্ট্যাটাস</option>
        <option value="approved">অনুমোদিত (Approved)</option>
        <option value="pending">অপেক্ষমান (Pending)</option>
      </select>
    </div>

    <!-- Vouchers Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>ভাউচার নম্বর</th>
              <th>তারিখ</th>
              <th>ধরন</th>
              <th>ডেবিট হিসাব খাত</th>
              <th>ক্রেডিট হিসাব খাত</th>
              <th class="text-right">টাকার পরিমাণ (৳)</th>
              <th class="text-center">অনুমোদন অবস্থা</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="v in filteredVouchers" :key="v.id">
              <td><strong class="mono-font">{{ v.voucher_no }}</strong></td>
              <td>{{ v.date }}</td>
              <td>
                <span class="type-tag" :class="v.type">{{ v.type_label }}</span>
              </td>
              <td><strong>{{ v.debit_account }}</strong></td>
              <td><strong>{{ v.credit_account }}</strong></td>
              <td class="text-right font-bold text-success">৳ {{ Number(v.amount || 0).toLocaleString('bn-BD') }}</td>
              <td class="text-center">
                <span class="status-pill" :class="v.status === 'approved' ? 'badge-approved' : 'badge-pending'">
                  <span class="status-dot" />
                  {{ v.status === 'approved' ? 'অনুমোদিত' : 'অপেক্ষমান' }}
                </span>
              </td>
              <td class="text-right">
                <button class="action-btn" title="প্রিন্ট ও বিস্তারিত"><icon name="printer" /></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Voucher Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card lg">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন ডাবল-এন্ট্রি ভাউচার এন্ট্রি</h3>
            <p>ডেবিট ও ক্রেডিট হিসাব খাতের সমন্বয়ে লেনদেন রেকর্ড করুন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveVoucher" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">ভাউচারের ধরন *</label>
              <select v-model="form.type" class="form-select" required>
                <option value="PV">পেমেন্ট ভাউচার (PV - নগদ/ব্যাংক প্রদান)</option>
                <option value="RV">রিসিট ভাউচার (RV - নগদ/ব্যাংক গ্রহণ)</option>
                <option value="JV">জার্নাল ভাউচার (JV - সমন্বয় দাখিলা)</option>
                <option value="CV">কন্ট্রা ভাউচার (CV - ব্যাংক ও ক্যাশ স্থানান্তর)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">তারিখ *</label>
              <input v-model="form.date" type="date" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">ডেবিট হিসাব খাত (Dr) *</label>
              <select v-model="form.debit_account" class="form-select" required>
                <option value="শিক্ষক ও স্টাফ বেতন ভাতা (5000)">শিক্ষক ও স্টাফ বেতন ভাতা (5000)</option>
                <option value="বোর্ডিং খাদ্য ও বাজার খরচ (5010)">বোর্ডিং খাদ্য ও বাজার খরচ (5010)</option>
                <option value="ইসলামী ব্যাংক চলতি হিসাব (1010)">ইসলামী ব্যাংক চলতি হিসাব (1010)</option>
                <option value="প্রধান ক্যাশ ইন হ্যান্ড (1000)">প্রধান ক্যাশ ইন হ্যান্ড (1000)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">ক্রেডিট হিসাব খাত (Cr) *</label>
              <select v-model="form.credit_account" class="form-select" required>
                <option value="প্রধান ক্যাশ ইন হ্যান্ড (1000)">প্রধান ক্যাশ ইন হ্যান্ড (1000)</option>
                <option value="ইসলামী ব্যাংক চলতি হিসাব (1010)">ইসলামী ব্যাংক চলতি হিসাব (1010)</option>
                <option value="শিক্ষার্থী মাসিক বেতন ও ভর্তি ফি (4000)">শিক্ষার্থী মাসিক বেতন ও ভর্তি ফি (4000)</option>
                <option value="সাধারণ দান ও অনুদান (4010)">সাধারণ দান ও অনুদান (4010)</option>
              </select>
            </div>
            <div class="form-group wide">
              <label class="form-label">টাকার পরিমাণ (৳) *</label>
              <input v-model.number="form.amount" type="number" class="form-input" placeholder="৳ ২৫,০০০" required />
            </div>
            <div class="form-group wide">
              <label class="form-label">লেনদেনের সংক্ষিপ্ত বিবরণ / ন্যারেশন (Narration) *</label>
              <input v-model="form.narration" class="form-input" placeholder="যেমন: আগস্ট মাসের শিক্ষক বেতন ভাতা প্রদান" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">ভাউচার তৈরি সম্পন্ন করুন</button>
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
const search = ref('')
const voucherTypeFilter = ref('')
const statusFilter = ref('')
const showModal = ref(false)

const vouchersList = ref<any[]>([
  {
    id: 1,
    voucher_no: 'PV-2026-042',
    date: '২৬ আগস্ট, ২০২৬',
    type: 'PV',
    type_label: 'পেমেন্ট (PV)',
    debit_account: 'বোর্ডিং খাদ্য ও বাজার খরচ (5010)',
    credit_account: 'প্রধান ক্যাশ ইন হ্যান্ড (1000)',
    amount: 4850,
    status: 'approved'
  }
])

const form = reactive({
  type: 'PV',
  date: new Date().toISOString().slice(0, 10),
  debit_account: 'বোর্ডিং খাদ্য ও বাজার খরচ (5010)',
  credit_account: 'প্রধান ক্যাশ ইন হ্যান্ড (1000)',
  amount: 0,
  narration: ''
})

async function loadVouchers() {
  try {
    const res = await api.get('/accounting/vouchers').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data?.data || res.data?.data || []
    if (fetched.length > 0) {
      vouchersList.value = fetched.map((v: any) => ({
        id: v.id,
        voucher_no: v.entry_number || 'JV-' + v.id,
        date: v.date,
        type: v.entry_type || 'PV',
        type_label: v.entry_type === 'PV' ? 'পেমেন্ট (PV)' : v.entry_type === 'RV' ? 'রিসিট (RV)' : v.entry_type === 'CV' ? 'কন্ট্রা (CV)' : 'জার্নাল (JV)',
        debit_account: v.description || 'বোর্ডিং ও সাধারণ ব্যয়',
        credit_account: 'প্রধান ক্যাশ ইন হ্যান্ড (1000)',
        amount: v.total_debit || v.amount || 0,
        status: v.status || 'approved'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredVouchers = computed(() => {
  return vouchersList.value.filter(v => {
    const term = (v.voucher_no + ' ' + v.debit_account + ' ' + v.credit_account).toLowerCase()
    const matchesSearch = !search.value || term.includes(search.value.toLowerCase())
    const matchesType = !voucherTypeFilter.value || v.type === voucherTypeFilter.value
    const matchesStatus = !statusFilter.value || v.status === statusFilter.value
    return matchesSearch && matchesType && matchesStatus
  })
})

function openCreateVoucherModal() {
  form.amount = 0
  form.narration = ''
  showModal.value = true
}

async function saveVoucher() {
  try {
    const res = await api.post('/accounting/vouchers', {
      entry_type: form.type,
      date: form.date,
      description: form.narration || `${form.debit_account} / ${form.credit_account}`,
      amount: form.amount
    })

    const saved = res?.data?.data
    if (saved) {
      vouchersList.value.unshift({
        id: saved.id,
        voucher_no: saved.entry_number,
        date: saved.date,
        type: form.type,
        type_label: form.type === 'PV' ? 'পেমেন্ট (PV)' : form.type === 'RV' ? 'রিসিট (RV)' : form.type === 'CV' ? 'কন্ট্রা (CV)' : 'জার্নাল (JV)',
        debit_account: form.debit_account,
        credit_account: form.credit_account,
        amount: saved.total_debit || form.amount,
        status: 'approved'
      })
      showModal.value = false
    }
  } catch (e: any) {
    console.error('Failed to save voucher:', e)
    alert(e?.response?.data?.message || 'ভাউচার সংরক্ষণে ত্রুটি দেখা দিয়েছে।')
  }
}

onMounted(loadVouchers)
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

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.text-success { color: #15803d; }

.type-tag { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
.type-tag.PV { background: #fee2e2; color: #dc2626; }
.type-tag.RV { background: #dcfce7; color: #15803d; }
.type-tag.CV { background: #eff6ff; color: #2563eb; }
.type-tag.JV { background: #f3e8ff; color: #7e22ce; }

.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-card.lg { max-width: 680px; }
.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
