<template>
  <div class="results-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">একাডেমিক রিপোর্ট</span>
        <h1>ফলাফল তালিকা</h1>
        <p>সকল পরীক্ষার ফলাফল, জিপিএ ও শতকরা হার এক নজরে</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline btn-sm" type="button" @click="load"><icon name="refresh" /> রিফ্রেশ</button>
        <NuxtLink to="/exams" class="btn btn-ghost btn-sm"><icon name="exam" /> পরীক্ষায় যান</NuxtLink>
        <NuxtLink to="/results/gpa" class="btn btn-ghost btn-sm"><icon name="chart" /> জিপিএ হিসাব</NuxtLink>
      </div>
    </div>

    <div class="stats-row" v-if="stats">
      <div class="stat-card green">
        <div class="stat-icon"><icon name="students" /></div>
        <div class="stat-content"><span class="stat-value">{{ stats.total }}</span><span class="stat-label">মোট ফলাফল</span></div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon"><icon name="check" /></div>
        <div class="stat-content"><span class="stat-value">{{ stats.published }}</span><span class="stat-label">প্রকাশিত</span></div>
      </div>
      <div class="stat-card purple">
        <div class="stat-icon"><icon name="clock" /></div>
        <div class="stat-content"><span class="stat-value">{{ stats.unpublished }}</span><span class="stat-label">অপ্রকাশিত</span></div>
      </div>
      <div class="stat-card gold">
        <div class="stat-icon"><icon name="trophy" /></div>
        <div class="stat-content"><span class="stat-value">{{ stats.avgGpa ?? '-' }}</span><span class="stat-label">গড় জিপিএ</span></div>
      </div>
    </div>

    <div class="toolbar card">
      <div class="search-box"><icon name="search" /><input v-model="filters.search" placeholder="শিক্ষার্থী বা পরীক্ষা খুঁজুন..." @keyup.enter="load" /></div>
      <select v-model="filters.exam_id" class="form-control compact" @change="load">
        <option value="">সব পরীক্ষা</option>
        <option v-for="e in exams" :key="e.id" :value="String(e.id)">{{ e.name_bn || e.name_en || 'পরীক্ষা ' + e.id }}</option>
      </select>
      <select v-model="filters.is_published" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="true">প্রকাশিত</option>
        <option value="false">অপ্রকাশিত</option>
      </select>
      <button class="btn btn-ghost btn-sm" type="button" @click="clearFilters"><icon name="close" /> পরিষ্কার</button>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>ফলাফল লোড হচ্ছে...</p></div>
    <div v-else-if="!results.length" class="empty-card">
      <div class="empty-icon"><icon name="document" /></div>
      <h3>এখনও কোনো ফলাফল নেই</h3>
      <p>প্রথমে পরীক্ষার ফলাফল লেখা শুরু করুন</p>
      <NuxtLink to="/exams" class="btn btn-primary"><icon name="exam" /> পরীক্ষা তৈরি করুন</NuxtLink>
    </div>
    <div v-else class="results-table-wrapper">
      <table class="results-table">
        <thead>
          <tr>
            <th>#</th>
            <th>ছাত্র</th>
            <th>পরীক্ষা</th>
            <th>শ্রেণি</th>
            <th>জিপিএ</th>
            <th>শতকরা</th>
            <th>গ্রেড</th>
            <th>অবস্থা</th>
            <th>প্রকাশিত</th>
            <th>কর্ম</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r, i) in results" :key="r.id">
            <td class="text-muted">{{ (page - 1) * perPage + i + 1 }}</td>
            <td>
              <div class="student-cell">
                <span class="student-name">{{ r.student?.name_bn || 'ছাত্র ' + r.student_id }}</span>
                <small class="text-muted">{{ r.student?.name_en }}</small>
              </div>
            </td>
            <td>
              <NuxtLink v-if="r.exam?.id" :to="`/exams/${r.exam.id}`" class="exam-link">{{ r.exam?.name_bn || 'পরীক্ষা ' + r.exam_id }}</NuxtLink>
              <span v-else class="text-muted">পরীক্ষা {{ r.exam_id }}</span>
            </td>
            <td class="text-muted">{{ getClassLabel(r) }}</td>
            <td><span class="gpa-value">{{ r.gpa ?? '-' }}</span></td>
            <td><span class="percentage-value" :class="rowStatus(r)">{{ r.percentage ?? '-' }}</span></td>
            <td><span class="badge" :class="getGradeBadge(r.grade)">{{ r.grade || '-' }}</span></td>
            <td><span class="status-badge" :class="statusClass(r)">{{ statusLabel(r) }}</span></td>
            <td class="text-center"><span class="published-indicator" :class="r.is_published ? 'published' : 'unpublished'">{{ r.is_published ? 'হ্যাঁ' : 'না' }}</span></td>
            <td class="actions-cell">
              <NuxtLink :to="`/exam-results/${r.id}`" class="btn btn-ghost btn-sm" title="বিস্তারিত"><icon name="eye" /></NuxtLink>
              <button v-if="r.is_published" class="btn btn-outline-danger btn-sm" @click="togglePublish(r.id, false)" title="অপ্রকাশ করুন"><icon name="eye-off" /></button>
              <button v-else class="btn btn-outline btn-sm" @click="togglePublish(r.id, true)" title="প্রকাশ করুন"><icon name="check" /></button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="pagination" v-if="lastPage > 1">
        <button class="btn btn-outline btn-sm" :disabled="page <= 1" @click="goPage(page - 1)"><icon name="arrow-left" /> আগে</button>
        <span class="page-info">পৃষ্ঠা {{ page }} / {{ lastPage }}</span>
        <button class="btn btn-outline btn-sm" :disabled="page >= lastPage" @click="goPage(page + 1)">পরে <icon name="arrow-right" /></button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const results = ref<any[]>([])
const exams = ref<any[]>([])
const stats = ref({ total: 0, published: 0, unpublished: 0, avgGpa: null })
const loading = ref(true)
const error = ref('')
const page = ref(1)
const perPage = ref(15)
const lastPage = ref(1)
const filters = ref({ search: '', exam_id: '', is_published: '' })

async function load() {
  loading.value = true
  error.value = ''
  try {
    const q = new URLSearchParams()
    if (filters.value.search) q.set('search', filters.value.search)
    if (filters.value.exam_id) q.set('exam_id', filters.value.exam_id)
    if (filters.value.is_published !== '') q.set('is_published', filters.value.is_published === 'true' ? '1' : '0')
    q.set('page', String(page.value))
    q.set('per_page', String(perPage.value))
    const r = await api.get(`/exam-results?${q.toString()}`)
    const d = r.data?.data || {}
    results.value = Array.isArray(d) ? d : (d?.data || [])
    lastPage.value = Number(d?.last_page || 1)
    stats.value = {
      total: Number(d?.total || 0),
      published: results.value.filter(r => r.is_published).length,
      unpublished: results.value.filter(r => !r.is_published).length,
      avgGpa: results.value.length ? (results.value.reduce((s: number, r: any) => s + (Number(r.gpa) || 0), 0) / results.value.length).toFixed(2) : null,
    }
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'ফলাফল লোড করা যায়নি'
    results.value = []
  } finally {
    loading.value = false
  }
}

async function loadExams() {
  try {
    const r = await api.get('/exams')
    exams.value = r.data?.data?.data || r.data?.data || []
  } catch { exams.value = [] }
}

function clearFilters() {
  filters.value = { search: '', exam_id: '', is_published: '' }
  page.value = 1
  load()
}

function goPage(p: number) {
  if (p < 1 || p > lastPage.value) return
  page.value = p
  load()
}

async function togglePublish(id: number, published: boolean) {
  try {
    await api.patch(`/exam-results/${id}/${published ? 'publish' : 'unpublish'}`)
    load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'প্রকাশ/অপ্রকাশ করা যায়নি'
  }
}

function statusClass(r: any) {
  if (r.pass_fail_status === 'passed') return 'passed'
  if (r.pass_fail_status === 'failed') return 'failed'
  return ''
}
function statusLabel(r: any) {
  if (r.pass_fail_status === 'passed') return 'পাস'
  if (r.pass_fail_status === 'failed') return 'ফেল'
  return r.pass_fail_status || 'মুলতবি'
}
function rowStatus(r: any) {
  const p = Number(r.percentage)
  if (p >= 33) return 'pass'
  if (p !== undefined && p !== null) return 'fail'
  return ''
}
function getGradeBadge(grade: string) {
  if (!grade) return ''
  const g = grade.toUpperCase()
  if (['A+','A','A-'].includes(g)) return 'grade-a'
  if (['B+','B','B-'].includes(g)) return 'grade-b'
  if (['C+','C','C-'].includes(g)) return 'grade-c'
  if (['D'].includes(g)) return 'grade-d'
  return ''
}
function getClassLabel(r: any) {
  if (r.exam?.class_id) return 'শ্রেণি ' + r.exam.class_id
  return '-'
}

onMounted(() => { Promise.all([load(), loadExams()]) })
</script>

<style scoped>
.results-page { max-width: 1480px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:1.4rem; flex-wrap:wrap; }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn); }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.65rem var(--font-bn); }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn); }
.header-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.2rem; }
.stat-card { display:flex; align-items:center; gap:.7rem; padding:.9rem 1rem; background:#fff; border:1px solid var(--color-border-light); border-radius:14px; }
.stat-icon { width:42px; height:42px; border-radius:12px; display:grid; place-items:center; background:var(--color-bg-muted); color:var(--color-primary); }
.stat-content { display:flex; flex-direction:column; }
.stat-value { font:800 1.3rem var(--font-sans); color:var(--color-text); }
.stat-label { font:.72rem var(--font-bn); color:var(--color-text-muted); }
.stat-card.green { border-left:4px solid #16a34a; }
.stat-card.blue { border-left:4px solid #2563eb; }
.stat-card.purple { border-left:4px solid #7c3aed; }
.stat-card.gold { border-left:4px solid #ca8a04; }
.toolbar { display:flex; align-items:center; gap:.6rem; padding:.7rem; margin-bottom:1rem; flex-wrap:wrap; }
.search-box { display:flex; align-items:center; gap:.5rem; flex:1; min-width:200px; padding:.55rem .75rem; background:var(--color-bg-muted); border-radius:10px; color:var(--color-text-muted); }
.search-box input { flex:1; border:0; outline:0; background:transparent; font:.88rem var(--font-bn); }
.form-control.compact { width:150px; padding:.55rem .65rem; font:.85rem var(--font-bn); }
.results-table-wrapper { background:#fff; border:1px solid var(--color-border-light); border-radius:16px; overflow:hidden; }
.results-table { width:100%; border-collapse:collapse; font-size:.88rem; }
.results-table th { background:var(--color-bg-muted); color:var(--color-text-muted); font:600 .72rem var(--font-bn); text-align:left; padding:.7rem .8rem; border-bottom:1px solid var(--color-border); }
.results-table td { padding:.65rem .8rem; border-bottom:1px solid var(--color-border-light); vertical-align:middle; }
.results-table tbody tr:hover { background:var(--color-bg-muted); }
.results-table tbody tr:last-child td { border-bottom:0; }
.student-cell { display:flex; flex-direction:column; gap:.1rem; }
.student-name { font-weight:600; color:var(--color-text); }
.exam-link { color:var(--color-primary); text-decoration:none; font-weight:500; }
.exam-link:hover { text-decoration:underline; }
.gpa-value { font-weight:700; color:var(--color-primary); }
.percentage-value { font-weight:600; }
.percentage-value.pass { color:#16a34a; }
.percentage-value.fail { color:#dc2626; }
.badge { display:inline-block; padding:.2rem .55rem; border-radius:99px; font:.72rem var(--font-bn); font-weight:600; }
.grade-a { background:#dcfce7; color:#166534; }
.grade-b { background:#dbeafe; color:#1e40af; }
.grade-c { background:#fef9c3; color:#854d0e; }
.grade-d { background:#fee2e2; color:#991b1b; }
.status-badge { display:inline-block; padding:.2rem .55rem; border-radius:6px; font:.72rem var(--font-bn); font-weight:600; }
.status-badge.passed { background:#dcfce7; color:#166534; }
.status-badge.failed { background:#fee2e2; color:#991b1b; }
.published-indicator { font:.75rem var(--font-bn); }
.published-indicator.published { color:#16a34a; font-weight:600; }
.published-indicator.unpublished { color:var(--color-text-muted); }
.actions-cell { white-space:nowrap; }
.actions-cell .btn { margin-right:.25rem; }
.pagination { display:flex; align-items:center; justify-content:space-between; padding:.8rem; border-top:1px solid var(--color-border-light); }
.page-info { font:.85rem var(--font-bn); color:var(--color-text-muted); }
.empty-card { padding:3rem 1.5rem; text-align:center; background:#fff; border:1px solid var(--color-border-light); border-radius:16px; }
.empty-icon { width:64px; height:64px; margin:0 auto .8rem; border-radius:50%; background:var(--color-bg-muted); display:grid; place-items:center; color:var(--color-text-muted); }
.empty-card h3 { margin:.4rem 0; font:700 1.1rem var(--font-bn); }
.empty-card p { color:var(--color-text-muted); font:.88rem var(--font-bn); margin-bottom:1rem; }
.loading-state { padding:3rem; text-align:center; color:var(--color-text-muted); }
</style>
