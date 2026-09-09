<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন ফান্ড</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="create-form card">
      <div class="form-group">
        <label>ফান্ডের নাম (বাংলা) *</label>
        <input v-model="form.name_bn" type="text" placeholder="যেমন: জেনারেল ফান্ড" :disabled="loading" />
      </div>
      <div class="form-group">
        <label>ফান্ডের নাম (ইংরেজি)</label>
        <input v-model="form.name_en" type="text" placeholder="General Fund" :disabled="loading" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>ধরণ *</label>
          <select v-model="form.type" :disabled="loading">
            <option value="রাশনির্দিষ্ট">রাশনির্দিষ্ট</option>
            <option value="অনানুদানিক">অনানুদানিক</option>
            <option value="উন্নয়ন">উন্নয়ন</option>
            <option value="শেয়ার">শেয়ার</option>
            <option value="অন্যান্য">অন্যান্য</option>
          </select>
        </div>
        <div class="form-group">
          <label>লক্ষ্যমাত্রা (৳)</label>
          <input v-model.number="form.target_amount" type="number" min="0" placeholder="0" :disabled="loading" />
        </div>
      </div>
      <div class="form-group">
        <label>প্রাথমিক সংগ্রহ (৳)</label>
        <input v-model.number="form.collected_amount" type="number" min="0" placeholder="0" :disabled="loading" />
      </div>
      <div class="form-group">
        <label>বিবরণ</label>
        <textarea v-model="form.description_bn" rows="3" placeholder="ফান্ড সম্পর্কে সংক্ষিপ্ত বিবরণ" :disabled="loading"></textarea>
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
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated } = useAuth()

const form = ref({
  name_bn: '',
  name_en: '',
  type: 'রাশনির্দিষ্ট',
  target_amount: null as number | null,
  collected_amount: null as number | null,
  description_bn: '',
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/finance/funds', {
      name_bn: form.value.name_bn,
      name_en: form.value.name_en || undefined,
      type: form.value.type,
      target_amount: form.value.target_amount || undefined,
      collected_amount: form.value.collected_amount || undefined,
      description_bn: form.value.description_bn || undefined,
    })
    success.value = 'ফান্ড সফলভাবে তৈরি হয়েছে!'
    setTimeout(() => navigateTo('/finance'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ফান্ড তৈরি করা যায়নি'
  } finally {
    loading.value = false
  }
}
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
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(20,80,50,0.12);
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
</style>
