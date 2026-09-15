<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/finance" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন ব্যয় এন্ট্রি</h1>
        <p class="page-subtitle">প্রতিষ্ঠানের সকল খরচ ও ব্যয়ের তথ্য সঠিকভাবে সংরক্ষণ করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">ব্যয়ের মূল তথ্য</h3>

          <div class="form-group">
            <label class="form-label">
              ব্যয়ের বিবরণ (বাংলা) <span class="required-star">*</span>
            </label>
            <input v-model="form.description_bn" type="text" class="form-control" placeholder="যেমন: জানুয়ারি মাসের বিদ্যুৎ বিল বা অফিস স্টেশনারি" :disabled="loading" required />
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                পরিমাণ (৳) <span class="required-star">*</span>
              </label>
              <input v-model.number="form.amount" type="number" min="1" class="form-control" placeholder="0" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">ফান্ড (ঐচ্ছিক)</label>
              <select v-model="form.fund_id" class="form-control" :disabled="loading">
                <option value="">সাধারণ তহবিল</option>
                <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name_bn }}</option>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">ব্যয়ের তারিখ</label>
              <input v-model="form.transaction_date" type="date" class="form-control" :disabled="loading" />
            </div>
            <div class="form-group">
              <label class="form-label">পরিশোধের পদ্ধতি</label>
              <select v-model="form.payment_method" class="form-control" :disabled="loading">
                <option value="নগদ">নগদ (Cash)</option>
                <option value="ব্যাংক">ব্যাংক (Bank Transfer)</option>
                <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং (bKash/Nagad)</option>
                <option value="চেক">চেক (Cheque)</option>
                <option value="অন্যান্য">অন্যান্য</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">অবস্থা ও অনুমোদন</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_paid" :disabled="loading" />
                <span>পরিশোধ সম্পন্ন হয়েছে (Paid)</span>
              </label>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_approved" :disabled="loading" />
                <span>অনুমোদিত (Approved)</span>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">মন্তব্য / অতিরিক্ত বিবরণ</label>
            <textarea v-model="form.notes" rows="3" class="form-control" placeholder="ব্যয় সংক্রান্ত কোনো বিশেষ নোট থাকলে লিখুন (ঐচ্ছিক)" :disabled="loading"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/finance" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.description_bn || !form.amount">
            <span v-if="loading" class="spinner"></span>
            <span v-else>ব্যয় সংরক্ষণ করুন</span>
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

const funds = ref<any[]>([])
const form = ref({
  description_bn: '',
  amount: null as number | null,
  fund_id: '' as string | number,
  transaction_date: new Date().toISOString().slice(0, 10),
  payment_method: 'নগদ',
  is_paid: true,
  is_approved: false,
  notes: '',
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadFunds() {
  try {
    const res = await api.get('/finance/funds').catch(() => ({ data: { data: [] } }))
    funds.value = res.data.data || []
  } catch { /* ignore */ }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const payload: any = {
      description_bn: form.value.description_bn,
      amount: form.value.amount,
      transaction_date: form.value.transaction_date,
      payment_method: form.value.payment_method,
      is_paid: form.value.is_paid,
      is_approved: form.value.is_approved,
      notes: form.value.notes || undefined,
    }
    if (form.value.fund_id) payload.fund_id = form.value.fund_id
    await api.post('/finance/expenses', payload)
    success.value = 'ব্যয় সফলভাবে যোগ করা হয়েছে!'
    setTimeout(() => navigateTo('/finance'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ব্যয় যোগ করা যায়নি'
  } finally {
    loading.value = false
  }
}

if (isAuthenticated.value) onMounted(loadFunds)
</script>



