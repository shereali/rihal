<template>
  <div class="collect-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/fees" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন ফি সংগ্রহ</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="collect-form card">
      <div class="form-group">
        <label>ছাত্র *</label>
        <select v-model="form.student_id" :disabled="loading || students.length === 0">
          <option value="" disabled>ছাত্র নির্বাচন করুন</option>
          <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name_bn || s.name_en }}</option>
        </select>
        <small v-if="students.length === 0" class="text-muted">কোনো ছাত্র নেই</small>
      </div>
      <div class="form-group">
        <label>ফি কাঠামো</label>
        <select v-model="form.fee_structure_id" :disabled="loading">
          <option value="">সাধারণ ফি</option>
          <option v-for="f in structures" :key="f.id" :value="f.id">{{ f.name_bn }} (৳{{ Number(f.total_fee || 0).toLocaleString('bn-BD') }})</option>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>মোট পরিমাণ (৳) *</label>
          <input v-model.number="form.total_amount" type="number" min="0" step="0.01" placeholder="0.00" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>পরিশোধিত (৳)</label>
          <input v-model.number="form.paid_amount" type="number" min="0" step="0.01" placeholder="0.00" :disabled="loading" @input="updateBalance" />
        </div>
      </div>
      <div class="form-group">
        <label>বকেয়া (৳)</label>
        <input :value="balance" type="number" readonly class="readonly-field" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>শেষ তারিখ</label>
          <input v-model="form.due_date" type="date" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>পরিশোধের তারিখ</label>
          <input v-model="form.paid_date" type="date" :disabled="loading" />
        </div>
      </div>
      <div class="form-group">
        <label>পরিশোধ পদ্ধতি</label>
        <select v-model="form.payment_method" :disabled="loading">
          <option value="নগদ">নগদ</option>
          <option value="ব্যাংক">ব্যাংক</option>
          <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং</option>
          <option value="অনলাইন">অনলাইন</option>
          <option value="অন্যান্য">অন্যান্য</option>
        </select>
      </div>
      <div class="form-group">
        <label>লেনদেন রেফারেন্স</label>
        <input v-model="form.transaction_ref" type="text" placeholder="ঐচ্ছিক" :disabled="loading" />
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || !form.student_id || !form.total_amount">
          <span v-if="loading" class="spinner"></span>
          <span v-else>সংরক্ষণ করুন</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated } = useAuth()

const students = ref<any[]>([])
const structures = ref<any[]>([])
const form = ref({
  student_id: '' as string | number,
  fee_structure_id: '' as string | number,
  total_amount: null as number | null,
  paid_amount: 0 as number,
  due_date: '',
  paid_date: '',
  payment_method: 'নগদ',
  transaction_ref: '',
})

const balance = computed(() => {
  const total = Number(form.value.total_amount) || 0
  const paid = Number(form.value.paid_amount) || 0
  return Math.max(0, total - paid)
})

const loading = ref(false)
const error = ref('')
const success = ref('')

function updateBalance() { /* computed handles it */ }

async function loadStudents() {
  try { const r = await api.get('/students?per_page=100'); students.value = r.data?.data?.data || r.data?.data || [] } catch {}
}
async function loadStructures() {
  try { const r = await api.get('/finance/fee-structures?per_page=100'); structures.value = r.data?.data?.data || r.data?.data || [] } catch {}
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/finance/fee-payments', {
      student_id: form.value.student_id,
      fee_structure_id: form.value.fee_structure_id || undefined,
      total_amount: form.value.total_amount,
      paid_amount: form.value.paid_amount || 0,
      due_date: form.value.due_date || undefined,
      paid_date: form.value.paid_date || undefined,
      payment_method: form.value.payment_method,
      transaction_ref: form.value.transaction_ref || undefined,
    })
    success.value = 'ফি সংগ্রহ সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/fees'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ফি সংগ্রহ যোগ করা যায়নি'
  } finally {
    loading.value = false
  }
}

if (isAuthenticated.value) onMounted(async () => { await Promise.all([loadStudents(), loadStructures()]) })
</script>

<style scoped>
.collect-page { max-width: 640px; margin: 0 auto; padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.header-left h1 { margin: 0.5rem 0 0; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.collect-form { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group input, .form-group select {
  padding: 0.7rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 1rem;
  font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg);
}
.readonly-field { background: var(--color-bg-muted); color: var(--color-text-muted); }
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
