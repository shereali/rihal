<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/promotions" class="back-link"><icon name="arrow-left" /> প্রমোশন ড্যাশবোর্ড</NuxtLink>
        <h1>ফারেগীন ও গ্রাজুয়েট ডিরেক্টরি (Alumni Directory)</h1>
        <p class="page-subtitle">দাওরায়ে হাদীস ও হিফজ সম্পন্নকারী সাবেক শিক্ষার্থীদের কেন্দ্রীয় পরিচিতি ও যোগাযোগ তালিকা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/promotions/jobless" class="btn btn-outline">
          <icon name="alert-circle" /> বেকার ফারেগীন
        </NuxtLink>
        <NuxtLink to="/promotions/employed" class="btn btn-outline">
          <icon name="check-circle" /> কর্মরত ফারেগীন
        </NuxtLink>
        <button class="btn btn-primary" @click="openRegisterModal">
          <icon name="plus" /> নতুন ফারেগীন নিবন্ধন
        </button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ alumniList.length.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">মোট নিবন্ধিত ফারেগীন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ employedCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">কর্মসংস্থানে নিয়োজিত</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ joblessCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">খিদমত / চাকরির সন্ধানে</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="academic" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ higherStudyCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">উচ্চ শিক্ষায় অধ্যয়নরত</span>
        </div>
      </div>
    </div>

    <!-- Search Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ফারেগীনের নাম, মোবাইল বা কর্মস্থল খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="batchFilter" class="form-select">
        <option value="">সকল শিক্ষাবর্ষ / ব্যাচ</option>
        <option value="2025-2026">২০২৫-২০২৬ ব্যাচ</option>
        <option value="2024-2025">২০২৪-২০২৫ ব্যাচ</option>
        <option value="2023-2024">২০২৩-২০২৪ ব্যাচ</option>
      </select>
      <select v-model="statusFilter" class="form-select">
        <option value="">সকল বর্তমান অবস্থা</option>
        <option value="employed">কর্মরত</option>
        <option value="jobless">কর্মসন্ধানী / বেকার</option>
        <option value="higher_study">উচ্চ শিক্ষায় রত</option>
      </select>
    </div>

    <!-- Alumni Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>ফারেগীন শিক্ষার্থী</th>
              <th>পাসের সন / ব্যাচ</th>
              <th>মোবাইল নম্বর</th>
              <th>বর্তমান কর্মস্থল / খিদমত</th>
              <th>পদবী</th>
              <th class="text-center">অবস্থা</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="al in filteredAlumni" :key="al.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(al.name) }">
                    {{ al.name.charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ al.name }}</strong>
                    <div class="sub-text">সনদ নং: {{ al.sanad_no }}</div>
                  </div>
                </div>
              </td>
              <td><span class="type-tag">{{ al.batch }}</span></td>
              <td class="mono-font">{{ al.phone }}</td>
              <td>{{ al.workplace || '—' }}</td>
              <td>{{ al.designation || '—' }}</td>
              <td class="text-center">
                <span class="status-pill" :class="statusBadgeClass(al.status)">
                  <span class="status-dot" />
                  {{ statusLabel(al.status) }}
                </span>
              </td>
              <td class="text-right">
                <button class="action-btn" title="যোগাযোগ ও তথ্য"><icon name="eye" /></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Register Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন ফারেগীন নিবন্ধন</h3>
            <p>ডিগ্রি সম্পন্নকারী শিক্ষার্থীর যোগাযোগের তথ্য ও বর্তমান অবস্থা যুক্ত করুন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveAlumni" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">শিক্ষার্থীর নাম *</label>
              <input v-model="form.name" class="form-input" placeholder="মুহাম্মদ আবদুল্লাহ" required />
            </div>
            <div class="form-group">
              <label class="form-label">মোবাইল নম্বর *</label>
              <input v-model="form.phone" class="form-input" placeholder="০১৭১১..." required />
            </div>
            <div class="form-group">
              <label class="form-label">পাসের সন / শিক্ষাবর্ষ *</label>
              <select v-model="form.batch" class="form-select" required>
                <option value="2025-2026">২০২৫-২০২৬ ব্যাচ</option>
                <option value="2024-2025">২০২৪-২০২৫ ব্যাচ</option>
                <option value="2023-2024">২০২৩-২০২৪ ব্যাচ</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">বর্তমান অবস্থা *</label>
              <select v-model="form.status" class="form-select" required>
                <option value="employed">কর্মরত (খিদমতে নিয়োজিত)</option>
                <option value="jobless">কর্মসন্ধানী (বেকার)</option>
                <option value="higher_study">উচ্চ শিক্ষায় অধ্যয়নরত</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">কর্মস্থল / মাদ্রাসার নাম</label>
              <input v-model="form.workplace" class="form-input" placeholder="যেমন: জামিয়া ইসলামিয়া ঢাকা" />
            </div>
            <div class="form-group">
              <label class="form-label">বর্তমান পদবী</label>
              <input v-model="form.designation" class="form-input" placeholder="যেমন: ইমাম ও খতীব / উস্তাদ" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">নিবন্ধন সম্পন্ন করুন</button>
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
const batchFilter = ref('')
const statusFilter = ref('')
const showModal = ref(false)

const alumniList = ref<any[]>([
  {
    id: 1,
    name: 'মাওলানা মুহাম্মদ আবদুল্লাহ',
    sanad_no: 'SND-2025-012',
    batch: '2024-2025',
    phone: '০১৭১২-৩৪৫৬৭৮',
    workplace: 'দারুল উলুম মাদ্রাসা ঢাকা',
    designation: 'সহকারী শিক্ষক',
    status: 'employed'
  },
  {
    id: 2,
    name: 'হাফেজ ক্বারী ইব্রাহীম খলিল',
    sanad_no: 'SND-2025-018',
    batch: '2024-2025',
    phone: '০১৮১২-৯৮৭৬৫৪',
    workplace: 'বায়তুল মোকাররম শাখা মসজিদ',
    designation: 'ইমাম ও খতীব',
    status: 'employed'
  }
])

const form = reactive({
  name: '',
  phone: '',
  batch: '2025-2026',
  status: 'employed',
  workplace: '',
  designation: ''
})

async function loadAlumni() {
  try {
    const res = await api.get('/alumni').catch(() => ({ data: { data: [] } }))
    const list = res.data?.data || []
    if (list.length > 0) {
      alumniList.value = list
    }
  } catch (e) {
    console.error(e)
  }
}

const employedCount = computed(() => alumniList.value.filter(a => a.status === 'employed').length)
const joblessCount = computed(() => alumniList.value.filter(a => a.status === 'jobless').length)
const higherStudyCount = computed(() => alumniList.value.filter(a => a.status === 'higher_study').length)

const filteredAlumni = computed(() => {
  return alumniList.value.filter(a => {
    const term = (a.name + ' ' + a.phone + ' ' + (a.workplace || '')).toLowerCase()
    const matchesSearch = !search.value || term.includes(search.value.toLowerCase())
    const matchesBatch = !batchFilter.value || a.batch === batchFilter.value
    const matchesStatus = !statusFilter.value || a.status === statusFilter.value
    return matchesSearch && matchesBatch && matchesStatus
  })
})

function openRegisterModal() {
  form.name = ''
  form.phone = ''
  form.workplace = ''
  form.designation = ''
  showModal.value = true
}

async function saveAlumni() {
  try {
    const res = await api.post('/alumni', { ...form }).catch(() => null)
    const saved = res?.data?.data
    alumniList.value.unshift(saved || {
      id: Date.now(),
      name: form.name,
      sanad_no: 'SND-2026-' + Math.floor(100 + Math.random() * 900),
      batch: form.batch,
      phone: form.phone,
      workplace: form.workplace || '—',
      designation: form.designation || '—',
      status: form.status
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

async function deleteAlumni(id: number) {
  if (confirm('আপনি কি এই ফারেগীন রেকর্ডটি মুছে ফেলতে চান?')) {
    await api.delete(`/alumni/${id}`).catch(() => {})
    alumniList.value = alumniList.value.filter(a => a.id !== id)
  }
}

onMounted(loadAlumni)

function statusBadgeClass(s: string) {
  if (s === 'employed') return 'badge-approved'
  if (s === 'jobless') return 'badge-rejected'
  return 'badge-pending'
}

function statusLabel(s: string) {
  if (s === 'employed') return 'কর্মরত'
  if (s === 'jobless') return 'বেকার / কর্মসন্ধানী'
  return 'উচ্চ শিক্ষায় রত'
}

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
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.type-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(0, 0, 0, 0.05); border-radius: 4px; font-size: 0.75rem; font-weight: 600; }

.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
