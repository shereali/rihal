<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/exams" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন মার্ক এন্ট্রি</h1>
        <p class="page-subtitle">পরীক্ষা, শিক্ষার্থী ও বিষয় নির্বাচন করে প্রাপ্ত নম্বর সংরক্ষণ করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">পরীক্ষা ও শিক্ষার্থী</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                পরীক্ষা নির্বাচন <span class="required-star">*</span>
              </label>
              <select v-model="form.exam_id" class="form-control" :disabled="loading || exams.length === 0" required>
                <option value="" disabled>পরীক্ষা নির্বাচন করুন</option>
                <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name_bn || e.name_en || e.title_bn }}</option>
              </select>
              <span v-if="exams.length === 0" class="form-hint">কোনো পরীক্ষা নেই — আগে পরীক্ষা তৈরি করুন</span>
            </div>
            <div class="form-group">
              <label class="form-label">
                শিক্ষার্থী নির্বাচন <span class="required-star">*</span>
              </label>
              <select v-model="form.student_id" class="form-control" :disabled="loading || students.length === 0" required>
                <option value="" disabled>ছাত্র নির্বাচন করুন</option>
                <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name_bn || s.user?.name_bn || s.name_en || s.email }}</option>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">বিষয় (ঐচ্ছিক)</label>
              <select v-model="form.subject_id" class="form-control" :disabled="loading">
                <option value="">বিষয় নির্বাচন করুন</option>
                <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name_bn }} ({{ sub.name_en || sub.code || '' }})</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">মূল্যায়নকারী শিক্ষক</label>
              <select v-model="form.graded_by_teacher_id" class="form-control" :disabled="loading">
                <option value="">শিক্ষক নির্বাচন করুন</option>
                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name_bn || t.user?.name_bn || t.name_en || t.employee_id }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">নম্বর ও ফলাফল</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                প্রাপ্ত নম্বর <span class="required-star">*</span>
              </label>
              <input v-model.number="form.marks_obtained" type="number" min="0" max="100" class="form-control" placeholder="০.০০" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">সর্বোচ্চ নম্বর</label>
              <input v-model.number="form.max_marks" type="number" min="1" class="form-control" placeholder="১০০" :disabled="loading" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">মন্তব্য (বাংলা)</label>
            <textarea v-model="form.remarks_bn" rows="3" class="form-control" placeholder="শিক্ষার্থীর পারফরম্যান্স সংক্রান্ত মূল্যায়ন নোট (ঐচ্ছিক)" :disabled="loading"></textarea>
          </div>

          <div class="form-row-2 mt-2">
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_graded" :disabled="loading" />
                <span>মূল্যায়ন সম্পন্ন হয়েছে (Graded)</span>
              </label>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_published_in_result" :disabled="loading" />
                <span>ফলাফলে প্রকাশ করুন (Published)</span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/exams" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.exam_id || !form.student_id">
            <span v-if="loading" class="spinner"></span>
            <span v-else>মার্ক সংরক্ষণ করুন</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import Icon from '~/components/Icon.vue'

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
  max_marks: 100,
  remarks_bn: '',
  is_graded: true,
  is_published_in_result: true,
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadData() {
  try {
    const [e, s, sub, t] = await Promise.all([
      api.get('/exams?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/students?per_page=1000').catch(() => ({ data: { data: [] } })),
      api.get('/subjects?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/teachers?per_page=100').catch(() => ({ data: { data: [] } })),
    ])
    exams.value = e.data.data || []
    students.value = s.data.data || []
    subjects.value = sub.data.data || []
    teachers.value = t.data.data || []
  } catch { /* ignore */ }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const payload: any = {
      exam_id: form.value.exam_id,
      student_id: form.value.student_id,
      marks_obtained: form.value.marks_obtained,
      max_marks: form.value.max_marks || 100,
      is_graded: form.value.is_graded,
      is_published_in_result: form.value.is_published_in_result,
    }
    if (form.value.subject_id) payload.subject_id = form.value.subject_id
    if (form.value.graded_by_teacher_id) payload.graded_by_teacher_id = form.value.graded_by_teacher_id
    if (form.value.remarks_bn) payload.remarks_bn = form.value.remarks_bn
    await api.post('/mark-entries', payload)
    success.value = 'মার্ক সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/exams'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'মার্ক যোগ করা যায়নি'
  } finally {
    loading.value = false
  }
}

if (isAuthenticated.value) onMounted(loadData)
</script>



