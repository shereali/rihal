<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/attendance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন হাজিরা এন্ট্রি</h1>
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
          <label>তারিখ *</label>
          <input v-model="form.date" type="date" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>অবস্থা *</label>
          <select v-model="form.status" :disabled="loading">
            <option value="present">উপস্থিত</option>
            <option value="absent">অনুপস্থিত</option>
            <option value="late">দেরি</option>
            <option value="half">অর্ধদিবস</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>চেক-ইন সময়</label>
          <input v-model="form.check_in_time" type="time" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>চেক-আউট সময়</label>
          <input v-model="form.check_out_time" type="time" :disabled="loading" />
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || !form.student_id || !form.date">
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
  date: new Date().toISOString().slice(0, 10),
  status: 'present',
  check_in_time: '',
  check_out_time: '',
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadStudents() {
  try {
    const s = await api.get('/students?per_page=1000').catch(() => ({ data: { data: [] } }))
    students.value = s.data.data || []
  } catch { /* ignore */ }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const payload: any = {
      student_id: form.value.student_id,
      date: form.value.date,
      status: form.value.status,
      method: 'manual',
    }
    if (form.value.check_in_time) payload.check_in_time = `${form.value.date} ${form.value.check_in_time}`
    if (form.value.check_out_time) payload.check_out_time = `${form.value.date} ${form.value.check_out_time}`
    await api.post('/attendance', payload)
    success.value = 'হাজিরা রেকর্ড সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/attendance'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'হাজিরা যোগ করা যায়নি'
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
.form-group input, .form-group select {
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
