<template>
  <div class="bulk-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/attendance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>বাল্ক হাজিরা</h1>
        <p class="text-muted">শ্রেণি নির্বাচন করে একসাথে হাজিরা চিহ্নিত করুন</p>
      </div>
    </div>

    <div class="filters card">
      <div class="form-row">
        <div class="form-group">
          <label>শ্রেণি *</label>
          <select v-model="classId" :disabled="loading" @change="loadStudents">
            <option value="">শ্রেণি নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>তারিখ *</label>
          <input v-model="date" type="date" :disabled="loading" @change="prefill" />
        </div>
        <div class="form-group bulk-actions">
          <button class="btn btn-outline btn-sm" @click="markAll('present')">সব উপস্থিত</button>
          <button class="btn btn-outline btn-sm" @click="markAll('absent')">সব অনুপস্থিত</button>
        </div>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div v-if="students.length" class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr><th>নাম</th><th>ভর্তি নং</th><th>অবস্থা</th></tr>
            </thead>
            <tbody>
              <tr v-for="s in students" :key="s.id">
                <td>{{ s.name_bn || s.name_en }}</td>
                <td>{{ s.admission_number || '-' }}</td>
                <td>
                  <div class="status-toggle">
                    <button class="toggle" :class="{ active: s.status === 'present', present: true }" @click="setStatus(s, 'present')">উপস্থিত</button>
                    <button class="toggle" :class="{ active: s.status === 'absent', absent: true }" @click="setStatus(s, 'absent')">অনুপস্থিত</button>
                    <button class="toggle" :class="{ active: s.status === 'late', late: true }" @click="setStatus(s, 'late')">দেরি</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" :disabled="saving || !classId || !date" @click="submitAll">
            <span v-if="saving" class="spinner"></span>
            <span v-else>সংরক্ষণ করুন ({{ students.length }})</span>
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="classId && date && !loading" class="empty-state"><p>এই শ্রেণির কোনো ছাত্র নেই</p></div>
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
.bulk-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.25rem; }
.header-left h1 { margin: 0.4rem 0 0; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.filters { padding: 1rem 1.25rem; margin-bottom: 1.25rem; background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.form-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group select, .form-group input { padding: 0.65rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 1rem; font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg); }
.bulk-actions { flex-direction: row; gap: 0.5rem; }
.btn { padding: 0.6rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; }
.btn-sm { padding: 0.4rem 0.75rem; font-size: 0.85rem; }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.status-toggle { display: flex; gap: 0.35rem; }
.toggle { padding: 0.35rem 0.7rem; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-bg); cursor: pointer; font-family: 'Noto Sans Bengali', sans-serif; font-size: 0.85rem; }
.toggle.present.active { background: var(--color-success); color: #fff; border-color: var(--color-success); }
.toggle.absent.active { background: var(--color-error); color: #fff; border-color: var(--color-error); }
.toggle.late.active { background: var(--color-warning); color: #fff; border-color: var(--color-warning); }
.form-actions { margin-top: 1rem; }
.spinner { width: 16px; height: 16px; border: 2px solid var(--color-text-on-primary); border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 0.7rem 1rem; border-radius: 8px; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: #fce4e4; color: var(--color-error); }
.alert-success { background: #e8f5e9; color: var(--color-success); }
.table-responsive { overflow-x: auto; }
.empty-state { padding: 2rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
</style>
