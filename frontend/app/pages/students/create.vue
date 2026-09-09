<template>
  <div class="student-create">
    <div class="page-header">
      <NuxtLink to="/students" class="btn btn-outline btn-sm">
        <icon name="arrow-left" /> ফিরে যান
      </NuxtLink>
      <h1>নতুন ছাত্র ভর্তি</h1>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="saveStudent">
          <div class="form-section">
            <h4 class="section-title">ব্যক্তিগত তথ্য</h4>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">নাম (বাংলা) *</label>
                <input v-model="form.name_bn" type="text" class="form-control" placeholder="যেমন: আবদুল্লাহ আল নোমান" required />
              </div>
              <div class="form-group">
                <label class="form-label">নাম (ইংরেজি)</label>
                <input v-model="form.name_en" type="text" class="form-control" placeholder="যেমন: Abdullah Al Noman" />
              </div>
            </div>

            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">ভর্তি নং</label>
                <input v-model="form.admission_number" type="text" class="form-control" placeholder="যেমন: 2026-001" />
              </div>
              <div class="form-group">
                <label class="form-label">রোল নং</label>
                <input v-model="form.roll_number" type="text" class="form-control" placeholder="যেমন: 01" />
              </div>
              <div class="form-group">
                <label class="form-label">ফোন নম্বর</label>
                <input v-model="form.phone" type="tel" class="form-control" placeholder="+8801700000000" />
              </div>
            </div>

            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">ইমেইল (ঐচ্ছিক)</label>
                <input v-model="form.email" type="email" class="form-control" placeholder="student@domain.com" />
              </div>
              <div class="form-group">
                <label class="form-label">জন্ম তারিখ</label>
                <input v-model="form.date_of_birth" type="date" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">লিঙ্গ</label>
                <select v-model="form.gender" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                  <option value="ছেলে">ছেলে</option>
                  <option value="মেয়ে">মেয়ে</option>
                  <option value="অন্যান্য">অন্যান্য</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">একাডেমিক তথ্য</h4>
            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">শ্রেণি *</label>
                <select v-model="form.class_id" class="form-control" @change="loadSections">
                  <option value="">শ্রেণি নির্বাচন করুন</option>
                  <option v-for="cls in classOptions" :key="cls.id" :value="cls.id">{{ cls.name_bn }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">শাখা / বিভাগ</label>
                <select v-model="form.section_id" class="form-control" :disabled="!form.class_id">
                  <option value="">শাখা ছাড়া</option>
                  <option v-for="sec in sectionOptions" :key="sec.id" :value="sec.id">{{ sec.name_bn }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">রক্তের গ্রুপ</label>
                <select v-model="form.blood_group" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">অভিভাবক ও ঠিকানা</h4>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">পিতার নাম</label>
                <input v-model="form.father_name" type="text" class="form-control" placeholder="পিতার নাম" />
              </div>
              <div class="form-group">
                <label class="form-label">মাতার নাম</label>
                <input v-model="form.mother_name" type="text" class="form-control" placeholder="মাতার নাম" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">বাড়ির ঠিকানা (বাংলা)</label>
              <textarea v-model="form.address_bn" class="form-control" rows="2" placeholder="গ্রাম/মহল্লা, ডাকঘর, উপজেলা, জেলা"></textarea>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving || !form.name_bn">
              <span v-if="saving" class="spinner"></span>
              <span v-else>সংরক্ষণ করুন</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated } = useAuth()

const saving = ref(false)
const error = ref('')
const success = ref('')
const classOptions = ref<any[]>([])
const sectionOptions = ref<any[]>([])

const form = reactive({
  name_bn: '',
  name_en: '',
  admission_number: '',
  roll_number: '',
  phone: '',
  email: '',
  date_of_birth: '',
  gender: '',
  blood_group: '',
  class_id: '' as string | number,
  section_id: '' as string | number,
  father_name: '',
  mother_name: '',
  address_bn: '',
})

async function loadClassOptions() {
  try {
    const res = await api.get('/academic/classes')
    classOptions.value = res.data?.data || []
  } catch (err) {
    console.error('Failed to load classes:', err)
  }
}

async function loadSections() {
  sectionOptions.value = []
  form.section_id = ''
  if (!form.class_id) return
  try {
    const res = await api.get(`/academic/sections?class_id=${form.class_id}`)
    sectionOptions.value = res.data?.data || []
  } catch (err) {
    console.error('Failed to load sections:', err)
  }
}

async function saveStudent() {
  if (!form.name_bn.trim()) return
  saving.value = true
  error.value = ''
  success.value = ''

  try {
    await api.post('/students', {
      ...form,
      class_id: form.class_id || undefined,
      section_id: form.section_id || undefined,
    })
    success.value = 'ছাত্র সফলভাবে তৈরি হয়েছে!'
    setTimeout(() => {
      navigateTo('/students')
    }, 1200)
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'ছাত্র সংরক্ষণে ত্রুটি দেখা দিয়েছে।'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  if (isAuthenticated.value) {
    loadClassOptions()
  }
})
</script>

<style scoped>
.student-create {
  max-width: 800px;
  margin: 0 auto;
  padding: 1.5rem;
}
.page-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.page-header h1 {
  font-size: 1.5rem;
  margin: 0;
}
.card {
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
  box-shadow: var(--shadow-sm);
}
.card-body {
  padding: 1.75rem;
}
.form-section {
  margin-bottom: 1.5rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
}
.form-section:last-of-type {
  border-bottom: none;
  margin-bottom: 1rem;
}
.section-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--color-primary);
  margin-bottom: 1rem;
}
.form-row {
  display: grid;
  gap: 1rem;
  margin-bottom: 1rem;
}
.form-row-2 {
  grid-template-columns: repeat(2, 1fr);
}
.form-row-3 {
  grid-template-columns: repeat(3, 1fr);
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.form-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--color-text);
}
.form-control {
  padding: 0.6rem 0.85rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  font-size: 0.9rem;
  color: var(--color-text);
  background: var(--color-bg);
  transition: border-color var(--transition-fast);
}
.form-control:focus {
  outline: none;
  border-color: var(--color-primary);
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1rem;
}
.btn {
  padding: 0.65rem 1.5rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
.btn-primary {
  background: var(--color-primary);
  color: #fff;
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.alert {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  margin-bottom: 1rem;
  font-size: 0.9rem;
}
.alert-error {
  background: var(--color-error-bg);
  color: var(--color-error);
  border: 1px solid rgba(198, 40, 40, 0.2);
}
.alert-success {
  background: var(--color-success-bg);
  color: var(--color-success);
  border: 1px solid rgba(46, 125, 50, 0.2);
}
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
@media (max-width: 640px) {
  .form-row-2, .form-row-3 {
    grid-template-columns: 1fr;
  }
}
</style>
