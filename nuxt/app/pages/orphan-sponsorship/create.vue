<template>
  <div class="create-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/orphan-sponsorship" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>নতুন অর্ফান</h1>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="handleSubmit" class="create-form card">
      <div class="form-group">
        <label>নাম (বাংলা) *</label>
        <input v-model="form.name_bn" type="text" placeholder="শিশুর নাম" :disabled="loading" />
      </div>
      <div class="form-group">
        <label>শিশুর ছবি</label>
        <PhotoUpload v-model="form.photo_url" />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>নাম (ইংরেজি)</label>
          <input v-model="form.name_en" type="text" placeholder="Name in English" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>জন্মতারিখ</label>
          <input v-model="form.birth_date" type="date" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>লিঙ্গ *</label>
          <select v-model="form.gender" :disabled="loading">
            <option value="male">পুরুষ</option>
            <option value="female">মহিলা</option>
            <option value="other">অন্যান্য</option>
          </select>
        </div>
        <div class="form-group">
          <label>শ্রেণি</label>
          <input v-model="form.class_id" type="text" placeholder="যেমন: ৫ ম বা Class 5" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>বড়/অভিভাবকের নাম (বাংলা)</label>
          <input v-model="form.guardian_name_bn" type="text" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>ফোন</label>
          <input v-model="form.guardian_phone" type="text" :disabled="loading" />
        </div>
      </div>
      <div class="form-group">
        <label>ঠিকানা (বাংলা)</label>
        <textarea v-model="form.address_bn" rows="2" :disabled="loading"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>মাসিক স্পন্সরশিপ (৳)</label>
          <input v-model.number="form.monthly_amount" type="number" min="0" step="0.01" placeholder="0" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>স্পন্সর (দাতা)</label>
          <select v-model="form.sponsor_id" :disabled="loading || !donors.length">
            <option value="">স্পন্সর নির্বাচন করুন</option>
            <option v-for="d in donors" :key="d.id" :value="d.id">{{ d.name_bn || d.name_en }}</option>
          </select>
          <small v-if="!donors.length" class="text-muted">কোনো দাতা নেই — আগে দাতা যোগ করুন</small>
        </div>
      </div>
      <div class="form-group">
        <label>জীবনী / গল্প</label>
        <textarea v-model="form.story" rows="3" placeholder="শিশুর গল্প..." :disabled="loading"></textarea>
      </div>
      <div class="form-group">
        <label>নোট</label>
        <textarea v-model="form.notes" rows="2" placeholder="অতিরিক্ত তথ্য..." :disabled="loading"></textarea>
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
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useRouter } from 'vue-router'

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
  gender: 'other',
  class_id: '',
  guardian_name_bn: '',
  guardian_name_en: '',
  guardian_phone: '',
  address_bn: '',
  address_en: '',
  monthly_amount: 0,
  sponsor_id: null,
  story: '',
  notes: '',
  is_active: true,
  is_orphaned: true,
  is_needy: true,
})

async function loadDonors() {
  try {
    const r = await api.get('/orphans/sponsors')
    donors.value = r.data?.data || []
  } catch (e: any) {
    console.error(e)
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const payload = { ...form.value }
    if (payload.sponsor_id === null) delete payload.sponsor_id
    await api.post('/orphans', payload)
    success.value = 'অর্ফান তৈরি সফল!'
    setTimeout(() => router.push('/orphan-sponsorship'), 1500)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'অর্ফান তৈরি করা যায়নি'
  } finally {
    loading.value = false
  }
}

onMounted(loadDonors)
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
.text-muted { color: var(--color-text-light); font-size: 0.8rem; font-family: 'Noto Sans Bengali', sans-serif; }
</style>
