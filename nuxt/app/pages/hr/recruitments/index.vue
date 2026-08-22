<template>
  <div class="page-wrapper">
    <div class="page-header">
      <div class="page-header-row">
        <div class="page-title-block">
          <div class="breadcrumb">
            <NuxtLink to="/hr">স্টাফ ও কর্মী</NuxtLink>
            <span class="sep">/</span>
            <span class="current">নিয়োগ</span>
          </div>
        </div>
        <button class="btn btn-primary" @click="showForm = !showForm">
          <icon name="plus" /> নতুন নিয়োগ বিজ্ঞপ্তি
        </button>
      </div>
      <h1>নিয়োগ বিজ্ঞপ্তি তালিকা</h1>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createRecruitment">
      <div class="form-heading">
        <div><h2>নতুন নিয়োগ বিজ্ঞপ্তি প্রকাশ</h2><p>পদের তথ্য, যোগ্যতা ও সময়সীমা উল্লেখ করুন</p></div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-row">
        <div class="form-group"><label>পদের শিরোনাম *</label><input v-model="formData.post_title_bn" class="form-control" required placeholder="যেমন: সহায়ক শিক্ষক (আরবি)" /></div>
        <div class="form-group"><label>বিভাগ</label><input v-model="formData.department" class="form-control" placeholder="যেমন: একাডেমিক বিভাগ" /></div>
      </div>
      <div class="form-group wide"><label>পদের বিবরণ</label><textarea v-model="formData.description_bn" class="form-control" rows="3" placeholder="দায়িত্ব, প্রয়োজনীয় দক্ষতা ইত্যাদি..."></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>যোগ্যতা</label><input v-model="formData.qualifications_bn" class="form-control" placeholder="যেমন: স্নাতক অথবা সমমান" /></div>
        <div class="form-group"><label>অভিজ্ঞতা</label><input v-model="formData.experience_bn" class="form-control" placeholder="যেমন: ২ বছর বা তদূরূপ" /></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>সর্বোচ্চ বেতন (টাকা)</label><input v-model.number="formData.salary_max" type="number" class="form-control" min="0" step="0.01" placeholder="০" /></div>
        <div class="form-group"><label>শেষ তারিখ</label><input v-model="formData.deadline" type="date" class="form-control" /></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>অবস্থা</label><select v-model="formData.status" class="form-control"><option value="open">চাহিদা খোলা</option><option value="closed">বন্ধ</option><option value="filled">পূরণ হয়েছে</option></select></div>
        <div class="form-group"><label>ফিরিশি</label><select v-model="formData.fiscal_year_id" class="form-control"><option value="">নির্বাচন করুন</option><option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.fiscal_year_bn || fy.fiscal_year_en }}</option></select></div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে...' : 'বিজ্ঞপ্তি প্রকাশ করুন' }}</button>
        <button class="btn btn-ghost" type="button" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-overlay"><div class="spinner" /></div>
    <div v-else-if="!recruitments.length" class="empty-card">
      <div class="empty-icon"><icon name="users" /></div>
      <h3>কোনো নিয়োগ বিজ্ঞপ্তি নেই</h3><p>নতুন নিয়োগ বিজ্ঞপ্তি প্রকাশ করে শুরু করুন</p>
    </div>
    <div v-else class="recruitments-grid">
      <article v-for="rec in recruitments" :key="rec.id" class="recruitment-card card">
        <div class="card-body">
          <div class="rec-header">
            <h3 class="rec-title">{{ rec.post_title_bn || rec.post_title_en }}</h3>
            <span class="rec-status-badge" :class="rec.status">{{ formatStatus(rec.status) }}</span>
          </div>
          <p v-if="rec.department" class="rec-department">{{ rec.department }}</p>
          <div class="rec-meta">
            <div class="meta-item" v-if="rec.qualifications_bn"><icon name="academic" /><span>{{ rec.qualifications_bn }}</span></div>
            <div class="meta-item" v-if="rec.experience_bn"><icon name="clock" /><span>{{ rec.experience_bn }}</span></div>
            <div class="meta-item" v-if="rec.salary_max"><icon name="money" /><span>{{ rec.salary_max.toLocaleString('bn-BD') }} টাকা (সর্বোচ্চ)</span></div>
            <div class="meta-item" v-if="rec.deadline"><icon name="calendar" /><span>সর্বোচ্চ: {{ formatDate(rec.deadline) }}</span></div>
          </div>
          <div class="rec-footer">
            <span class="rec-applicant-count">{{ rec.application_count || 0 }} আবেদন</span>
            <NuxtLink :to="`/hr/recruitments/${rec.id}/applications`" class="view-link">আবেদন দেখুন <icon name="arrow-right" /></NuxtLink>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const recruitments = ref<any[]>([])
const fiscalYears = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')

interface RecruitmentFormData {
  post_title_bn: string; post_title_en: string; department: string
  description_bn: string; qualifications_bn: string; experience_bn: string
  salary_max: number; deadline: string; status: string; fiscal_year_id: number
}

const formData = reactive<RecruitmentFormData>({
  post_title_bn: '', post_title_en: '', department: '',
  description_bn: '', qualifications_bn: '', experience_bn: '',
  salary_max: 0, deadline: '', status: 'open', fiscal_year_id: 0,
})

async function loadData() {
  loading.value = true
  try {
    const [recRes, fyRes] = await Promise.all([
      api.get('/hr/recruitments'),
      api.get('/academic/fiscal-years'),
    ])
    recruitments.value = recRes.data?.data?.data || recRes.data?.data || []
    fiscalYears.value = fyRes.data?.data?.data || fyRes.data?.data || []
  } catch (e) { console.error('Failed to load:', e) }
  finally { loading.value = false }
}

async function createRecruitment() {
  saving.value = true; error.value = ''
  try {
    await api.post('/hr/recruitments', { ...formData, salary_max: formData.salary_max || undefined })
    showForm.value = false
    formData.post_title_bn = ''; formData.post_title_en = ''; formData.department = ''
    formData.description_bn = ''; formData.qualifications_bn = ''; formData.experience_bn = ''
    formData.salary_max = 0; formData.deadline = ''; formData.status = 'open'; formData.fiscal_year_id = 0
    await loadData()
  } catch (e: any) { error.value = e?.response?.data?.message || 'নিয়োগ বিজ্ঞপ্তি তৈরি করা যায়নি' }
  finally { saving.value = false }
}

function formatDate(date: string) { return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) }
function formatStatus(status: string) { const map: Record<string,string> = { open: 'চাহিদা খোলা', closed: 'বন্ধ', filled: 'পূরণ হয়েছে' }; return map[status] || status || '-' }

onMounted(loadData)
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding-bottom: 2rem; }
.page-header { margin-bottom: 1.2rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem; }
.breadcrumb { display: flex; align-items: center; gap: 0.3rem; font-size: 0.78rem; color: var(--color-text-muted); }
.breadcrumb .sep { color: var(--color-text-muted); }
.breadcrumb .current { color: var(--color-text); font-weight: 500; }
.page-header h1 { font-size: 1.4rem; color: var(--color-primary); margin: 0; font-family: var(--font-bn); }
.create-panel { margin-bottom: 1.2rem; border: 1px solid var(--color-primary-100); }
.form-heading { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; }
.form-heading h2 { font-size: 1.05rem; font-family: var(--font-bn); margin: 0; }
.close-btn { background: none; border: none; font-size: 1.5rem; color: var(--color-text-muted); cursor: pointer; padding: 0; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 0.7rem; }
.form-group { display: flex; flex-direction: column; gap: 0.3rem; }
.form-group.wide { grid-column: span 2; }
.form-group label { font-size: 0.78rem; font-weight: 600; color: var(--color-text-muted); font-family: var(--font-bn); text-transform: uppercase; letter-spacing: 0.02em; }
.form-control { padding: 0.6rem 0.8rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 0.85rem; font-family: var(--font-bn); background: var(--color-bg); color: var(--color-text); outline: none; }
.form-control:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary-100); }
.form-actions { display: flex; gap: 0.6rem; margin-top: 0.6rem; justify-content: flex-end; }
.alert { padding: 0.6rem 0.9rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 0.8rem; }
.alert-error { background: #fde8e8; color: #a03030; border: 1px solid #f5c6c6; }
.recruitments-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
.recruitment-card { transition: transform 0.15s, box-shadow 0.15s; }
.recruitment-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.card-body { padding: 1.1rem; display: flex; flex-direction: column; gap: 0.4rem; }
.rec-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.2rem; }
.rec-title { font-size: 1rem; font-weight: 700; color: var(--color-text); margin: 0; font-family: var(--font-bn); }
.rec-status-badge { padding: 0.2rem 0.55rem; border-radius: 99px; font-size: 0.65rem; font-weight: 600; white-space: nowrap; }
.status-open { background: #e6f4ec; color: #19724a; }
.status-closed { background: #fde8e8; color: #a03030; }
.status-filled { background: #e3f2fa; color: #1a5276; }
.rec-department { font-size: 0.75rem; color: var(--color-text-muted); margin: 0; font-family: var(--font-bn); }
.rec-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.4rem; }
.meta-item { display: inline-flex; align-items: center; gap: 0.25rem; }
.meta-item icon { width: 12px; height: 12px; }
.rec-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--color-border-light); }
.rec-applicant-count { font-size: 0.72rem; color: var(--color-text-muted); font-family: var(--font-bn); }
.view-link { color: var(--color-primary); text-decoration: none; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 600; }
.empty-card { text-align: center; padding: 3rem 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.6rem; }
.empty-icon { width: 48px; height: 48px; color: var(--color-text-muted); margin-bottom: 0.3rem; }
.empty-card h3 { font-size: 1rem; color: var(--color-text); margin: 0; font-family: var(--font-bn); }
.empty-card p { font-size: 0.82rem; color: var(--color-text-muted); margin: 0; font-family: var(--font-bn); }
.loading-overlay { display: flex; justify-content: center; padding: 3rem 0; }
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } .recruitments-grid { grid-template-columns: 1fr; } }
</style>