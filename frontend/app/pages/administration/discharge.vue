<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/administration" class="back-link"><icon name="arrow-left" /> প্রশাসনিক ড্যাশবোর্ড</NuxtLink>
        <h1>শিক্ষক ও কর্মী অব্যাহতি রেজিস্টার (Staff Discharge Registry)</h1>
        <p class="page-subtitle">প্রতিষ্ঠানের সাবেক শিক্ষক ও কর্মচারীদের পদত্যাগ, অব্যাহতি ও ক্লিয়ারেন্স সংক্রান্ত রেকর্ড</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddDischargeModal">
          <icon name="plus" /> নতুন অব্যাহতি এন্ট্রি
        </button>
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
            <tr v-for="d in dischargeList" :key="d.id">
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
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const showModal = ref(false)

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

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }
.mono-font { font-family: monospace; font-size: 0.84rem; }

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
