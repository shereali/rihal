<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/loan-due" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন ঋণ</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="create-form card">
      <div class="form-group">
        <label>শিরোনাম (বাংলা) *</label>
        <input v-model="form.title_bn" type="text" placeholder="যেমন: গভর্নর ঋণ" :disabled="loading" />
      </div>
      <div class="form-group">
        <label>শিরোনাম (ইংরেজি)</label>
        <input v-model="form.title_en" type="text" placeholder="Governor Loan" :disabled="loading" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>ধরণ *</label>
          <select v-model="form.loan_type" :disabled="loading">
            <option value="general">সাধারণ</option>
            <option value="student">শিক্ষার্থী</option>
            <option value="staff">কর্মী</option>
            <option value="emergency">জরুরি</option>
            <option value="development">উন্নয়ন</option>
          </select>
        </div>
        <div class="form-group">
          <label>প্রতিষ্ঠানকর্তা (ব্যবহারকারী আইডি)</label>
          <input v-model.number="form.user_id" type="number" min="0" placeholder="ব্যবহারকারীর আইডি" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>মূল পরিমাণ (৳) *</label>
          <input v-model.number="form.principal_amount" type="number" min="1" step="0.01" placeholder="0" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>সুদের হার (%)</label>
          <input v-model.number="form.interest_rate" type="number" min="0" step="0.01" placeholder="0" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>শুরুর তারিখ</label>
          <input v-model="form.start_date" type="date" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>শেষ তারিখ (ডিউটি)</label>
          <input v-model="form.due_date" type="date" :disabled="loading" />
        </div>
      </div>
      <div class="form-group">
        <label>প্রতি মাসের কিস্তি (৳)</label>
        <input v-model.number="form.monthly_installment" type="number" min="0" step="0.01" placeholder="0" :disabled="loading" />
      </div>
      <div class="form-group">
        <label>নোট</label>
        <textarea v-model="form.notes" rows="3" placeholder="ঋণ সংক্রেতন..." :disabled="loading"></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading" class="spinner"></span>
          <span v-else>সংরক্ষণ করুন</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useApiClient } from '~/utils/api'
import { useRouter } from 'vue-router'

const api = useApiClient()
const router = useRouter()
const loading = ref(false)
const error = ref('')
const success = ref('')
const form = ref({
  loan_type: 'general',
  title_bn: '',
  title_en: '',
  user_id: null,
  principal_amount: null,
  interest_rate: 0,
  start_date: null,
  due_date: null,
  monthly_installment: 0,
  notes: '',
})

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const payload = { ...form.value }
    if (payload.user_id === null) delete payload.user_id
    if (payload.start_date === null) delete payload.start_date
    if (payload.due_date === null) delete payload.due_date
    await api.post('/loans', payload)
    success.value = 'ঋণ তৈরি সফল!'
    setTimeout(() => router.push('/loan-due'), 1500)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ঋণ তৈরি করা যায়নি'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.create-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.25rem; }
.header-left h1 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.create-form { padding: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 0.75rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group input, .form-group textarea, .form-group select {
  padding: 0.6rem 0.85rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 0.95rem;
  font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg);
}
.form-group textarea { resize: vertical; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-row .form-group { margin-bottom: 0; }
.form-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
.btn { padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; display: inline-flex; align-items: center; gap: 0.35rem; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.spinner { width: 16px; height: 16px; border: 2px solid var(--color-text-on-primary); border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 0.6rem 0.9rem; border-radius: 8px; margin-bottom: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: #fde2e2; color: var(--color-error); }
.alert-success { background: #dcfce8; color: #16a34a; }
</style>
