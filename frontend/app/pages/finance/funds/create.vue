<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/finance" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন ফান্ড তৈরি</h1>
        <p class="page-subtitle">প্রতিষ্ঠানের নির্দিষ্ট উন্নয়ন বা সাধারণ তহবিল তৈরি করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">ফান্ডের প্রাথমিক তথ্য</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                ফান্ডের নাম (বাংলা) <span class="required-star">*</span>
              </label>
              <input v-model="form.name_bn" type="text" class="form-control" placeholder="যেমন: সাধারণ তহবিল বা এতিমখানা ফান্ড" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">ফান্ডের নাম (ইংরেজি)</label>
              <input v-model="form.name_en" type="text" class="form-control" placeholder="যেমন: General Fund" :disabled="loading" />
            </div>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label class="form-label">
                ফান্ডের ধরণ <span class="required-star">*</span>
              </label>
              <select v-model="form.type" class="form-control" :disabled="loading" required>
                <option value="রাশনির্দিষ্ট">রাশনির্দিষ্ট</option>
                <option value="অনানুদানিক">অনানুদানিক</option>
                <option value="উন্নয়ন">উন্নয়ন</option>
                <option value="শেয়ার">শেয়ার</option>
                <option value="অন্যান্য">অন্যান্য</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">লক্ষ্যমাত্রা (৳)</label>
              <input v-model.number="form.target_amount" type="number" min="0" class="form-control" placeholder="০.০০" :disabled="loading" />
            </div>
            <div class="form-group">
              <label class="form-label">প্রাথমিক সংগ্রহ (৳)</label>
              <input v-model.number="form.collected_amount" type="number" min="0" class="form-control" placeholder="০.০০" :disabled="loading" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">বিবরণ / উদ্দেশ্য</label>
            <textarea v-model="form.description_bn" rows="3" class="form-control" placeholder="ফান্ডের উদ্দেশ্য বা বিবরণ লিখুন (ঐচ্ছিক)" :disabled="loading"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/finance" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.name_bn">
            <span v-if="loading" class="spinner"></span>
            <span v-else>ফান্ড সংরক্ষণ করুন</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import Icon from '~/components/Icon.vue'

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

