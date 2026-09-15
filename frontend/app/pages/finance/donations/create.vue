<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/finance" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন দান এন্ট্রি</h1>
        <p class="page-subtitle">দাতার অনুদান ও ফান্ড এন্ট্রি সঠিকভাবে সংরক্ষণ করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">দাতা ও ফান্ডের তথ্য</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                দাতা নির্বাচন <span class="required-star">*</span>
              </label>
              <select v-model="form.donor_id" class="form-control" :disabled="loading || donors.length === 0" required>
                <option value="" disabled>দাতা নির্বাচন করুন</option>
                <option v-for="d in donors" :key="d.id" :value="d.id">{{ d.name_bn || d.name_en }}</option>
              </select>
              <span v-if="donors.length === 0" class="form-hint">কোনো দাতা নেই — আগে দাতা যোগ করুন</span>
            </div>
            <div class="form-group">
              <label class="form-label">
                ফান্ড নির্বাচন <span class="required-star">*</span>
              </label>
              <select v-model="form.fund_id" class="form-control" :disabled="loading || funds.length === 0" required>
                <option value="" disabled>ফান্ড নির্বাচন করুন</option>
                <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name_bn }}</option>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                পরিমাণ (৳) <span class="required-star">*</span>
              </label>
              <input v-model.number="form.amount" type="number" min="1" class="form-control" placeholder="যেমন: ৫০০০" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">পদ্ধতি</label>
              <select v-model="form.payment_method" class="form-control" :disabled="loading">
                <option value="নগদ">নগদ (Cash)</option>
                <option value="ব্যাংক">ব্যাংক (Bank Transfer)</option>
                <option value="চেক">চেক (Cheque)</option>
                <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং (bKash/Nagad/Rocket)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">তারিখ ও অন্যান্য</h3>

          <div class="form-group">
            <label class="form-label">দানের তারিখ</label>
            <input v-model="form.donation_date" type="date" class="form-control" :disabled="loading" />
          </div>

          <div class="form-group">
            <label class="form-label">মন্তব্য / বিবরণ</label>
            <textarea v-model="form.notes" rows="3" class="form-control" placeholder="অনুদান সংক্রান্ত অতিরিক্ত তথ্য (ঐচ্ছিক)" :disabled="loading"></textarea>
          </div>

          <div class="form-group mt-2">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_anonymous" :disabled="loading" />
              <span>গোপনীয় রাখুন (রশিদ বা তালিকায় নাম প্রকাশ করবেন না)</span>
            </label>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/finance" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.donor_id || !form.fund_id || !form.amount">
            <span v-if="loading" class="spinner"></span>
            <span v-else>দান সংরক্ষণ করুন</span>
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
    await api.post('/finance/donations', form.value)
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



