<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> অর্থায়ন ড্যাশবোর্ড</NuxtLink>
        <h1>তহবিল ও ফান্ডসমূহ</h1>
        <p class="page-subtitle">মাদ্রাসার বিভিন্ন ফান্ড, কালেকশন ও লক্ষ্যমাত্রা পরিচালনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openFundModal()">
          <icon name="plus" /> নতুন ফান্ড তৈরি করুন
        </button>
        <button class="btn btn-outline" @click="loadFunds(currentPage)">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ফান্ডের নাম বা বিবরণ খুঁজুন..." @keyup.enter="loadFunds(1)" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadFunds(1)">×</button>
      </div>
      <select v-model="typeFilter" class="form-select" @change="loadFunds(1)">
        <option value="">সব ধরনের ফান্ড (All Types)</option>
        <option value="সাধারণ">সাধারণ ফান্ড</option>
        <option value="যাকাত">যাকাত ফান্ড</option>
        <option value="নির্মাণ">নির্মাণ / অবকাঠামো</option>
        <option value="এতিম">এতিম সহায়তা</option>
        <option value="বিশেষ">বিশেষ ফান্ড</option>
      </select>
      <div class="pagination-info" v-if="fundsList.length">
        মোট <span class="highlight">{{ fundsList.length.toLocaleString('bn-BD') }}</span> টি ফান্ড
      </div>
    </div>

    <!-- Create / Edit Fund Modal -->
    <div v-if="showFundModal" class="modal-overlay" @click.self="showFundModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingId ? 'ফান্ড সম্পাদনা' : 'নতুন ফান্ড তৈরি করুন' }}</h3>
            <p>ফান্ডের নাম, ধরন, লক্ষ্যমাত্রা ও বিবরণ লিখুন</p>
          </div>
          <button class="modal-close-btn" @click="showFundModal = false">×</button>
        </div>
        <form @submit.prevent="saveFund" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>
          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">ফান্ডের নাম (বাংলা) *</label>
              <input v-model="form.name_bn" class="form-input" required placeholder="যেমন: নতুন মসজিদ নির্মাণ ফান্ড" />
            </div>
            <div class="form-group">
              <label class="form-label">ফান্ডের নাম (ইংরেজি)</label>
              <input v-model="form.name_en" class="form-input" placeholder="e.g. Mosque Construction Fund" />
            </div>
            <div class="form-group">
              <label class="form-label">ফান্ডের ধরন *</label>
              <select v-model="form.type" class="form-select" required>
                <option value="সাধারণ">সাধারণ ফান্ড</option>
                <option value="যাকাত">যাকাত ফান্ড</option>
                <option value="নির্মাণ">নির্মাণ / অবকাঠামো</option>
                <option value="এতিম">এতিম সহায়তা</option>
                <option value="বিশেষ">বিশেষ ফান্ড</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">লক্ষ্যমাত্রা (টাকা)</label>
              <input v-model.number="form.target_amount" type="number" min="0" class="form-input" placeholder="১০০০০০" />
            </div>
            <div class="form-group wide">
              <label class="form-label">বিবরণ</label>
              <textarea v-model="form.description_bn" class="form-textarea" rows="2" placeholder="ফান্ডের উদ্দেশ্য ও বিবরণ..."></textarea>
            </div>
            <div class="form-group wide">
              <label class="custom-checkbox">
                <input type="checkbox" v-model="form.is_active" />
                <span class="checkbox-text">ফান্ডটি সক্রিয় রাখুন</span>
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showFundModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'ফান্ড সংরক্ষণ করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Funds Grid -->
    <div v-if="loading" class="loading-state card"><div class="spinner" /><p>ফান্ড লোড হচ্ছে...</p></div>
    <div v-else-if="!fundsList.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="folder-plus" /></div>
      <h3>কোনো ফান্ড পাওয়া যায়নি</h3>
      <p>মাদ্রাসার কার্যক্রম পরিচালনার জন্য নতুন ফান্ড তৈরি করুন</p>
      <button class="btn btn-primary" @click="openFundModal()"><icon name="plus" /> নতুন ফান্ড তৈরি করুন</button>
    </div>
    <div v-else class="funds-grid">
      <div v-for="fund in fundsList" :key="fund.id" class="fund-card card">
        <div class="fund-card-header">
          <div class="fund-icon-box"><icon name="cash-multiple" /></div>
          <div class="fund-title-block">
            <h3>{{ fund.name_bn }}</h3>
            <span class="fund-type-tag">{{ fund.type || 'নির্দিষ্ট ফান্ড' }}</span>
          </div>
          <span class="status-pill" :class="fund.is_active ? 'badge-approved' : 'badge-rejected'">
            <span class="status-dot" />
            {{ fund.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="fund-amounts-row">
          <div class="amount-item">
            <span class="amount-label">সংগৃহীত তহবিল</span>
            <strong class="amount-val success">{{ formatCurrency(fund.collected_amount) }} ৳</strong>
          </div>
          <div class="amount-item" v-if="fund.target_amount">
            <span class="amount-label">লক্ষ্যমাত্রা</span>
            <strong class="amount-val">{{ formatCurrency(fund.target_amount) }} ৳</strong>
          </div>
        </div>

        <div v-if="fund.target_amount" class="fund-progress-wrap">
          <div class="fund-progress-bar" :style="{ width: getFundPercent(fund) + '%' }" />
          <span class="progress-label">{{ getFundPercent(fund) }}% অর্জিত</span>
        </div>

        <div class="card-footer-actions">
          <NuxtLink :to="`/finance/funds/${fund.id}`" class="view-link">
            বিস্তারিত বিবরণ <icon name="arrow-right" />
          </NuxtLink>
          <div class="action-buttons">
            <button class="action-btn" @click="openFundModal(fund)" title="সম্পাদনা">
              <icon name="pencil" />
            </button>
          </div>
        </div>
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
const showFundModal = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')
const funds = ref<any>(null)
const currentPage = ref(1)

const search = ref('')
const typeFilter = ref('')

const form = reactive({
  name_bn: '',
  name_en: '',
  type: 'সাধারণ',
  target_amount: 0,
  description_bn: '',
  is_active: true,
})

const fundsList = computed(() => {
  const list = funds.value?.data?.data || funds.value?.data || []
  return list.filter((f: any) => {
    const matchesSearch = !search.value || (f.name_bn || '').toLowerCase().includes(search.value.toLowerCase()) || (f.name_en || '').toLowerCase().includes(search.value.toLowerCase())
    const matchesType = !typeFilter.value || f.type === typeFilter.value
    return matchesSearch && matchesType
  })
})

async function loadFunds(page = 1) {
  loading.value = true
  currentPage.value = page
  try {
    const r = await api.get(`/finance/funds?per_page=100&page=${page}`)
    funds.value = r.data
  } catch (e: any) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openFundModal(fund: any = null) {
  error.value = ''
  if (fund) {
    editingId.value = fund.id
    form.name_bn = fund.name_bn || ''
    form.name_en = fund.name_en || ''
    form.type = fund.type || 'সাধারণ'
    form.target_amount = Number(fund.target_amount) || 0
    form.description_bn = fund.description_bn || ''
    form.is_active = fund.is_active !== false
  } else {
    editingId.value = null
    form.name_bn = ''
    form.name_en = ''
    form.type = 'সাধারণ'
    form.target_amount = 0
    form.description_bn = ''
    form.is_active = true
  }
  showFundModal.value = true
}

async function saveFund() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/finance/funds/${editingId.value}`, form)
    } else {
      await api.post('/finance/funds', form)
    }
    showFundModal.value = false
    await loadFunds(1)
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'ফান্ড সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

function getFundPercent(fund: any) {
  const target = Number(fund.target_amount) || 1
  const col = Number(fund.collected_amount) || 0
  return Math.min(100, Math.round((col / target) * 100))
}

function formatCurrency(val: any) {
  if (!val) return '০'
  return Number(val).toLocaleString('bn-BD')
}

onMounted(() => loadFunds())
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

/* Funds Grid */
.funds-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 1.25rem; }
.fund-card { padding: 1.35rem; display: flex; flex-direction: column; transition: transform 0.2s ease, box-shadow 0.2s ease; border-radius: 14px; }
.fund-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); }
.fund-card-header { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.1rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--color-border-light); }
.fund-icon-box { width: 44px; height: 44px; border-radius: 12px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
.fund-title-block { flex: 1; min-width: 0; }
.fund-title-block h3 { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fund-type-tag { display: inline-block; font-size: 0.75rem; color: var(--color-text-light); }

.fund-amounts-row { display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
.amount-item { display: flex; flex-direction: column; }
.amount-label { font-size: 0.76rem; color: var(--color-text-light); }
.amount-val { font-size: 1.15rem; font-weight: 800; color: var(--color-text); }
.amount-val.success { color: #15803d; }

.fund-progress-wrap { position: relative; height: 8px; background: rgba(0, 0, 0, 0.06); border-radius: 4px; margin-bottom: 1.25rem; }
.fund-progress-bar { height: 100%; background: linear-gradient(90deg, #10b981 0%, #145032 100%); border-radius: 4px; transition: width 0.3s ease; }
.progress-label { position: absolute; right: 0; top: 12px; font-size: 0.74rem; color: var(--color-text-light); font-weight: 600; }

.card-footer-actions { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 0.85rem; border-top: 1px solid var(--color-border-light); }
.view-link { font-size: 0.84rem; font-weight: 600; color: var(--color-primary); display: inline-flex; align-items: center; gap: 0.25rem; text-decoration: none; }
.view-link:hover { text-decoration: underline; }

.action-buttons { display: flex; gap: 0.35rem; }
.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; text-decoration: none; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); transform: translateY(-1px); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
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
.custom-checkbox { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 500; cursor: pointer; }
.custom-checkbox input { accent-color: var(--color-primary); width: 16px; height: 16px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.empty-icon-wrap { width: 60px; height: 60px; border-radius: 16px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem; }
.empty-state { padding: 3rem 1.5rem; text-align: center; }
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>
