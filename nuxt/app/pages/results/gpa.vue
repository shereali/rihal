<template>
  <div class="gpa-page">
    <div class="page-header-row">
      <div><span class="eyebrow">একাডেমিক বিশ্লেষণ</span><h1>জিপিএ ও শতকরা হারের হিসাব</h1><p>শ্রেণি ভিত্তিক গড় জিপিএ, শতকরা গড় ও শীর্ষ নির্বাচনের হিসাব</p></div>
      <div class="header-actions">
        <button class="btn btn-outline btn-sm" @click="load"><icon name="refresh" /> রিফ্রেশ</button>
        <select v-model="groupBy" class="form-control compact" @change="load">
          <option value="class">শ্রেণি অনুযায়ী</option>
          <option value="exam">পরীক্ষা অনুযায়ী</option>
        </select>
      </div>
    </div>

    <div class="stats-row" v-if="stats">
      <div class="stat-card green"><div class="stat-icon"><icon name="students" /></div><div class="stat-content"><span class="stat-value">{{ stats.totalStudents }}</span><span class="stat-label">মোট শিক্ষার্থী</span></div></div>
      <div class="stat-card blue"><div class="stat-icon"><icon name="star" /></div><div class="stat-content"><span class="stat-value">{{ stats.overallGpa }}</span><span class="stat-label">সামগ্রিক গড় জিপিএ</span></div></div>
      <div class="stat-card purple"><div class="stat-icon"><icon name="percent" /></div><div class="stat-content"><span class="stat-value">{{ stats.overallPercentage }}%</span><span class="stat-label">গড় শতকরা</span></div></div>
      <div class="stat-card gold"><div class="stat-icon"><icon name="trophy" /></div><div class="stat-content"><span class="stat-value">{{ stats.topperName }}</span><span class="stat-label">সর্বোচ্চ জিপিএ</span></div></div>
    </div>

    <div class="tabs card">
      <button v-for="tab in tabs" :key="tab.key" class="tab" :class="{ active: activeTab === tab.key }" @click="activeTab = tab.key">{{ tab.label }}</button>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>হিসাব লোড হচ্ছে...</p></div>

    <div v-else-if="activeTab === 'summary'" class="summary-section">
      <div class="gpa-table-card card">
        <div class="card-header"><h3>{{ groupLabel }} জিপিএ সারসংক্ষেপ</h3></div>
        <div class="table-wrap">
          <table class="gpa-table">
            <thead><tr><th>শ্রেণি / পরীক্ষা</th><th>শিক্ষার্থী</th><th>গড় জিপিএ</th><th>গড় শতকরা</th><th>সর্বোচ্চ জিপিএ</th><th>পাস হার</th><th>শ্রেণি পদ</th></tr></thead>
            <tbody>
              <tr v-for="row in summaryRows" :key="row.key">
                <td class="group-label">{{ row.label }}</td>
                <td>{{ row.studentCount }} জন</td>
                <td class="gpa-cell"><span class="gpa-value">{{ row.avgGpa ?? '-' }}</span></td>
                <td class="percentage-cell"><span :class="passRateClass(row)">{{ row.avgPercentage ?? '-' }}%</span></td>
                <td><span class="badge" :class="gradeBadge(row.maxGpa)">{{ row.maxGpa ?? '-' }}</span></td>
                <td><span class="pass-rate">{{ row.passRate }}%</span></td>
                <td class="text-muted">{{ row.topPosition ?? '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="chart-card card">
        <div class="card-header"><h3>জিপিএ বন্টন</h3></div>
        <div class="bar-chart">
          <div v-for="bar in gpaBars" :key="bar.label" class="bar-row">
            <span class="bar-label">{{ bar.label }}</span>
            <div class="bar-track"><div class="bar-fill" :style="{ width: Math.min(100, bar.pct) + '%', background: bar.color }" /></div>
            <span class="bar-value">{{ bar.count }} জন</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'toppers'" class="toppers-section">
      <div class="card">
        <div class="card-header"><h3>শ্রেণি ভিত্তিক সর্বোচ্চ জিপিএ</h3></div>
        <div v-if="!toppers.length" class="empty-inline">কোনো তথ্য নেই</div>
        <div v-else class="toppers-list">
          <div v-for="(t, i) in toppers" :key="t.resultId" class="topper-row">
            <span class="rank-badge" :class="'rank-' + ((i % 3) + 1)">{{ i + 1 }}</span>
            <div class="topper-info">
              <span class="topper-name">{{ t.studentName }}</span>
              <small class="text-muted">{{ t.classLabel || t.examLabel }} · {{ t.sessionLabel }}</small>
            </div>
            <div class="topper-stats">
              <span class="gpa-chip"><icon name="star" /> {{ t.gpa }}</span>
              <span class="pct-chip"><icon name="percent" /> {{ t.percentage }}%</span>
              <span class="grade-chip"><icon name="award" /> {{ t.grade }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'details'" class="details-section">
      <div class="toolbar-inline card">
        <div class="search-box"><icon name="search" /><input v-model="detailSearch" placeholder="শিক্ষার্থী খুঁজুন..." @keyup.enter="loadDetails" /></div>
        <button class="btn btn-ghost btn-sm" @click="loadDetails">রিফ্রেশ</button>
      </div>
      <div v-if="!detailRows.length" class="empty-inline">কোনো ফলাফল পাওয়া যায়নি</div>
      <div v-else class="gpa-table-card card">
        <div class="table-wrap">
          <table class="gpa-table">
            <thead><tr><th>ছাত্র</th><th>পরীক্ষা</th><th>শ্রেণি</th><th>জিপিএ</th><th>শতকরা</th><th>গ্রেড</th><th>অবস্থা</th><th>প্রকাশ</th></tr></thead>
            <tbody>
              <tr v-for="r in detailRows" :key="r.resultId">
                <td><span class="student-name">{{ r.studentName }}</span></td>
                <td class="text-muted">{{ r.examName || 'পরীক্ষা ' + r.examId }}</td>
                <td class="text-muted">{{ r.classLabel || '-' }}</td>
                <td class="gpa-cell"><span class="gpa-value">{{ r.gpa ?? '-' }}</span></td>
                <td class="percentage-cell"><span :class="r.percentage >= 33 ? 'pass' : 'fail'">{{ r.percentage ?? '-' }}%</span></td>
                <td><span class="badge" :class="gradeBadge(r.grade)">{{ r.grade || '-' }}</span></td>
                <td><span class="status-badge" :class="r.pass_fail_status === 'passed' ? 'passed' : r.pass_fail_status === 'failed' ? 'failed' : ''">{{ r.pass_fail_status === 'passed' ? 'পাস' : r.pass_fail_status === 'failed' ? 'ফেল' : 'মুলতবি' }}</span></td>
                <td class="text-center"><span :class="r.is_published ? 'pub-yes' : 'pub-no'">{{ r.is_published ? 'হ্যাঁ' : 'না' }}</span></td>
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
const loading = ref(true)
const stats = ref({ totalStudents: 0, overallGpa: '-', overallPercentage: '-', topperName: '-' })
const summaryRows = ref<any[]>([])
const gpaBars = ref<any[]>([])
const toppers = ref<any[]>([])
const detailRows = ref<any[]>([])
const groupBy = ref<'class' | 'exam'>('class')
const activeTab = ref<'summary' | 'toppers' | 'details'>('summary')
const detailSearch = ref('')

const groupLabel = computed(() => groupBy.value === 'class' ? 'শ্রেণি' : 'পরীক্ষা')

const tabs = [
  { key: 'summary', label: 'সারসংক্ষেপ' },
  { key: 'toppers', label: 'শীর্ষ নির্বাচন' },
  { key: 'details', label: 'বিস্তারিত তালিকা' },
]

async function load() {
  loading.value = true
  try {
    const r = await api.get('/exam-results')
    const data = r.data?.data?.data || []
    computeStats(data)
    computeSummary(data)
    computeBars(data)
    computeToppers(data)
  } catch { /* ignore */ } finally { loading.value = false }
}

function computeStats(data: any[]) {
  const students = new Set()
  let totalGpa = 0, totalPct = 0, count = 0, highest = -1, highestName = '-'
  data.forEach(d => {
    if (d.student?.id) students.add(d.student.id)
    const g = Number(d.gpa) || 0, p = Number(d.percentage) || 0
    if (g > 0) { totalGpa += g; count++ }
    if (p > 0) totalPct += p
    if (g > highest) { highest = g; highestName = d.student?.name_bn || '-' }
  })
  stats.value = {
    totalStudents: students.size,
    overallGpa: count ? (totalGpa / count).toFixed(2) : '-',
    overallPercentage: count ? (totalPct / count).toFixed(1) : '-',
    topperName: highestName,
  }
}

function computeSummary(data: any[]) {
  const groups: any = {}
  data.forEach(d => {
    const key = groupBy.value === 'class' ? (d.exam?.class_id || 'সব শ্রেণি') : (d.exam?.name_bn || 'সব পরীক্ষা')
    if (!groups[key]) groups[key] = { gpa: [], pct: [], max: -1, passed: 0, failed: 0, students: new Set() }
    const g = Number(d.gpa) || 0, p = Number(d.percentage) || 0
    if (g > 0) groups[key].gpa.push(g)
    if (p > 0) groups[key].pct.push(p)
    if (g > groups[key].max) groups[key].max = g
    if (d.pass_fail_status === 'passed') groups[key].passed++
    else if (d.pass_fail_status === 'failed') groups[key].failed++
    if (d.student?.id) groups[key].students.add(d.student.id)
  })
  summaryRows.value = Object.entries(groups).map(([key, v]) => ({
    key, label: key,
    studentCount: v.students.size,
    avgGpa: v.gpa.length ? (v.gpa.reduce((a,b)=>a+b,0)/v.gpa.length).toFixed(2) : '-',
    avgPercentage: v.pct.length ? (v.pct.reduce((a,b)=>a+b,0)/v.pct.length).toFixed(1) : '-',
    maxGpa: v.max >= 0 ? v.max.toFixed(2) : '-',
    passRate: v.passed + v.failed > 0 ? ((v.passed/(v.passed+v.failed))*100).toFixed(0) : '-',
    topPosition: '-',
  }))
}

function computeBars(data: any[]) {
  const bins = { 'A+ (4.0)':0, 'A (3.75)':0, 'A- (3.5)':0, 'B+ (3.25)':0, 'B (3.0)':0, 'C+ (2.75)':0, 'C (2.5)':0, 'D (2.0)':0, 'F (<2.0)':0 }
  data.forEach(d => {
    const g = Number(d.gpa) || 0
    if (g >= 4.0) bins['A+ (4.0)']++
    else if (g >= 3.75) bins['A (3.75)']++
    else if (g >= 3.5) bins['A- (3.5)']++
    else if (g >= 3.25) bins['B+ (3.25)']++
    else if (g >= 3.0) bins['B (3.0)']++
    else if (g >= 2.75) bins['C+ (2.75)']++
    else if (g >= 2.5) bins['C (2.5)']++
    else if (g >= 2.0) bins['D (2.0)']++
    else bins['F (<2.0)']++
  })
  const colors = ['#16a34a','#22c55e','#4ade80','#facc15','#f97316','#fb923c','#ef4444','#dc2626','#1e293b']
  gpaBars.value = Object.entries(bins).map(([label, count], i) => ({
    label, count,
    pct: data.length ? (count / data.length) * 100 : 0,
    color: colors[i],
  })).filter(b => b.count > 0)
}

function computeToppers(data: any[]) {
  const seen = new Set()
  const list: any[] = []
  const sorted = [...data].sort((a,b) => (Number(b.gpa)||0) - (Number(a.gpa)||0))
  for (const d of sorted) {
    const key = d.student?.id
    if (key && !seen.has(d.student.id)) {
      seen.add(d.student.id)
      list.push({
        resultId: d.id, studentName: d.student?.name_bn || 'ছাত্র',
        gpa: d.gpa, percentage: d.percentage, grade: d.grade,
        classLabel: d.exam?.class_id ? 'শ্রেণি ' + d.exam.class_id : '',
        examLabel: d.exam?.name_bn || '', sessionLabel: d.session?.name_bn || '',
      })
    }
    if (list.length >= 20) break
  }
  toppers.value = list
}

async function loadDetails() {
  try {
    const q = new URLSearchParams()
    if (detailSearch.value) q.set('search', detailSearch.value)
    const r = await api.get(`/exam-results?${q}`)
    detailRows.value = (r.data?.data?.data || []).map((d: any) => ({
      resultId: d.id, studentName: d.student?.name_bn || 'ছাত্র', examId: d.exam_id,
      examName: d.exam?.name_bn || '', classLabel: d.exam?.class_id ? 'শ্রেণি ' + d.exam.class_id : '',
      gpa: d.gpa, percentage: d.percentage, grade: d.grade,
      pass_fail_status: d.pass_fail_status, is_published: d.is_published,
    }))
  } catch { detailRows.value = [] }
}

function passRateClass(row: any) { return row.passRate === 100 ? 'pass' : row.passRate === 0 ? 'fail' : '' }
function gradeBadge(v: string) { if (!v) return ''; const x = v.toUpperCase(); if (['A+','A','A-'].includes(x)) return 'grade-a'; if (['B+','B','B-'].includes(x)) return 'grade-b'; if (['C+','C','C-'].includes(x)) return 'grade-c'; return 'grade-d' }

onMounted(load)
</script>

<style scoped>
.gpa-page { max-width: 1280px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:1.3rem; flex-wrap:wrap; }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn); }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.65rem var(--font-bn); }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn); }
.header-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:.8rem; margin-bottom:1.2rem; }
.stat-card { display:flex; align-items:center; gap:.6rem; padding:.8rem; background:#fff; border:1px solid var(--color-border-light); border-radius:14px; }
.stat-icon { width:40px; height:40px; border-radius:11px; display:grid; place-items:center; background:var(--color-bg-muted); }
.stat-content { display:flex; flex-direction:column; }
.stat-value { font:800 1.25rem var(--font-sans); line-height:1.15; }
.stat-label { font:.72rem var(--font-bn); color:var(--color-text-muted); }
.stat-card.green { border-left:4px solid #16a34a; } .stat-card.blue { border-left:4px solid #2563eb; }
.stat-card.purple { border-left:4px solid #7c3aed; } .stat-card.gold { border-left:4px solid #ca8a04; }
.tabs { display:flex; gap:.3rem; padding:.6rem; border-bottom:1px solid var(--color-border-light); margin-bottom:.8rem; }
.tab { border:0; background:transparent; padding:.5rem .8rem; font:.85rem var(--font-bn); font-weight:600; color:var(--color-text-muted); cursor:pointer; border-radius:8px; }
.tab.active { background:var(--color-primary-100); color:var(--color-primary); }
.card { background:#fff; border:1px solid var(--color-border-light); border-radius:16px; overflow:hidden; margin-bottom:.8rem; }
.card-header { padding:.8rem .9rem; border-bottom:1px solid var(--color-border-light); background:var(--color-bg-muted); }
.card-header h3 { margin:0; font:700 1rem var(--font-bn); }
.table-wrap { overflow-x:auto; }
.gpa-table { width:100%; border-collapse:collapse; font-size:.86rem; }
.gpa-table th { background:var(--color-bg-muted); color:var(--color-text-muted); font:600 .7rem var(--font-bn); text-align:left; padding:.55rem .7rem; white-space:nowrap; }
.gpa-table td { padding:.5rem .7rem; border-bottom:1px solid var(--color-border-light); vertical-align:middle; }
.gpa-table tbody tr:last-child td { border-bottom:0; }
.group-label { font-weight:600; color:var(--color-primary); }
.gpa-cell { font-weight:700; color:var(--color-primary); }
.percentage-cell { font-weight:600; }
.percentage-cell.pass { color:#16a34a; } .percentage-cell.fail { color:#dc2626; }
.gpa-value { font-weight:700; }
.badge { display:inline-block; padding:.15rem .5rem; border-radius:99px; font:.7rem var(--font-bn); font-weight:600; }
.grade-a { background:#dcfce7; color:#166534; } .grade-b { background:#dbeafe; color:#1e40af; }
.grade-c { background:#fef9c3; color:#854d0e; } .grade-d { background:#fee2e2; color:#991b1b; }
.pass-rate { font-weight:600; color:#16a34a; }
.bar-chart { padding:.8rem; }
.bar-row { display:flex; align-items:center; gap:.6rem; margin-bottom:.45rem; }
.bar-label { width:95px; font:.76rem var(--font-bn); color:var(--color-text-muted); flex-shrink:0; }
.bar-track { flex:1; height:22px; background:var(--color-bg-muted); border-radius:99px; overflow:hidden; }
.bar-fill { height:100%; border-radius:99px; }
.bar-value { width:70px; font:.74rem var(--font-bn); text-align:right; color:var(--color-text-muted); flex-shrink:0; }
.toppers-section .card { padding:0; }
.card-header { padding:.8rem .9rem; }
.toppers-list { padding:.5rem; }
.topper-row { display:grid; grid-template-columns:50px 1fr auto; gap:.7rem; align-items:center; padding:.55rem .7rem; border-bottom:1px solid var(--color-border-light); }
.topper-row:last-child { border-bottom:0; }
.rank-badge { width:36px; height:36px; border-radius:50%; display:grid; place-items:center; font:800 .9rem var(--font-sans); background:#fff; border:2px solid; }
.rank-1 { border-color:#ca8a04; color:#ca8a04; } .rank-2 { border-color:#94a3b8; color:#64748b; } .rank-3 { border-color:#d97706; color:#b45309; }
.topper-info { display:flex; flex-direction:column; }
.topper-name { font-weight:600; }
.topper-stats { display:flex; gap:.4rem; flex-wrap:wrap; }
.gpa-chip, .pct-chip, .grade-chip { display:inline-flex; align-items:center; gap:.25rem; padding:.2rem .5rem; border-radius:99px; font:.72rem var(--font-bn); font-weight:600; background:var(--color-bg-muted); }
.gpa-chip { background:#dcfce7; color:#166534; }
.pct-chip { background:#dbeafe; color:#1e40af; }
.grade-chip { background:#fef9c3; color:#854d0e; }
.toolbar-inline { display:flex; align-items:center; gap:.6rem; padding:.7rem; }
.search-box { display:flex; align-items:center; gap:.5rem; flex:1; padding:.55rem .75rem; background:var(--color-bg-muted); border-radius:10px; }
.search-box input { flex:1; border:0; outline:0; background:transparent; font:.88rem var(--font-bn); }
.summary-section, .toppers-section, .details-section { padding:.5rem; }
.empty-inline { padding:.8rem; text-align:center; color:var(--color-text-muted); font:.86rem var(--font-bn); }
.form-control.compact { width:150px; padding:.55rem .65rem; font:.85rem var(--font-bn); }
.pub-yes { color:#16a34a; font-weight:600; } .pub-no { color:var(--color-text-muted); }
.student-name { font-weight:600; }
.loading-state { padding:2.5rem; text-align:center; color:var(--color-text-muted); }
</style>
