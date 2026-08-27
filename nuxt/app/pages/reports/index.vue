<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <span class="eyebrow">বিশ্লেষণ ও উপাত্ত</span>
        <h1>রিপোর্ট ও এক্সপোর্ট কেন্দ্র</h1>
        <p class="page-subtitle">হাজিরা, পরীক্ষার ফলাফল, আর্থিক হিসাব ও স্পনসরশিপ রিপোর্ট বিশ্লেষণ এবং ডাউনলোড</p>
      </div>
      <div class="header-actions">
        <button v-if="(tab === 'attendance' && attReport) || (tab === 'results' && resReport)" class="btn btn-outline" @click="printReport">
          <icon name="printer" /> প্রিন্ট করুন
        </button>
        <button v-if="tab === 'attendance' && attReport" class="btn btn-primary" @click="exportCsv('attendance')">
          <icon name="download" /> CSV ডাউনলোড
        </button>
        <button v-if="tab === 'results' && resReport" class="btn btn-primary" @click="exportCsv('results')">
          <icon name="download" /> CSV ডাউনলোড
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="report-tabs no-print">
      <button class="report-tab-btn" :class="{ active: tab === 'attendance' }" @click="tab = 'attendance'; clearData()">
        <icon name="calendar" /> হাজিরা রিপোর্ট
      </button>
      <button class="report-tab-btn" :class="{ active: tab === 'results' }" @click="tab = 'results'; clearData()">
        <icon name="document-text" /> ফলাফল রিপোর্ট
      </button>
      <button class="report-tab-btn" :class="{ active: tab === 'financial' }" @click="tab = 'financial'; clearData()">
        <icon name="money" /> আর্থিক ঋণ রিপোর্ট
      </button>
      <button class="report-tab-btn" :class="{ active: tab === 'orphans' }" @click="tab = 'orphans'; clearData()">
        <icon name="heart" /> এতিম স্পনসরশিপ রিপোর্ট
      </button>
    </div>

    <!-- Attendance Filters -->
    <div v-if="tab === 'attendance'" class="toolbar card no-print">
      <div class="filter-item">
        <label class="filter-label">শ্রেণি নির্বাচন *</label>
        <select v-model="classId" class="form-select" :disabled="loading">
          <option value="">শ্রেণি নির্বাচন করুন</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn || c.name_en }}</option>
        </select>
      </div>
      <div class="filter-item">
        <label class="filter-label">শুরুর তারিখ</label>
        <input v-model="from" type="date" class="form-input" :disabled="loading" />
      </div>
      <div class="filter-item">
        <label class="filter-label">শেষ তারিখ</label>
        <input v-model="to" type="date" class="form-input" :disabled="loading" />
      </div>
      <div class="filter-item align-end">
        <button class="btn btn-primary" :disabled="!classId || loading" @click="loadAttendance">
          <icon name="search" /> রিপোর্ট তৈরি করুন
        </button>
      </div>
    </div>

    <!-- Results Filters -->
    <div v-else-if="tab === 'results'" class="toolbar card no-print">
      <div class="filter-item flex-2">
        <label class="filter-label">পরীক্ষা নির্বাচন করুন *</label>
        <select v-model="examId" class="form-select" :disabled="loading">
          <option value="">পরীক্ষা নির্বাচন করুন</option>
          <option v-for="e in exams" :key="e.id" :value="e.id">
            {{ e.name_bn || e.name_en || e.title_bn || e.title }}
          </option>
        </select>
      </div>
      <div class="filter-item align-end">
        <button class="btn btn-primary" :disabled="!examId || loading" @click="loadResults">
          <icon name="search" /> রিপোর্ট তৈরি করুন
        </button>
      </div>
    </div>

    <!-- Financial Reports Box -->
    <div v-else-if="tab === 'financial'" class="card report-box-card no-print">
      <div class="report-box-header">
        <div class="report-box-icon amber"><icon name="money" /></div>
        <div class="report-box-info">
          <h3>কর্জে হাসানা ও ঋণ বিবরণী রিপোর্ট</h3>
          <p>সকল সক্রিয়, পরিশোধিত ও চলমান ঋণ হিসাবের পূর্ণাঙ্গ তালিকা এবং বিবরণ এক্সেল/সিএসভি ফরম্যাটে ডাউনলোড করুন</p>
        </div>
      </div>
      <div class="report-box-action">
        <button class="btn btn-primary" @click="downloadFinancial('loans')">
          <icon name="download" /> ঋণ রিপোর্ট (CSV) ডাউনলোড
        </button>
      </div>
    </div>

    <!-- Orphans Reports Box -->
    <div v-else-if="tab === 'orphans'" class="card report-box-card no-print">
      <div class="report-box-header">
        <div class="report-box-icon purple"><icon name="heart" /></div>
        <div class="report-box-info">
          <h3>এতিম শিক্ষার্থী ও স্পনসরশিপ রিপোর্ট</h3>
          <p>নিবন্ধিত এতিম শিক্ষার্থী, মাসিক সহায়তা অঙ্গীকার, অভিভাবক তথ্য ও স্পনসর তালিকা এক্সপোর্ট করুন</p>
        </div>
      </div>
      <div class="report-box-action">
        <button class="btn btn-primary" @click="downloadFinancial('orphans')">
          <icon name="download" /> এতিম স্পনসর রিপোর্ট (CSV) ডাউনলোড
        </button>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="alert alert-error no-print">{{ error }}</div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state card no-print">
      <div class="spinner" />
      <p>রিপোর্ট প্রস্তুত করা হচ্ছে...</p>
    </div>

    <!-- ================= ATTENDANCE REPORT VIEW ================= -->
    <div v-if="tab === 'attendance' && attReport" class="report-content-container">
      <!-- Attendance Stats KPI -->
      <div class="stats-grid no-print">
        <div class="stat-card">
          <div class="stat-icon-wrap blue"><icon name="users" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ attReport.rows.length.toLocaleString('bn-BD') }}</span>
            <span class="stat-label">মোট শিক্ষার্থী</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ attendanceSummary.avgRate }}%</span>
            <span class="stat-label">গড় উপস্থিতি হার</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap green"><icon name="user-check" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ attendanceSummary.totalPresent.toLocaleString('bn-BD') }}</span>
            <span class="stat-label">মোট উপস্থিত (দিন×জন)</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap red"><icon name="close-circle" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ attendanceSummary.totalAbsent.toLocaleString('bn-BD') }}</span>
            <span class="stat-label">মোট অনুপস্থিত</span>
          </div>
        </div>
      </div>

      <!-- Report Card -->
      <div class="card report-table-card">
        <div class="report-header-banner">
          <div>
            <h2>হাজিরা রিপোর্ট: {{ attReport.class.name_bn || attReport.class.name_en }}</h2>
            <p class="report-date-range">
              সময়সীমা: <strong>{{ formatDate(attReport.from) }}</strong> থেকে <strong>{{ formatDate(attReport.to) }}</strong> (মোট {{ attReport.dates.length.toLocaleString('bn-BD') }} দিন)
            </p>
          </div>
          <div class="report-legend no-print">
            <span class="legend-item"><span class="legend-dot present" /> উপস্থিত (✓)</span>
            <span class="legend-item"><span class="legend-dot absent" /> অনুপস্থিত (✗)</span>
            <span class="legend-item"><span class="legend-dot late" /> বিলম্ব (~)</span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="premium-table attendance-matrix-table">
            <thead>
              <tr>
                <th class="sticky-col">শিক্ষার্থীর নাম</th>
                <th>ভর্তি নং</th>
                <th v-for="d in attReport.dates" :key="d" class="date-header-cell">
                  {{ formatDateShort(d) }}
                </th>
                <th class="summary-header text-center">উপস্থিত</th>
                <th class="summary-header text-center">অনুপস্থিত</th>
                <th class="summary-header text-center">বিলম্ব</th>
                <th class="summary-header text-center">হার (%)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in attReport.rows" :key="r.student_id">
                <td class="sticky-col student-name-cell">
                  <div class="user-cell">
                    <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(r.name_bn || r.name_en) }">
                      {{ (r.name_bn || r.name_en || 'শ').charAt(0) }}
                    </div>
                    <strong>{{ r.name_bn || r.name_en }}</strong>
                  </div>
                </td>
                <td class="mono-font">{{ r.admission_number || '—' }}</td>
                <td v-for="d in attReport.dates" :key="d" class="matrix-cell" :class="r.by_date[d]">
                  <span class="status-indicator-glyph">{{ statusGlyph(r.by_date[d]) }}</span>
                </td>
                <td class="text-center highlight-success font-bold">{{ r.summary.present.toLocaleString('bn-BD') }}</td>
                <td class="text-center highlight-danger font-bold">{{ r.summary.absent.toLocaleString('bn-BD') }}</td>
                <td class="text-center highlight-warning">{{ r.summary.late.toLocaleString('bn-BD') }}</td>
                <td class="text-center">
                  <span class="rate-badge" :class="getRateBadgeClass(calculateStudentRate(r))">
                    {{ calculateStudentRate(r) }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= RESULTS REPORT VIEW ================= -->
    <div v-else-if="tab === 'results' && resReport" class="report-content-container">
      <!-- Results Stats KPI -->
      <div class="stats-grid no-print">
        <div class="stat-card">
          <div class="stat-icon-wrap blue"><icon name="users" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ resReport.rows.length.toLocaleString('bn-BD') }}</span>
            <span class="stat-label">মোট পরীক্ষার্থী</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ resultsSummary.passRate }}%</span>
            <span class="stat-label">পাসের হার (≥ ৪০%)</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap amber"><icon name="award" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ resultsSummary.highestMarks }}%</span>
            <span class="stat-label">সর্বোচ্চ প্রাপ্ত শতকরা</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap purple"><icon name="chart-bar" /></div>
          <div class="stat-content">
            <span class="stat-value">{{ resultsSummary.avgPercentage }}%</span>
            <span class="stat-label">গড় শতকরা নম্বর</span>
          </div>
        </div>
      </div>

      <!-- Results Table Card -->
      <div class="card report-table-card">
        <div class="report-header-banner">
          <div>
            <h2>পরীক্ষার ফলাফল: {{ resReport.exam.name_bn || resReport.exam.name_en }}</h2>
            <p class="report-date-range">মেধাতালিকা ও বিষয়ভিত্তিক নম্বর মূল্যায়ন বিবরণী</p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="premium-table">
            <thead>
              <tr>
                <th style="width: 60px;">মেধাক্রম</th>
                <th>শিক্ষার্থীর নাম</th>
                <th>ভর্তি নং</th>
                <th class="text-center">প্রাপ্ত নম্বর</th>
                <th class="text-center">মোট পূর্ণমান</th>
                <th class="text-center">শতকরা (%)</th>
                <th class="text-center">লেটার গ্রেড</th>
                <th class="text-center">অবস্থা</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, index) in sortedResults" :key="r.student_id">
                <td class="text-center font-bold">
                  <span class="rank-badge" :class="index < 3 ? 'rank-top' : ''">
                    {{ (index + 1).toLocaleString('bn-BD') }}
                  </span>
                </td>
                <td>
                  <div class="user-cell">
                    <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(r.name_bn || r.name_en) }">
                      {{ (r.name_bn || r.name_en || 'শ').charAt(0) }}
                    </div>
                    <strong>{{ r.name_bn || r.name_en }}</strong>
                  </div>
                </td>
                <td class="mono-font">{{ r.admission_number || '—' }}</td>
                <td class="text-center font-bold">{{ (r.total_marks || 0).toLocaleString('bn-BD') }}</td>
                <td class="text-center">{{ (r.total_max || 0).toLocaleString('bn-BD') }}</td>
                <td class="text-center">
                  <div class="percentage-cell">
                    <div class="progress-bar-wrap">
                      <div class="progress-bar-fill" :style="{ width: Math.min(100, r.percentage || 0) + '%' }" />
                    </div>
                    <span>{{ (r.percentage || 0) }}%</span>
                  </div>
                </td>
                <td class="text-center">
                  <span class="grade-pill" :class="getGradeClass(r.grade)">
                    {{ r.grade || '—' }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="status-pill" :class="(r.percentage || 0) >= 40 ? 'badge-approved' : 'badge-rejected'">
                    <span class="status-dot" />
                    {{ (r.percentage || 0) >= 40 ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const tab = ref<'attendance' | 'results' | 'financial' | 'orphans'>('attendance')
const classes = ref<any[]>([])
const exams = ref<any[]>([])
const classId = ref('' as string | number)
const examId = ref('' as string | number)
const from = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10))
const to = ref(new Date().toISOString().slice(0, 10))
const loading = ref(false)
const error = ref('')
const attReport = ref<any>(null)
const resReport = ref<any>(null)

async function loadMeta() {
  try {
    const [cRes, eRes] = await Promise.all([
      api.get('/academic/classes').catch(() => ({ data: { data: [] } })),
      api.get('/exams').catch(() => ({ data: { data: [] } })),
    ])
    classes.value = cRes.data?.data?.data || cRes.data?.data || []
    exams.value = eRes.data?.data?.data || eRes.data?.data || []
  } catch (e) {
    console.error(e)
  }
}

function clearData() {
  attReport.value = null
  resReport.value = null
  error.value = ''
}

function statusGlyph(s: string) {
  if (s === 'present') return '✓'
  if (s === 'absent') return '✗'
  if (s === 'late') return '~'
  return '—'
}

async function loadAttendance() {
  error.value = ''
  attReport.value = null
  loading.value = true
  try {
    const r = await api.get(`/reports/attendance?class_id=${classId.value}&from=${from.value}&to=${to.value}`)
    attReport.value = r.data?.data || r.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'হাজিরা রিপোর্ট লোড করা যায়নি'
  } finally {
    loading.value = false
  }
}

async function loadResults() {
  error.value = ''
  resReport.value = null
  loading.value = true
  try {
    const r = await api.get(`/reports/results?exam_id=${examId.value}`)
    resReport.value = r.data?.data || r.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ফলাফল রিপোর্ট লোড করা যায়নি'
  } finally {
    loading.value = false
  }
}

async function exportCsv(which: 'attendance' | 'results') {
  error.value = ''
  const base = which === 'attendance'
    ? `/reports/attendance/export?class_id=${classId.value}&from=${from.value}&to=${to.value}`
    : `/reports/results/export?exam_id=${examId.value}`
  try {
    const res = await api.get(base, { responseType: 'blob' })
    const blob = res.data as Blob
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = which === 'attendance' ? `attendance_${from.value}_${to.value}.csv` : `results_exam_${examId.value}.csv`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e: any) {
    error.value = 'CSV ফাইল ডাউনলোড করা যায়নি'
  }
}

async function downloadFinancial(type: 'loans' | 'orphans') {
  error.value = ''
  const url = type === 'loans' ? '/reports/loans.csv' : '/reports/orphans.csv'
  try {
    const res = await api.get(url, { responseType: 'blob' })
    const blob = res.data as Blob
    const blobUrl = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = blobUrl
    a.download = `${type}_report_${new Date().toISOString().slice(0, 10)}.csv`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(blobUrl)
  } catch (e) {
    error.value = 'রিপোর্ট ফাইল ডাউনলোড করা যায়নি'
  }
}

function printReport() {
  window.print()
}

// Attendance summary statistics
const attendanceSummary = computed(() => {
  if (!attReport.value || !attReport.value.rows) return { avgRate: 0, totalPresent: 0, totalAbsent: 0, totalLate: 0 }
  let totalPresent = 0
  let totalAbsent = 0
  let totalLate = 0
  let totalPossible = 0

  const totalDates = attReport.value.dates?.length || 1

  attReport.value.rows.forEach((r: any) => {
    totalPresent += r.summary?.present || 0
    totalAbsent += r.summary?.absent || 0
    totalLate += r.summary?.late || 0
    totalPossible += totalDates
  })

  const avgRate = totalPossible > 0 ? Math.round((totalPresent / totalPossible) * 100) : 0
  return { avgRate, totalPresent, totalAbsent, totalLate }
})

function calculateStudentRate(r: any) {
  const datesCount = attReport.value?.dates?.length || 1
  const pres = r.summary?.present || 0
  return Math.round((pres / datesCount) * 100)
}

function getRateBadgeClass(rate: number) {
  if (rate >= 80) return 'badge-green'
  if (rate >= 60) return 'badge-amber'
  return 'badge-red'
}

// Results summary statistics
const sortedResults = computed(() => {
  if (!resReport.value || !resReport.value.rows) return []
  return [...resReport.value.rows].sort((a: any, b: any) => (b.percentage || 0) - (a.percentage || 0))
})

const resultsSummary = computed(() => {
  const rows = sortedResults.value
  if (!rows.length) return { passRate: 0, highestMarks: 0, avgPercentage: 0 }

  const passed = rows.filter((r: any) => (r.percentage || 0) >= 40).length
  const passRate = Math.round((passed / rows.length) * 100)
  const highestMarks = rows[0]?.percentage || 0
  const totalPct = rows.reduce((acc: number, r: any) => acc + (Number(r.percentage) || 0), 0)
  const avgPercentage = Math.round(totalPct / rows.length)

  return { passRate, highestMarks, avgPercentage }
})

function getGradeClass(grade: string) {
  if (!grade) return ''
  const g = grade.toUpperCase()
  if (g.includes('A+') || g.includes('A')) return 'grade-a'
  if (g.includes('B')) return 'grade-b'
  if (g.includes('C') || g.includes('D')) return 'grade-c'
  return 'grade-f'
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

function formatDate(dStr: string) {
  if (!dStr) return '—'
  try {
    return new Date(dStr).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch { return dStr }
}

function formatDateShort(dStr: string) {
  if (!dStr) return ''
  try {
    const d = new Date(dStr)
    return d.getDate().toLocaleString('bn-BD') + '/' + (d.getMonth() + 1).toLocaleString('bn-BD')
  } catch { return dStr }
}

onMounted(loadMeta)
</script>

<style scoped>
.page-wrapper {
  max-width: 1320px;
  margin: 0 auto;
  padding: 1.75rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-primary);
  letter-spacing: 0.08em;
}

.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0.2rem 0 0.35rem;
  color: var(--color-text);
}

.page-subtitle {
  color: var(--color-text-light);
  font-size: 0.88rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

/* Report Navigation Tabs */
.report-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
  padding-bottom: 0.5rem;
  overflow-x: auto;
}

.report-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1.15rem;
  border-radius: 10px;
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text-light);
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.report-tab-btn:hover {
  background: rgba(20, 80, 50, 0.04);
  color: var(--color-text);
}

.report-tab-btn.active {
  background: rgba(20, 80, 50, 0.09);
  color: var(--color-primary);
  border-color: rgba(20, 80, 50, 0.18);
  font-weight: 700;
}

/* Filter Toolbar */
.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  min-width: 180px;
}

.filter-item.flex-2 {
  flex: 2;
}

.filter-item.align-end {
  align-self: flex-end;
}

.filter-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-light);
}

/* Report Boxes (Loans & Orphans) */
.report-box-card {
  padding: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  border-radius: 14px;
}

.report-box-header {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.report-box-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  flex-shrink: 0;
}

.report-box-icon.amber {
  background: #fef3c7;
  color: #d97706;
}

.report-box-icon.purple {
  background: #ede9fe;
  color: #7c3aed;
}

.report-box-info h3 {
  font-size: 1.2rem;
  font-weight: 800;
  margin: 0 0 0.3rem;
}

.report-box-info p {
  color: var(--color-text-light);
  font-size: 0.88rem;
  margin: 0;
  max-width: 600px;
}

/* Report Table Card */
.report-table-card {
  border-radius: 14px;
  overflow: hidden;
  margin-top: 1.5rem;
}

.report-header-banner {
  padding: 1.25rem 1.5rem;
  background: rgba(0, 0, 0, 0.015);
  border-bottom: 1px solid var(--color-border-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.report-header-banner h2 {
  font-size: 1.2rem;
  font-weight: 800;
  margin: 0 0 0.25rem;
}

.report-date-range {
  font-size: 0.85rem;
  color: var(--color-text-light);
  margin: 0;
}

.report-legend {
  display: flex;
  gap: 1rem;
  font-size: 0.82rem;
  color: var(--color-text-light);
}

.legend-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

.legend-dot.present { background: #10b981; }
.legend-dot.absent { background: #ef4444; }
.legend-dot.late { background: #f59e0b; }

/* Matrix Table Specifics */
.attendance-matrix-table th,
.attendance-matrix-table td {
  padding: 0.65rem 0.6rem;
  font-size: 0.84rem;
}

.sticky-col {
  position: sticky;
  left: 0;
  background: var(--color-bg);
  z-index: 2;
  min-width: 170px;
}

.student-name-cell {
  background: var(--color-bg);
}

.date-header-cell {
  font-size: 0.72rem;
  text-align: center;
  min-width: 38px;
  padding: 0.5rem 0.2rem !important;
}

.matrix-cell {
  text-align: center;
  font-weight: 700;
  border-left: 1px dashed var(--color-border-light);
}

.matrix-cell.present {
  background: rgba(16, 185, 129, 0.08);
  color: #059669;
}

.matrix-cell.absent {
  background: rgba(239, 68, 68, 0.08);
  color: #dc2626;
}

.matrix-cell.late {
  background: rgba(245, 158, 11, 0.08);
  color: #d97706;
}

.status-indicator-glyph {
  font-size: 0.9rem;
}

.summary-header {
  border-left: 1px solid var(--color-border);
}

.rate-badge {
  display: inline-block;
  padding: 0.15rem 0.45rem;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 700;
}

.badge-green { background: #dcfce7; color: #15803d; }
.badge-amber { background: #fef3c7; color: #b45309; }
.badge-red { background: #fee2e2; color: #b91c1c; }

/* Results Table Specifics */
.rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.05);
  font-size: 0.85rem;
}

.rank-top {
  background: #fef3c7;
  color: #b45309;
  font-weight: 800;
  box-shadow: 0 2px 6px rgba(180, 83, 9, 0.2);
}

.percentage-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
}

.progress-bar-wrap {
  width: 60px;
  height: 6px;
  background: rgba(0, 0, 0, 0.06);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981 0%, #145032 100%);
  border-radius: 3px;
}

.grade-pill {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 800;
}

.grade-a { background: #dcfce7; color: #15803d; }
.grade-b { background: #e0f2fe; color: #0369a1; }
.grade-c { background: #fef3c7; color: #b45309; }
.grade-f { background: #fee2e2; color: #b91c1c; }

.highlight-success { color: #059669; }
.highlight-danger { color: #dc2626; }
.highlight-warning { color: #d97706; }
.font-bold { font-weight: 700; }
.mono-font { font-family: monospace; font-size: 0.82rem; }

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.user-avatar-initials {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  color: #fff;
  font-size: 0.82rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn {
  padding: 0.6rem 1.15rem;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #145032 0%, #1a6b43 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35);
}

.btn-outline {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.empty-state {
  padding: 3rem 1.5rem;
  text-align: center;
}

.alert-error {
  padding: 0.75rem 1.25rem;
  background: #fee2e2;
  color: #b91c1c;
  border-radius: 8px;
  margin-top: 1rem;
}

/* Print Optimization */
@media print {
  .no-print {
    display: none !important;
  }
  .page-wrapper {
    padding: 0;
    max-width: 100%;
  }
  .report-table-card {
    box-shadow: none;
    border: 1px solid #ddd;
  }
  .sticky-col {
    position: static;
  }
}
</style>
