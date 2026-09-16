<template>
  <div class="page-wrapper">
    <!-- Printable Header Block (Print Only) -->
    <div class="print-header-block print-only">
      <div class="print-bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
      <div class="print-inst-name">মারকাযুল উলূম আল-ইসলামিয়া</div>
      <div class="print-inst-sub">হিসাব ও অর্থায়ন বিভাগ · স্থায়ী সম্পদ ও অবচয় রেজিস্টার (Fixed Assets)</div>
      <div class="print-meta-row">
        <span>তারিখ: {{ new Date().toLocaleDateString('bn-BD', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
        <span>মুদ্রণ সময়: {{ new Date().toLocaleTimeString('bn-BD') }}</span>
      </div>
    </div>

    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/accounting" class="back-link"><icon name="arrow-left" /> অ্যাকাউন্টিং ড্যাশবোর্ড</NuxtLink>
        <h1>স্থায়ী সম্পদ ও অবচয় রেজিস্টার (Fixed Assets & Depreciation)</h1>
        <p class="page-subtitle">মাদ্রাসার জমি, ভবন, আসবাবপত্র, কম্পিউটার ও সরঞ্জামের ক্রয়মূল্য এবং বার্ষিক অবচয় হিসাব</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printAssets">
          <icon name="printer" /> রেজিস্টার প্রিন্ট
        </button>
        <button class="btn btn-primary" @click="openAddAssetModal">
          <icon name="plus" /> নতুন সম্পদ যুক্ত করুন
        </button>
      </div>
    </div>

    <!-- Assets KPI Summary -->
    <div class="stats-grid no-print">
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ totalCost.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট মূল ক্রয়মূল্য (Historical Cost)</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ totalDepreciation.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">পুঞ্জীভূত অবচয় (Accumulated Dep.)</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ totalBookValue.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">বর্তমান পুস্তকমূল্য (Book Value)</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card no-print">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="সম্পদের নাম, ট্যাগ বা অবস্থান খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="categoryFilter" class="form-select">
        <option value="">সকল ক্যাটাগরি</option>
        <option value="জমি ও ভবন">জমি ও ভবন</option>
        <option value="আসবাবপত্র ও ফিটিংস">আসবাবপত্র ও ফিটিংস</option>
        <option value="কম্পিউটার ও ইলেকট্রনিক্স">কম্পিউটার ও ইলেকট্রনিক্স</option>
        <option value="যানবাহন">যানবাহন</option>
      </select>
      <div class="pagination-info" v-if="filteredAssets.length">
        মোট <span class="highlight">{{ filteredAssets.length.toLocaleString('bn-BD') }}</span> টি সম্পদ
      </div>
    </div>

    <!-- Fixed Assets Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>সম্পদ ট্যাগ / কোড</th>
              <th>সম্পদের নাম ও বিবরণ</th>
              <th>ক্যাটাগরি</th>
              <th>ক্রয়ের তারিখ</th>
              <th class="text-right">ক্রয়মূল্য (৳)</th>
              <th class="text-center">অবচয়ের হার (%)</th>
              <th class="text-right">পুস্তকমূল্য (৳)</th>
              <th class="text-right no-print">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in filteredAssets" :key="a.id">
              <td><strong class="mono-font">{{ a.tag }}</strong></td>
              <td>
                <strong>{{ a.name }}</strong>
                <div class="sub-text">অবস্থান: {{ a.location }}</div>
              </td>
              <td><span class="fund-tag">{{ a.category }}</span></td>
              <td>{{ a.purchase_date }}</td>
              <td class="text-right font-bold">৳ {{ Number(a.cost || 0).toLocaleString('bn-BD') }}</td>
              <td class="text-center">{{ toBn(a.dep_rate) }}%</td>
              <td class="text-right font-bold text-success">৳ {{ Number(a.book_value ?? a.cost ?? 0).toLocaleString('bn-BD') }}</td>
              <td class="text-right no-print">
                <button class="action-btn delete" @click="deleteAsset(a.id)" title="মুছুন"><icon name="trash" /></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Asset Modal (Teleported) -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
          <div class="modal-card">
            <div class="modal-header">
              <div class="modal-title-group">
                <h3>নতুন স্থায়ী সম্পদ নিবন্ধন</h3>
                <p>সম্পদের বিবরণ, ক্রয়ের তথ্য ও অবচয়ের হার যুক্ত করুন</p>
              </div>
              <button class="modal-close-btn" @click="showModal = false">×</button>
            </div>
            <form @submit.prevent="saveAsset" class="modal-form">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">সম্পদের নাম *</label>
                  <input v-model="form.name" class="form-input" placeholder="যেমন: ক্লাসরুম ডেস্ক ও বেঞ্চ (৫০ সেট)" required />
                </div>
                <div class="form-group">
                  <label class="form-label">ক্যাটাগরি *</label>
                  <select v-model="form.category" class="form-select" required>
                    <option value="আসবাবপত্র ও ফিটিংস">আসবাবপত্র ও ফিটিংস</option>
                    <option value="জমি ও ভবন">জমি ও ভবন</option>
                    <option value="কম্পিউটার ও ইলেকট্রনিক্স">কম্পিউটার ও ইলেকট্রনিক্স</option>
                    <option value="যানবাহন">যানবাহন</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">ক্রয়ের তারিখ *</label>
                  <input v-model="form.purchase_date" type="date" class="form-input" required />
                </div>
                <div class="form-group">
                  <label class="form-label">ক্রয়মূল্য (৳) *</label>
                  <input v-model.number="form.cost" type="number" class="form-input" placeholder="৳ ১,৫০,০০০" required min="1" />
                </div>
                <div class="form-group">
                  <label class="form-label">বার্ষিক অবচয়ের হার (%)</label>
                  <input v-model.number="form.dep_rate" type="number" class="form-input" placeholder="10" />
                </div>
                <div class="form-group">
                  <label class="form-label">অবস্থান / রুম</label>
                  <input v-model="form.location" class="form-input" placeholder="যেমন: প্রধান একাডেমিক ভবন" />
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
                <button type="submit" class="btn btn-primary">সম্পদ সংরক্ষণ করুন</button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const showModal = ref(false)
const search = ref('')
const categoryFilter = ref('')

const assetsList = ref<any[]>([
  {
    id: 1,
    tag: 'AST-BLD-01',
    name: 'মাদ্রাসার ৪ তলা বিশিষ্ট নতুন একাডেমিক ভবন',
    category: 'জমি ও ভবন',
    purchase_date: '১০ জানু, ২০২০',
    location: 'মূল ক্যাম্পাস',
    cost: 8500000,
    dep_rate: 5,
    book_value: 6375000
  },
  {
    id: 2,
    tag: 'AST-DSK-02',
    name: 'ক্লাসরুম কাঠের ডেস্ক ও বেঞ্চ (৫০ সেট)',
    category: 'আসবাবপত্র ও ফিটিংস',
    purchase_date: '১৫ মার্চ, ২০২২',
    location: 'একাডেমিক ভবন ২য় তলা',
    cost: 180000,
    dep_rate: 10,
    book_value: 126000
  },
  {
    id: 3,
    tag: 'AST-COM-03',
    name: 'কম্পিউটার ল্যাব পিসি ও সার্ভার (১০ সেট)',
    category: 'কম্পিউটার ও ইলেকট্রনিক্স',
    purchase_date: '১০ মে, ২০২৪',
    location: 'আইটি ল্যাব',
    cost: 300000,
    dep_rate: 20,
    book_value: 220000
  }
])

const form = reactive({
  name: '',
  category: 'আসবাবপত্র ও ফিটিংস',
  purchase_date: new Date().toISOString().slice(0, 10),
  cost: 0,
  dep_rate: 10,
  location: ''
})

const filteredAssets = computed(() => {
  return assetsList.value.filter(a => {
    const term = (a.tag + ' ' + a.name + ' ' + (a.location || '')).toLowerCase()
    const matchesSearch = !search.value || term.includes(search.value.toLowerCase())
    const matchesCategory = !categoryFilter.value || a.category === categoryFilter.value
    return matchesSearch && matchesCategory
  })
})

const totalCost = computed(() => {
  return assetsList.value.reduce((sum, a) => sum + Number(a.cost || 0), 0)
})

const totalBookValue = computed(() => {
  return assetsList.value.reduce((sum, a) => sum + Number(a.book_value ?? a.cost ?? 0), 0)
})

const totalDepreciation = computed(() => {
  return Math.max(0, totalCost.value - totalBookValue.value)
})

async function loadAssets() {
  try {
    const res = await api.get('/accounting/fixed-assets').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      assetsList.value = fetched
    }
  } catch (e) {
    console.error(e)
  }
}

function openAddAssetModal() {
  form.name = ''
  form.cost = 0
  form.dep_rate = 10
  form.location = ''
  showModal.value = true
}

function printAssets() {
  window.print()
}

async function saveAsset() {
  try {
    const res = await api.post('/accounting/fixed-assets', { ...form }).catch(() => null)
    const saved = res?.data?.data
    assetsList.value.unshift(saved || {
      id: Date.now(),
      tag: 'AST-' + Math.floor(100 + Math.random() * 900),
      name: form.name,
      category: form.category,
      purchase_date: form.purchase_date,
      location: form.location || 'মূল ক্যাম্পাস',
      cost: form.cost,
      dep_rate: form.dep_rate,
      book_value: form.cost
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

async function deleteAsset(id: number) {
  if (confirm('আপনি কি এই স্থায়ী সম্পদটি মুছে ফেলতে চান?')) {
    await api.delete(`/accounting/fixed-assets/${id}`).catch(() => {})
    assetsList.value = assetsList.value.filter(a => a.id !== id)
  }
}

onMounted(loadAssets)

function toBn(num: any) {
  if (num === null || num === undefined) return ''
  return String(num).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d])
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

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--color-bg-card, #fff); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border: 1px solid var(--color-border); box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
.stat-icon-wrap { width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
.stat-icon-wrap.green { background: #dcfce7; color: #15803d; }
.stat-icon-wrap.amber { background: #fef3c7; color: #b45309; }
.stat-icon-wrap.blue { background: #dbeafe; color: #1d4ed8; }
.stat-value { font-size: 1.35rem; font-weight: 800; display: block; color: var(--color-text); }
.stat-label { font-size: 0.8rem; color: var(--color-text-light); }

.clear-search-btn { background: none; border: none; font-size: 1.1rem; color: var(--color-text-light); cursor: pointer; padding: 0 0.2rem; }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }
.fund-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); border-radius: 6px; font-size: 0.78rem; font-weight: 600; }
.text-success { color: #15803d; }

.action-btn.delete { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn.delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

/* Printable header styling */
.print-header-block { text-align: center; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px double #000; }
.print-bismillah { font-family: 'Traditional Arabic', serif; font-size: 1.25rem; margin-bottom: 0.35rem; }
.print-inst-name { font-size: 1.75rem; font-weight: 800; }
.print-inst-sub { font-size: 0.95rem; color: #444; margin-top: 0.2rem; }
.print-meta-row { display: flex; justify-content: space-between; margin-top: 0.75rem; font-size: 0.85rem; }

@media screen {
  .print-only { display: none !important; }
}

@media print {
  .no-print { display: none !important; }
  .print-only { display: block !important; }
  .page-wrapper { max-width: 100% !important; padding: 0 !important; }
  .table-card { border: none !important; box-shadow: none !important; }
  .premium-table { width: 100% !important; border-collapse: collapse !important; }
  .premium-table th, .premium-table td { border: 1px solid #ddd !important; padding: 6px !important; }
}
</style>
