<template>
  <div class="notice-create">
    <NuxtLink to="/notice" class="btn btn-outline btn-sm" style="margin-bottom: 1rem;"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
    <div class="card"><div class="card-body">
      <h3 class="section-title">নতুন বিজ্ঞপ্তি পোস্ট করুন</h3>
      <form @submit.prevent="saveNotice">
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">শিরোনাম (বাংলা) *</label><input v-model="form.title_bn" type="text" class="form-control" required /></div>
          <div class="form-group"><label class="form-label">শিরোনাম (ইংরেজি)</label><input v-model="form.title_en" type="text" class="form-control" /></div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">বিভাগ</label>
            <select v-model="form.category" class="form-control"><option value="">নির্বাচন করুন</option><option value="সাধারণ">সাধারণ</option><option value="শিক্ষা">শিক্ষা</option><option value="পরীক্ষা">পরীক্ষা</option><option value="ছুটি">ছুটি</option><option value="অনুষ্ঠান">অনুষ্ঠান</option><option value="যোগাযোগ">যোগাযোগ</option><option value="সতর্কবার্তা">সতর্কবার্তা</option></select></div>
          <div class="form-group"><label class="form-label">গুরুত্বপূর্ণতা</label>
            <select v-model="form.priority" class="form-control"><option value="normal">সাধারণ</option><option value="low">নিম্ন</option><option value="high">উচ্চ</option><option value="urgent">জরুরি</option></select></div>
        </div>
        <div class="form-group"><label class="form-label">বিবরণ (বাংলা) *</label><textarea v-model="form.content_bn" class="form-control" rows="6" required></textarea></div>
        <div class="form-row form-row-2">
          <div class="form-group form-check"><label class="checkbox-label"><input type="checkbox" v-model="form.is_pinned" /> পিন করুন</label></div>
          <div class="form-group form-check"><label class="checkbox-label"><input type="checkbox" v-model="form.is_active" checked /> সক্রিয়</label></div>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে...' : 'প্রকাশ করুন' }}</button>
      </form>
    </div></div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const saving = ref(false)

const form = reactive({
  title_bn: '', title_en: '', content_bn: '', content_en: '',
  category: '', priority: 'normal', is_pinned: false, is_active: true,
  scheduled_at: '', expired_at: '',
})

async function saveNotice() {
  saving.value = true
  try {
    await api.post('/notices', form)
    navigateTo('/notice')
  } catch (error: any) {
    alert('সংরক্ষণে ত্রুটি: ' + (error.response?.data?.message || 'অজানা ত্রুটি'))
  } finally { saving.value = false }
}

if (!isAuthenticated.value && !authLoading.value) navigateTo('/login')
</script>

<style scoped>
.notice-create { padding: 1.5rem; }
.section-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; }
.form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.form-row-2 { grid-template-columns: repeat(2, 1fr); }
.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-label { font-size: 0.875rem; font-weight: 500; color: var(--color-text); }
.form-control { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9375rem; color: var(--color-text); background: var(--color-bg); }
.form-control:focus { outline: none; border-color: var(--color-primary); }
textarea.form-control { resize: vertical; }
.form-check { flex-direction: row; align-items: center; gap: 0.5rem; }
.checkbox-label { font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; }
.card { background: var(--color-bg-card); border-radius: var(--radius-md); border: 1px solid var(--color-border-light); }
.card-body { padding: 1.5rem; }
</style>
