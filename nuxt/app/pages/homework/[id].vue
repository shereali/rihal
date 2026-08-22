<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/homework">বাড়ির কাজ</NuxtLink>
      <icon name="chevron-right" class="breadcrumb-sep" />
      <span>{{ assignment?.title_bn || assignment?.title_en }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">একাডেমিক কার্যক্রম</span>
        <h1>গৃহকাজের বিবরণী</h1>
        <p>{{ assignment?.title_bn || assignment?.title_en }} — নির্দেশনা, সময়সীমা ও জমা তথ্য</p>
      </div>
      <NuxtLink to="/homework" class="btn btn-ghost">
        <icon name="arrow-left" /> সব গৃহকাজে ফিরে যান
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!assignment" class="empty-card">
      <div class="empty-icon"><icon name="book" /></div>
      <h3>এই গৃহকাজটি পাওয়া যায়নি</h3>
      <NuxtLink to="/homework" class="btn btn-primary">সব গৃহকাজ দেখুন</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <div class="card detail-card">
        <div class="detail-header">
          <h2 class="detail-title">{{ assignment.title_bn || assignment.title_en }}</h2>
          <span class="status-badge" :class="assignment.is_active ? 'active' : 'inactive'">
            {{ assignment.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>বিষয়</label>
            <p>{{ assignment.subject?.name_bn || assignment.subject?.name_en || 'সাধারণ' }}</p>
          </div>
          <div class="info-block">
            <label>শ্রেণি</label>
            <p>{{ assignment.class?.name_bn || assignment.class?.name_en || 'সব শ্রেণি' }}</p>
          </div>
          <div class="info-block">
            <label>জমার শেষ তারিখ</label>
            <p>{{ assignment.due_date ? formatDate(assignment.due_date) : 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block">
            <label>সর্বোচ্চ নম্বর</label>
            <p>{{ assignment.max_score ?? '-' }}</p>
          </div>
          <div class="info-block wide">
            <label>শিক্ষক</label>
            <p>{{ assignment.teacher?.user?.name_bn || assignment.teacher?.user?.name_en || 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block wide">
            <label>ক্রিয়েটর</label>
            <p v-if="assignment.created_by">শিক্ষক #{{ assignment.created_by }}</p>
            <p v-else class="text-muted">নেই</p>
          </div>
        </div>

        <div class="description-section">
          <span class="section-label">বিস্তারিত নির্দেশনা</span>
          <p class="description-text">{{ assignment.description_bn || 'কোনো নির্দেশনা যোগ করা হয়নি' }}</p>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>জমা তথ্য ({{ submissions?.length ?? 0 }})</h3>
          <button class="btn btn-primary btn-sm" @click="loadSubmissions" :disabled="submitLoading">
            <icon name="refresh" /> রিফ্রেশ
          </button>
        </div>
        <div class="card-body">
          <div v-if="submitLoading && !submissionData?.data?.data?.length" class="loading-state">
            <div class="spinner" />
          </div>
          <div v-else-if="!submissions || submissions.length === 0" class="empty-slate">
            <icon name="file-text" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো জমা নেই</p>
          </div>
          <div v-else class="submissions-table">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>শিক্ষার্থী</th>
                    <th>জমার তারিখ</th>
                    <th>ফাইল/লিংক</th>
                    <th>সম্ভাব্য নম্বর</th>
                    <th>অবস্থা</th>
                    <th>কর্ম</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="sub in submissions" :key="sub.id">
                    <td class="student-cell">
                      <span v-if="sub.student?.user">{{ sub.student.user.name_bn || sub.student.user.name_en }}</span>
                      <span v-else class="text-muted">অজ্ঞাত</span>
                    </td>
                    <td class="text-center">{{ sub.submitted_at ? formatDate(sub.submitted_at) : '-' }}</td>
                    <td class="text-center">
                      <span v-if="sub.submission_file_url">
                        <a :href="sub.submission_file_url" target="_blank" rel="noopener">
                          <icon name="file-download" /> ডাউনলোড
                        </a>
                      </span>
                      <span v-else class="text-muted">নেই</span>
                    </td>
                    <td class="text-center">{{ sub.score ?? '-' }}</td>
                    <td>
                      <span class="status-badge" :class="statusClass(sub.status)">
                        {{ statusLabel(sub.status) }}
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-outline btn-sm" @click="gradeSubmission(sub)">
                        <icon name="pencil" /> নম্বর দিন
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div v-if="submitLoading" class="card">
        <div class="card-header"><h3>নতুন জমা দেওয়া হচ্ছে...</h3></div>
        <div class="card-body">
          <div class="loading-state"><div class="spinner" /></div>
        </div>
      </div>
      <div v-else-if="newSubmissionSuccess" class="card success-card">
        <div class="card-body text-center">
          <icon name="check-circle" class="success-icon" />
          <p>জমা সফলভাবে গ্রহণ করা হয়েছে</p>
          <NuxtLink to="/homework" class="btn btn-primary">ফিরে যান</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(true)
const submitLoading = ref(false)
const newSubmissionSuccess = ref(false)
const assignment = ref<any>(null)
const submissions = ref<any[]>([])

async function load() {
  loading.value = true
  const id = Number(route.params.id)
  try {
    const [assignmentRes, submissionsRes] = await Promise.all([
      api.get(`/homework-assignments/${id}`),
      api.get(`/homework-assignments/${id}/submissions`),
    ])
    assignment.value = assignmentRes.data?.data
    submissions.value = submissionsRes.data?.data?.data || submissionsRes.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadSubmissions() {
  submitLoading.value = true
  const id = Number(route.params.id)
  try {
    const r = await api.get(`/homework-assignments/${id}/submissions`)
    submissions.value = r.data?.data?.data || r.data?.data || []
  } catch (e) { console.error(e) }
  finally { submitLoading.value = false }
}

function formatDate(v: string) {
  return v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    pending: 'মুলতুবি',
    submitted: 'জমা দেওয়া হয়েছে',
    graded: 'মূল্যায়ন করা হয়েছে',
    rejected: 'প্রত্যাখ্যাত',
  }
  return map[s] || s || '-'
}

function statusClass(s: string) {
  if (s === 'graded') return 'status-graded'
  if (s === 'submitted') return 'status-submitted'
  if (s === 'rejected') return 'status-rejected'
  return 'status-pending'
}

function gradeSubmission(sub: any) {
  const newScore = prompt('নম্বর লিখুন:', String(sub.score ?? ''))
  if (newScore !== null && newScore !== '') {
    api.put(`/homework-submissions/${sub.id}`, { score: Number(newScore) })
      .then(() => { loadSubmissions() })
      .catch(() => {})
  }
}

onMounted(load)
</script>

<style scoped>
.module-page { max-width: 1000px; margin: 0 auto; padding-bottom: 2rem }
.breadcrumb { display:flex; align-items:center; gap:.4rem; margin-bottom:.7rem; font:.82rem var(--font-bn); color:var(--color-text-muted) }
.breadcrumb-sep { color:var(--color-text-muted) }
.page-header-row { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin-bottom:1.4rem }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn) }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.5rem var(--font-bn) }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn) }
.header-actions { display:flex; gap:.5rem }
.loading-state { text-align:center; padding:3rem 0; display:flex; justify-content:center; gap:.5rem }
.empty-card { text-align:center; padding:3rem 0; display:flex; flex-direction:column; align-items:center; gap:.7rem }
.empty-icon { width:56px; height:56px; color:var(--color-text-muted); margin-bottom:.3rem }
.empty-icon-slate { width:34px; height:34px; color:var(--color-text-muted) }
.detail-layout { display:flex; flex-direction:column; gap:.7rem }
.detail-card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.detail-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; padding:1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02) }
.detail-title { margin:0; font:700 1.3rem var(--font-bn); color:var(--color-primary) }
.status-badge { padding:.2rem .6rem; border-radius:99px; font:.7rem var(--font-bn); font-weight:600; white-space:nowrap }
.status-badge.active { background:#e6f4ec; color:#19724a }
.status-badge.inactive { background:#fde8e8; color:#a03030 }
.info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.8rem; padding:1.1rem }
.info-block { display:flex; flex-direction:column; gap:.2rem }
.info-block.wide { grid-column:span 2 }
.info-block label { font:600 .72rem var(--color-text-muted); font-family:var(--font-bn) }
.info-block p { margin:0; font:.88rem var(--font-bn) }
.description-section { padding:1.1rem; border-top:1px solid var(--color-border-light); background:#fafbfc }
.section-label { display:block; font:600 .78rem var(--font-bn); color:var(--color-text-muted); margin-bottom:.4rem }
.description-text { margin:0; font:.88rem var(--font-bn); line-height:1.5 }
.card { background:var(--color-bg-card); border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.card-header { padding:.85rem 1.1rem; border-bottom:1px solid var(--color-border-light); background:rgba(0,0,0,0.02); display:flex; justify-content:space-between; align-items:center }
.card-header h3 { margin:0; font:700 1rem var(--font-bn); color:var(--color-text) }
.card-body { padding:1.1rem }
.btn-sm { padding:.3rem .7rem; font:.72rem var(--font-bn); }
.success-card { border-color:#19724a; }
.success-icon { width:48px; height:48px; color:#19724a; margin-bottom:.5rem }
.submissions-table { display:grid; grid-template-columns:1fr; }
.table-responsive { overflow-x:auto }
.table { width:100%; border-collapse:collapse; font:.82rem var(--font-bn) }
.table th { background:rgba(0,0,0,0.03); padding:.7rem 1rem; text-align:left; font:600 .75rem var(--font-bn); color:var(--color-text-muted); border-bottom:1px solid var(--color-border-light); white-space:nowrap }
.table td { padding:.6rem 1rem; border-bottom:1px solid var(--color-border-light); vertical-align:middle }
.table tr:last-child td { border-bottom:0 }
.table tr:hover td { background:#fafbfc }
.text-center { text-align:center }
.student-cell { font-weight:600; color:var(--color-text) }
.text-muted { color:var(--color-text-muted) }
.status-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.15rem .5rem; border-radius:99px; font:.65rem var(--font-bn); font-weight:600 }
.status-pending { background:#fff0e4; color:#a05c35 }
.status-submitted { background:#e3f2fa; color:#1a5276 }
.status-graded { background:#e6f4ec; color:#19724a }
.status-rejected { background:#fde8e8; color:#a03030 }
@media(max-width:650px){ .info-grid { grid-template-columns:1fr } .info-block.wide { grid-column:auto } .page-header-row { flex-direction:column; align-items:flex-start } }
</style>