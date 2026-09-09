<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/fees" class="back-link"><icon name="arrow-left" /> ফি তালিকা</NuxtLink>
        <h1>নতুন ফি সংগ্রহ ও রশিদ তৈরি</h1>
        <p class="page-subtitle">শিক্ষার্থীর মাসিক বেতন, ভর্তি ফি বা বকেয়া আদায়ের তথ্য লিপিবদ্ধ করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card form-container-card">
      <form @submit.prevent="handleSubmit" class="collect-form">
        <div class="form-grid">
          <div class="form-group wide">
            <label class="form-label">শিক্ষার্থী নির্বাচন করুন *</label>
            <select v-model="form.student_id" class="form-select" :disabled="loading || students.length === 0" required>
              <option value="" disabled>শিক্ষার্থী নির্বাচন করুন</option>
              <option v-for="s in students" :key="s.id" :value="s.id">
                {{ s.name_bn || s.name_en }} ({{ s.admission_number ? 'রোল: ' + s.admission_number : 'আইডি: #' + s.id }})
              </option>
            </select>
          </div>

          <div class="form-group wide">
            <label class="form-label">ফি কাঠামো / প্যাকেজ</label>
            <select v-model="form.fee_structure_id" class="form-select" :disabled="loading" @change="onStructureChange">
              <option value="">সাধারণ ফি (কাস্টম পরিমাণ)</option>
              <option v-for="f in structures" :key="f.id" :value="f.id">
                {{ f.name_bn }} (মোট: ৳{{ Number(f.total_fee || 0).toLocaleString('bn-BD') }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">মোট প্রদেয় পরিমাণ (টাকা) *</label>
            <input v-model.number="form.total_amount" type="number" min="0" step="1" class="form-input" placeholder="0" :disabled="loading" required />
          </div>

          <div class="form-group">
            <label class="form-label">আদায়কৃত পরিমাণ (টাকা) *</label>
            <input v-model.number="form.paid_amount" type="number" min="0" step="1" class="form-input" placeholder="0" :disabled="loading" required />
          </div>

          <div class="form-group">
            <label class="form-label">অবশিষ্ট বকেয়া (টাকা)</label>
            <input :value="balance" type="number" readonly class="form-input readonly-input" />
          </div>

          <div class="form-group">
            <label class="form-label">পরিশোধ পদ্ধতি *</label>
            <select v-model="form.payment_method" class="form-select" :disabled="loading" required>
              <option value="নগদ">নগদ (Cash)</option>
              <option value="বিকাশ">বিকাশ (bKash)</option>
              <option value="নগদ/রকেট">নগদ / রকেট</option>
              <option value="ব্যাংক">ব্যাংক ট্রান্সফার</option>
              <option value="চেক">চেক</option>
              <option value="অন্যান্য">অন্যান্য</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">পরিশোধের শেষ তারিখ</label>
            <input v-model="form.due_date" type="date" class="form-input" :disabled="loading" />
          </div>

          <div class="form-group">
            <label class="form-label">আদায়ের তারিখ</label>
            <input v-model="form.paid_date" type="date" class="form-input" :disabled="loading" />
          </div>

          <div class="form-group wide">
            <label class="form-label">লেনদেন রেফারেন্স / রশিদ নম্বর</label>
            <input v-model="form.transaction_ref" type="text" class="form-input" placeholder="ঐচ্ছিক রেফারেন্স নম্বর" :disabled="loading" />
          </div>
        </div>

        <div class="form-footer-actions">
          <NuxtLink to="/fees" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.student_id || !form.total_amount">
            <span v-if="loading" class="spinner" />
            <span v-else><icon name="check" /> ফি সংগ্রহ সংরক্ষণ করুন</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()

const students = ref<any[]>([])
const structures = ref<any[]>([])
const loading = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  student_id: '' as string | number,
  fee_structure_id: '' as string | number,
  total_amount: null as number | null,
  paid_amount: 0 as number,
  due_date: '',
  paid_date: new Date().toISOString().slice(0, 10),
  payment_method: 'নগদ',
  transaction_ref: '',
})

const balance = computed(() => {
  const total = Number(form.value.total_amount) || 0
  const paid = Number(form.value.paid_amount) || 0
  return Math.max(0, total - paid)
})

function onStructureChange() {
  if (!form.value.fee_structure_id) return
  const found = structures.value.find(s => String(s.id) === String(form.value.fee_structure_id))
  if (found && found.total_fee) {
    form.value.total_amount = Number(found.total_fee)
    form.value.paid_amount = Number(found.total_fee)
  }
}

async function loadData() {
  loading.value = true
  try {
    const [studentsRes, structuresRes] = await Promise.all([
      api.get('/students?per_page=200').catch(() => ({ data: { data: [] } })),
      api.get('/finance/fee-structures').catch(() => ({ data: { data: [] } })),
    ])
    students.value = studentsRes.data?.data?.data || studentsRes.data?.data || []
    structures.value = structuresRes.data?.data?.data || structuresRes.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/finance/fee-payments', {
      ...form.value,
      is_fully_paid: balance.value === 0,
      balance: balance.value,
    })
    success.value = 'ফি সফলভাবে সংগ্রহ করা হয়েছে!'
    setTimeout(() => navigateTo('/fees'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'ফি সংগ্রহ সংরক্ষণ করা যায়নি'
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.page-wrapper { max-width: 800px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.form-container-card { padding: 2rem; border-radius: 16px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.form-group.wide { grid-column: 1 / -1; }
.readonly-input { background: rgba(0, 0, 0, 0.03); cursor: not-allowed; font-weight: 700; }

.form-footer-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.btn { padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }
.btn-ghost { background: transparent; color: var(--color-text); }
.btn-ghost:hover { background: rgba(0, 0, 0, 0.05); }

.alert { padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; font-weight: 500; }
.alert-error { background: #fee2e2; color: #b91c1c; }
.alert-success { background: #dcfce7; color: #15803d; }
</style>
