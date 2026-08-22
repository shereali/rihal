<template>
  <div class="notice-create">
    <NuxtLink to="/notice" class="btn btn-outline btn-sm" style="margin-bottom:1rem"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
    <div class="card"><div class="card-body">
      <h3 class="section-title">নতুন বিজ্ঞপ্তি পোস্ট করুন</h3>
      <form @submit.prevent="saveNotice">
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">শিরোনাম (বাংলা) *</label><input v-model="form.title_bn" type="text" class="form-control" required /></div>
          <div class="form-group"><label class="form-label">শিরোনাম (ইংরেজি)</label><input v-model="form.title_en" type="text" class="form-control" /></div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group"><label class="form-label">বিভাগ</label>
            <select v-model="form.category" class="form-control">
              <option value="">নির্বাচন করুন</option>
              <option value="সাধারণ">সাধারণ</option>
              <option value="শিক্ষা">শিক্ষা</option>
              <option value="পরীক্ষা">পরীক্ষা</option>
              <option value="ছুটি">ছুটি</option>
              <option value="অনুষ্ঠান">অনুষ্ঠান</option>
              <option value="যোগাযোগ">যোগাযোগ</option>
              <option value="সতর্কবার্তা">সতর্কবার্তা</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">গুরুত্বপূর্ণতা</label>
            <select v-model="form.priority" class="form-control">
              <option value="normal">সাধারণ</option>
              <option value="low">নিম্ন</option>
              <option value="high">উচ্চ</option>
              <option value="urgent">জরুরি</option>
            </select>
          </div>
        </div>

        <!-- Target audience (admin only) -->
        <div v-if="isAdmin" class="target-section">
          <label class="form-label">লক্ষ্য দর্শক</label>
          <div class="target-options">
            <label class="target-option">
              <input type="radio" v-model="form.target_mode" value="all" /> সব শিক্ষার্থী ও অভিভাবক
            </label>
            <label class="target-option">
              <input type="radio" v-model="form.target_mode" value="class" /> নির্দিষ্ট শ্রেণি
            </label>
            <label class="target-option">
              <input type="radio" v-model="form.target_mode" value="group" /> নির্দিষ্ট গ্রুপ
            </label>
            <label class="target-option">
              <input type="radio" v-model="form.target_mode" value="single" /> নির্দিষ্ট একজন
            </label>
          </div>

          <div v-if="form.target_mode === 'class'" class="form-group">
            <label class="form-label">শ্রেণি নির্বাচন</label>
            <select v-model="form.target_class_id" class="form-control">
              <option value="">শ্রেণি চয়েজ করুন</option>
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn || c.name_en || 'শ্রেণি ' + c.id }}</option>
            </select>
          </div>

          <div v-if="form.target_mode === 'group'" class="form-group">
            <label class="form-label">গ্রুপ/বিভাগ</label>
            <input v-model="form.target_group_name" type="text" class="form-control" placeholder="যেমন: সকল অভিভাবক, সকল শিক্ষক, সাধারণ সদস্য" />
          </div>

          <div v-if="form.target_mode === 'single'" class="form-group">
            <label class="form-label">ব্যক্তি</label>
            <select v-model="form.target_user_id" class="form-control">
              <option value="">ব্যক্তি চয়েজ করুন</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name_bn || u.name }} ({{ u.email }})</option>
            </select>
          </div>
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
import { ref, reactive, onMounted, computed } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRouter } from 'vue-router'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading, currentUser } = useAuth()
const router = useRouter()

const saving = ref(false)
const classes = ref([])
const users = ref([])

const isAdmin = computed(() => {
  const r = (currentUser.value as any)?.role
  return r === 'admin' || r === 'super_admin'
})

const form = reactive({
  title_bn: '', title_en: '', content_bn: '', content_en: '',
  category: '', priority: 'normal', is_pinned: false, is_active: true,
  scheduled_at: '', expired_at: '',
  target_mode: 'all',
  target_class_id: '',
  target_group_name: '',
  target_user_id: '',
})

async function saveNotice() {
  saving.value = true
  try {
    await api.post('/notices', form)
    router.push('/notice')
  } catch (error: any) {
    alert('সংরক্ষণে ত্রুটি: ' + (error.response?.data?.message || 'অজানা ত্রুটি'))
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
  if (isAdmin.value) {
    try {
      const [cs, us] = await Promise.all([
        api.get('/academic/classes'),
        api.get('/users'),
      ])
      classes.value = cs.data?.data || []
      users.value = us.data?.data || []
    } catch {}
  }
})
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
.target-section { margin: 1.5rem 0; padding: 1rem; background: var(--color-bg); border-radius: var(--radius-sm); border: 1px solid var(--color-border-light); }
.target-section label.form-label { display: block; margin-bottom: 0.75rem; }
.target-options { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; }
.target-option { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer; padding: 0.375rem 0.75rem; background: var(--color-bg-card); border-radius: var(--radius-sm); border: 1px solid var(--color-border-light); }
.target-option input { cursor: pointer; }
</style>