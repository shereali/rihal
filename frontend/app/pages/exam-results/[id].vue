<template>
  <div class="page-wrapper">
    <!-- Top Action Navigation -->
    <div class="no-print header-nav">
      <NuxtLink to="/results" class="back-link">
        <icon name="arrow-left" /> ফলাফল তালিকায় ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <button
          v-if="result?.is_published"
          class="btn btn-outline-danger btn-sm"
          @click="confirmTogglePublish(false)"
          :disabled="actionLoading"
        >
          <icon name="eye-off" /> অপ্রকাশিত করুন
        </button>
        <button
          v-else
          class="btn btn-outline-success btn-sm"
          @click="confirmTogglePublish(true)"
          :disabled="actionLoading"
        >
          <icon name="check" /> ফলাফল প্রকাশ করুন
        </button>
        <button class="btn btn-primary btn-sm" @click="printTranscript">
          <icon name="printer" /> মার্কশীট প্রিন্ট করুন
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>ফলাফল ও মার্কশীট প্রস্তুত হচ্ছে...</p>
    </div>

    <div v-else-if="error" class="alert alert-error card">
      <p>{{ error }}</p>
      <NuxtLink to="/results" class="btn btn-primary btn-sm" style="margin-top: 0.75rem;">ফলাফল তালিকায় যান</NuxtLink>
    </div>

    <!-- Official Marksheet Document -->
    <div v-else-if="result" class="marksheet-sheet card animate-fade-in" id="printable-transcript">
      <!-- Institute Letterhead -->
      <div class="institute-letterhead">
        <div class="institute-logo-seal">
          <span class="arabic-bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</span>
        </div>
        <h1 class="institute-title">দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h1>
        <p class="institute-subtitle">আল-জামেয়াতুল ইসলামিয়া দারুল হাদিস মাদরাসা কমপ্লেক্স</p>
        <div class="doc-badge">
          <span>অফিসিয়াল মার্কশীট ও একাডেমিক ফলাফল বিবরণী</span>
        </div>
        <p class="exam-title-badge">
          {{ result.exam?.name_bn || result.exam?.name_en || 'বার্ষিক পরীক্ষা ২০২৬' }}
        </p>
      </div>

      <!-- Student & Exam Information Grid -->
      <div class="meta-card">
        <div class="meta-grid">
          <div class="meta-item">
            <span class="meta-label">শিক্ষার্থীর পূর্ণ নাম:</span>
            <span class="meta-val font-semibold">{{ result.student?.name_bn || result.student?.name_en || 'সাধারণ শিক্ষার্থী' }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">ইংরেজি নাম:</span>
            <span class="meta-val">{{ result.student?.name_en || '—' }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">এনরোলমেন্ট / রোল নং:</span>
            <span class="meta-val font-mono">{{ result.enrollment_number || ('ID-' + result.student_id) }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">শিক্ষাবর্ষ / সেশন:</span>
            <span class="meta-val">{{ result.session?.name_bn || result.session?.name_en || '২০২৬' }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">শ্রেণি:</span>
            <span class="meta-val">{{ result.exam?.class?.name_bn || 'নির্ধারিত শ্রেণি' }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">ফলাফল প্রকাশের তারিখ:</span>
            <span class="meta-val">{{ formatDate(result.published_at || result.updated_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Summary KPI Scoreboard -->
      <div class="scoreboard-grid">
        <div class="score-card gpa-card">
          <span class="score-label">প্রাপ্ত জিপিএ (GPA)</span>
          <span class="score-value">{{ result.gpa ?? '০.০০' }}</span>
          <span class="score-scale">স্কেল ৫.০০</span>
        </div>
        <div class="score-card grade-card">
          <span class="score-label">লেটার গ্রেড</span>
          <span class="score-value" :class="gradeClass(result.grade)">{{ result.grade || '—' }}</span>
          <span class="score-scale">{{ result.percentage ? result.percentage + '%' : '' }}</span>
        </div>
        <div class="score-card merit-card">
          <span class="score-label">মেধাস্থান / শ্রেণি ক্রম</span>
          <span class="score-value">{{ result.class_position ? result.class_position + 'ম' : (result.merit_list_position ? result.merit_list_position + 'ম' : '—') }}</span>
          <span class="score-scale">মেরিট পজিশন</span>
        </div>
        <div class="score-card status-card">
          <span class="score-label">চূড়ান্ত ফলাফল</span>
          <span class="score-status" :class="isPassed ? 'status-pass' : 'status-fail'">
            {{ isPassed ? 'উত্তীর্ণ (Passed)' : 'অনুত্তীর্ণ (Failed)' }}
          </span>
          <span class="score-scale">{{ result.is_published ? 'প্রকাশিত' : 'অপ্রকাশিত' }}</span>
        </div>
      </div>

      <!-- Subject Marks Breakdown Table -->
      <div class="table-wrap">
        <table class="marks-table">
          <thead>
            <tr>
              <th style="width: 45px;">ক্রম</th>
              <th>কিতাব / বিষয়ের নাম</th>
              <th class="text-center" style="width: 100px;">পূর্ণমান</th>
              <th class="text-center" style="width: 110px;">প্রাপ্ত নম্বর</th>
              <th class="text-center" style="width: 110px;">সর্বোচ্চ নম্বর</th>
              <th class="text-center" style="width: 90px;">লেটার গ্রেড</th>
              <th class="text-center" style="width: 90px;">গ্রেড পয়েন্ট</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(sub, idx) in parsedSubjects" :key="idx">
              <td class="text-center font-mono">{{ (idx + 1).toLocaleString('bn-BD') }}</td>
              <td class="font-medium">{{ sub.subject_name || sub.name_bn || sub.subject || ('বিষয় ' + (idx + 1)) }}</td>
              <td class="text-center">{{ sub.full_marks || '১০০' }}</td>
              <td class="text-center font-bold" :class="Number(sub.obtained_marks || sub.marks || 0) < 33 ? 'text-fail' : ''">
                {{ sub.obtained_marks ?? sub.marks ?? '—' }}
              </td>
              <td class="text-center text-muted">{{ sub.highest_marks || '—' }}</td>
              <td class="text-center">
                <span class="grade-pill" :class="gradeClass(sub.grade)">{{ sub.grade || calculateGrade(sub.obtained_marks ?? sub.marks) }}</span>
              </td>
              <td class="text-center font-mono">{{ sub.gpa ?? sub.grade_point ?? calculateGpa(sub.obtained_marks ?? sub.marks) }}</td>
            </tr>
            <tr v-if="!parsedSubjects.length">
              <td colspan="7" class="text-center text-muted py-4">
                কোনো বিষয়ভিত্তিক মার্কস অন্তর্ভুক্ত করা হয়নি
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="2" class="text-right font-bold">মোট প্রাপ্ত নম্বর ও অর্জিত গড়:</td>
              <td class="text-center font-bold">{{ totalFullMarks }}</td>
              <td class="text-center font-bold highlight-text">{{ totalObtainedMarks }}</td>
              <td class="text-center text-muted">—</td>
              <td class="text-center font-bold">{{ result.grade || '—' }}</td>
              <td class="text-center font-bold font-mono">{{ result.gpa ?? '০.০০' }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Grading Scale Matrix Table -->
      <div class="grading-scale-box">
        <h4 class="scale-title">গ্রেডিং পদ্ধতি নির্দেশিকা (Grading Scale):</h4>
        <div class="scale-pills">
          <span class="scale-item"><strong>৮০-১০০:</strong> A+ (৫.০০)</span>
          <span class="scale-item"><strong>৭০-৭৯:</strong> A (৪.০০)</span>
          <span class="scale-item"><strong>৬০-৬৯:</strong> A- (৩.৫০)</span>
          <span class="scale-item"><strong>৫০-৫৯:</strong> B (৩.০০)</span>
          <span class="scale-item"><strong>৪০-৪৯:</strong> C (২.০০)</span>
          <span class="scale-item"><strong>৩৩-৩৯:</strong> D (১.০০)</span>
          <span class="scale-item"><strong>০-৩২:</strong> F (০.০০)</span>
        </div>
      </div>

      <!-- Signatures Footer -->
      <div class="signatures-grid">
        <div class="sig-block">
          <div class="sig-line" />
          <span class="sig-title">শ্রেণি শিক্ষক</span>
        </div>
        <div class="sig-block">
          <div class="sig-line" />
          <span class="sig-title">পরীক্ষা নিয়ন্ত্রক</span>
        </div>
        <div class="sig-block">
          <div class="sig-line" />
          <span class="sig-title">মুহতামিম / প্রধান শিক্ষক</span>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toastMessage" class="toast-notification animate-fade-in">
      {{ toastMessage }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()

const result = ref<any>(null)
const loading = ref(true)
const error = ref('')
const actionLoading = ref(false)
const toastMessage = ref('')

function showToast(msg: string) {
  toastMessage.value = msg
  setTimeout(() => { toastMessage.value = '' }, 3500)
}

const parsedSubjects = computed(() => {
  if (!result.value) return []
  const subData = result.value.subject_results
  if (Array.isArray(subData)) return subData
  if (typeof subData === 'string') {
    try { return JSON.parse(subData) } catch { return [] }
  }
  return []
})

const isPassed = computed(() => {
  if (!result.value) return false
  if (result.value.grade === 'F') return false
  if (Number(result.value.gpa) === 0) return false
  return true
})

const totalFullMarks = computed(() => {
  return parsedSubjects.value.reduce((sum, s) => sum + (Number(s.full_marks) || 100), 0)
})

const totalObtainedMarks = computed(() => {
  return parsedSubjects.value.reduce((sum, s) => sum + (Number(s.obtained_marks ?? s.marks) || 0), 0)
})

async function loadResult() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/exam-results/${route.params.id}`)
    result.value = res.data?.data || res.data
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'ফলাফল বিবরণী লোড করা যায়নি'
  } finally {
    loading.value = false
  }
}

async function confirmTogglePublish(publish: boolean) {
  actionLoading.value = true
  try {
    const endpoint = `/exam-results/${route.params.id}/${publish ? 'publish' : 'unpublish'}`
    await api.patch(endpoint)
    showToast(publish ? 'ফলাফল সফলভাবে প্রকাশিত হয়েছে' : 'ফলাফল অপ্রকাশিত করা হয়েছে')
    await loadResult()
  } catch (e: any) {
    console.error(e)
  } finally {
    actionLoading.value = false
  }
}

function printTranscript() {
  window.print()
}

function calculateGrade(marks: any): string {
  const m = Number(marks)
  if (isNaN(m)) return '—'
  if (m >= 80) return 'A+'
  if (m >= 70) return 'A'
  if (m >= 60) return 'A-'
  if (m >= 50) return 'B'
  if (m >= 40) return 'C'
  if (m >= 33) return 'D'
  return 'F'
}

function calculateGpa(marks: any): string {
  const m = Number(marks)
  if (isNaN(m)) return '—'
  if (m >= 80) return '৫.০০'
  if (m >= 70) return '৪.০০'
  if (m >= 60) return '৩.৫০'
  if (m >= 50) return '৩.০০'
  if (m >= 40) return '২.০০'
  if (m >= 33) return '১.০০'
  return '০.০০'
}

function gradeClass(grade: string) {
  if (!grade) return ''
  const g = grade.toUpperCase()
  if (g.includes('A')) return 'grade-a'
  if (g.includes('B')) return 'grade-b'
  if (g.includes('C')) return 'grade-c'
  if (g.includes('D')) return 'grade-d'
  if (g.includes('F')) return 'grade-f'
  return ''
}

function formatDate(val: string) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('bn-BD', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(loadResult)
</script>

<style scoped>
.page-wrapper {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem;
}

.header-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-primary, #0284c7);
  font-weight: 600;
  text-decoration: none;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.marksheet-sheet {
  background: #ffffff;
  border-radius: 16px;
  padding: 2.5rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

/* Header Letterhead */
.institute-letterhead {
  text-align: center;
  border-bottom: 2px solid #0f172a;
  padding-bottom: 1.25rem;
  margin-bottom: 1.5rem;
}

.arabic-bismillah {
  font-family: 'Amiri', serif;
  font-size: 1.25rem;
  color: #0f172a;
  display: block;
  margin-bottom: 0.35rem;
}

.institute-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0.25rem 0;
}

.institute-subtitle {
  font-size: 0.95rem;
  color: #475569;
  margin: 0 0 0.75rem;
}

.doc-badge {
  display: inline-block;
  background: #f1f5f9;
  padding: 0.35rem 1.25rem;
  border-radius: 9999px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.exam-title-badge {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--color-primary, #0284c7);
  margin: 0;
}

/* Metadata Grid */
.meta-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  margin-bottom: 1.5rem;
}

.meta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 0.75rem 1.5rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
}

.meta-label {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 600;
}

.meta-val {
  font-size: 0.95rem;
  color: #0f172a;
}

/* Scoreboard Grid */
.scoreboard-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.75rem;
}

.score-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.score-label {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.score-value {
  font-size: 1.65rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
}

.score-scale {
  font-size: 0.7rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

.score-status {
  font-size: 1.1rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
}

.status-pass { color: #16a34a; }
.status-fail { color: #dc2626; }

.grade-a { color: #16a34a; }
.grade-b { color: #0284c7; }
.grade-c { color: #d97706; }
.grade-d { color: #ea580c; }
.grade-f { color: #dc2626; }

/* Marks Table */
.table-wrap {
  margin-bottom: 1.5rem;
  overflow-x: auto;
}

.marks-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.marks-table th {
  background: #f1f5f9;
  padding: 0.75rem 1rem;
  color: #334155;
  font-weight: 700;
  border: 1px solid #cbd5e1;
}

.marks-table td {
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  color: #1e293b;
}

.total-row td {
  background: #f8fafc;
  border-top: 2px solid #0f172a;
}

.highlight-text {
  color: var(--color-primary, #0284c7);
  font-size: 1.05rem;
}

.grade-pill {
  display: inline-block;
  font-weight: 700;
  font-size: 0.85rem;
}

.text-fail {
  color: #dc2626;
}

/* Grading Scale Box */
.grading-scale-box {
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 10px;
  padding: 0.85rem 1.25rem;
  margin-bottom: 2.5rem;
}

.scale-title {
  font-size: 0.8rem;
  color: #475569;
  margin: 0 0 0.5rem;
  font-weight: 700;
}

.scale-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1.5rem;
  font-size: 0.8rem;
  color: #334155;
}

/* Signatures */
.signatures-grid {
  display: flex;
  justify-content: space-between;
  margin-top: 4rem;
  padding: 0 1.5rem;
}

.sig-block {
  text-align: center;
  width: 180px;
}

.sig-line {
  border-top: 1px solid #0f172a;
  margin-bottom: 0.5rem;
}

.sig-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: #0f172a;
}

/* Toast */
.toast-notification {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  background: #10b981;
  color: white;
  padding: 0.75rem 1.25rem;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  z-index: 9999;
  font-weight: 500;
}

@media print {
  body {
    background: transparent !important;
  }
  .no-print {
    display: none !important;
  }
  .page-wrapper {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .marksheet-sheet {
    border: none !important;
    box-shadow: none !important;
    padding: 1.5rem !important;
  }
  .scoreboard-grid {
    gap: 0.5rem !important;
  }
  .signatures-grid {
    margin-top: 3.5rem !important;
  }
}

@media (max-width: 768px) {
  .scoreboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .signatures-grid {
    flex-direction: column;
    align-items: center;
    gap: 2.5rem;
  }
}
</style>
