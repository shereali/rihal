<template>
  <div class="exam-results-page">
    <div class="page-header-row">
      <NuxtLink to="/exams" class="btn btn-outline btn-sm"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
      <div class="header-actions">
        <button class="btn btn-outline btn-sm" type="button" @click="load"><icon name="refresh" /> রিফ্রেশ</button>
        <NuxtLink to="/results" class="btn btn-ghost btn-sm"><icon name="list" /> সব ফলাফল</NuxtLink>
        <NuxtLink to="/results/gpa" class="btn btn-ghost btn-sm"><icon name="chart" /> জিপিএ হিসাব</NuxtLink>
        <button class="btn btn-primary btn-sm" type="button" @click="exportBtn"><icon name="download" /> ডাউনলোড</button>
      </div>
    </div>

    <div class="exam-hero card" v-if="exam">
      <div class="hero-left">
        <span class="eyebrow">একাডেমিক রিপোর্ট</span>
        <h1>{{ exam.name_bn }}</h1>
        <p v-if="exam.name_en">{{ exam.name_en }}</p>
        <div class="hero-meta">
          <span><icon name="academic" /> শ্রেণি আইডি: {{ exam.class_id }}</span>
          <span><icon name="calendar" /> শুরু: {{ formatDate(exam.start_date) }}</span>
          <span><icon name="calendar" /> শেষ: {{ formatDate(exam.end_date) }}</span>
          <span><icon name="timer" /> সময়কাল: {{ exam.duration_minutes ?? '-' }} মিনিট</span>
          <span><icon name="star" /> মোট নম্বর: {{ exam.total_marks ?? '-' }}</span>
          <span><icon name="award" /> পাস নম্বর: {{ exam.passing_marks ?? '-' }}</span>
        </div>
      </div>
      <div class="hero-right">
        <div class="publish-banner" :class="examPublishedClass">
          <icon :name="examPublishedClass === 'published' ? 'check-circle' : 'clock'" />
          <div>
            <strong class="banner-title">{{ examPublishedClass === 'published' ? 'ফলাফল প্রকাশিত' : 'ফলাফল অপ্রকাশিত' }}</strong>
            <small>{{ examPublishedClass === 'published' ? 'শিক্ষার্থীরা দেখতে পারবে' : 'শিক্ষার্থীরা দেখতে পারবে না' }}</small>
          </div>
          <button class="btn btn-sm" :class="examPublishedClass === 'published' ? 'btn-outline-danger' : 'btn-primary'" @click="toggleExamPublish(exam.id)">
            <icon :name="examPublishedClass === 'published' ? 'eye-off' : 'eye'" />
            {{ examPublishedClass === 'published' ? 'অপ্রকাশ করুন' : 'প্রকাশ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <div class="stats-row" v-if="stats">
      <div class="stat-card green"><div class="stat-icon"><icon name="students" /></div><div class="stat-content"><span class="stat-value">{{ stats.total }}</span><span class="stat-label">মোট ফলাফল</span></div></div>
      <div class="stat-card blue"><div class="stat-icon"><icon name="check" /></div><div class="stat-content"><span class="stat-value">{{ stats.passed }}</span><span class="stat-label">পাস</span></div></div>
      <div class="stat-card purple"><div class="stat-icon"><icon name="x" /></div><div class="stat-content"><span class="stat-value">{{ stats.failed }}</span><span class="stat-label">ফেল</span></div></div>
      <div class="stat-card gold"><div class="stat-icon"><icon name="trophy" /></div><div class="stat-content"><span class="stat-value">{{ stats.topper }}</span><span class="stat-label">সর্বোচ্চ জিপিএ</span></div></div>
    </div>

    <div class="toolbar card">
      <div class="search-box"><icon name="search" /><input v-model="search" placeholder="শিক্ষার্থী খুঁজুন..." @keyup.enter="load" /></div>
      <select v-model="statusFilter" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="passed">পাস</option>
        <option value="failed">ফেল</option>
        <option value="pending">মুলতবি</option>
      </select>
      <select v-model="typeFilter" class="form-control compact" @change="load">
        <option value="">সব প্রকাশ অবস্থা</option>
        <option value="published">প্রকাশিত</option>
        <option value="unpublished">অপ্রকাশিত</option>
      </select>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>ফলাফল লোড হচ্ছে...</p></div>
    <div v-else-if="!results.length" class="empty-card">
      <div class="empty-icon"><icon name="document" /></div>
      <h3>এই পরীক্ষার কোনো ফলাফল নেই</h3>
      <p>প্রথমে মার্ক এন্ট্রি করুন অথবা ফলাফল তৈরি করুন</p>
      <NuxtLink :to="`/marks/create?exam=${exam?.id}`" class="btn btn-primary"><icon name="plus" /> মার্ক এন্ট্রি</NuxtLink>
      <NuxtLink to="/exam-results/create" class="btn btn-outline btn-sm">ফলাফল তৈরি করুন</NuxtLink>
    </div>
    <div v-else class="results-table-wrapper">
      <table class="results-table">
        <thead><tr>
          <th>#</th><th>ছাত্র</th><th>এনরোলমেন্ট</th><th>জিপিএ</th><th>শতকরা</th><th>গ্রেড</th><th>অবস্থা</th><th>প্রকাশ</th><th>কর্ম</th>
        </tr></thead>
        <tbody>
          <tr v-for="(r, i) in results" :key="r.id">
            <td class="text-muted">{{ i + 1 }}</td>
            <td><div class="student-cell"><span class="student-name">{{ r.student?.name_bn || 'ছাত্র ' + r.student_id }}</span><small class="text-muted">{{ r.student?.name_en }}</small></div></td>
            <td class="text-muted">{{ r.enrollment_number || '-' }}</td>
            <td><span class="gpa-value">{{ r.gpa ?? '-' }}</span></td>
            <td><span class="percentage-value" :class="rowStatusClass(r)">{{ r.percentage ?? '-' }}</span></td>
            <td><span class="badge" :class="gradeBadgeClass(r.grade)">{{ r.grade || '-' }}</span></td>
            <td><span class="status-badge" :class="statusBadge(r)">{{ statusText(r) }}</span></td>
            <td class="text-center"><span class="pub-dot" :class="r.is_published ? 'pub-yes' : 'pub-no'">{{ r.is_published ? 'প্রকাশিত' : 'অপ্রকাশিত' }}</span></td>
            <td class="actions-cell">
              <NuxtLink :to="`/exam-results/${r.id}`" class="btn btn-ghost btn-sm" title="বিস্তারিত"><icon name="eye" /></NuxtLink>
              <button v-if="r.is_published" class="btn btn-outline-danger btn-sm" @click="togglePublish(r.id, false)" title="অপ্রকাশ"><icon name="eye-off" /></button>
              <button v-else class="btn btn-outline btn-sm" @click="togglePublish(r.id, true)" title="প্রকাশ"><icon name="check" /></button>
              <button class="btn btn-ghost-danger btn-sm" @click="confirmDelete(r)" title="মুছুন"><icon name="delete" /></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="deleteTarget" class="modal-overlay">
      <div class="modal">
        <h3>ফলাফল মুছবেন?</h3>
        <p>{{ deleteTarget.student?.name_bn }} — {{ deleteTarget.exam?.name_bn }}</p>
        <div class="modal-actions">
          <button class="btn btn-outline-danger" @click="deleteResult()">হ্যাঁ, মুছুন</button>
          <button class="btn btn-ghost" @click="deleteTarget = null">বাতিল</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, navigateTo } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const examId = Number(route.params.id)

const exam = ref<any>(null)
const results = ref<any[]>([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const statusFilter = ref('')
const typeFilter = ref('')
const deleteTarget = ref<any>(null)

const stats = ref({ total: 0, passed: 0, failed: 0, topper: '-' })

async function load() {
  loading.value = true; error.value = ''
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (statusFilter.value) q.set('pass_fail_status', statusFilter.value)
    if (typeFilter.value === 'published') q.set('is_published', '1')
    if (typeFilter.value === 'unpublished') q.set('is_published', '0')
    const r = await api.get(`/exam-results?exam_id=${examId}&${q.toString()}`)
    const d = r.data?.data || {}
    results.value = Array.isArray(d) ? d : (d?.data || [])
    stats.value = {
      total: results.value.length,
      passed: results.value.filter(r => r.pass_fail_status === 'passed').length,
      failed: results.value.filter(r => r.pass_fail_status === 'failed').length,
      topper: results.value.length ? Math.max(...results.value.map(r => Number(r.gpa) || 0)).toFixed(2) : '-',
    }
  } catch (e: any) { error.value = e?.response?.data?.message || 'লোড করা যায়নি' } finally { loading.value = false }
}

async function loadExam() {
  try {
    const r = await api.get(`/exams/${examId}`)
    exam.value = r.data?.data || null
  } catch { exam.value = null }
}

async function togglePublish(id: number, published: boolean) {
  try {
    await api.patch(`/exam-results/${id}/${published ? 'publish' : 'unpublish'}`)
    load()
  } catch (e: any) { error.value = e?.response?.data?.message || 'প্রকাশ/অপ্রকাশ করা যায়নি' }
}

async function toggleExamPublish(id: number) {
  const isPub = exam.value?.is_published
  try {
    await api.patch(`/exam-results/${id}/${isPub ? 'unpublish' : 'publish'}`)
    exam.value.is_published = !isPub
    load()
  } catch (e: any) { error.value = e?.response?.data?.message || 'করা যায়নি' }
}

function confirmDelete(r: any) { deleteTarget.value = r }

async function deleteResult() {
  if (!deleteTarget.value) return
  try {
    await api.delete(`/exam-results/${deleteTarget.value.id}`)
    deleteTarget.value = null
    load()
  } catch (e: any) { error.value = e?.response?.data?.message || 'মুছে ফেলা যায়নি' }
}

function exportBtn() {
  window.open(`/exams/${examId}/results?export=csv`, '_blank')
}

function formatDate(v: any) { return v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: '2-digit' }) : '-' }
function rowStatusClass(r: any) { const p = Number(r.percentage); return p >= 33 ? 'pass' : 'fail' }
function statusBadge(r: any) { if (r.pass_fail_status === 'passed') return 'passed'; if (r.pass_fail_status === 'failed') return 'failed'; return '' }
function statusText(r: any) { if (r.pass_fail_status === 'passed') return 'পাস'; if (r.pass_fail_status === 'failed') return 'ফেল'; return r.pass_fail_status || 'মুলতবি' }
function gradeBadgeClass(g: string) { if (!g) return ''; const x = g.toUpperCase(); if (['A+','A','A-'].includes(x)) return 'grade-a'; if (['B+','B','B-'].includes(x)) return 'grade-b'; if (['C+','C','C-'].includes(x)) return 'grade-c'; return 'grade-d' }
function examPublishedClass() { return exam.value?.is_published ? 'published' : 'unpublished' }

onMounted(() => Promise.all([loadExam(), load()]))
</script>

<style scoped>
.exam-results-page { max-width: 1280px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.3rem; flex-wrap:wrap; }
.header-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.exam-hero { display:grid; grid-template-columns:1fr auto; gap:1.2rem; padding:1.2rem; margin-bottom:1rem; }
.hero-left h1 { margin:.2rem 0; font:700 1.45rem var(--font-bn); color:var(--color-primary); }
.hero-left p { color:var(--color-text-muted); font:.88rem var(--font-bn); }
.hero-meta { display:flex; flex-wrap:wrap; gap:.6rem; margin-top:.6rem; }
.hero-meta span { display:inline-flex; align-items:center; gap:.3rem; font:.78rem var(--font-bn); color:var(--color-text-muted); background:var(--color-bg-muted); padding:.25rem .5rem; border-radius:99px; }
.publish-banner { display:flex; align-items:center; gap:.6rem; padding:.7rem .8rem; border-radius:12px; }
.publish-banner.published { background:#dcfce7; color:#166534; }
.publish-banner.unpublished { background:#fef9c3; color:#854d0e; }
.publish-banner .banner-title { display:block; font-weight:700; }
.publish-banner small { font:.72rem; opacity:.8; }
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:.8rem; margin-bottom:1rem; }
.stat-card { display:flex; align-items:center; gap:.6rem; padding:.75rem; background:#fff; border:1px solid var(--color-border-light); border-radius:12px; }
.stat-icon { width:38px; height:38px; border-radius:10px; display:grid; place-items:center; background:var(--color-bg-muted); }
.stat-content { display:flex; flex-direction:column; }
.stat-value { font:800 1.2rem var(--font-sans); line-height:1.1; }
.stat-label { font:.7rem var(--font-bn); color:var(--color-text-muted); }
.stat-card.green { border-left:4px solid #16a34a; } .stat-card.blue { border-left:4px solid #2563eb; }
.stat-card.purple { border-left:4px solid #7c3aed; } .stat-card.gold { border-left:4px solid #ca8a04; }
.toolbar { display:flex; align-items:center; gap:.6rem; padding:.7rem; margin-bottom:1rem; flex-wrap:wrap; }
.search-box { display:flex; align-items:center; gap:.5rem; flex:1; min-width:200px; padding:.55rem .75rem; background:var(--color-bg-muted); border-radius:10px; }
.search-box input { flex:1; border:0; outline:0; background:transparent; font:.88rem var(--font-bn); }
.form-control.compact { width:140px; padding:.55rem .65rem; font:.85rem var(--font-bn); }
.results-table-wrapper { background:#fff; border:1px solid var(--color-border-light); border-radius:16px; overflow:hidden; }
.results-table { width:100%; border-collapse:collapse; font-size:.86rem; }
.results-table th { background:var(--color-bg-muted); color:var(--color-text-muted); font:600 .72rem var(--font-bn); text-align:left; padding:.6rem .7rem; }
.results-table td { padding:.6rem .7rem; border-bottom:1px solid var(--color-border-light); vertical-align:middle; }
.results-table tbody tr:last-child td { border-bottom:0; }
.student-cell { display:flex; flex-direction:column; gap:.1rem; }
.student-name { font-weight:600; }
.gpa-value { font-weight:700; color:var(--color-primary); }
.percentage-value { font-weight:600; }
.percentage-value.pass { color:#16a34a; } .percentage-value.fail { color:#dc2626; }
.badge { display:inline-block; padding:.18rem .5rem; border-radius:99px; font:.7rem var(--font-bn); font-weight:600; }
.grade-a { background:#dcfce7; color:#166534; } .grade-b { background:#dbeafe; color:#1e40af; }
.grade-c { background:#fef9c3; color:#854d0e; } .grade-d { background:#fee2e2; color:#991b1b; }
.status-badge { display:inline-block; padding:.18rem .5rem; border-radius:6px; font:.7rem var(--font-bn); font-weight:600; }
.status-badge.passed { background:#dcfce7; color:#166534; }
.status-badge.failed { background:#fee2e2; color:#991b1b; }
.pub-dot { font:.72rem var(--font-bn); }
.pub-yes { color:#16a34a; font-weight:600; } .pub-no { color:var(--color-text-muted); }
.actions-cell { white-space:nowrap; } .actions-cell .btn { margin-right:.2mo; }
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.4); display:grid; place-items:center; z-index:200; }
.modal { background:#fff; border-radius:18px; padding:1.5rem; max-width:380px; width:90%; }
.modal h3 { margin:0 0 .5rem; font:700 1.1rem var(--font-bn); }
.modal p { color:var(--color-text-muted); margin-bottom:1rem; }
.modal-actions { display:flex; gap:.6rem; justify-content:flex-end; }
.empty-card { padding:2.5rem 1.5rem; text-align:center; background:#fff; border:1px solid var(--color-border-light); border-radius:16px; }
.empty-icon { width:60px; height:60px; margin:0 auto .7rem; border-radius:50%; background:var(--color-bg-muted); display:grid; place-items:center; color:var(--color-text-muted); }
.empty-card h3 { margin:.3rem 0; font:700 1.05rem var(--font-bn); }
.empty-card p { color:var(--color-text-muted); font:.86rem var(--font-bn); margin-bottom:1rem; }
.loading-state { padding:2.5rem; text-align:center; color:var(--color-text-muted); }
</style>
