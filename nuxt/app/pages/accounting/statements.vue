<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/accounting" class="back-link"><icon name="arrow-left" /> অ্যাকাউন্টিং ড্যাশবোর্ড</NuxtLink>
        <h1>আর্থিক বিবরণী ও লেজার রিপোর্ট (Financial Statements)</h1>
        <p class="page-subtitle">রেওয়ামিল (Trial Balance), উদ্বৃত্তপত্র (Balance Sheet) এবং লাভ-ক্ষতি বিবরণী (Profit & Loss)</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="printStatement">
          <icon name="printer" /> রিপোর্ট প্রিন্ট করুন
        </button>
      </div>
    </div>

    <!-- Statement Type Switcher -->
    <div class="acc-tabs-row no-print">
      <button class="acc-tab-btn" :class="{ active: currentTab === 'trial' }" @click="currentTab = 'trial'">
        রেওয়ামিল (Trial Balance)
      </button>
      <button class="acc-tab-btn" :class="{ active: currentTab === 'pl' }" @click="currentTab = 'pl'">
        আয়-ব্যয় ও উদ্বৃত্ত (Profit & Loss)
      </button>
      <button class="acc-tab-btn" :class="{ active: currentTab === 'balance_sheet' }" @click="currentTab = 'balance_sheet'">
        উদ্বৃত্তপত্র (Balance Sheet)
      </button>
    </div>

    <!-- Printable Header -->
    <div class="print-only print-header-box">
      <h2>মারকাযুল উলুম মাদ্রাসা গোপালগঞ্জ</h2>
      <p>আর্থিক হিসাব বিবরণী · শিক্ষাবর্ষ: ২০২৬</p>
      <h3>{{ statementHeading }}</h3>
    </div>

    <!-- Statement Table: Trial Balance -->
    <div v-if="currentTab === 'trial'" class="card table-card">
      <div class="table-responsive">
        <table class="premium-table ledger-table">
          <thead>
            <tr>
              <th style="width: 120px;">কোড</th>
              <th>হিসাবের নাম (Account Head)</th>
              <th class="text-right">ডেবিট ব্যালেন্স (৳)</th>
              <th class="text-right">ক্রেডিট ব্যালেন্স (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in trialRows" :key="t.code">
              <td><strong class="mono-font">{{ t.code }}</strong></td>
              <td>{{ t.name }}</td>
              <td class="text-right">{{ t.debit ? '৳ ' + t.debit.toLocaleString('bn-BD') : '—' }}</td>
              <td class="text-right">{{ t.credit ? '৳ ' + t.credit.toLocaleString('bn-BD') : '—' }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="2" class="text-right"><strong>মোট সমাপনী যোগফল (Total):</strong></td>
              <td class="text-right font-bold text-success">৳ ২৪,৫০,০০০</td>
              <td class="text-right font-bold text-success">৳ ২৪,৫০,০০০</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Statement Table: Profit & Loss -->
    <div v-else-if="currentTab === 'pl'" class="card table-card">
      <div class="table-responsive">
        <table class="premium-table ledger-table">
          <thead>
            <tr>
              <th>আয় ও ব্যয়ের বিবরণ</th>
              <th class="text-right">পরিমাণ (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr class="section-heading"><td colspan="2"><strong>আয়সমূহ (Incomes / Revenues):</strong></td></tr>
            <tr><td>শিক্ষার্থী মাসিক বেতন ও ভর্তি ফি</td><td class="text-right">৳ ৫,৮০,০০০</td></tr>
            <tr><td>সাধারণ দান ও অনুদান তহবিল</td><td class="text-right">৳ ২,৭০,০০০</td></tr>
            <tr class="subtotal-row"><td><strong>মোট রাজস্ব আয় (A):</strong></td><td class="text-right font-bold">৳ ৮,৫০,০০০</td></tr>

            <tr class="section-heading"><td colspan="2"><strong>ব্যয়সমূহ (Operating Expenses):</strong></td></tr>
            <tr><td>শিক্ষক ও কর্মকর্তা-কর্মচারী বেতন ভাতা</td><td class="text-right">৳ ৩,৮০,০০০</td></tr>
            <tr><td>বোর্ডিং খাদ্য ও মেস বাজার খরচ</td><td class="text-right">৳ ১,৪০,০০০</td></tr>
            <tr class="subtotal-row"><td><strong>মোট সার্বিক ব্যয় (B):</strong></td><td class="text-right font-bold">৳ ৫,২০,০০০</td></tr>
          </tbody>
          <tfoot>
            <tr class="total-row net-surplus">
              <td><strong>নিট উদ্বৃত্ত / মুনাফা (Net Surplus A - B):</strong></td>
              <td class="text-right font-bold text-success">৳ ৩,৩০,০০০</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Statement Table: Balance Sheet -->
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table ledger-table">
          <thead>
            <tr>
              <th>সম্পদ ও দায়ের বিবরণ</th>
              <th class="text-right">পরিমাণ (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr class="section-heading"><td colspan="2"><strong>চলতি ও স্থায়ী সম্পদ (Assets):</strong></td></tr>
            <tr><td>প্রধান ক্যাশ ইন হ্যান্ড</td><td class="text-right">৳ ৩,৪৫,০০০</td></tr>
            <tr><td>ইসলামী ব্যাংক চলতি হিসাব</td><td class="text-right">৳ ১৮,৫০,০০০</td></tr>
            <tr><td>বিকাশ / ডিজিটাল ক্যাশ</td><td class="text-right">৳ ৭৫,০০০</td></tr>
            <tr><td>স্থায়ী আসবাবপত্র ও সরঞ্জামাদি</td><td class="text-right">৳ ১,৮০,০০০</td></tr>
            <tr class="total-row"><td><strong>মোট সম্পদ (Total Assets):</strong></td><td class="text-right font-bold text-success">৳ ২৪,৫০,০০০</td></tr>

            <tr class="section-heading"><td colspan="2"><strong>দায় ও তহবিল (Liabilities & Capital):</strong></td></tr>
            <tr><td>চলতি বকেয়া পাওনাদার বিল</td><td class="text-right">৳ ১,২০,০০০</td></tr>
            <tr><td>মাদ্রাসার মূলধন ও সঞ্চিতি তহবিল</td><td class="text-right">৳ ২০,০০,০০০</td></tr>
            <tr><td>চলতি বছরের নিট উদ্বৃত্ত</td><td class="text-right">৳ ৩,৩০,০০০</td></tr>
            <tr class="total-row"><td><strong>মোট দায় ও ইকুইটি (Total Liabilities & Equity):</strong></td><td class="text-right font-bold text-success">৳ ২৪,৫০,০০০</td></tr>
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
const currentTab = ref('trial')

const trialRows = ref<any[]>([
  { code: '1000', name: 'প্রধান ক্যাশ ইন হ্যান্ড', debit: 345000, credit: null },
  { code: '1010', name: 'ইসলামী ব্যাংক চলতি হিসাব', debit: 1850000, credit: null },
  { code: '1020', name: 'বিকাশ / নগদ ওয়ালেট', debit: 75000, credit: null },
  { code: '1050', name: 'স্থায়ী আসবাবপত্র', debit: 180000, credit: null },
  { code: '2000', name: 'প্রদেয় হিসাব / বিল', debit: null, credit: 120000 },
  { code: '3000', name: 'মাদ্রাসার মূলধন ও সঞ্চিতি', debit: null, credit: 2000000 },
  { code: '4000', name: 'শিক্ষার্থী বেতন আয়', debit: null, credit: 580000 },
  { code: '4010', name: 'দান ও অনুদান আয়', debit: null, credit: 270000 },
  { code: '5000', name: 'শিক্ষক স্টাফ বেতন ভাতা', debit: 380000, credit: null },
  { code: '5010', name: 'বোর্ডিং খাদ্য খরচ', debit: 140000, credit: null }
])

async function loadTrialBalance() {
  try {
    const res = await api.get('/accounting/trial-balance').catch(() => null)
    if (res?.data?.data && res.data.data.length > 0) {
      trialRows.value = res.data.data.map((r: any) => ({
        code: r.code,
        name: r.name,
        debit: r.debit > 0 ? r.debit : null,
        credit: r.credit > 0 ? r.credit : null
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadTrialBalance)

const statementHeading = computed(() => {
  if (currentTab.value === 'trial') return 'রেওয়ামিল (Trial Balance)'
  if (currentTab.value === 'pl') return 'আয়-ব্যয় ও নিট উদ্বৃত্ত বিবরণী (Profit & Loss)'
  return 'উদ্বৃত্তপত্র / স্থিতিপত্র (Balance Sheet)'
})

function printStatement() {
  window.print()
}
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.acc-tabs-row { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.acc-tab-btn { padding: 0.55rem 1.2rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-bg); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.15s ease; color: var(--color-text); }
.acc-tab-btn.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.text-success { color: #15803d; }

.section-heading td { background: #f8fafc; font-size: 0.88rem; color: var(--color-primary); padding-top: 0.75rem; border-bottom: 1.5px solid var(--color-border); }
.subtotal-row td { background: rgba(0,0,0,0.02); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border); }
.total-row td { background: #f0fdf4; border-top: 2px solid #145032; border-bottom: 2px solid #145032; font-size: 0.95rem; }
.total-row.net-surplus td { background: #ecfdf5; font-size: 1.05rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }

.print-only { display: none; }
@media print {
  .no-print { display: none !important; }
  .print-only { display: block !important; }
  .page-wrapper { max-width: 100%; padding: 0; }
  .table-card { box-shadow: none; border: none; }
  .print-header-box { text-align: center; margin-bottom: 1.5rem; border-bottom: 2px solid #000; padding-bottom: 0.75rem; }
}
</style>
