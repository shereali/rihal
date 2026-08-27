<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/hostel" class="back-link"><icon name="arrow-left" /> হোস্টেল ও বোর্ডিং ড্যাশবোর্ড</NuxtLink>
        <h1>বোর্ডিং দৈনিক বাজার ও খরচের হিসাব</h1>
        <p class="page-subtitle">বোর্ডিং রান্নাঘরের জন্য চাল, ডাল, তেল, মাছ, মাংস ও শাকসবজি ক্রয়ের দৈনিক খরচের ভাউচার</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/hostel/boarding-meals" class="btn btn-outline">
          <icon name="calendar" /> দৈনিক মিল হিসাব
        </NuxtLink>
        <button class="btn btn-primary" @click="openBazaarModal">
          <icon name="plus" /> নতুন বাজার এন্ট্রি
        </button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ totalMonthExpense.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">চলতি মাসের মোট বাজার খরচ</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">৳ {{ todayExpense.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">আজকের বাজার খরচ</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ bazaarList.length.toLocaleString('bn-BD') }} টি</span>
          <span class="stat-label">মোট বাজার ভাউচার সংখ্যা</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ক্রেতার নাম, মালের বিবরণ বা মেমো নম্বর খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="monthFilter" class="form-select">
        <option value="">চলতি মাস (আগস্ট ২০২৬)</option>
        <option value="07">জুলাই ২০২৬</option>
        <option value="06">জুন ২০২৬</option>
      </select>
    </div>

    <!-- Bazaar Vouchers Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>মেমো / ভাউচার নং</th>
              <th>তারিখ</th>
              <th>বাজারকারীর নাম (দায়িত্বপ্রাপ্ত)</th>
              <th>ক্রয়কৃত খাদ্যদ্রব্যের বিবরণ</th>
              <th class="text-right">মোট পরিমাণ (কেজি/লি)</th>
              <th class="text-right">খরচের পরিমাণ (৳)</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in filteredBazaar" :key="b.id">
              <td><strong class="mono-font">{{ b.voucher_no }}</strong></td>
              <td>{{ b.date }}</td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(b.buyer_name) }">
                    {{ b.buyer_name.charAt(0) }}
                  </div>
                  <strong>{{ b.buyer_name }}</strong>
                </div>
              </td>
              <td><span class="items-text">{{ b.items_summary }}</span></td>
              <td class="text-right">{{ b.total_qty }}</td>
              <td class="text-right font-bold text-success">৳ {{ b.amount.toLocaleString('bn-BD') }}</td>
              <td class="text-right">
                <button class="action-btn delete" @click="deleteEntry(b.id)" title="মুছুন"><icon name="trash" /></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Bazaar Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন বোর্ডিং বাজার এন্ট্রি</h3>
            <p>বোর্ডিং বাবুর্চিখানার ক্রয়ের রশিদ ও টাকার পরিমাণ রেকর্ড করুন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveBazaar" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">বাজারের তারিখ *</label>
              <input v-model="form.date" type="date" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">বাজারকারীর নাম *</label>
              <input v-model="form.buyer_name" class="form-input" placeholder="মাওলানা রফিকুল ইসলাম (তত্ত্বাবধায়ক)" required />
            </div>
            <div class="form-group wide">
              <label class="form-label">পণ্য ও পরিমাণের বিবরণ *</label>
              <textarea v-model="form.items_summary" class="form-textarea" rows="3" placeholder="যেমন: মিনিকেট চাল ৫০ কেজি, রুই মাছ ১০ কেজি, সয়াবিন তেল ৫ লিটার, আলু ও পেঁয়াজ" required></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">মোট পরিমাণ</label>
              <input v-model="form.total_qty" class="form-input" placeholder="যেমন: ৬৫ কেজি" />
            </div>
            <div class="form-group">
              <label class="form-label">মোট বাজার খরচ (৳) *</label>
              <input v-model.number="form.amount" type="number" class="form-input" placeholder="৳ ৫,৪০০" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">বাজার ভাউচার সংরক্ষণ করুন</button>
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
const monthFilter = ref('')
const showModal = ref(false)

const bazaarList = ref<any[]>([
  {
    id: 1,
    voucher_no: 'BZR-2026-104',
    date: '২৬ আগস্ট, ২০২৬',
    buyer_name: 'মাওলানা রফিকুল ইসলাম',
    items_summary: 'মিনিকেট চাল ৫০ কেজি, ডাল ৫ কেজি, আলু ১০ কেজি',
    total_qty: '৬৫ কেজি',
    amount: 4850
  }
])

const form = reactive({
  date: new Date().toISOString().slice(0, 10),
  buyer_name: '',
  items_summary: '',
  total_qty: '',
  amount: 0
})

async function loadBazaars() {
  try {
    const res = await api.get('/boarding/bazaar').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      bazaarList.value = fetched
    }
  } catch (e) {
    console.error(e)
  }
}

const totalMonthExpense = computed(() => bazaarList.value.reduce((acc, b) => acc + (parseFloat(b.amount) || 0), 0))
const todayExpense = computed(() => parseFloat(bazaarList.value[0]?.amount) || 0)

const filteredBazaar = computed(() => {
  return bazaarList.value.filter(b => {
    const term = (b.voucher_no + ' ' + b.buyer_name + ' ' + b.items_summary).toLowerCase()
    return !search.value || term.includes(search.value.toLowerCase())
  })
})

function openBazaarModal() {
  form.buyer_name = 'মাওলানা রফিকুল ইসলাম'
  form.items_summary = ''
  form.total_qty = ''
  form.amount = 0
  showModal.value = true
}

async function saveBazaar() {
  try {
    const res = await api.post('/boarding/bazaar', { ...form }).catch(() => null)
    const saved = res?.data?.data
    bazaarList.value.unshift(saved || {
      id: Date.now(),
      voucher_no: 'BZR-2026-' + Math.floor(100 + Math.random() * 900),
      date: form.date,
      buyer_name: form.buyer_name,
      items_summary: form.items_summary,
      total_qty: form.total_qty || '—',
      amount: form.amount
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

async function deleteEntry(id: number) {
  if (confirm('আপনি কি এই বাজার ভাউচারটি মুছে ফেলতে চান?')) {
    await api.delete(`/boarding/bazaar/${id}`).catch(() => {})
    bazaarList.value = bazaarList.value.filter(b => b.id !== id)
  }
}

onMounted(loadBazaars)

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
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

.clear-search-btn { background: none; border: none; font-size: 1.1rem; color: var(--color-text-light); cursor: pointer; padding: 0 0.2rem; }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.items-text { font-size: 0.84rem; color: var(--color-text); }
.text-success { color: #15803d; }

.action-btn.delete { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn.delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
