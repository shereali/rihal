<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>ছুটির দিন</h1>
        <p>মাদ্রাসার ছুটি, ছুটির প্রকার ও বার্ষিক ছুটি হিসাব পরিচালনা করুন</p>
      </div>
      <button class="btn btn-primary" @click="showForm = !showForm">
        <icon name="plus" /> নতুন ছুটির দিন
      </button>
    </div>

    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" />
        <input v-model="search" placeholder="ছুটির নাম খুঁজুন..." @keyup.enter="load" />
      </div>
      <select v-model="typeFilter" class="form-control compact" @change="load">
        <option value="">সব প্রকার</option>
        <option value="রাষ্ট্রীয়">রাষ্ট্রীয়</option>
        <option value="ধর্মীয়">ধর্মীয়</option>
        <option value="অনুষ্ঠানিক">অনুষ্ঠানিক</option>
        <option value="বিশেষ">বিশেষ</option>
        <option value="অন্যান্য">অন্যান্য</option>
      </select>
      <button class="btn btn-outline btn-sm" @click="load">
        <icon name="refresh" /> রিফ্রেশ
      </button>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createHoliday">
      <div class="form-heading">
        <div>
          <h2>নতুন ছুটির দিন যোগ করুন</h2>
          <p>ছুটির তারিখ ও প্রকার নির্বাচন করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-grid">
        <div class="form-group wide">
          <label>ছুটির নাম *</label>
          <input v-model="form.name_bn" class="form-control" required placeholder="যেমন: ঈদুল ফিতর ২০২৬" />
        </div>
        <div class="form-group">
          <label>তারিখ *</label>
          <input v-model="form.date" type="date" class="form-control" required />
        </div>
        <div class="form-group">
          <label>প্রকার</label>
          <select v-model="form.type" class="form-control">
            <option value="">নির্বাচন করুন</option>
            <option value="রাষ্ট্রীয়">রাষ্ট্রীয়</option>
            <option value="ধর্মীয়">ধর্মীয়</option>
            <option value="অনুষ্ঠানিক">অনুষ্ঠানিক</option>
            <option value="বিশেষ">বিশেষ</option>
            <option value="অন্যান্য">অন্যান্য</option>
          </select>
        </div>
        <div class="form-group wide">
          <label>বিবরণ</label>
          <textarea v-model="form.description_bn" class="form-control" rows="2" placeholder="ছুটির সংক্ষিপ্ত বর্ণনা..."></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'ছুটি যোগ করুন' }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!holidays.length" class="empty-card">
      <div class="empty-icon"><icon name="calendar" /></div>
      <h3>এখনও কোনো ছুটির দিন নেই</h3>
      <p>ছুটির তালিকা শুরু করতে প্রথম ছুটি যোগ করুন</p>
    </div>

    <div v-else class="holidays-table">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>তারিখ</th>
              <th>ছুটির নাম</th>
              <th>প্রকার</th>
              <th>বিবরণ</th>
              <th>অবস্থা</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="h in holidays" :key="h.id">
              <td class="text-center date-cell">{{ formatDate(h.date) }}</td>
              <td class="name-cell">
                <strong>{{ h.name_bn || h.name_en }}</strong>
              </td>
              <td>
                <span class="type-badge">{{ h.type || '-' }}</span>
              </td>
              <td class="description-cell">
                <span class="text-muted">{{ h.description_bn || '-' }}</span>
              </td>
              <td>
                <span class="status-badge" :class="h.is_active ? 'active' : 'inactive'">
                  {{ h.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                </span>
              </td>
              <td class="text-center">
                <NuxtLink :to="`/hr/holidays/${h.id}`" class="btn btn-ghost btn-sm">
                  <icon name="pencil" />
                </NuxtLink>
                <button class="btn btn-ghost btn-sm text-danger" @click="deleteHoliday(h.id)">
                  <icon name="delete" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const holidays = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const search = ref('')
const typeFilter = ref('')

interface HolidayForm {
  name_bn: string; name_en: string; date: string; type: string; description_bn: string
}

const form = reactive<HolidayForm>({
  name_bn: '', name_en: '', date: '', type: '', description_bn: '',
})

async function load() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (typeFilter.value) q.set('type', typeFilter.value)
    const r = await api.get(`/hr/holidays?${q}`)
    holidays.value = r.data?.data?.data || r.data?.data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function createHoliday() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/hr/holidays', form)
    showForm.value = false
    form.name_bn = ''; form.name_en = ''; form.date = ''
    form.type = ''; form.description_bn = ''
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'ছুটি যোগ করা যায়নি'
  } finally { saving.value = false }
}

async function deleteHoliday(id: number) {
  if (!confirm('এই ছুটির দিন মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/hr/holidays/${id}`)
    await load()
  } catch (e) { console.error(e) }
}

function formatDate(v: string) {
  return v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
}

onMounted(load)
</script>

<style scoped>
.module-page { max-width: 1200px; margin: 0 auto; padding-bottom: 2rem }
.page-header-row { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin-bottom:1.4rem }
.eyebrow { color:var(--color-primary); font:600 .78rem var(--font-bn) }
.page-header-row h1 { margin:.25rem 0; color:var(--color-primary); font:700 1.65rem var(--font-bn) }
.page-header-row p { color:var(--color-text-light); font:.88rem var(--font-bn) }
.toolbar { display:flex; gap:.7rem; padding:.7rem; margin-bottom:1rem; flex-wrap:wrap }
.search-box { display:flex; align-items:center; gap:.5rem; flex:1; padding:0 .75rem; background:var(--color-bg-muted); border-radius:10px; min-width:180px }
.search-box input { width:100%; padding:.65rem 0; border:0; outline:0; background:transparent; font:.86rem var(--font-bn) }
.form-control.compact { width:140px; padding:.62rem .7rem }
.create-panel { padding:1.2rem; margin-bottom:1rem; border:1px solid var(--color-primary-100) }
.form-heading { display:flex; justify-content:space-between; margin-bottom:1rem }
.form-heading h2 { font:700 1rem var(--font-bn) }
.close-btn { border:0; background:transparent; font-size:1.5rem; color:var(--color-text-muted); cursor:pointer }
.form-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.7rem }
.form-group label { display:block; margin-bottom:.3rem; font:600 .78rem var(--font-bn) }
.form-group.wide { grid-column:span 2 }
.form-actions { display:flex; gap:.6rem; margin-top:1rem }
.holidays-table { background:#fff; border:1px solid var(--color-border-light); border-radius:15px; overflow:hidden }
.table-responsive { overflow-x:auto }
.table { width:100%; border-collapse:collapse; font:.82rem var(--font-bn) }
.table th { background:rgba(0,0,0,0.03); padding:.7rem 1rem; text-align:left; font:600 .75rem var(--font-bn); color:var(--color-text-muted); border-bottom:1px solid var(--color-border-light); white-space:nowrap }
.table td { padding:.6rem 1rem; border-bottom:1px solid var(--color-border-light); vertical-align:middle }
.table tr:last-child td { border-bottom:0 }
.table tr:hover td { background:#fafbfc }
.text-center { text-align:center }
.date-cell { font-weight:600; color:var(--color-primary) }
.name-cell { font-weight:600; color:var(--color-text) }
.description-cell { font-size:.78rem; max-width:250px }
.text-muted { color:var(--color-text-muted) }
.type-badge { display:inline-block; padding:.15rem .5rem; background:#f0f4f8; border-radius:99px; font:.65rem var(--font-bn); color:var(--color-text-muted) }
.status-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.15rem .5rem; border-radius:99px; font:.65rem var(--font-bn); font-weight:600 }
.status-badge.active { background:#e6f4ec; color:#19724a }
.status-badge.inactive { background:#fde8e8; color:#a03030 }
.text-danger { color:#a03030 }
@media(max-width:700px){ .page-header-row { align-items:flex-start; flex-direction:column } .toolbar { flex-wrap:wrap } .search-box { min-width:100% } .form-grid { grid-template-columns:1fr } .form-group.wide { grid-column:auto } }
</style>