<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/accounting" class="back-link"><icon name="arrow-left" /> অ্যাকাউন্টিং ড্যাশবোর্ড</NuxtLink>
        <h1>স্থায়ী সম্পদ ও অবচয় রেজিস্টার (Fixed Assets & Depreciation)</h1>
        <p class="page-subtitle">মাদ্রাসার জমি, ভবন, আসবাবপত্র, কম্পিউটার ও সরঞ্জামের ক্রয়মূল্য এবং বার্ষিক অবচয় হিসাব</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddAssetModal">
          <icon name="plus" /> নতুন সম্পদ যুক্ত করুন
        </button>
      </div>
    </div>

    <!-- Assets KPI Summary -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ ৫২,৮০,০০০</span>
          <span class="stat-label">মোট মূল ক্রয়মূল্য (Historical Cost)</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ ৪,২০,০০০</span>
          <span class="stat-label">পুঞ্জীভূত অবচয় (Accumulated Dep.)</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ ৪৮,৬০,০০০</span>
          <span class="stat-label">বর্তমান পুস্তকমূল্য (Book Value)</span>
        </div>
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
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in assetsList" :key="a.id">
              <td><strong class="mono-font">{{ a.tag }}</strong></td>
              <td>
                <strong>{{ a.name }}</strong>
                <div class="sub-text">অবস্থান: {{ a.location }}</div>
              </td>
              <td><span class="fund-tag">{{ a.category }}</span></td>
              <td>{{ a.purchase_date }}</td>
              <td class="text-right font-bold">৳ {{ a.cost.toLocaleString('bn-BD') }}</td>
              <td class="text-center">{{ toBn(a.dep_rate) }}%</td>
              <td class="text-right font-bold text-success">৳ {{ a.book_value.toLocaleString('bn-BD') }}</td>
              <td class="text-right">
                <button class="action-btn delete" @click="deleteAsset(a.id)" title="মুছুন"><icon name="trash" /></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Asset Modal -->
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
                <option value="ভবন ও স্থাপনা">ভবন ও স্থাপনা</option>
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
              <input v-model.number="form.cost" type="number" class="form-input" placeholder="৳ ১,৫০,০০০" required />
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
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const showModal = ref(false)

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
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
