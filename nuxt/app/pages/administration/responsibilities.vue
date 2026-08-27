<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/administration" class="back-link"><icon name="arrow-left" /> প্রশাসনিক ড্যাশবোর্ড</NuxtLink>
        <h1>দায়িত্ব ও দায়িত্ব বণ্টন ম্যাট্রিক্স (Duty Assignments)</h1>
        <p class="page-subtitle">শৃঙ্খলা শিক্ষক, বোর্ডিং তত্ত্বাবধায়ক, মসজিদ ইমাম ও পরীক্ষা কমিটির দায়িত্ব বণ্টন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddDutyModal">
          <icon name="plus" /> নতুন দায়িত্ব অর্পণ
        </button>
      </div>
    </div>

    <!-- Responsibilities Grid -->
    <div class="responsibilities-grid">
      <div v-for="d in dutiesList" :key="d.id" class="card duty-card">
        <div class="duty-header">
          <div class="duty-icon-box" :class="d.color">
            <icon :name="d.icon" />
          </div>
          <div class="duty-info">
            <h3>{{ d.title }}</h3>
            <span class="duty-dept">{{ d.department }}</span>
          </div>
        </div>

        <div class="assigned-persons-box">
          <span class="sec-label">দায়িত্বপ্রাপ্ত শিক্ষক / কর্মকর্তা:</span>
          <div class="user-cell">
            <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(d.person_name) }">
              {{ d.person_name.charAt(0) }}
            </div>
            <div>
              <strong>{{ d.person_name }}</strong>
              <div class="sub-text">{{ d.designation }} · {{ d.phone }}</div>
            </div>
          </div>
        </div>

        <div class="duty-details-box">
          <span class="sec-label">দায়িত্বের বিবরণ:</span>
          <p>{{ d.description }}</p>
        </div>

        <div class="duty-card-footer">
          <span class="status-pill badge-approved">
            <span class="status-dot" /> সক্রিয় দায়িত্ব
          </span>
          <div class="actions-group">
            <button class="action-btn" title="সম্পাদনা"><icon name="pencil" /></button>
            <button class="action-btn delete" @click="deleteDuty(d.id)" title="মুছুন"><icon name="trash" /></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন প্রাতিষ্ঠানিক দায়িত্ব অর্পণ</h3>
            <p>শিক্ষক বা কর্মকর্তাকে দায়িত্বের পদ ও বিবরণ অর্পণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveDuty" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">দায়িত্বের নাম / পদবী *</label>
              <input v-model="form.title" class="form-input" placeholder="যেমন: প্রধান নাজেমে তা'লীমাত" required />
            </div>
            <div class="form-group">
              <label class="form-label">বিভাগ / দপ্তর *</label>
              <select v-model="form.department" class="form-select" required>
                <option value="তা'লীমাত (শিক্ষা বিভাগ)">তা'লীমাত (শিক্ষা বিভাগ)</option>
                <option value="বোর্ডিং ও মেস ব্যবস্থাপনা">বোর্ডিং ও মেস ব্যবস্থাপনা</option>
                <option value="শৃঙ্খলা ও দারুল ইকামা">শৃঙ্খলা ও দারুল ইকামা</option>
                <option value="হিসাব ও অর্থায়ন">হিসাব ও অর্থায়ন</option>
              </select>
            </div>
            <div class="form-group wide">
              <label class="form-label">দায়িত্বপ্রাপ্ত শিক্ষক / ব্যক্তি *</label>
              <input v-model="form.person_name" class="form-input" placeholder="মাওলানা মাহমুদ হাসান" required />
            </div>
            <div class="form-group wide">
              <label class="form-label">দায়িত্বের বিস্তারিত পরিধি</label>
              <textarea v-model="form.description" class="form-textarea" rows="3" placeholder="দায়িত্বের করণীয় বিষয়সমূহ লিখুন..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">দায়িত্ব সংরক্ষণ করুন</button>
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

const dutiesList = ref<any[]>([
  {
    id: 1,
    title: 'প্রধান নাজেমে তা\'লীমাত (শিক্ষা নিয়ন্ত্রক)',
    department: 'তা\'লীমাত (শিক্ষা বিভাগ)',
    person_name: 'মাওলানা মাহমুদ হাসান',
    designation: 'সিনিয়র উস্তাদ ও মুহাদ্দিস',
    phone: '০১৭১২-৩৪৫৬৭৮',
    description: 'দৈনিক ক্লাসের রুটিন পর্যবেক্ষণ, উস্তাদদের উপস্থিতি ও পাঠ মূল্যায়ন তদারকি।',
    icon: 'academic',
    color: 'blue'
  },
  {
    id: 2,
    title: 'বোর্ডিং ও বাবুর্চিখানা তত্ত্বাবধায়ক',
    department: 'বোর্ডিং ও মেস ব্যবস্থাপনা',
    person_name: 'মাওলানা রফিকুল ইসলাম',
    designation: 'সহকারী উস্তাদ',
    phone: '০১৮১২-৯৮৭৬৫৪',
    description: 'দৈনিক বাজার নিয়ন্ত্রণ, খাদ্যের মান যাচাই ও মিল হিসাব সংরক্ষণ।',
    icon: 'building',
    color: 'green'
  }
])

const form = reactive({
  title: '',
  department: 'তা\'লীমাত (শিক্ষা বিভাগ)',
  person_name: '',
  description: ''
})

async function loadDuties() {
  try {
    const res = await api.get('/administration/duties').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      dutiesList.value = fetched.map((d: any, idx: number) => ({
        id: d.id,
        title: d.title,
        department: d.department,
        person_name: d.person_name,
        designation: d.designation || 'নিযুক্ত কর্মকর্তা',
        phone: d.phone || '০১৭১১-...',
        description: d.description || 'দায়িত্ব অর্পিত হয়েছে।',
        icon: idx % 2 === 0 ? 'academic' : 'building',
        color: idx % 2 === 0 ? 'blue' : 'green'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

function openAddDutyModal() {
  form.title = ''
  form.person_name = ''
  form.description = ''
  showModal.value = true
}

async function saveDuty() {
  try {
    const res = await api.post('/administration/duties', { ...form }).catch(() => null)
    const saved = res?.data?.data
    dutiesList.value.unshift(saved ? {
      id: saved.id,
      title: saved.title,
      department: saved.department,
      person_name: saved.person_name,
      designation: 'নিযুক্ত কর্মকর্তা',
      phone: '০১৭১১-...',
      description: saved.description || 'দায়িত্ব অর্পিত হয়েছে।',
      icon: 'academic',
      color: 'blue'
    } : {
      id: Date.now(),
      title: form.title,
      department: form.department,
      person_name: form.person_name,
      designation: 'নিযুক্ত কর্মকর্তা',
      phone: '০১৭১১-...',
      description: form.description || 'দায়িত্ব অর্পিত হয়েছে।',
      icon: 'academic',
      color: 'blue'
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

async function deleteDuty(id: number) {
  if (confirm('আপনি কি এই দায়িত্বটি বাতিল করতে চান?')) {
    await api.delete(`/administration/duties/${id}`).catch(() => {})
    dutiesList.value = dutiesList.value.filter(d => d.id !== id)
  }
}

onMounted(loadDuties)

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

.responsibilities-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.5rem; }
.duty-card { border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; }

.duty-header { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--color-border-light); }
.duty-icon-box { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.duty-icon-box.blue { background: #eff6ff; color: #2563eb; }
.duty-icon-box.green { background: #dcfce7; color: #15803d; }
.duty-icon-box.amber { background: #fffbeb; color: #b45309; }

.duty-info h3 { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.15rem; }
.duty-dept { font-size: 0.78rem; color: var(--color-text-light); }

.sec-label { font-size: 0.74rem; color: var(--color-text-light); font-weight: 700; margin-bottom: 0.35rem; display: block; }
.assigned-persons-box { margin-bottom: 1rem; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }

.duty-details-box { margin-bottom: 1.25rem; font-size: 0.84rem; line-height: 1.5; color: var(--color-text); }
.duty-details-box p { margin: 0; }

.duty-card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 0.85rem; border-top: 1px solid var(--color-border-light); }
.actions-group { display: flex; gap: 0.35rem; }

.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); }
.action-btn.delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
