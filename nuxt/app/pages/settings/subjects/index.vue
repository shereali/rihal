<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <h1>একাডেমিক বিষয়</h1>
        <p class="page-subtitle">বিষয় তৈরি, পাঠ্যক্রম, বই ও শিক্ষক নিয়োগ</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="showForm = !showForm">
          <Icon name="plus" class="btn-icon" /> নতুন বিষয়
        </button>
      </div>
    </div>

    <!-- Class Filter -->
    <div class="filter-bar card">
      <div class="filter-row">
        <div class="search-box">
          <Icon name="search" />
          <input v-model="search" placeholder="বিষয় খুঁজুন..." @keyup.enter="load" />
        </div>
        <select v-model="classFilter" class="form-control" @change="load">
          <option value="">সব শ্রেণি</option>
          <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.class_name_en || c.class_name }}</option>
        </select>
        <button class="btn btn-outline btn-sm" @click="load">
          <Icon name="refresh" class="btn-icon" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>বিষয়ের তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!subjects.length" class="empty-card">
      <div class="empty-icon"><Icon name="academic" /></div>
      <h3>এখনও কোনো বিষয় নেই</h3>
      <p>একাডেমিক বিষয় তৈরি করে শুরু করুন</p>
    </div>

    <div v-else class="subjects-table">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>বিষয়ের নাম</th>
              <th>বাংলা নাম</th>
              <th>শ্রেণি</th>
              <th>কোড</th>
              <th>মোট নম্বর</th>
              <th>অবস্থা</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in subjects" :key="s.id">
              <td class="subject-name"><strong>{{ s.subject_name_en || s.subject_name }}</strong></td>
              <td class="text-muted">{{ s.subject_bn || '-' }}</td>
              <td>{{ s.class?.class_name_en || s.class?.class_name || '-' }}</td>
              <td class="text-center">
                <span class="code-badge">{{ s.subject_code || '-' }}</span>
              </td>
              <td class="text-center">{{ s.total_marks || '-' }}</td>
              <td>
                <span class="status-badge" :class="statusClass(s.status)">
                  {{ statusLabel(s.status) }}
                </span>
              </td>
              <td class="text-center">
                <NuxtLink :to="`/settings/subjects/${s.id}`" class="btn btn-ghost btn-sm">
                  <Icon name="eye" class="btn-icon" /> দেখুন
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination" v-if="meta">
        <span class="text-muted">{{ (meta.current_page - 1) * meta.per_page + 1 }}–{{ Math.min(meta.current_page * meta.per_page, meta.total) }} / {{ meta.total }}</span>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" v-if="showForm" @click.self="showForm = false">
      <div class="modal card">
        <div class="modal-header">
          <h2>{{ isEditing ? 'বিষয় সম্পাদনা' : 'নতুন বিষয় তৈরি' }}</h2>
          <button class="close-btn" @click="showForm = false">×</button>
        </div>
        <form @submit.prevent="saveSubject" class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label>শ্রেণি *</label>
              <select v-model="form.class_id" class="form-control">
                <option value="">শ্রেণি নির্বাচন করুন</option>
                <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.class_name_en || c.class_name }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>বিষয়ের নাম (ইংরেজি) *</label>
              <input v-model="form.subject_name" class="form-control" placeholder="Mathematics" />
            </div>
            <div class="form-group">
              <label>বিষয়ের নাম (বাংলা)</label>
              <input v-model="form.subject_bn" class="form-control" placeholder="গণিত" />
            </div>
            <div class="form-group">
              <label>বিষয়ের কোড</label>
              <input v-model="form.subject_code" class="form-control" placeholder="MATH-9" />
            </div>
            <div class="form-group">
              <label>মোট নম্বর</label>
              <input v-model.number="form.total_marks" type="number" class="form-control" min="0" placeholder="100" />
            </div>
            <div class="form-group">
              <label>অবস্থা</label>
              <select v-model="form.status" class="form-control">
                <option value="active">সক্রিয়</option>
                <option value="inactive">নিষ্ক্রিয়</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (isEditing ? 'আপডেট করুন' : 'তৈরি করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const api = useApiClient()
const subjects = ref<any[]>([])
const classOptions = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const isEditing = ref(false)
const currentId = ref<number | null>(null)
const error = ref('')
const search = ref('')
const classFilter = ref('')

const form = reactive({
  class_id: '',
  subject_name: '',
  subject_bn: '',
  subject_code: '',
  total_marks: 100,
  status: 'active',
})

const meta = ref<any>(null)

async function load() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (search.value) params.set('search', search.value)
    if (classFilter.value) params.set('class_id', classFilter.value)
    params.set('per_page', '50')
    const res = await api.get(`/settings/subjects?${params}`)
    subjects.value = res.data?.data?.data || []
    meta.value = res.data?.data?.meta

    const classRes = await api.get('/settings/classes?per_page=50')
    classOptions.value = classRes.data?.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function saveSubject() {
  saving.value = true
  error.value = ''
  try {
    const payload: any = {
      class_id: parseInt(form.class_id),
      subject_name: form.subject_name,
      subject_bn: form.subject_bn,
      subject_code: form.subject_code,
      status: form.status,
    }
    if (form.total_marks > 0) payload.total_marks = form.total_marks

    if (isEditing.value && currentId.value) {
      await api.put(`/settings/subjects/${currentId.value}`, payload)
    } else {
      await api.post('/settings/subjects', payload)
    }
    showForm.value = false
    resetForm()
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

function resetForm() {
  form.class_id = ''
  form.subject_name = ''
  form.subject_bn = ''
  form.subject_code = ''
  form.total_marks = 100
  form.status = 'active'
  currentId.value = null
  isEditing.value = false
}

function statusClass(s: string) {
  return s === 'active' ? 'status-active' : 'status-inactive'
}

function statusLabel(s: string) {
  return s === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়'
}

onMounted(load)
</script>

<style scoped>
.page-wrapper { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.5rem; }
.btn { padding: 0.55rem 1.1rem; border-radius: 10px; font-family: var(--font-bn); font-weight: 600; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; border: 1px solid var(--color-border); background: white; color: var(--color-text); transition: all 0.15s ease; }
.btn:hover { background: var(--color-bg-muted); }
.btn-primary { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.btn-primary:hover { background: var(--color-primary-dark); }
.btn-ghost { background: transparent; border-color: var(--color-border-light); color: var(--color-text); }
.btn-icon { width: 15px; height: 15px; }
.btn-sm { padding: 0.35rem 0.7rem; font-size: 0.75rem; }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.filter-bar { padding: 0.7rem 1rem; margin-bottom: 1rem; border: 1px solid var(--color-border-light); border-radius: 12px; }
.filter-row { display: flex; gap: 0.7rem; align-items: center; flex-wrap: wrap; }
.search-box { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: var(--color-bg-muted); border-radius: 10px; max-width: 220px; }
.search-box input { width: 100%; padding: 0.4rem 0; border: 0; outline: 0; background: transparent; font-family: var(--font-bn); font-size: 0.82rem; }
.form-control { padding: 0.55rem 0.7rem; border: 1px solid var(--color-border); border-radius: 8px; font-family: var(--font-bn); font-size: 0.82rem; outline: none; }
.loading-overlay { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem; gap: 1rem; }
.spinner { width: 36px; height: 36px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-card { background: white; border: 1px solid var(--color-border-light); border-radius: 14px; padding: 3rem; text-align: center; }
.empty-icon { width: 56px; height: 56px; color: var(--color-primary-400); margin: 0 auto 1rem; }
.empty-card h3 { font-family: var(--font-bn); font-size: 1.1rem; color: var(--color-text); margin: 0 0 0.3rem; }
.empty-card p { font-family: var(--font-bn); font-size: 0.82rem; color: var(--color-text-muted); margin: 0; }
.subjects-table { background: white; border: 1px solid var(--color-border-light); border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-family: var(--font-bn); font-size: 0.82rem; }
.data-table th { background: var(--color-bg-muted); padding: 0.7rem 1rem; text-align: left; font-weight: 600; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-light); white-space: nowrap; }
.data-table td { padding: 0.6rem 1rem; border-bottom: 1px solid var(--color-border-light); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: 0; }
.data-table tr:hover td { background: var(--color-bg-muted); }
.text-center { text-align: center; }
.text-muted { color: var(--color-text-muted); }
.subject-name { font-weight: 600; color: var(--color-text); }
.code-badge { display: inline-flex; align-items: center; padding: 0.15rem 0.5rem; background: var(--color-bg-muted); border-radius: 6px; font-size: 0.65rem; font-weight: 600; color: var(--color-text-muted); }
.status-badge { display: inline-flex; align-items: center; padding: 0.15rem 0.5rem; border-radius: 99px; font-size: 0.65rem; font-weight: 600; }
.status-active { background: #e6f4ec; color: #19724a; }
.status-inactive { background: #fde8e8; color: #a03030; }
.pagination { padding: 0.7rem 1rem; background: var(--color-bg-muted); display: flex; align-items: center; font-size: 0.78rem; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.2rem; border-bottom: 1px solid var(--color-border-light); }
.modal-header h2 { font-family: var(--font-bn); font-size: 1.1rem; margin: 0; }
.close-btn { border: 0; background: transparent; font-size: 1.5rem; color: var(--color-text-muted); cursor: pointer; }
.modal-body { padding: 1.2rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group label { font-family: var(--font-bn); font-size: 0.78rem; font-weight: 600; color: var(--color-text); }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 1rem; padding-top: 0.8rem; border-top: 1px solid var(--color-border-light); }
</style>