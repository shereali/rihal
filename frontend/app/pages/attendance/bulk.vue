<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/attendance" class="btn btn-outline btn-sm mb-2">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <h1>বাল্ক হাজিরা এন্ট্রি</h1>
        <p class="page-subtitle">শ্রেণি ও তারিখ নির্বাচন করে একসাথে সকল শিক্ষার্থীর হাজিরা চিহ্নিত করুন</p>
      </div>
    </div>

    <div class="card card-pad mb-3">
      <div class="form-row-3" style="align-items: flex-end;">
        <div class="form-group mb-0">
          <label class="form-label">শ্রেণি নির্বাচন <span class="required-star">*</span></label>
          <select v-model="classId" class="form-control" :disabled="loading" @change="loadStudents">
            <option value="">শ্রেণি নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
          </select>
        </div>
        <div class="form-group mb-0">
          <label class="form-label">তারিখ <span class="required-star">*</span></label>
          <input v-model="date" type="date" class="form-control" :disabled="loading" @change="prefill" />
        </div>
        <div class="form-group mb-0" style="display: flex; gap: 0.5rem;">
          <button class="btn btn-outline btn-sm" @click="markAll('present')">সব উপস্থিত</button>
          <button class="btn btn-outline btn-sm" @click="markAll('absent')">সব অনুপস্থিত</button>
        </div>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div v-if="students.length" class="table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিক্ষার্থীর নাম</th>
              <th>ভর্তি নং</th>
              <th class="text-right">হাজিরা অবস্থা</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in students" :key="s.id">
              <td class="font-medium">{{ s.name_bn || s.name_en }}</td>
              <td class="text-muted">{{ s.admission_number || '-' }}</td>
              <td class="text-right">
                <div class="status-toggle" style="justify-content: flex-end;">
                  <button class="toggle" :class="{ active: s.status === 'present', present: true }" @click="setStatus(s, 'present')">উপস্থিত</button>
                  <button class="toggle" :class="{ active: s.status === 'absent', absent: true }" @click="setStatus(s, 'absent')">অনুপস্থিত</button>
                  <button class="toggle" :class="{ active: s.status === 'late', late: true }" @click="setStatus(s, 'late')">দেরি</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="form-actions" style="padding: 1rem 1.5rem; background: var(--color-bg);">
        <button class="btn btn-primary" :disabled="saving || !classId || !date" @click="submitAll">
          <span v-if="saving" class="spinner"></span>
          <span v-else>সংরক্ষণ করুন ({{ students.length }} জন)</span>
        </button>
      </div>
    </div>

    <div v-else-if="classId && date && !loading" class="empty-state">
      <p>এই শ্রেণির কোনো ছাত্রের তথ্য পাওয়া যায়নি</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated } = useAuth()

const classes = ref<any[]>([])
const students = ref<any[]>([])
const classId = ref('' as string | number)
const date = ref(new Date().toISOString().slice(0, 10))
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const success = ref('')

async function loadClasses() {
  try { const r = await api.get('/academic/classes'); classes.value = r.data?.data || [] } catch {}
}

async function loadStudents() {
  students.value = []
  if (!classId.value) return
  loading.value = true
  try {
    const r = await api.get(`/students?class_id=${classId.value}&per_page=100`)
    students.value = (r.data?.data?.data || r.data?.data || []).map((s: any) => ({
      id: s.id,
      user_id: s.user_id,
      name_bn: s.user?.name_bn ?? s.name_bn,
      name_en: s.user?.name_en ?? s.name_en,
      admission_number: s.admission_number,
      status: 'present' as string,
    }))
    await prefill()
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

// Pre-mark students who already have an attendance record for this date
async function prefill() {
  if (!classId.value || !date.value || !students.value.length) return
  try {
    const r = await api.get(`/attendance?date=${date.value}&per_page=200`)
    const records = r.data?.data?.data || []
    const byStudent: Record<number, string> = {}
    for (const rec of records) {
      if (rec.student_id) byStudent[rec.student_id] = rec.status
    }
    for (const s of students.value) {
      if (byStudent[s.user_id]) s.status = byStudent[s.user_id]
    }
  } catch (e) { console.error(e) }
}

function setStatus(s: any, status: string) { s.status = status }
function markAll(status: string) { students.value.forEach((s) => (s.status = status)) }

async function submitAll() {
  error.value = ''
  success.value = ''
  saving.value = true
  try {
    const requests = students.value.map((s) =>
      api.post('/attendance', {
        student_id: s.user_id,
        date: date.value,
        status: s.status,
        method: 'manual',
      })
    )
    await Promise.all(requests)
    success.value = `${students.value.length} জন ছাত্রের হাজিরা সংরক্ষিত হয়েছে!`
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'হাজিরা সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

if (isAuthenticated.value) onMounted(loadClasses)
</script>

<style scoped>
.status-toggle { display: flex; gap: 0.35rem; }
.toggle { padding: 0.35rem 0.75rem; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-bg-card); cursor: pointer; font-family: var(--font-bn); font-size: var(--text-xs); font-weight: var(--weight-medium); transition: all var(--transition-fast); }
.toggle:hover { border-color: var(--color-primary); }
.toggle.present.active { background: var(--color-success); color: #fff; border-color: var(--color-success); font-weight: var(--weight-bold); }
.toggle.absent.active { background: var(--color-error); color: #fff; border-color: var(--color-error); font-weight: var(--weight-bold); }
.toggle.late.active { background: var(--color-warning); color: #fff; border-color: var(--color-warning); font-weight: var(--weight-bold); }
</style>
