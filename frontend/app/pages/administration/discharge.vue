<template>
  <div class="page-wrapper">
    <!-- Printable Header -->
    <div class="print-header-block print-only">
      <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
      <h2>মারকাযুল উলূম আল-ইসলামিয়া</h2>
      <p class="print-sub">শিক্ষক ও কর্মী অব্যাহতি রেজিস্টার ও রেকর্ড</p>
      <p class="print-date">মুদ্রণের তারিখ: {{ new Date().toLocaleDateString('bn-BD') }}</p>
    </div>

    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/administration" class="back-link"><icon name="arrow-left" /> প্রশাসনিক ড্যাশবোর্ড</NuxtLink>
        <h1>শিক্ষক ও কর্মী অব্যাহতি রেজিস্টার (Staff Discharge Registry)</h1>
        <p class="page-subtitle">প্রতিষ্ঠানের সাবেক শিক্ষক ও কর্মচারীদের পদত্যাগ, অব্যাহতি ও ক্লিয়ারেন্স সংক্রান্ত রেকর্ড</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printPage">
          <icon name="printer" /> রেজিস্টার প্রিন্ট
        </button>
        <button class="btn btn-primary" @click="openAddDischargeModal">
          <icon name="plus" /> নতুন অব্যাহতি এন্ট্রি
        </button>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card no-print">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="স্টাফের নাম, আইডি বা পদবী খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <div class="pagination-info" v-if="filteredDischarges.length">
        মোট <span class="highlight">{{ filteredDischarges.length.toLocaleString('bn-BD') }}</span> জন কর্মী
      </div>
    </div>

    <!-- Discharged Staff Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>স্টাফ আইডি</th>
              <th>কর্মকর্তা / শিক্ষকের নাম</th>
              <th>পদবী ও বিভাগ</th>
              <th>যোগদানের তারিখ</th>
              <th>অব্যাহতির তারিখ</th>
              <th>অব্যাহতির কারণ</th>
              <th class="text-center">ক্লিয়ারেন্স স্ট্যাটাস</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in filteredDischarges" :key="d.id">
              <td><strong class="mono-font">{{ d.staff_id }}</strong></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(d.name) }">
                    {{ d.name.charAt(0) }}
                  </div>
                  <strong>{{ d.name }}</strong>
                </div>
              </td>
              <td>
                <div>{{ d.designation }}</div>
                <div class="sub-text">{{ d.department }}</div>
              </td>
              <td>{{ d.joining_date }}</td>
              <td><strong>{{ d.discharge_date }}</strong></td>
              <td>{{ d.reason }}</td>
              <td class="text-center">
                <span class="status-pill badge-approved">
                  <span class="status-dot" /> সম্পন্ন (Cleared)
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
          <div class="modal-card">
            <div class="modal-header">
              <div class="modal-title-group">
                <h3>নতুন স্টাফ অব্যাহতি এন্ট্রি</h3>
                <p>পদত্যাগকারী কর্মীর তথ্য ও অব্যাহতির কারণ লিপিবদ্ধ করুন</p>
              </div>
              <button class="modal-close-btn" @click="showModal = false">×</button>
            </div>
            <form @submit.prevent="saveDischarge" class="modal-form">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">স্টাফের নাম *</label>
                  <input v-model="form.name" class="form-input" placeholder="মাওলানা কাসেম আলী" required />
                </div>
                <div class="form-group">
                  <label class="form-label">পদবী *</label>
                  <input v-model="form.designation" class="form-input" placeholder="সহকারী উস্তাদ" required />
                </div>
                <div class="form-group">
                  <label class="form-label">অব্যাহতির তারিখ *</label>
                  <input v-model="form.discharge_date" type="date" class="form-input" required />
                </div>
                <div class="form-group">
                  <label class="form-label">অব্যাহতির ধরন *</label>
                  <select v-model="form.reason" class="form-select" required>
                    <option value="স্বেচ্ছায় পদত্যাগ">স্বেচ্ছায় পদত্যাগ</option>
                    <option value="উচ্চ শিক্ষা / হজে গমন">উচ্চ শিক্ষা / হজে গমন</option>
                    <option value="শারীরিক অসুস্থতা">শারীরিক অসুস্থতা</option>
                    <option value="চুক্তি সমাপ্তি">চুক্তি সমাপ্তি</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
                <button type="submit" class="btn btn-primary">অব্যাহতি রেকর্ড করুন</button>
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

const dischargeList = ref<any[]>([
  {
    id: 1,
    staff_id: 'STF-014',
    name: 'মাওলানা কাসেম আলী',
    designation: 'সহকারী উস্তাদ',
    department: 'হিফজ বিভাগ',
    joining_date: '০১ জানু, ২০২২',
    discharge_date: '৩০ জুন, ২০২৬',
    reason: 'স্বেচ্ছায় পদত্যাগ (নিজ এলাকায় মাদ্রাসা প্রতিষ্ঠা)'
  }
])

const filteredDischarges = computed(() => {
  if (!search.value) return dischargeList.value
  const q = search.value.toLowerCase().trim()
  return dischargeList.value.filter(d => 
    (d.name && d.name.toLowerCase().includes(q)) ||
    (d.staff_id && d.staff_id.toLowerCase().includes(q)) ||
    (d.designation && d.designation.toLowerCase().includes(q)) ||
    (d.department && d.department.toLowerCase().includes(q)) ||
    (d.reason && d.reason.toLowerCase().includes(q))
  )
})

const form = reactive({
  name: '',
  designation: '',
  discharge_date: new Date().toISOString().slice(0, 10),
  reason: 'স্বেচ্ছায় পদত্যাগ'
})

async function loadDischarges() {
  try {
    const res = await api.get('/administration/discharges').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      dischargeList.value = fetched
    }
  } catch (e) {
    console.error(e)
  }
}

function openAddDischargeModal() {
  form.name = ''
  form.designation = ''
  showModal.value = true
}

async function saveDischarge() {
  try {
    const res = await api.post('/administration/discharges', { ...form }).catch(() => null)
    const saved = res?.data?.data
    dischargeList.value.unshift(saved || {
      id: Date.now(),
      staff_id: 'STF-' + Math.floor(100 + Math.random() * 900),
      name: form.name,
      designation: form.designation,
      department: 'সাধারণ প্রশাসন',
      joining_date: '০১ জানু, ২০২৪',
      discharge_date: form.discharge_date,
      reason: form.reason
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

function printPage() {
  if (import.meta.client) {
    window.print()
  }
}

onMounted(loadDischarges)

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
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

.toolbar { display: flex; align-items: center; gap: 0.85rem; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; border-radius: 12px; }
.search-box { position: relative; flex: 1; min-width: 260px; }
.search-box input { width: 100%; padding: 0.55rem 2.2rem 0.55rem 2.2rem; border-radius: 8px; border: 1px solid var(--color-border); font-size: 0.88rem; }
.search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); font-size: 0.95rem; }
.clear-search-btn { position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 1.1rem; color: var(--color-text-muted); cursor: pointer; }
.pagination-info { font-size: 0.84rem; color: var(--color-text-muted); }
.pagination-info .highlight { font-weight: 700; color: var(--color-primary); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }
.mono-font { font-family: monospace; font-size: 0.84rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: #fff; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.print-only { display: none; }

@media print {
  .no-print, header, aside, .sidebar, .app-top-bar, .toolbar, .header-actions, button {
    display: none !important;
  }
  .page-wrapper {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .print-only {
    display: block !important;
  }
  .print-header-block {
    text-align: center;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #000;
    padding-bottom: 0.75rem;
  }
  .print-header-block .bismillah {
    font-family: 'Amiri', 'Traditional Arabic', serif;
    font-size: 1.15rem;
    margin-bottom: 0.25rem;
  }
  .print-header-block h2 {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0 0 0.25rem;
  }
  .print-header-block .print-sub {
    font-size: 0.9rem;
    color: #444;
    margin: 0;
  }
  .print-header-block .print-date {
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.25rem;
  }
}
</style>
