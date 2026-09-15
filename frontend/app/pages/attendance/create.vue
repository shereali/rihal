<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/attendance" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন হাজিরা এন্ট্রি</h1>
        <p class="page-subtitle">শিক্ষার্থীর দৈনিক হাজিরা ও সময় সংরক্ষণ করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label class="form-label">
            ছাত্র নির্বাচন <span class="required-star">*</span>
          </label>
          <select v-model="form.student_id" class="form-control" :disabled="loading || students.length === 0">
            <option value="" disabled>ছাত্র নির্বাচন করুন</option>
            <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name_bn || s.name_en || s.email }}</option>
          </select>
          <span v-if="students.length === 0" class="form-hint">কোনো ছাত্র নেই — আগে ছাত্র যোগ করুন</span>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">
              তারিখ <span class="required-star">*</span>
            </label>
            <input v-model="form.date" type="date" class="form-control" :disabled="loading" required />
          </div>
          <div class="form-group">
            <label class="form-label">
              অবস্থা <span class="required-star">*</span>
            </label>
            <select v-model="form.status" class="form-control" :disabled="loading">
              <option value="present">উপস্থিত (Present)</option>
              <option value="absent">অনুপস্থিত (Absent)</option>
              <option value="late">দেরি (Late)</option>
              <option value="half">অর্ধদিবস (Half Day)</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">চেক-ইন সময়</label>
            <input v-model="form.check_in_time" type="time" class="form-control" :disabled="loading" />
            <span class="form-hint">প্রবেশের সময় (ঐচ্ছিক)</span>
          </div>
          <div class="form-group">
            <label class="form-label">চেক-আউট সময়</label>
            <input v-model="form.check_out_time" type="time" class="form-control" :disabled="loading" />
            <span class="form-hint">প্রস্থানের সময় (ঐচ্ছিক)</span>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/attendance" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.student_id || !form.date">
            <span v-if="loading" class="spinner"></span>
            <span v-else>হাজিরা সংরক্ষণ করুন</span>
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

