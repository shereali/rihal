<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">হিসাব ও অর্থায়ন</span>
        <h1>পেশাদার ডাবল-এন্ট্রি অ্যাকাউন্টিং ড্যাশবোর্ড</h1>
        <p class="page-subtitle">চার্ট অব অ্যাকাউন্টস, জার্নাল ও পেমেন্ট ভাউচার, রেওয়ামিল, উদ্বৃত্তপত্র ও লাভ-ক্ষতি বিবরণী</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/accounting/vouchers" class="btn btn-outline">
          <icon name="document-text" /> ভাউচার এন্ট্রি
        </NuxtLink>
        <NuxtLink to="/accounting/statements" class="btn btn-outline">
          <icon name="chart" /> আর্থিক বিবরণী ও রিপোর্ট
        </NuxtLink>
        <NuxtLink to="/accounting/chart" class="btn btn-primary">
          <icon name="plus" /> চার্ট অব অ্যাকাউন্টস
        </NuxtLink>
      </div>
    </div>

    <!-- Financial Summary KPI Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ stats.assets.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট সম্পদ (Total Assets)</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap red"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ stats.liabilities.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট দায় (Total Liabilities)</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="cash" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ stats.income.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মাসিক মোট আয় (Income)</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ stats.expenses.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মাসিক মোট ব্যয় (Expenses)</span>
        </div>
      </div>
    </div>

    <!-- Quick Accounting Navigation Cards -->
    <div class="accounting-nav-grid">
      <NuxtLink to="/accounting/chart" class="card acc-nav-card">
        <div class="acc-icon-box green"><icon name="academic" /></div>
        <div class="acc-nav-text">
          <h3>চার্ট অব অ্যাকাউন্টস (Chart of Accounts)</h3>
          <p>সম্পদ, দায়, ইকুইটি, আয় ও ব্যয় খাতের শ্রেণিবিন্যাস ও কোডিং</p>
        </div>
      </NuxtLink>

      <NuxtLink to="/accounting/vouchers" class="card acc-nav-card">
        <div class="acc-icon-box blue"><icon name="document-text" /></div>
        <div class="acc-nav-text">
          <h3>ভাউচার ও খতিয়ান ভুক্তি (Voucher Entry)</h3>
          <p>জার্নাল, পেমেন্ট, রিসিট ও ব্যাংক কন্ট্রা ভাউচার তৈরি ও অনুমোদন</p>
        </div>
      </NuxtLink>

      <NuxtLink to="/accounting/statements" class="card acc-nav-card">
        <div class="acc-icon-box purple"><icon name="chart" /></div>
        <div class="acc-nav-text">
          <h3>আর্থিক বিবরণী (Financial Statements)</h3>
          <p>রেওয়ামিল (Trial Balance), উদ্বৃত্তপত্র (Balance Sheet) ও লাভ-ক্ষতি রিপোর্ট</p>
        </div>
      </NuxtLink>

      <NuxtLink to="/accounting/fixed-assets" class="card acc-nav-card">
        <div class="acc-icon-box amber"><icon name="building" /></div>
        <div class="acc-nav-text">
          <h3>স্থায়ী সম্পদ ও অবচয় (Fixed Assets & Depreciation)</h3>
          <p>জমি, ভবন, আসবাবপত্র ও সরঞ্জামের তালিকা এবং অবচয় হিসাব</p>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const stats = ref({
  assets: 2450000,
  liabilities: 120000,
  income: 850000,
  expenses: 520000
})

async function loadAccountingOverview() {
  try {
    const res = await api.get('/accounting/trial-balance').catch(() => null)
    if (res?.data?.data) {
      const tb = res.data.data
      let assets = 0, liab = 0, inc = 0, exp = 0
      tb.forEach((row: any) => {
        if (row.account_type === 'asset') assets += Number(row.debit || 0)
        else if (row.account_type === 'liability') liab += Number(row.credit || 0)
        else if (row.account_type === 'revenue') inc += Number(row.credit || 0)
        else if (row.account_type === 'expense') exp += Number(row.debit || 0)
      })
      if (assets > 0 || inc > 0) {
        stats.value = { assets, liabilities: liab, income: inc, expenses: exp }
      }
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadAccountingOverview)
</script>

<style scoped>
.page-wrapper { max-width: 1320px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.eyebrow { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--color-primary); letter-spacing: 0.08em; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.accounting-nav-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.5rem; margin-top: 1.75rem; }
.acc-nav-card { padding: 1.5rem; border-radius: 14px; display: flex; align-items: center; gap: 1.25rem; text-decoration: none; color: inherit; transition: all 0.2s ease; }
.acc-nav-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); border-color: var(--color-primary); }

.acc-icon-box { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.acc-icon-box.green { background: #dcfce7; color: #15803d; }
.acc-icon-box.blue { background: #eff6ff; color: #2563eb; }
.acc-icon-box.purple { background: #f3e8ff; color: #7e22ce; }
.acc-icon-box.amber { background: #fffbeb; color: #b45309; }

.acc-nav-text h3 { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.25rem; color: var(--color-text); }
.acc-nav-text p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; line-height: 1.4; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
</style>
