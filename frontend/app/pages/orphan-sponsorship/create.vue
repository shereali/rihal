<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/orphan-sponsorship" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন এতিম শিশু এন্ট্রি</h1>
        <p class="page-subtitle">এতিম ও অসহায় শিশুর বিস্তারিত তথ্য ও স্পন্সরশিপ নির্ধারণ করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">শিশুর প্রাথমিক তথ্য</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                নাম (বাংলা) <span class="required-star">*</span>
              </label>
              <input v-model="form.name_bn" type="text" class="form-control" placeholder="যেমন: উমর ফারুক" :disabled="loading" required />
            </div>
            <div class="form-group">
              <label class="form-label">নাম (ইংরেজি)</label>
              <input v-model="form.name_en" type="text" class="form-control" placeholder="Name in English" :disabled="loading" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">শিশুর ছবি</label>
            <PhotoUpload v-model="form.photo_url" />
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label class="form-label">জন্মতারিখ</label>
              <input v-model="form.birth_date" type="date" class="form-control" :disabled="loading" />
            </div>
            <div class="form-group">
              <label class="form-label">
                লিঙ্গ <span class="required-star">*</span>
              </label>
              <select v-model="form.gender" class="form-control" :disabled="loading" required>
                <option value="male">ছেলে (Male)</option>
                <option value="female">মেয়ে (Female)</option>
                <option value="other">অন্যান্য (Other)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">শ্রেণি</label>
              <input v-model="form.class_id" type="text" class="form-control" placeholder="যেমন: হিফজ বিভাগ বা ৪র্থ শ্রেণি" :disabled="loading" />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">অভিভাবক ও যোগাযোগ</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">অভিভাবকের নাম (বাংলা)</label>
              <input v-model="form.guardian_name_bn" type="text" class="form-control" placeholder="মাতা বা বৈধ অভিভাবকের নাম" :disabled="loading" />
            </div>
            <div class="form-group">
              <label class="form-label">অভিভাবকের মোবাইল নম্বর</label>
              <input v-model="form.guardian_phone" type="tel" class="form-control" placeholder="+8801XXXXXXXXX" :disabled="loading" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">ঠিকানা (বাংলা)</label>
            <textarea v-model="form.address_bn" rows="2" class="form-control" placeholder="গ্রাম, ডাকঘর, উপজেলা, জেলা" :disabled="loading"></textarea>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">স্পন্সরশিপ ও বিবরণ</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">মাসিক স্পন্সরশিপ অনুদান (৳)</label>
              <input v-model.number="form.monthly_amount" type="number" min="0" step="0.01" class="form-control" placeholder="০.০০" :disabled="loading" />
            </div>
            <div class="form-group">
              <label class="form-label">নির্ধারিত স্পন্সর / দাতা</label>
              <select v-model="form.sponsor_id" class="form-control" :disabled="loading || !donors.length">
                <option value="">স্পন্সর নির্বাচন করুন (ঐচ্ছিক)</option>
                <option v-for="d in donors" :key="d.id" :value="d.id">{{ d.name_bn || d.name_en }}</option>
              </select>
              <span v-if="!donors.length" class="form-hint">কোনো দাতা নেই — আগে দাতা যোগ করুন</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">শিশুর সংক্ষিপ্ত পরিচিতি / পেছনের গল্প</label>
            <textarea v-model="form.story" rows="3" class="form-control" placeholder="শিশুর পারিবারিক অবস্থা ও জীবনী লিখুন..." :disabled="loading"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">নোট / মন্তব্য</label>
            <textarea v-model="form.notes" rows="2" class="form-control" placeholder="অতিরিক্ত কোনো তথ্য (ঐচ্ছিক)" :disabled="loading"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/orphan-sponsorship" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.name_bn">
            <span v-if="loading" class="spinner"></span>
            <span v-else>তথ্য সংরক্ষণ করুন</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useRouter } from 'vue-router'
import PhotoUpload from '~/components/PhotoUpload.vue'
import Icon from '~/components/Icon.vue'

const api = useApiClient()
const router = useRouter()
const loading = ref(false)
const error = ref('')
const success = ref('')
const donors = ref<any[]>([])

const form = ref({
  name_bn: '',
  name_en: '',
  photo_url: '',
  birth_date: null,
  gender: 'male',
  class_id: '',
  guardian_name_bn: '',
  guardian_phone: '',
  address_bn: '',
  monthly_amount: null,
  sponsor_id: '',
  story: '',
  notes: '',
})

async function loadDonors() {
  try {
    const res = await api.get('/finance/donors').catch(() => ({ data: { data: [] } }))
    donors.value = res.data.data || []
  } catch { /* ignore */ }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const payload: any = { ...form.value }
    if (!payload.sponsor_id) delete payload.sponsor_id
    if (!payload.birth_date) delete payload.birth_date
    if (!payload.monthly_amount) delete payload.monthly_amount
    await api.post('/orphans', payload)
    success.value = 'এতিম শিশুর তথ্য সফলভাবে সংরক্ষণ করা হয়েছে!'
    setTimeout(() => router.push('/orphan-sponsorship'), 1500)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'তথ্য সংরক্ষণ করা যায়নি'
  } finally {
    loading.value = false
  }
}

onMounted(loadDonors)
</script>

<style scoped>
</style>
