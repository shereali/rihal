<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/students" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন ভর্তি</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="create-form card">
      <div class="form-group">
        <label>ছাত্র *</label>
        <select v-model="form.student_id" :disabled="loading || students.length === 0">
          <option value="" disabled>ছাত্র নির্বাচন করুন</option>
          <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name_bn || s.name_en || s.email }}</option>
        </select>
        <small v-if="students.length === 0" class="text-muted">কোনো ছাত্র নেই</small>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>ক্লাস আইডি *</label>
          <input v-model.number="form.class_id" type="number" placeholder="যেমন: 1" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>সেশন আইডি *</label>
          <input v-model.number="form.session_id" type="number" placeholder="যেমন: 1" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>সেকশন আইডি</label>
          <input v-model.number="form.section_id" type="number" placeholder="ঐচ্ছিক" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>ভর্তির তারিখ</label>
          <input v-model="form.enrollment_date" type="date" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>অবস্থা</label>
          <select v-model="form.status" :disabled="loading">
            <option value="active">সক্রিয়</option>
            <option value="pending">অপেক্ষমাণ</option>
            <option value="completed">সম্পন্ন</option>
            <option value="transferred">স্থানান্তরিত</option>
            <option value="dropped">ছাড়া</option>
          </select>
        </div>
        <div class="form-group">
          <label>ভর্তির ধরণ</label>
          <select v-model="form.admission_type" :disabled="loading">
            <option value="regular">নিয়মিত</option>
            <option value="transfer">স্থানান্তর</option>
            <option value="religious">ধর্মীয়</option>
            <option value="special">বিশেষ</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>মন্তব্য (বাংলা)</label>
        <textarea v-model="form.remarks_bn" rows="3" placeholder="ঐচ্ছিক মন্তব্য" :disabled="loading"></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || !form.student_id || !form.class_id || !form.session_id">
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

const students = ref<any[]>([])
const form = ref({
  student_id: '' as string | number,
  class_id: null as number | null,
  session_id: null as number | null,
  section_id: null as number | null,
  enrollment_date: new Date().toISOString().slice(0, 10),
  status: 'active',
  admission_type: 'regular',
  remarks_bn: '',
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadStudents() {
  try {
    const s = await api.get('/students').catch(() => ({ data: { data: [] } }))
    students.value = s.data.data || []
  } catch { /* ignore */ }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/enrollments', {
      student_id: form.value.student_id,
      class_id: form.value.class_id,
      session_id: form.value.session_id,
      section_id: form.value.section_id || undefined,
      enrollment_date: form.value.enrollment_date || undefined,
      status: form.value.status,
      admission_type: form.value.admission_type,
      remarks_bn: form.value.remarks_bn || undefined,
    })
    success.value = 'ভর্তি সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/students'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ভর্তি যোগ করা যায়নি'
  } finally {
    loading.value = false
  }
}

if (isAuthenticated.value) onMounted(loadStudents)
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
