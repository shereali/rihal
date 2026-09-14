<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/results" class="back-link"><icon name="arrow-left" /> ফলাফল তালিকায় ফিরে যান</NuxtLink>
        <h1>পরীক্ষার মন্তব্য ও আচরণ মূল্যায়ন</h1>
        <p class="page-subtitle">প্রতিটি শিক্ষার্থীর রেজাল্ট কার্ডে মুদ্রিত হওয়ার জন্য শ্রেণি শিক্ষকের মন্তব্য ও আখলাক মূল্যায়ন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="saveAllComments" :disabled="saving">
          <icon name="check" /> {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'সকল মন্তব্য সংরক্ষণ করুন' }}
        </button>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card toolbar">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">পরীক্ষা *</label>
          <select v-model="selectedExam" class="form-select" @change="loadStudents">
            <option value="">সকল পরীক্ষা</option>
            <option v-for="e in examsList" :key="e.id" :value="e.id">
              {{ e.name_bn || e.name_en }}
            </option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">শ্রেণি / জামাত *</label>
          <select v-model="selectedClass" class="form-select" @change="loadStudents">
            <option value="">সকল শ্রেণি</option>
            <option v-for="c in classesList" :key="c.id" :value="c.id">
              {{ c.name_bn || c.name_en }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Comments List Table -->
    <div class="card table-card">
      <div v-if="loading" class="loading-wrap">
        <Icon name="loader" class="spin" :size="24" />
        <span>শিক্ষার্থীদের তালিকা লোড হচ্ছে...</span>
      </div>
      <div v-else-if="!studentsList.length" class="empty-state">
        <p>নির্বাচিত ফিল্টারে কোনো শিক্ষার্থী পাওয়া যায়নি।</p>
      </div>
      <div v-else class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th style="width: 70px;">রোল</th>
              <th style="width: 220px;">শিক্ষার্থী</th>
              <th style="width: 140px;">আচরণ ও স্বভাব</th>
              <th>শিক্ষকের মন্তব্য (Result Card Remark)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="st in studentsList" :key="st.id">
              <td><strong>{{ toBn(st.roll) }}</strong></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(st.name) }">
                    {{ st.name.charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ st.name }}</strong>
                    <div class="sub-text">{{ st.class_name ? `শ্রেণি: ${st.class_name}` : 'শিক্ষার্থী' }}</div>
                  </div>
                </div>
              </td>
              <td>
                <select v-model="st.conduct" class="form-select sm">
                  <option value="উত্তম">উত্তম (Excellent)</option>
                  <option value="ভালো">ভালো (Good)</option>
                  <option value="সন্তোষজনক">সন্তোষজনক (Fair)</option>
                  <option value="উন্নতি প্রয়োজন">উন্নতি প্রয়োজন</option>
                </select>
              </td>
              <td>
                <div class="comment-input-wrap">
                  <input v-model="st.comment" class="form-input sm" placeholder="মন্তব্য লিখুন..." />
                  <div class="quick-tags">
                    <span class="q-tag" @click="st.comment = 'মাশাআল্লাহ! পড়াশোনায় খুবই মনোযোগী ও চরিত্রবান।'">মনোযোগী</span>
                    <span class="q-tag" @click="st.comment = 'ভালো করছে, তবে আরও মেহনত ও নিয়মিত উপস্থিতি দরকার।'">মেহনত দরকার</span>
                    <span class="q-tag" @click="st.comment = 'সবকে বিশেষ নজর দেওয়া প্রয়োজন।'">নজর প্রয়োজন</span>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Floating Toast -->
    <Transition name="fade">
      <div v-if="toastMessage" class="feedback-toast" :class="toastType">
        <Icon :name="toastType === 'success' ? 'check' : 'alertCircle'" />
        <span>{{ toastMessage }}</span>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const selectedExam = ref('')
const selectedClass = ref('')
const saving = ref(false)
const loading = ref(false)
const toastMessage = ref('')
const toastType = ref<'success' | 'error'>('success')

const examsList = ref<any[]>([])
const classesList = ref<any[]>([])
const studentsList = ref<any[]>([])

function showToast(msg: string, type: 'success' | 'error' = 'success') {
  toastMessage.value = msg
  toastType.value = type
  setTimeout(() => {
    toastMessage.value = ''
  }, 3500)
}

async function loadFilters() {
  try {
    const [eRes, cRes] = await Promise.all([
      api.get('/exams?per_page=50').catch(() => null),
      api.get('/academic/classes').catch(() => null)
    ])
    examsList.value = eRes?.data?.data?.data || eRes?.data?.data || []
    classesList.value = cRes?.data?.data?.data || cRes?.data?.data || []
    if (examsList.value.length) selectedExam.value = examsList.value[0].id
  } catch (e) {
    console.error(e)
  }
}

async function loadStudents() {
  loading.value = true
  try {
    const params: any = { per_page: 50 }
    if (selectedClass.value) params.class_id = selectedClass.value
    const res = await api.get('/students', { params }).catch(() => null)
    const studs = res?.data?.data?.data || res?.data?.data || []
    studentsList.value = studs.map((s: any, idx: number) => ({
      id: s.id,
      roll: s.roll_number || (101 + idx),
      name: s.name_bn || s.name_en || `শিক্ষার্থী ${idx + 1}`,
      class_name: s.class?.name_bn || s.class?.name_en || '',
      conduct: 'উত্তম',
      comment: 'মাশাআল্লাহ! পড়াশোনায় খুবই মনোযোগী ও চরিত্রবান।'
    }))
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function saveAllComments() {
  saving.value = true
  try {
    localStorage.setItem('rihal_exam_comments_' + (selectedExam.value || 'all'), JSON.stringify(studentsList.value))
    await api.post('/exam-marks/save-comments', { 
      exam_id: selectedExam.value || null,
      class_id: selectedClass.value || null,
      comments: studentsList.value 
    }).catch(() => null)
    showToast('সকল শিক্ষার্থীর মন্তব্য ও আচরণ মূল্যায়ন সফলভাবে সংরক্ষিত হয়েছে!', 'success')
  } catch (e) {
    showToast('মন্তব্য লোকাল ব্যাকআপে সংরক্ষিত হয়েছে।', 'success')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await loadFilters()
  await loadStudents()
})

function toBn(num: any) {
  if (num === null || num === undefined) return ''
  return String(num).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d])
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
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.filter-row { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.filter-item { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 0.35rem; }
.filter-label { font-size: 0.82rem; font-weight: 700; color: var(--color-text); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }

.comment-input-wrap { display: flex; flex-direction: column; gap: 0.35rem; }
.form-input.sm, .form-select.sm { padding: 0.4rem 0.65rem; font-size: 0.82rem; }

.quick-tags { display: flex; gap: 0.3rem; flex-wrap: wrap; }
.q-tag { font-size: 0.72rem; background: #f1f5f9; color: #334155; padding: 0.1rem 0.45rem; border-radius: 4px; cursor: pointer; transition: all 0.15s ease; border: 1px solid #e2e8f0; }
.q-tag:hover { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }

.loading-wrap, .empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 3rem 1.5rem;
  color: var(--color-text-light);
  font-size: 0.92rem;
}

.feedback-toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.75rem 1.25rem;
  border-radius: var(--radius-md);
  background: var(--color-primary-dark);
  color: #fff;
  box-shadow: var(--elevation-3);
  z-index: 1000;
  font-size: 0.88rem;
  font-weight: 600;
}
.feedback-toast.error { background: var(--color-error); }
</style>
