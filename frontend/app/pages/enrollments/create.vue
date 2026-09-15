<template>
  <div class="create-page-container">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/students" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>নতুন ভর্তি</h1>
        <p class="page-subtitle">শিক্ষার্থীর শ্রেণি ও সেশন নির্ধারণ করে ভর্তি সম্পন্ন করুন</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div class="card create-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h3 class="section-title">একাডেমিক তথ্য</h3>
          <p class="section-desc">শিক্ষার্থী, শ্রেণি ও শিক্ষাবর্ষ নির্বাচন করুন</p>

          <div class="form-group">
            <label class="form-label">
              ছাত্র নির্বাচন <span class="required-star">*</span>
            </label>
            <select v-model="form.student_id" class="form-control" :disabled="loading || students.length === 0" required>
              <option value="" disabled>ছাত্র নির্বাচন করুন</option>
              <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name_bn || s.user?.name_bn || s.name_en || s.email }}</option>
            </select>
            <span v-if="students.length === 0" class="form-hint">কোনো ছাত্র নেই — আগে ছাত্র যোগ করুন</span>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                শ্রেণি <span class="required-star">*</span>
              </label>
              <select v-model="form.class_id" class="form-control" :disabled="loading || classes.length === 0" @change="loadSections" required>
                <option value="" disabled>শ্রেণি নির্বাচন করুন</option>
                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">
                সেশন / শিক্ষাবর্ষ <span class="required-star">*</span>
              </label>
              <select v-model="form.session_id" class="form-control" :disabled="loading || sessions.length === 0" required>
                <option value="" disabled>সেশন নির্বাচন করুন</option>
                <option v-for="ses in sessions" :key="ses.id" :value="ses.id">{{ ses.name_bn || ses.name_en || ses.year }}</option>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">শাখা / সেকশন</label>
              <select v-model="form.section_id" class="form-control" :disabled="loading">
                <option value="">সেকশন ছাড়া</option>
                <option v-for="sec in sections" :key="sec.id" :value="sec.id">{{ sec.name_bn }}</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">ভর্তির তারিখ</label>
              <input v-model="form.enrollment_date" type="date" class="form-control" :disabled="loading" />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="section-title">ভর্তির অবস্থা ও অন্যান্য</h3>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">অবস্থা</label>
              <select v-model="form.status" class="form-control" :disabled="loading">
                <option value="active">সক্রিয় (Active)</option>
                <option value="pending">অপেক্ষমাণ (Pending)</option>
                <option value="completed">সম্পন্ন (Completed)</option>
                <option value="transferred">স্থানান্তরিত (Transferred)</option>
                <option value="dropped">বাতিল (Dropped)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">ভর্তির ধরণ</label>
              <select v-model="form.admission_type" class="form-control" :disabled="loading">
                <option value="regular">নিয়মিত (Regular)</option>
                <option value="transfer">স্থানান্তর (Transfer)</option>
                <option value="religious">ধর্মীয় / হিফজ (Religious)</option>
                <option value="special">বিশেষ (Special)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">মন্তব্য (বাংলা)</label>
            <textarea v-model="form.remarks_bn" rows="3" class="form-control" placeholder="ভর্তি সংক্রান্ত অতিরিক্ত বিবরণ (ঐচ্ছিক)" :disabled="loading"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/students" class="btn btn-ghost">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="loading || !form.student_id || !form.class_id || !form.session_id">
            <span v-if="loading" class="spinner"></span>
            <span v-else>ভর্তি সম্পন্ন করুন</span>
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

const students = ref<any[]>([])
const classes = ref<any[]>([])
const sessions = ref<any[]>([])
const sections = ref<any[]>([])

const form = ref({
  student_id: '' as string | number,
  class_id: '' as string | number,
  session_id: '' as string | number,
  section_id: '' as string | number,
  enrollment_date: new Date().toISOString().slice(0, 10),
  status: 'active',
  admission_type: 'regular',
  remarks_bn: '',
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadData() {
  loading.value = true
  try {
    const [st, cl, se] = await Promise.all([
      api.get('/students?per_page=1000').catch(() => ({ data: { data: [] } })),
      api.get('/classes?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/academic-sessions?per_page=100').catch(() => ({ data: { data: [] } })),
    ])
    students.value = st.data.data || []
    classes.value = cl.data.data || []
    sessions.value = se.data.data || []
  } catch (e: any) {
    error.value = 'তথ্য লোড করতে সমস্যা হয়েছে'
  } finally {
    loading.value = false
  }
}

async function loadSections() {
  if (!form.value.class_id) {
    sections.value = []
    return
  }
  try {
    const res = await api.get(`/sections?class_id=${form.value.class_id}&per_page=100`).catch(() => ({ data: { data: [] } }))
    sections.value = res.data.data || []
  } catch {
    sections.value = []
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/enrollments', form.value)
    success.value = 'ভর্তি সফলভাবে সম্পন্ন হয়েছে!'
    setTimeout(() => navigateTo('/students'), 1200)
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ভর্তি সংরক্ষণ করা যায়নি'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (isAuthenticated.value) {
    loadData()
  }
})
</script>

<style scoped>
small { font-size: 0.8rem; }
</style>
