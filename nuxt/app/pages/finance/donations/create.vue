<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন দান</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="create-form card">
      <div class="form-row">
        <div class="form-group">
          <label>দাতা *</label>
          <select v-model="form.donor_id" :disabled="loading || donors.length === 0">
            <option value="" disabled>দাতা নির্বাচন করুন</option>
            <option v-for="d in donors" :key="d.id" :value="d.id">{{ d.name_bn || d.name_en }}</option>
          </select>
          <small v-if="donors.length === 0" class="text-muted">কোনো দাতা নেই — আগে দাতা যোগ করুন</small>
        </div>
        <div class="form-group">
          <label>ফান্ড *</label>
          <select v-model="form.fund_id" :disabled="loading || funds.length === 0">
            <option value="" disabled>ফান্ড নির্বাচন করুন</option>
            <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name_bn }}</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>পরিমাণ (৳) *</label>
          <input v-model.number="form.amount" type="number" min="1" placeholder="0" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>পদ্ধতি</label>
          <select v-model="form.payment_method" :disabled="loading">
            <option value="নগদ">নগদ</option>
            <option value="ব্যাংক">ব্যাংক</option>
            <option value="চেক">চেক</option>
            <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>তারিখ</label>
        <input v-model="form.donation_date" type="date" :disabled="loading" />
      </div>
      <div class="form-group">
        <label>মন্তব্য</label>
        <textarea v-model="form.notes" rows="3" placeholder="ঐচ্ছিক মন্তব্য" :disabled="loading"></textarea>
      </div>
      <label class="checkbox-label">
        <input type="checkbox" v-model="form.is_anonymous" :disabled="loading" />
        গোপনীয় রাখুন (নাম প্রকাশ করবেন না)
      </label>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || !form.donor_id || !form.fund_id || !form.amount">
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

const donors = ref<any[]>([])
const funds = ref<any[]>([])
const form = ref({
  donor_id: '' as string | number,
  fund_id: '' as string | number,
  amount: null as number | null,
  payment_method: 'নগদ',
  donation_date: new Date().toISOString().slice(0, 10),
  notes: '',
  is_anonymous: false,
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadSelects() {
  try {
    const [d, f] = await Promise.all([
      api.get('/finance/donors').catch(() => ({ data: { data: [] } })),
      api.get('/finance/funds').catch(() => ({ data: { data: [] } })),
    ])
    donors.value = d.data.data || []
    funds.value = f.data.data || []
  } catch { /* ignore */ }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/finance/donations', {
      donor_id: form.value.donor_id,
      fund_id: form.value.fund_id,
      amount: form.value.amount,
      payment_method: form.value.payment_method,
      donation_date: form.value.donation_date || undefined,
      notes: form.value.notes || undefined,
      is_anonymous: form.value.is_anonymous,
    })
    success.value = 'দান সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/finance'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'দান যোগ করা যায়নি'
  } finally {
    loading.value = false
  }
}

if (isAuthenticated.value) onMounted(loadSelects)
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
