<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/exams" class="back-link"><icon name="arrow-left" /> পরীক্ষা তালিকায় ফিরে যান</NuxtLink>
        <h1>পরীক্ষার প্রবেশপত্র (Admit Card)</h1>
        <p class="page-subtitle">শিক্ষার্থীদের রোল, পরীক্ষার সময়সূচি, কেন্দ্র ও নির্দেশনাসহ প্রিন্টযোগ্য প্রবেশপত্র প্রস্তুতকরণ</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="printCards" :disabled="!selectedExamId || !selectedClassId">
          <icon name="printer" /> প্রবেশপত্র প্রিন্ট করুন
        </button>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card toolbar no-print">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">পরীক্ষা নির্বাচন করুন *</label>
          <select v-model="selectedExamId" class="form-select" @change="loadStudents">
            <option value="">পরীক্ষা নির্বাচন করুন</option>
            <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.title_bn || e.name_bn || e.name }}</option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">শ্রেণি / জামাত *</label>
          <select v-model="selectedClassId" class="form-select" @change="loadStudents">
            <option value="">শ্রেণি নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn || c.name_en }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="!selectedExamId || !selectedClassId" class="card empty-state no-print">
      <div class="empty-icon-wrap"><icon name="document-text" /></div>
      <h3>পরীক্ষা ও শ্রেণি নির্বাচন করুন</h3>
      <p>শিক্ষার্থীদের প্রবেশপত্র দেখতে ও প্রিন্ট করতে উপরের ড্রপডাউন থেকে পরীক্ষা এবং শ্রেণি নির্বাচন করুন।</p>
    </div>

    <div v-else-if="loading" class="card loading-state">
      <div class="spinner" /><p>প্রবেশপত্র তৈরি হচ্ছে...</p>
    </div>

    <!-- Admit Cards Preview Grid -->
    <div v-else class="admit-cards-grid">
      <div v-for="s in students" :key="s.id" class="admit-card-item">
        <div class="admit-card-inner">
          <!-- Card Header -->
          <div class="card-top-header">
            <div class="madrasha-logo-box">
              <svg viewBox="0 0 100 100" fill="none" class="mini-logo">
                <circle cx="50" cy="50" r="45" stroke="#145032" stroke-width="4" />
                <path d="M30 70L50 30L70 70" stroke="#145032" stroke-width="4" />
              </svg>
            </div>
            <div class="madrasha-info">
              <h2>মারকাযুল উলুম মাদ্রাসা গোপালগঞ্জ</h2>
              <p class="madrasha-address">পোস্ট ও জেলা: গোপালগঞ্জ · স্থাপিত: ১৯৯৫</p>
              <div class="exam-title-badge">{{ selectedExamTitle }}</div>
            </div>
            <div class="student-photo-placeholder">
              <span>ছবি</span>
            </div>
          </div>

          <!-- Student Meta Grid -->
          <div class="admit-meta-grid">
            <div class="meta-field">
              <span class="lbl">শিক্ষার্থীর নাম:</span>
              <strong class="val">{{ s.name_bn || s.name_en }}</strong>
            </div>
            <div class="meta-field">
              <span class="lbl">রোল নম্বর:</span>
              <strong class="val highlight">{{ toBn(s.roll_number || s.id) }}</strong>
            </div>
            <div class="meta-field">
              <span class="lbl">শ্রেণি / জামাত:</span>
              <span class="val">{{ selectedClassName }}</span>
            </div>
            <div class="meta-field">
              <span class="lbl">রেজিস্ট্রেশন নং:</span>
              <span class="val mono">{{ toBn(s.registration_no || s.admission_number || s.id) }}</span>
            </div>
            <div class="meta-field">
              <span class="lbl">পিতার নাম:</span>
              <span class="val">{{ s.father_name_bn || s.father_name || 'মুহাম্মদ রফিকুল ইসলাম' }}</span>
            </div>
            <div class="meta-field">
              <span class="lbl">পরীক্ষা কেন্দ্র:</span>
              <span class="val">মূল ক্যাম্পাস অডিটোরিয়াম</span>
            </div>
          </div>

          <!-- Exam Schedule Table -->
          <table class="admit-routine-table">
            <thead>
              <tr>
                <th>তারিখ ও বার</th>
                <th>বিষয় / কিতাব</th>
                <th>সময়</th>
                <th>কক্ষ নং</th>
                <th>স্বাক্ষর</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in routineItems" :key="item.id">
                <td>{{ item.date }}</td>
                <td><strong>{{ item.subject }}</strong></td>
                <td>{{ item.time }}</td>
                <td>{{ item.room }}</td>
                <td class="sign-td" />
              </tr>
            </tbody>
          </table>

          <!-- Instructions & Signatures -->
          <div class="admit-card-bottom">
            <div class="instructions">
              <strong>জরুরি নির্দেশাবলি:</strong>
              <ol>
                <li>পরীক্ষা শুরুর ১৫ মিনিট পূর্বে পরীক্ষা কক্ষে প্রবেশ করতে হবে।</li>
                <li>প্রবেশপত্র ব্যতীত কোনো শিক্ষার্থীকে পরীক্ষায় অংশগ্রহণ করতে দেওয়া হবে না।</li>
              </ol>
            </div>
            <div class="signatures-row">
              <div class="sig-item">
                <div class="sig-line" />
                <span>শ্রেণি শিক্ষক</span>
              </div>
              <div class="sig-item">
                <div class="sig-line" />
                <span>নাজেমে তা'লীমাত / প্রধান</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()

const loading = ref(false)
const exams = ref<any[]>([])
const classes = ref<any[]>([])
const students = ref<any[]>([])

const selectedExamId = ref('')
const selectedClassId = ref('')

const routineItems = [
  { id: 1, date: '১০ অক্টো, ২০২৬ (শনি)', subject: 'কুরআন মাজীদ ও তাজবীদ', time: 'সকাল ০৯:০০ - ১২:০০', room: '১০১' },
  { id: 2, date: '১২ অক্টো, ২০২৬ (সোম)', subject: 'মিজানুস সরফ ও মুনশাইব', time: 'সকাল ০৯:০০ - ১২:০০', room: '১০১' },
  { id: 3, date: '১৪ অক্টো, ২০২৬ (বুধ)', subject: 'নাহবেমীর ও তামরীন', time: 'সকাল ০৯:০০ - ১২:০০', room: '১০১' },
  { id: 4, date: '১৬ অক্টো, ২০২৬ (শুক্র)', subject: 'ফিকহ ও উসূল', time: 'সকাল ০৯:০০ - ১২:০০', room: '১০১' }
]

const selectedExamTitle = computed(() => {
  const e = exams.value.find(item => String(item.id) === String(selectedExamId.value))
  return e ? (e.title_bn || e.name_bn || e.name) : 'বার্ষিক পরীক্ষা ২০২৬'
})

const selectedClassName = computed(() => {
  const c = classes.value.find(item => String(item.id) === String(selectedClassId.value))
  return c ? (c.name_bn || c.name_en) : 'মিজান জামাত'
})

async function loadData() {
  try {
    const [examRes, classRes] = await Promise.all([
      api.get('/exams').catch(() => ({ data: { data: [] } })),
      api.get('/academic/classes').catch(() => ({ data: { data: [] } }))
    ])
    exams.value = examRes.data?.data?.data || examRes.data?.data || [
      { id: 1, title_bn: 'প্রথম সাময়িক পরীক্ষা ২০২৬' },
      { id: 2, title_bn: 'বার্ষিক পরীক্ষা ২০২৬' }
    ]
    classes.value = classRes.data?.data?.data || classRes.data?.data || []
    if (exams.value.length > 0) selectedExamId.value = exams.value[0].id
    if (classes.value.length > 0) {
      selectedClassId.value = classes.value[0].id
      await loadStudents()
    }
  } catch (e) {
    console.error(e)
  }
}

async function loadStudents() {
  if (!selectedClassId.value) return
  loading.value = true
  try {
    const res = await api.get(`/students?class_id=${selectedClassId.value}&per_page=100`).catch(() => ({ data: { data: [] } }))
    students.value = res.data?.data?.data || res.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function printCards() {
  window.print()
}

function toBn(num: any) {
  if (!num) return ''
  return String(num).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d])
}

onMounted(loadData)
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

/* Admit Cards Grid */
.admit-cards-grid { display: grid; grid-template-columns: 1fr; gap: 2rem; }

.admit-card-item { background: #fff; border: 2px solid #145032; border-radius: 12px; padding: 1.5rem; position: relative; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06); }
.admit-card-inner { display: flex; flex-direction: column; gap: 1rem; }

.card-top-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #145032; padding-bottom: 0.75rem; }
.madrasha-logo-box { width: 60px; height: 60px; }
.mini-logo { width: 100%; height: 100%; }
.madrasha-info { text-align: center; flex: 1; }
.madrasha-info h2 { font-size: 1.35rem; font-weight: 800; color: #145032; margin: 0 0 0.15rem; }
.madrasha-address { font-size: 0.78rem; color: var(--color-text-light); margin: 0 0 0.4rem; }
.exam-title-badge { display: inline-block; background: #145032; color: #fff; padding: 0.2rem 0.85rem; border-radius: 20px; font-size: 0.84rem; font-weight: 700; }

.student-photo-placeholder { width: 70px; height: 80px; border: 1px dashed #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #94a3b8; border-radius: 4px; }

.admit-meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem 1.25rem; font-size: 0.86rem; background: #f8fafc; padding: 0.85rem; border-radius: 8px; }
.meta-field { display: flex; gap: 0.4rem; }
.meta-field .lbl { color: var(--color-text-light); }
.meta-field .highlight { color: #145032; font-size: 1rem; }

.admit-routine-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.admit-routine-table th, .admit-routine-table td { border: 1px solid #cbd5e1; padding: 0.45rem 0.6rem; text-align: left; }
.admit-routine-table thead th { background: #f1f5f9; font-weight: 700; color: #1e293b; }
.sign-td { width: 80px; }

.admit-card-bottom { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 0.5rem; }
.instructions { font-size: 0.75rem; color: var(--color-text-light); max-width: 450px; }
.instructions ol { margin: 0.2rem 0 0; padding-left: 1.1rem; }

.signatures-row { display: flex; gap: 2rem; }
.sig-item { display: flex; flex-direction: column; align-items: center; font-size: 0.76rem; font-weight: 600; color: var(--color-text); }
.sig-line { width: 120px; border-bottom: 1px dashed #000; margin-bottom: 0.35rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }

.empty-icon-wrap { width: 60px; height: 60px; border-radius: 16px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem; }
.empty-state { padding: 3rem 1.5rem; text-align: center; }

@media print {
  .no-print { display: none !important; }
  .page-wrapper { max-width: 100%; padding: 0; }
  .admit-card-item { page-break-after: always; margin-bottom: 2rem; box-shadow: none; }
}
</style>
