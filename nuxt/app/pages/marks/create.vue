<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/exams" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন মার্ক এন্ট্রি</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="create-form card">
      <div class="form-row">
        <div class="form-group">
          <label>পরীক্ষা *</label>
          <select v-model="form.exam_id" :disabled="loading || exams.length === 0" required>
            <option value="" disabled>পরীক্ষা নির্বাচন করুন</option>
            <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name_bn || e.name_en || e.title_bn }}</option>
          </select>
          <small v-if="exams.length === 0" class="text-muted">কোনো পরীক্ষা নেই — আগে পরীক্ষা তৈরি করুন</small>
        </div>
        <div class="form-group">
          <label>ছাত্র *</label>
          <select v-model="form.student_id" :disabled="loading || students.length === 0" required>
            <option value="" disabled>ছাত্র নির্বাচন করুন</option>
            <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name_bn || s.user?.name_bn || s.name_en || s.email }}</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>বিষয়</label>
          <select v-model="form.subject_id" :disabled="loading">
            <option value="">বিষয় নির্বাচন করুন (ঐচ্ছিক)</option>
            <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name_bn }} ({{ sub.name_en || sub.code || '' }})</option>
          </select>
        </div>
        <div class="form-group">
          <label>মূল্যায়নকারী শিক্ষক</label>
          <select v-model="form.graded_by_teacher_id" :disabled="loading">
            <option value="">শিক্ষক নির্বাচন করুন (ঐচ্ছিক)</option>
            <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name_bn || t.user?.name_bn || t.name_en || t.employee_id }}</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>প্রাপ্ত নম্বর</label>
          <input v-model.number="form.marks_obtained" type="number" min="0" max="100" placeholder="0" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>সর্বোচ্চ নম্বর</label>
          <input v-model.number="form.max_marks" type="number" min="0" placeholder="100" :disabled="loading" />
        </div>
      </div>

      <div class="form-group">
        <label>মন্তব্য (বাংলা)</label>
        <textarea v-model="form.remarks_bn" rows="3" placeholder="ঐচ্ছিক মন্তব্য" :disabled="loading"></textarea>
      </div>

      <label class="checkbox-label">
        <input type="checkbox" v-model="form.is_graded" :disabled="loading" />
        মূল্যায়ন করা হয়েছে
      </label>

      <label class="checkbox-label">
        <input type="checkbox" v-model="form.is_published_in_result" :disabled="loading" />
        ফলাফলে প্রকাশ করুন
      </label>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || !form.exam_id || !form.student_id">
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

const exams = ref<any[]>([])
const students = ref<any[]>([])
const subjects = ref<any[]>([])
const teachers = ref<any[]>([])

const form = ref({
  exam_id: '' as string | number,
  student_id: '' as string | number,
  subject_id: '' as string | number,
  graded_by_teacher_id: '' as string | number,
  marks_obtained: null as number | null,
  max_marks: 100 as number | null,
  remarks_bn: '',
  is_graded: false,
  is_published_in_result: false,
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadSelects() {
  try {
    const [e, s, sub, t] = await Promise.all([
      api.get('/exams').catch(() => ({ data: { data: [] } })),
      api.get('/students?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/academic/subjects').catch(() => ({ data: { data: [] } })),
      api.get('/teachers').catch(() => ({ data: { data: [] } })),
    ])
    exams.value = e.data?.data?.data || e.data?.data || []
    students.value = s.data?.data?.data || s.data?.data || []
    subjects.value = sub.data?.data || []
    teachers.value = t.data?.data?.data || t.data?.data || []
  } catch (err) {
    console.error('Failed to load selects:', err)
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/mark-entries', {
      exam_id: form.value.exam_id,
      student_id: form.value.student_id,
      subject_id: form.value.subject_id || undefined,
      graded_by_teacher_id: form.value.graded_by_teacher_id || undefined,
      marks_obtained: form.value.marks_obtained ?? undefined,
      max_marks: form.value.max_marks ?? undefined,
      remarks_bn: form.value.remarks_bn || undefined,
      is_graded: form.value.is_graded,
      is_published_in_result: form.value.is_published_in_result,
    })
    success.value = 'মার্ক এন্ট্রি সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/exams'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'মার্ক এন্ট্রি যোগ করা যায়নি'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (isAuthenticated.value) {
    loadSelects()
  }
})
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
.form-group input, .form-group select, .form-group textarea {
  padding: 0.7rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 1rem;
  font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg);
}
.checkbox-label { display: flex; align-items: center; gap: 0.5rem; font-family: 'Noto Sans Bengali', sans-serif; font-size: 0.9rem; }
.form-actions { margin-top: 0.5rem; display: flex; justify-content: flex-end; }
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
