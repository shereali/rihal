<template>
  <div class="reports-page">
    <div class="page-header">
      <h1>রিপোর্ট ও এক্সপোর্ট</h1>
      <p class="text-muted">হাজিরা ও ফলাফলের রিপোর্ট তৈরি করুন</p>
    </div>

    <div class="tabs">
      <button class="tab" :class="{ active: tab === 'attendance' }" @click="tab = 'attendance'; clearData()">হাজিরা রিপোর্ট</button>
      <button class="tab" :class="{ active: tab === 'results' }" @click="tab = 'results'; clearData()">ফলাফল রিপোর্ট</button>
    </div>

    <!-- Attendance filters -->
    <div v-if="tab === 'attendance'" class="filters card">
      <div class="form-row">
        <div class="form-group">
          <label>শ্রেণি *</label>
          <select v-model="classId" :disabled="loading">
            <option value="">নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>শুরুর তারিখ</label>
          <input v-model="from" type="date" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>শেষ তারিখ</label>
          <input v-model="to" type="date" :disabled="loading" />
        </div>
        <div class="form-group">
          <button class="btn btn-primary btn-sm" :disabled="!classId || loading" @click="loadAttendance">রিপোর্ট দেখুন</button>
        </div>
      </div>
    </div>

    <!-- Results filters -->
    <div v-else class="filters card">
      <div class="form-row">
        <div class="form-group">
          <label>পরীক্ষা *</label>
          <select v-model="examId" :disabled="loading">
            <option value="">নির্বাচন করুন</option>
            <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name_bn }}</option>
          </select>
        </div>
        <div class="form-group">
          <button class="btn btn-primary btn-sm" :disabled="!examId || loading" @click="loadResults">রিপোর্ট দেখুন</button>
        </div>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>

    <!-- Attendance report -->
    <div v-if="tab === 'attendance' && attReport" class="card no-print">
      <div class="card-header">
        <h3>হাজিরা: {{ attReport.class.name_bn }} ({{ attReport.from }} → {{ attReport.to }})</h3>
        <div class="header-actions">
          <button class="btn btn-outline btn-sm" @click="exportCsv('attendance')">CSV ডাউনলোড</button>
          <button class="btn btn-outline btn-sm" @click="printReport">প্রিন্ট</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>নাম</th>
                <th>ভর্তি নং</th>
                <th v-for="d in attReport.dates" :key="d" class="date-col">{{ d.slice(5) }}</th>
                <th>উপ.</th><th>অনু.</th><th>দেরি</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in attReport.rows" :key="r.student_id">
                <td>{{ r.name_bn }}</td>
                <td>{{ r.admission_number || '-' }}</td>
                <td v-for="d in attReport.dates" :key="d" :class="['cell', r.by_date[d]]">
                  {{ statusGlyph(r.by_date[d]) }}
                </td>
                <td>{{ r.summary.present }}</td>
                <td>{{ r.summary.absent }}</td>
                <td>{{ r.summary.late }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Results report -->
    <div v-if="tab === 'results' && resReport" class="card no-print">
      <div class="card-header">
        <h3>ফলাফল: {{ resReport.exam.name_bn }}</h3>
        <div class="header-actions">
          <button class="btn btn-outline btn-sm" @click="exportCsv('results')">CSV ডাউনলোড</button>
          <button class="btn btn-outline btn-sm" @click="printReport">প্রিন্ট</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr><th>নাম</th><th>ভর্তি নং</th><th>প্রাপ্ত</th><th>মোট</th><th>শতকরা</th><th>গ্রেড</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in resReport.rows" :key="r.student_id">
                <td>{{ r.name_bn }}</td>
                <td>{{ r.admission_number || '-' }}</td>
                <td>{{ r.total_marks }}</td>
                <td>{{ r.total_max }}</td>
                <td>{{ r.percentage }}%</td>
                <td><span class="badge badge-success">{{ r.grade || '-' }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const tab = ref<'attendance' | 'results'>('attendance')
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
    const c = await api.get('/academic/classes'); classes.value = c.data?.data || []
    const e = await api.get('/exams'); exams.value = e.data?.data?.data || e.data?.data || []
  } catch {}
}
function clearData() { attReport.value = null; resReport.value = null; error.value = '' }
function statusGlyph(s: string) {
  if (s === 'present') return '✓'
  if (s === 'absent') return '✗'
  if (s === 'late') return '~'
  return '-'
}

async function loadAttendance() {
  error.value = ''; attReport.value = null; loading.value = true
  try {
    const r = await api.get(`/reports/attendance?class_id=${classId.value}&from=${from.value}&to=${to.value}`)
    attReport.value = r.data
  } catch (e: any) { error.value = e?.response?.data?.message ?? 'রিপোর্ট লোড করা যায়নি' }
  finally { loading.value = false }
}
async function loadResults() {
  error.value = ''; resReport.value = null; loading.value = true
  try {
    const r = await api.get(`/reports/results?exam_id=${examId.value}`)
    resReport.value = r.data
  } catch (e: any) { error.value = e?.response?.data?.message ?? 'রিপোর্ট লোড করা যায়নি' }
  finally { loading.value = false }
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
    a.download = which === 'attendance' ? `attendance_${from.value}_${to.value}.csv` : `results_${examId.value}.csv`
    document.body.appendChild(a); a.click(); a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e: any) {
    error.value = 'CSV ডাউনলোড করা যায়নি'
  }
}
function printReport() { window.print() }

onMounted(loadMeta)
</script>

<style scoped>
.reports-page { padding: 1.5rem; }
.page-header { margin-bottom: 1rem; }
.page-header h1 { font-family: 'Noto Sans Bengali', sans-serif; margin: 0; }
.tabs { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
.tab { padding: 0.55rem 1rem; border: 1px solid var(--color-border); background: var(--color-bg); border-radius: 8px; cursor: pointer; font-family: 'Noto Sans Bengali', sans-serif; }
.tab.active { background: var(--color-primary); color: var(--color-text-on-primary); border-color: var(--color-primary); }
.filters { padding: 1rem 1.25rem; margin-bottom: 1rem; background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.form-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group select, .form-group input { padding: 0.6rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg); }
.btn { padding: 0.55rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; }
.btn-sm { padding: 0.45rem 0.85rem; font-size: 0.85rem; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.header-actions { display: flex; gap: 0.5rem; }
.card-body { padding: 1.25rem; }
.table-responsive { overflow-x: auto; }
.date-col { font-size: 0.72rem; text-align: center; }
.cell { text-align: center; }
.cell.present { background: #e8f5e9; color: var(--color-success); }
.cell.absent { background: #fce4e4; color: var(--color-error); }
.cell.late { background: #fff4e0; color: var(--color-warning); }
.badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.78rem; }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.alert { padding: 0.7rem 1rem; border-radius: 8px; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: #fce4e4; color: var(--color-error); }
.loading-state { padding: 3rem; text-align: center; font-family: 'Noto Sans Bengali', sans-serif; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
@media print { .no-print .card-header, .tabs, .filters { display: none; } }
</style>
