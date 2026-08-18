<template>
  <div class="donor-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>দাতাবৃন্দ</h1>
        <p class="text-muted">{{ donors?.data?.total || 0 }} জন দাতা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary btn-sm" @click="showForm = !showForm"><icon name="plus" /> নতুন দাতা</button>
      </div>
    </div>

    <form v-if="showForm" @submit.prevent="handleSubmit" class="card create-card">
      <div class="form-row">
        <div class="form-group">
          <label>নাম (বাংলা) *</label>
          <input v-model="form.name_bn" type="text" placeholder="দাতার নাম" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>নাম (ইংরেজি)</label>
          <input v-model="form.name_en" type="text" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>ফোন</label>
          <input v-model="form.phone" type="text" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>ইমেইল</label>
          <input v-model="form.email" type="email" :disabled="loading" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>রক্তের গ্রুপ</label>
          <input v-model="form.blood_group" type="text" maxlength="5" :disabled="loading" />
        </div>
        <div class="form-group">
          <label>প্রতিষ্ঠান</label>
          <input v-model="form.organization" type="text" :disabled="loading" />
        </div>
      </div>
      <div class="form-group">
        <label>ঠিকানা</label>
        <input v-model="form.address_bn" type="text" :disabled="loading" />
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || !form.name_bn">
          <span v-if="loading" class="spinner"></span><span v-else>সংরক্ষণ করুন</span>
        </button>
        <button type="button" class="btn btn-outline" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(donors?.data?.data || []).length === 0" class="empty-state"><p>কোনো দাতা নেই</p></div>
        <table v-else class="table table-hover">
          <thead><tr><th>নাম</th><th>ফোন</th><th>প্রতিষ্ঠান</th><th>রক্তের গ্রুপ</th></tr></thead>
          <tbody>
            <tr v-for="d in (donors?.data?.data || [])" :key="d.id">
              <td>{{ d.name_bn }} <span v-if="d.name_en" class="text-muted"> ({{ d.name_en }})</span></td>
              <td>{{ d.phone || '-' }}</td>
              <td>{{ d.organization || '-' }}</td>
              <td>{{ d.blood_group || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(false)
const showForm = ref(false)
const donors = ref<any>(null)
const error = ref('')
const form = ref({ name_bn: '', name_en: '', phone: '', email: '', blood_group: '', organization: '', address_bn: '' })

async function loadDonors() {
  loading.value = true
  try { const r = await api.get('/finance/donors?per_page=50'); donors.value = r.data } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await api.post('/finance/donors', { ...form.value })
    showForm.value = false
    form.value = { name_bn: '', name_en: '', phone: '', email: '', blood_group: '', organization: '', address_bn: '' }
    await loadDonors()
  } catch (e: any) { error.value = e?.response?.data?.message ?? 'দাতা যোগ করা যায়নি' }
  finally { loading.value = false }
}

onMounted(loadDonors)
</script>

<style scoped>
.donor-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { margin: 0.4rem 0 0; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.create-card { padding: 1.25rem; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 1rem; background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group input { padding: 0.65rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 1rem; font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg); }
.form-actions { display: flex; gap: 0.75rem; }
.btn { padding: 0.7rem 1.4rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.spinner { width: 16px; height: 16px; border: 2px solid var(--color-text-on-primary); border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 0.6rem 0.9rem; border-radius: 8px; }
.alert-error { background: #fce4e4; color: var(--color-error); font-family: 'Noto Sans Bengali', sans-serif; }
.text-muted { color: var(--color-text-light); }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.empty-state { padding: 2rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
</style>
