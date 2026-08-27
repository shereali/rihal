<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>ছুটির দিন ব্যবস্থাপনা</h1>
        <p class="page-subtitle">মাদ্রাসার বাৎসরিক ছুটির দিন, ধর্মীয় উৎসব ও সাধারণ ছুটি পরিচালনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন ছুটির দিন
        </button>
        <button class="btn btn-outline" @click="load">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid" v-if="holidays.length">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="calendar" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ holidays.length.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট ছুটির দিন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ upcomingCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">আসন্ন ছুটি</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="repeat" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ recurringCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">প্রতি বছরের ছুটি</span>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ছুটির নাম বা বিবরণ খুঁজুন..." @keyup.enter="load" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; load()">×</button>
      </div>
      <select v-model="typeFilter" class="form-select" @change="load">
        <option value="">সব প্রকার (All Types)</option>
        <option value="রাষ্ট্রীয়">রাষ্ট্রীয়</option>
        <option value="ধর্মীয়">ধর্মীয়</option>
        <option value="অনুষ্ঠানিক">অনুষ্ঠানিক</option>
        <option value="বিশেষ">বিশেষ</option>
        <option value="অন্যান্য">অন্যান্য</option>
      </select>
      <div class="pagination-info" v-if="holidays.length">
        মোট <span class="highlight">{{ holidays.length.toLocaleString('bn-BD') }}</span> টি ছুটি
      </div>
    </div>

    <!-- Create / Edit Holiday Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingId ? 'ছুটির দিন সম্পাদনা' : 'নতুন ছুটির দিন যোগ করুন' }}</h3>
            <p>ছুটির নাম, তারিখ, প্রকার ও বিবরণ নির্ধারণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showForm = false">×</button>
        </div>

        <form @submit.prevent="saveHoliday" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>

          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">ছুটির নাম (বাংলা) *</label>
              <input v-model="form.name_bn" class="form-input" required placeholder="যেমন: ঈদুল ফিতর" />
            </div>
            <div class="form-group">
              <label class="form-label">ছুটির নাম (ইংরেজি)</label>
              <input v-model="form.name_en" class="form-input" placeholder="e.g. Eid-ul-Fitr" />
            </div>
            <div class="form-group">
              <label class="form-label">ছুটির প্রকার *</label>
              <select v-model="form.type" class="form-select" required>
                <option value="ধর্মীয়">ধর্মীয়</option>
                <option value="রাষ্ট্রীয়">রাষ্ট্রীয়</option>
                <option value="অনুষ্ঠানিক">অনুষ্ঠানিক</option>
                <option value="বিশেষ">বিশেষ</option>
                <option value="অন্যান্য">অন্যান্য</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">শুরুর তারিখ *</label>
              <input v-model="form.start_date" type="date" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">শেষ তারিখ (ঐচ্ছিক)</label>
              <input v-model="form.end_date" type="date" class="form-input" />
            </div>
            <div class="form-group wide">
              <label class="custom-checkbox">
                <input type="checkbox" v-model="form.is_recurring" />
                <span class="checkbox-text">প্রতি বছর পুনরাবৃত্তি হবে (Recurring Holiday)</span>
              </label>
            </div>
            <div class="form-group wide">
              <label class="form-label">বিবরণ</label>
              <textarea v-model="form.description_bn" class="form-textarea" rows="2" placeholder="ছুটি সংক্রান্ত বিবরণ..."></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'ছুটি যোগ করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>ছুটির তালিকা লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!holidays.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="calendar" /></div>
      <h3>কোনো ছুটির দিন পাওয়া যায়নি</h3>
      <p>নতুন ছুটির দিন যোগ করে ক্যালেন্ডার তৈরি করুন</p>
      <button class="btn btn-primary" @click="openCreate"><icon name="plus" /> প্রথম ছুটির দিন যোগ করুন</button>
    </div>

    <!-- Holidays Grid -->
    <div v-else class="holidays-grid">
      <div v-for="holiday in holidays" :key="holiday.id" class="holiday-card card">
        <div class="holiday-date-badge">
          <span class="holiday-month">{{ formatMonth(holiday.start_date) }}</span>
          <span class="holiday-day">{{ formatDay(holiday.start_date) }}</span>
        </div>

        <div class="holiday-info">
          <div class="holiday-title-row">
            <h3>{{ holiday.name_bn || holiday.name_en || holiday.title_bn || holiday.title }}</h3>
            <span class="type-tag">{{ holiday.type || holiday.holiday_type || 'সাধারণ' }}</span>
          </div>
          <p v-if="holiday.description_bn" class="holiday-desc">{{ holiday.description_bn }}</p>
          <div class="holiday-dates-meta">
            <icon name="calendar" class="meta-icon" />
            <span>{{ formatDate(holiday.start_date) }}</span>
            <span v-if="holiday.end_date"> — {{ formatDate(holiday.end_date) }}</span>
            <span v-if="holiday.is_recurring" class="recurring-tag"><icon name="repeat" /> বার্ষিক</span>
          </div>
        </div>

        <div class="holiday-actions">
          <button class="action-btn" @click="openEdit(holiday)" title="সম্পাদনা">
            <icon name="pencil" />
          </button>
          <button class="action-btn text-danger" @click="deleteHoliday(holiday.id)" title="মুছুন">
            <icon name="delete" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, computed } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const holidays = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')
const search = ref('')
const typeFilter = ref('')

const form = reactive({
  name_bn: '',
  name_en: '',
  type: 'ধর্মীয়',
  start_date: '',
  end_date: '',
  description_bn: '',
  is_recurring: false,
})

const upcomingCount = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return holidays.value.filter(h => (h.start_date || '') >= today).length
})
const recurringCount = computed(() => holidays.value.filter(h => h.is_recurring).length)

async function load() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (typeFilter.value) q.set('type', typeFilter.value)
    const r = await api.get(`/hr/holidays?${q.toString()}`)
    holidays.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  error.value = ''
  form.name_bn = ''
  form.name_en = ''
  form.type = 'ধর্মীয়'
  form.start_date = new Date().toISOString().split('T')[0]
  form.end_date = ''
  form.description_bn = ''
  form.is_recurring = false
  showForm.value = true
}

function openEdit(h: any) {
  editingId.value = h.id
  error.value = ''
  form.name_bn = h.name_bn || h.title_bn || ''
  form.name_en = h.name_en || h.title || ''
  form.type = h.type || h.holiday_type || 'ধর্মীয়'
  form.start_date = h.start_date || h.date || ''
  form.end_date = h.end_date || ''
  form.description_bn = h.description_bn || ''
  form.is_recurring = !!h.is_recurring
  showForm.value = true
}

async function saveHoliday() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/hr/holidays/${editingId.value}`, form)
    } else {
      await api.post('/hr/holidays', form)
    }
    showForm.value = false
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'ছুটি সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function deleteHoliday(id: number) {
  if (!confirm('আপনি কি এই ছুটির দিন মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/hr/holidays/${id}`)
    await load()
  } catch (e) {
    console.error(e)
  }
}

function formatMonth(dStr: string) {
  if (!dStr) return ''
  try {
    return new Date(dStr).toLocaleDateString('bn-BD', { month: 'short' })
  } catch { return '' }
}

function formatDay(dStr: string) {
  if (!dStr) return ''
  try {
    return new Date(dStr).toLocaleDateString('bn-BD', { day: 'numeric' })
  } catch { return '' }
}

function formatDate(dateStr: string) {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch { return dateStr }
}

onMounted(load)
</script>

<style scoped>
.page-wrapper {
  max-width: 1320px;
  margin: 0 auto;
  padding: 1.75rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-primary);
  letter-spacing: 0.08em;
}

.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0.2rem 0 0.35rem;
  color: var(--color-text);
}

.page-subtitle {
  color: var(--color-text-light);
  font-size: 0.88rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

.clear-search-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0 0.2rem;
}

.pagination-info {
  margin-left: auto;
  font-size: 0.85rem;
  color: var(--color-text-light);
}

.pagination-info .highlight {
  font-weight: 700;
  color: var(--color-primary);
}

/* Holidays Grid */
.holidays-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
  gap: 1.25rem;
}

.holiday-card {
  padding: 1.35rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 14px;
  position: relative;
}

.holiday-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.holiday-date-badge {
  background: rgba(20, 80, 50, 0.08);
  border-radius: 12px;
  padding: 0.5rem 0.75rem;
  text-align: center;
  min-width: 52px;
  flex-shrink: 0;
}

.holiday-month {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-primary);
}

.holiday-day {
  display: block;
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--color-text);
  line-height: 1.2;
}

.holiday-info {
  flex: 1;
  min-width: 0;
}

.holiday-title-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.35rem;
  flex-wrap: wrap;
}

.holiday-title-row h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0;
}

.type-tag {
  display: inline-block;
  padding: 0.15rem 0.55rem;
  background: rgba(20, 80, 50, 0.07);
  color: var(--color-primary);
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.holiday-desc {
  font-size: 0.83rem;
  color: var(--color-text-light);
  margin: 0 0 0.5rem;
}

.holiday-dates-meta {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: var(--color-text-light);
  flex-wrap: wrap;
}

.meta-icon {
  font-size: 0.9rem;
  color: var(--color-text-muted);
}

.recurring-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #7c3aed;
  font-weight: 600;
  margin-left: 0.3rem;
}

.holiday-actions {
  display: flex;
  gap: 0.35rem;
  align-self: flex-start;
}

.action-btn {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  border: 1px solid var(--color-border-light);
  background: var(--color-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-text-light);
  transition: all 0.15s ease;
}

.action-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--color-text);
  transform: translateY(-1px);
}

.action-btn.text-danger:hover {
  color: #dc2626;
  border-color: #dc2626;
  background: rgba(239, 68, 68, 0.1);
}

.btn {
  padding: 0.6rem 1.15rem;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #145032 0%, #1a6b43 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35);
}

.btn-outline {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.btn-ghost {
  background: transparent;
  color: var(--color-text);
}

.btn-ghost:hover {
  background: rgba(0, 0, 0, 0.05);
}

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.custom-checkbox { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 500; cursor: pointer; }
.custom-checkbox input { accent-color: var(--color-primary); width: 16px; height: 16px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.empty-icon-wrap {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1rem;
}
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>