<template>
  <div class="exam-create">
    <NuxtLink to="/exams" class="btn btn-outline btn-sm" style="margin-bottom: 1rem;"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
    <div class="card"><div class="card-body">
      <h3 class="section-title">নতুন পরীক্ষা তৈরি করুন</h3>
      <form @submit.prevent="saveExam">
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">শিরোনাম (বাংলা) *</label><input v-model="form.title_bn" type="text" class="form-control" required /></div>
          <div class="form-group"><label class="form-label">শিরোনাম (ইংরেজি)</label><input v-model="form.title_en" type="text" class="form-control" /></div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">ধরণ</label>
            <select v-model="form.type" class="form-control"><option value="নিয়মিত">নিয়মিত</option><option value="মাসিক">মাসিক</option><option value="ত্রৈমাসিক">ত্রৈমাসিক</option><option value="বার্ষিক">বার্ষিক</option><option value="মডেল">মডেল</option></select></div>
          <div class="form-group"><label class="form-label">শ্রেণি *</label>
            <select v-model="form.class_id" class="form-control"><option value="">নির্বাচন করুন</option><option v-for="cls in classOptions" :key="cls.id" :value="cls.id">{{ cls.name_bn }}</option></select></div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group"><label class="form-label">শুরুর তারিখ *</label><input v-model="form.start_date" type="date" class="form-control" required /></div>
          <div class="form-group"><label class="form-label">শুরুর সময় *</label><input v-model="form.start_time" type="time" class="form-control" required /></div>
          <div class="form-group"><label class="form-label">শেষ তারিখ *</label><input v-model="form.end_date" type="date" class="form-control" required /></div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group"><label class="form-label">শেষ সময়</label><input v-model="form.end_time" type="time" class="form-control" /></div>
          <div class="form-group"><label class="form-label">মোট নম্বর *</label><input v-model.number="form.total_marks" type="number" class="form-control" min="0" required /></div>
          <div class="form-group"><label class="form-label">পাস নম্বর</label><input v-model.number="form.passing_marks" type="number" class="form-control" min="0" /></div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">সময়কাল (মিনিট)</label><input v-model.number="form.duration_minutes" type="number" class="form-control" min="0" /></div>
          <div class="form-group"><label class="form-label">অবস্থা</label>
            <select v-model="form.status" class="form-control"><option value="scheduled">নির্ধারিত</option><option value="draft">খসখসে</option><option value="cancelled">বাতিল</option></select></div>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে...' : 'সংরক্ষণ করুন' }}</button>
      </form>
    </div></div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const saving = ref(false)
const classOptions = ref<any[]>([])

const form = reactive({
  title_bn: '', title_en: '', type: 'নিয়মিত', class_id: '',
  start_date: '', start_time: '', end_date: '', end_time: '',
  total_marks: null as null | number, passing_marks: null as null | number,
  duration_minutes: null as null | number, status: 'scheduled',
  attendance_required: true, instructions_bn: '', instructions_en: '',
})

async function loadClassOptions() {
  try {
    const res = await api.get('/students?per_page=1000')
    const classes = new Map()
    (res.data.data || []).forEach((s: any) => {
      const key = s.class?.id || s.class_id
      const name = s.class?.name_bn || s.class_name || 'Unknown'
      if (key && !classes.has(key)) classes.set(key, { id: key, name_bn: name })
    })
    classOptions.value = Array.from(classes.values())
  } catch (error) { console.error('Failed to load class options:', error) }
}

async function saveExam() {
  saving.value = true
  try {
    await api.post('/exams', form)
    navigateTo('/exams')
  } catch (error: any) {
    alert('সংরক্ষণে ত্রুটি: ' + (error.response?.data?.message || 'অজানা ত্রুটি'))
  } finally { saving.value = false }
}

if (!isAuthenticated.value && !authLoading.value) navigateTo('/login')
else onMounted(() => { if (isAuthenticated.value) loadClassOptions() })
</script>

<style scoped>
.exam-create { padding: 1.5rem; }
.section-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; }
.form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.form-row-2 { grid-template-columns: repeat(2, 1fr); }
.form-row-3 { grid-template-columns: repeat(3, 1fr); }
.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-label { font-size: 0.875rem; font-weight: 500; color: var(--color-text); }
.form-control { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9375rem; color: var(--color-text); background: var(--color-bg); }
.form-control:focus { outline: none; border-color: var(--color-primary); }
.card { background: var(--color-bg-card); border-radius: var(--radius-md); border: 1px solid var(--color-border-light); }
.card-body { padding: 1.5rem; }
</style>
