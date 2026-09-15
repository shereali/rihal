<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/loan-due" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন ঋণ এন্ট্রি</h1>
        <p class="page-subtitle">প্রতিষ্ঠানের ঋণ ও কিস্তির তথ্য নির্ধারণ করে সংরক্ষণ করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">ঋণের সাধারণ তথ্য</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                শিরোনাম (বাংলা) <span class="required-star">*</span>
              </label>
              <input v-model="form.title_bn" type="text" class="form-control" placeholder="যেমন: শিক্ষক কল্যাণ ঋণ বা জরুরি ফান্ড" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">শিরোনাম (ইংরেজি)</label>
              <input v-model="form.title_en" type="text" class="form-control" placeholder="যেমন: Staff Welfare Loan" :disabled="loading" />
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                ঋণের ধরণ <span class="required-star">*</span>
              </label>
              <select v-model="form.loan_type" class="form-control" :disabled="loading">
                <option value="general">সাধারণ (General)</option>
                <option value="student">শিক্ষার্থী (Student)</option>
                <option value="staff">কর্মী / শিক্ষক (Staff)</option>
                <option value="emergency">জরুরি (Emergency)</option>
                <option value="development">উন্নয়ন (Development)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">গ্রাহক (ব্যবহারকারী আইডি)</label>
              <input v-model.number="form.user_id" type="number" min="0" class="form-control" placeholder="ঐচ্ছিক গ্রাহক আইডি" :disabled="loading" />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">পরিমাণ ও কিস্তির হিসাব</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                মূল পরিমাণ (৳) <span class="required-star">*</span>
              </label>
              <input v-model.number="form.principal_amount" type="number" min="1" step="0.01" class="form-control" placeholder="০.০০" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">বার্ষিক লাভ / সুদ (%)</label>
              <input v-model.number="form.interest_rate" type="number" min="0" step="0.01" class="form-control" placeholder="০" :disabled="loading" />
            </div>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label class="form-label">পদ্ধতি</label>
              <select v-model="form.interest_type" class="form-control" :disabled="loading">
                <option value="reducing">হ্রাসমান ব্যালেন্স (Reducing)</option>
                <option value="flat">ফ্ল্যাট রেট (Flat Rate)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">
                কিস্তির সংখ্যা <span class="required-star">*</span>
              </label>
              <input v-model.number="form.installment_count" type="number" min="1" max="600" class="form-control" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">পরিশোধের বিরতি</label>
              <select v-model="form.repayment_frequency" class="form-control" :disabled="loading">
                <option value="monthly">মাসিক (Monthly)</option>
                <option value="weekly">সাপ্তাহিক (Weekly)</option>
                <option value="quarterly">ত্রৈমাসিক (Quarterly)</option>
                <option value="yearly">বার্ষিক (Yearly)</option>
              </select>
            </div>
          </div>

          <!-- EMI Live Estimate Card -->
          <div class="emi-preview-card">
            <div class="emi-icon-col">
              <Icon name="cash" />
            </div>
            <div class="emi-text-col">
              <span class="emi-label">আনুমানিক প্রতি কিস্তির পরিমাণ</span>
              <strong class="emi-value">৳ {{ estimatedEmi.toLocaleString('bn-BD', { maximumFractionDigits: 2 }) }}</strong>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">সময়সূচি ও নোট</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">শুরুর তারিখ</label>
              <input v-model="form.start_date" type="date" class="form-control" :disabled="loading" />
            </div>
            <div class="form-group">
              <label class="form-label">সর্বশেষ পরিশোধ তারিখ</label>
              <input v-model="form.due_date" type="date" class="form-control" :disabled="loading" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">নোট / বিবরণ</label>
            <textarea v-model="form.notes" rows="3" class="form-control" placeholder="ঋণ প্রদান ও চুক্তি সংক্রান্ত মন্তব্য (ঐচ্ছিক)" :disabled="loading"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/loan-due" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.title_bn || !form.principal_amount">
            <span v-if="loading" class="spinner"></span>
            <span v-else>ঋণ তৈরি করুন</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useApiClient } from '~/utils/api'
import { useRouter } from 'vue-router'
import Icon from '~/components/Icon.vue'

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
  interest_type: 'reducing',
  installment_count: 12,
  repayment_frequency: 'monthly',
  start_date: null,
  due_date: null,
  notes: '',
})

const estimatedEmi = computed(() => {
  const principal = Number(form.value.principal_amount || 0)
  const count = Math.max(1, Number(form.value.installment_count || 1))
  const frequency = form.value.repayment_frequency
  const periodsPerYear = frequency === 'weekly' ? 52
    : frequency === 'quarterly' ? 4
      : frequency === 'yearly' ? 1
        : 12
  const rate = Number(form.value.interest_rate || 0) / 100 / periodsPerYear
  if (!principal) return 0
  if (form.value.interest_type === 'flat') {
    return (principal + principal * Number(form.value.interest_rate || 0) / 100 * (count / periodsPerYear)) / count
  }
  if (!rate) return principal / count
  const factor = Math.pow(1 + rate, count)
  return principal * rate * factor / (factor - 1)
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
.emi-preview-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  margin: 1.25rem 0;
  border-radius: var(--radius-md);
  background: var(--color-primary-50);
  border: 1px solid var(--color-primary-100);
  color: var(--color-primary);
}
.emi-icon-col {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: var(--color-primary);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}
.emi-text-col {
  display: flex;
  flex-direction: column;
}
.emi-label {
  font-size: var(--text-xs);
  color: var(--color-primary-light);
  font-family: var(--font-bn);
  font-weight: var(--weight-medium);
}
.emi-value {
  font-size: 1.4rem;
  font-weight: 800;
  font-family: var(--font-bn);
  color: var(--color-primary-dark);
}
</style>

