<template>
  <div class="student-create">
    <NuxtLink to="/students" class="btn btn-outline btn-sm" style="margin-bottom: 1rem;"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
    <div class="card"><div class="card-body">
      <h3 class="section-title">নতুন ছাত্র যোগ করুন</h3>
      <form @submit.prevent="saveStudent">
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">নাম (বাংলা) *</label>
            <input v-model="form.name_bn" type="text" class="form-control" required /></div>
          <div class="form-group"><label class="form-label">নাম (ইংরেজি)</label>
            <input v-model="form.name_en" type="text" class="form-control" /></div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group"><label class="form-label">ভর্তি নং</label><input v-model="form.admission_number" type="text" class="form-control" /></div>
          <div class="form-group"><label class="form-label">রোল নং</label><input v-model="form.roll_number" type="text" class="form-control" /></div>
          <div class="form-group"><label class="form-label">ফোন</label><input v-model="form.phone" type="tel" class="form-control" /></div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">জন্ম তারিখ</label><input v-model="form.date_of_birth" type="date" class="form-control" /></div>
          <div class="form-group"><label class="form-label">লিঙ্গ</label>
            <select v-model="form.gender" class="form-control"><option value="">নির্বাচন করুন</option><option value="ছেলে">ছেলে</option><option value="মেয়ে">মেয়ে</option></select></div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">রক্তের গ্রুপ</label>
            <select v-model="form.blood_group" class="form-control"><option value="">নির্বাচন করুন</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option></select></div>
          <div class="form-group"><label class="form-label">শ্রেণি</label>
            <select v-model="form.class_id" class="form-control"><option value="">নির্বাচন করুন</option><option v-for="cls in classOptions" :key="cls.id" :value="cls.id">{{ cls.name_bn }}</option></select></div>
        </div>
        <div class="form-group"><label class="form-label">বাড়ির ঠিকানা (বাংলা)</label><textarea v-model="form.address_bn" class="form-control" rows="3"></textarea></div>
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
  name_bn: '', name_en: '', admission_number: '', roll_number: '',
  phone: '', date_of_birth: '', gender: '', blood_group: '',
  class_id: '', address_bn: '',
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

async function saveStudent() {
  saving.value = true
  try {
    await api.post('/students', form)
    navigateTo('/students')
  } catch (error: any) {
    alert('সংরক্ষণে ত্রুটি: ' + (error.response?.data?.message || 'অজানা ত্রুটি'))
  } finally {
    saving.value = false
  }
}

if (!isAuthenticated.value && !authLoading.value) {
  navigateTo('/login')
} else {
  onMounted(() => { if (isAuthenticated.value) loadClassOptions() })
}
</script>

<style scoped>
.student-create { padding: 1.5rem; }
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
