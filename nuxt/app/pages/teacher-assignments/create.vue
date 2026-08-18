<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/teacher-assignments" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন শিক্ষক বরাদ্দ</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="create-form card">
      <div class="form-group">
        <label>শিক্ষক *</label>
        <select v-model="form.teacher_id" :disabled="loading || teachers.length === 0">
          <option value="" disabled>শিক্ষক নির্বাচন করুন</option>
          <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name_bn || t.name_en }}</option>
        </select>
        <small v-if="teachers.length === 0" class="text-muted">কোনো শিক্ষক নেই</small>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>শ্রেণি *</label>
          <select v-model="form.class_id" :disabled="loading || classes.length === 0" @change="loadSections">
            <option value="" disabled>শ্রেণি নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>সেকশন</label>
          <select v-model="form.section_id" :disabled="loading">
            <option value="">সেকশন ছাড়া</option>
            <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name_bn }}</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>বিষয়</label>
          <select v-model="form.subject_id" :disabled="loading || subjects.length === 0">
            <option value="">বিষয় ছাড়া</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name_bn }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>অবস্থা</label>
          <select v-model="form.status" :disabled="loading">
            <option value="active">সক্রিয়</option>
            <option value="inactive">নিষ্ক্রিয়</option>
            <option value="paused">স্থগিত</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>বিষয়বস্তু (বাংলা)</label>
        <input v-model="form.topic_bn" type="text" placeholder="যেমন: আল-কুরআনের প্রথম পারা" :disabled="loading" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>শুরুর তারিখ</label>
          <input v-model="form.start_date" type="date" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>শেষ তারিখ</label>
          <input v-model="form.end_date" type="date" :disabled="loading" />
        </div>
      </div>
      <label class="checkbox-label">
        <input type="checkbox" v-model="form.is_active" :disabled="loading" />
        সক্রিয়
      </label>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || !form.teacher_id || !form.class_id">
          <span v-if="loading" class="spinner"></span>
          <span v-else>সংরক্ষণ করুন</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated } = useAuth()

const teachers = ref<any[]>([])
const classes = ref<any[]>([])
const sections = ref<any[]>([])
const subjects = ref<any[]>([])
const form = ref({
  teacher_id: '' as string | number,
  class_id: '' as string | number,
  section_id: '' as string | number,
  subject_id: '' as string | number,
  status: 'active',
  topic_bn: '',
  start_date: '',
  end_date: '',
  is_active: true,
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadTeachers() {
  try { const r = await api.get('/teachers'); teachers.value = r.data?.data?.data || r.data?.data || [] } catch {}
}
async function loadClasses() {
  try { const r = await api.get('/academic/classes'); classes.value = r.data?.data || [] } catch {}
}
async function loadSections() {
  sections.value = []
  form.value.section_id = ''
  if (!form.value.class_id) return
  try { const r = await api.get(`/academic/sections?class_id=${form.value.class_id}`); sections.value = r.data?.data || [] } catch {}
}
async function loadSubjects() {
  try { const r = await api.get('/academic/subjects'); subjects.value = r.data?.data || [] } catch {}
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/teacher-assignments', {
      teacher_id: form.value.teacher_id,
      class_id: form.value.class_id,
      section_id: form.value.section_id || undefined,
      subject_id: form.value.subject_id || undefined,
      status: form.value.status,
      topic_bn: form.value.topic_bn || undefined,
      start_date: form.value.start_date || undefined,
      end_date: form.value.end_date || undefined,
      is_active: form.value.is_active,
    })
    success.value = 'শিক্ষক বরাদ্দ সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/teacher-assignments'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'বরাদ্দ যোগ করা যায়নি'
  } finally {
    loading.value = false
  }
}

if (isAuthenticated.value) onMounted(async () => { await Promise.all([loadTeachers(), loadClasses(), loadSubjects()]) })
</script>

<style scoped>
.create-page { max-width: 640px; margin: 0 auto; padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.header-left h1 { margin: 0.5rem 0 0; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.create-form { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group input, .form-group select {
  padding: 0.7rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 1rem;
  font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg);
}
.checkbox-label { display: flex; align-items: center; gap: 0.5rem; font-family: 'Noto Sans Bengali', sans-serif; font-size: 0.9rem; }
.form-actions { margin-top: 0.5rem; }
.btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.spinner { width: 18px; height: 18px; border: 2px solid var(--color-text-on-primary); border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 0.75rem 1rem; border-radius: 8px; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: #fce4e4; color: var(--color-error); }
.alert-success { background: #e8f5e9; color: var(--color-success); }
.text-muted { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
small { font-size: 0.8rem; }
</style>
