<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">একাডেমিক কার্যক্রম</span>
        <h1>পাঠ পরিকল্পনা</h1>
        <p>শিক্ষকদের দৈনিক পাঠ, উদ্দেশ্য ও শ্রেণি কার্যক্রম সাজান</p>
      </div>
      <button class="btn btn-primary" @click="showForm = !showForm">
        <icon name="plus" /> নতুন পাঠ পরিকল্পনা
      </button>
    </div>

    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" />
        <input v-model="search" placeholder="পাঠের বিষয় খুঁজুন..." @keyup.enter="load" />
      </div>
      <button class="btn btn-outline btn-sm" @click="load">
        <icon name="refresh" /> রিফ্রেশ
      </button>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createPlan">
      <div class="form-heading">
        <div>
          <h2>নতুন পাঠ পরিকল্পনা</h2>
          <p>আজকের পাঠের মূল তথ্য লিখুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-grid">
        <div class="form-group wide">
          <label>পাঠের বিষয় *</label>
          <input v-model="form.topic_bn" class="form-control" required placeholder="যেমন: ইসলামের পাঁচ স্তম্ভ" />
        </div>
        <div class="form-group">
          <label>শ্রেণির তারিখ</label>
          <input v-model="form.class_date" type="date" class="form-control" />
        </div>
        <div class="form-group wide">
          <label>একাডেমিক সেশন</label>
          <input v-model="form.session_id" class="form-control" placeholder="যেমন: ২০২৫-২০২৬" />
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিকল্পনা সংরক্ষণ করুন' }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!plans.length" class="empty-card">
      <div class="empty-icon"><icon name="academic" /></div>
      <h3>এখনও কোনো পাঠ পরিকল্পনা নেই</h3>
      <p>প্রথম পাঠ পরিকল্পনা তৈরি করুন</p>
    </div>

    <div v-else class="plan-table">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>পাঠের বিষয়</th>
              <th>শ্রেণির তারিখ</th>
              <th>সেশন</th>
              <th>বর্তমান অবস্থা</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="plan in plans" :key="plan.id">
              <td class="topic-cell">
                <div class="topic-block">
                  <span class="topic-title">{{ plan.topic_bn || plan.topic_en }}</span>
                  <p v-if="plan.content_bn" class="topic-content">{{ plan.content_bn }}</p>
                  <p v-else class="text-muted">পাঠের বিবরণ নেই</p>
                  <div v-if="plan.objectives?.length" class="objectives-block">
                    <span class="objectives-label">শিক্ষার উদ্দেশ্য:</span>
                    <ul class="objectives-list">
                      <li v-for="obj in plan.objectives" :key="obj.id" class="objective-item">
                        - {{ obj }}
                      </li>
                    </ul>
                  </div>
                  <p v-if="!plan.objectives?.length" class="text-muted">উদ্দেশ্য যোগ করা হয়নি</p>
                </div>
              </td>
              <td class="text-center">{{ plan.class_date ? formatDate(plan.class_date) : '-' }}</td>
              <td class="text-center">{{ plan.session_id || '-' }}</td>
              <td class="text-center">
                <span class="status-badge" :class="plan.status">
                  {{ statusLabel(plan.status) }}
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-ghost btn-sm" @click="editPlan(plan.id)">
                  <icon name="pencil" />
                </button>
                <button class="btn btn-ghost btn-sm text-danger" @click="removePlan(plan.id)">
                  <icon name="delete" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const plans = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const search = ref('')

interface PlanForm {
  topic_bn: string; content_bn: string; objectives_text: string
  class_date: string; session_id: string; status: string
}

const form = reactive<PlanForm>({
  topic_bn: '', content_bn: '', objectives_text: '',
  class_date: '', session_id: '', status: 'draft',
})

async function load() {
  loading.value = true
  try {
    const q = search.value ? `?search=${encodeURIComponent(search.value)}` : ''
    const r = await api.get(`/lesson-plans${q}`)
    plans.value = r.data?.data?.data || r.data?.data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function createPlan() {
  saving.value = true
  error.value = ''
  try {
    const payload: any = { ...form, objectives: form.objectives_text ? [form.objectives_text] : [] }
    delete (payload as any).objectives_text
    await api.post('/lesson-plans', payload)
    form.topic_bn = ''; form.content_bn = ''; form.objectives_text = ''
    form.class_date = ''; form.session_id = ''; form.status = 'draft'
    showForm.value = false
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'পাঠ পরিকল্পনা তৈরি করা যায়নি'
  } finally { saving.value = false }
}

async function removePlan(id: number) {
  if (!confirm('এই পাঠ পরিকল্পনা মুছে ফেলবেন?')) return
  try {
    await api.delete(`/lesson-plans/${id}`)
    await load()
  } catch (e) { console.error(e) }
}

function editPlan(id: number) {
  window.location.href = `/lesson-plans/${id}`
}

function formatDate(v: string) {
  return v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    draft: 'প্রাথমিক',
    published: 'প্রকাশিত',
    archived: 'বাতিল',
  }
  return map[s] || s || '-'
}

onMounted(load)
</script>

<style scoped>
.module-page { max-width: 1200px; margin: 0 auto; padding-bottom: 2rem }
.page-header-row { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin-bottom:1.4rem }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn) }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.65rem var(--font-bn) }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn) }
.toolbar { display:flex; gap:.7rem; padding:.7rem; margin-bottom:1rem }
.search-box { display:flex; align-items:center; gap:.5rem; flex:1; padding:0 .75rem; background:var(--color-bg-muted); border-radius:10px; min-width:200px }
.search-box input { width:100%; padding:.65rem 0; border:0; outline:0; background:transparent; font:.86rem var(--font-bn) }
.create-panel { padding:1.2rem; margin-bottom:1rem; border:1px solid var(--color-primary-100) }
.form-heading { display:flex; justify-content:space-between; margin-bottom:1rem }
.form-heading h2 { font:700 1rem var(--font-bn) }
.close-btn { border:0; background:transparent; font-size:1.5rem; color:var(--color-text-muted); cursor:pointer }
.form-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.7rem }
.form-group label { display:block; margin-bottom:.3rem; font:600 .78rem var(--font-bn) }
.form-group.wide { grid-column:span 2 }
.form-actions { display:flex; gap:.6rem; margin-top:1rem }
.plan-table { background:#fff; border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.table-responsive { overflow-x:auto }
.table { width:100%; border-collapse:collapse; font:.85rem var(--font-bn) }
.table th { background:rgba(0,0,0,0.03); padding:.7rem 1rem; text-align:left; font:600 .75rem var(--font-bn); color:var(--color-text-muted); border-bottom:1px solid var(--color-border-light); white-space:nowrap }
.table td { padding:.6rem 1rem; border-bottom:1px solid var(--color-border-light); vertical-align:top }
.table tr:last-child td { border-bottom:0 }
.table tr:hover td { background:#fafbfc }
.text-center { text-align:center }
.topic-cell { max-width:450px }
.topic-title { display:block; font:700 .9rem var(--font-bn); color:var(--color-text); margin-bottom:.3rem }
.topic-content { margin:0; font:.8rem var(--font-bn); color:var(--color-text-light); line-height:1.5 }
.objectives-block { margin-top:.5rem }
.objectives-label { display:block; font:600 .7rem var(--font-bn); color:var(--color-text-muted); margin-bottom:.3rem }
.objectives-list { list-style:none; padding:0; margin:0; font:.72rem var(--font-bn); color:var(--color-text-light) }
.objective-item { padding:.1rem 0; }
.text-muted { color:var(--color-text-muted) }
.status-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.15rem .5rem; border-radius:99px; font:.65rem var(--font-bn); font-weight:600 }
.status-draft { background:#f0f0f0; color:#666 }
.status-published { background:#e6f4ec; color:#19724a }
.status-archived { background:#fde8e8; color:#a03030 }
.text-danger { color:#a03030 }
@media(max-width:700px){ .page-header-row { align-items:flex-start; flex-direction:column } .toolbar { flex-wrap:wrap } .search-box { min-width:100% } .form-grid { grid-template-columns:1fr } .form-group.wide { grid-column:auto } }
</style>