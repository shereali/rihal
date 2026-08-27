<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">প্রশাসনিক বিভাগ</span>
        <h1>স্টাফ ও কর্মকর্তা ব্যবস্থাপনা</h1>
        <p class="page-subtitle">মাদ্রাসার কর্মকর্তা, শিক্ষক ও কর্মচারীদের তথ্য, পদবী, বিভাগ ও দায়িত্ব</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন কর্মী যোগ করুন
        </button>
        <button class="btn btn-outline" @click="loadStaff">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid" v-if="staff.length">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ staff.length.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট কর্মকর্তা ও কর্মী</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ activeCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">সক্রিয় কর্মী</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ departmentCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">কার্যরত বিভাগ</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(totalSalary) }} ৳</span>
          <span class="stat-label">মোট মাসিক বেতন</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="নাম, পদবী, বিভাগ, ফোন বা ইমেইল খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="departmentFilter" class="form-select">
        <option value="">সব বিভাগ (All Departments)</option>
        <option value="Academic">একাডেমিক (Academic)</option>
        <option value="Administration">প্রশাসন (Administration)</option>
        <option value="Finance">হিসাব ও অর্থ (Finance)</option>
        <option value="IT">আইটি ও প্রযুক্তি (IT)</option>
        <option value="Support">সহায়ক কর্মী (Support)</option>
      </select>
      <select v-model="statusFilter" class="form-select">
        <option value="">সব অবস্থা</option>
        <option value="active">সক্রিয় কর্মী</option>
        <option value="inactive">নিষ্ক্রিয় কর্মী</option>
      </select>
      <div class="pagination-info" v-if="filteredStaff.length">
        মোট <span class="highlight">{{ filteredStaff.length.toLocaleString('bn-BD') }}</span> জন কর্মী
      </div>
    </div>

    <!-- Create / Edit Staff Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingId ? 'কর্মী তথ্য সম্পাদনা' : 'নতুন কর্মী যোগ করুন' }}</h3>
            <p>ব্যক্তিগত তথ্য, পদবী, বিভাগ ও যোগাযোগের বিবরণ পূরণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showForm = false">×</button>
        </div>

        <form @submit.prevent="saveStaff" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">পূর্ণ নাম (বাংলা) *</label>
              <input v-model="form.name_bn" class="form-input" required placeholder="যেমন: মাওলানা আব্দুর রহমান" />
            </div>
            <div class="form-group">
              <label class="form-label">পূর্ণ নাম (ইংরেজি)</label>
              <input v-model="form.name_en" class="form-input" placeholder="e.g. Abdur Rahman" />
            </div>
            <div class="form-group">
              <label class="form-label">পদবী *</label>
              <input v-model="form.designation" class="form-input" required placeholder="যেমন: প্রধান শিক্ষক / হিসাবরক্ষক" />
            </div>
            <div class="form-group">
              <label class="form-label">বিভাগ *</label>
              <select v-model="form.department" class="form-select" required>
                <option value="Academic">একাডেমিক (Academic)</option>
                <option value="Administration">প্রশাসন (Administration)</option>
                <option value="Finance">হিসাব ও অর্থ (Finance)</option>
                <option value="IT">আইটি ও প্রযুক্তি (IT)</option>
                <option value="Support">সহায়ক কর্মী (Support)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">ফোন নম্বর *</label>
              <input v-model="form.phone" class="form-input" required placeholder="০১৭১২৩৪৫৬৭৮" />
            </div>
            <div class="form-group">
              <label class="form-label">ইমেইল ঠিকানা</label>
              <input v-model="form.email" type="email" class="form-input" placeholder="staff@example.com" />
            </div>
            <div class="form-group">
              <label class="form-label">যোগদানের তারিখ</label>
              <input v-model="form.join_date" type="date" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">মাসিক বেতন (টাকা)</label>
              <input v-model.number="form.salary" type="number" min="0" class="form-input" placeholder="০" />
            </div>
            <div class="form-group">
              <label class="form-label">জাতীয় পরিচয়পত্র (NID) নম্বর</label>
              <input v-model="form.nid_number" class="form-input" placeholder="NID নম্বর" />
            </div>
            <div class="form-group">
              <label class="form-label">পিতা / অভিভাবকের নাম</label>
              <input v-model="form.fathers_name_bn" class="form-input" placeholder="পিতার নাম" />
            </div>
            <div class="form-group wide">
              <label class="form-label">বর্তমান ঠিকানা</label>
              <textarea v-model="form.address_bn" class="form-textarea" rows="2" placeholder="পূর্ণ ঠিকানা..."></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'কর্মী যোগ করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>কর্মী তালিকা লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!filteredStaff.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="users" /></div>
      <h3>কোনো কর্মী পাওয়া যায়নি</h3>
      <p>নতুন কর্মী যোগ করে স্টাফ তালিকা শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate"><icon name="plus" /> প্রথম কর্মী যোগ করুন</button>
    </div>

    <!-- Staff Cards Grid -->
    <div v-else class="staff-grid">
      <div v-for="s in filteredStaff" :key="s.id" class="staff-card card">
        <div class="staff-card-header">
          <div class="staff-avatar" :style="{ backgroundColor: userColor(s.name_bn || s.name_en || 'S') }">
            {{ initials(s.name_bn || s.name_en) }}
          </div>
          <div class="staff-title-group">
            <h3>{{ s.name_bn || s.name_en }}</h3>
            <span class="designation">{{ s.designation || 'কর্মকর্তা' }}</span>
          </div>
          <span class="status-pill" :class="s.is_active ? 'badge-approved' : 'badge-rejected'">
            <span class="status-dot" />
            {{ s.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="staff-meta">
          <div class="meta-row" v-if="s.department">
            <icon name="building" class="meta-icon" />
            <span>বিভাগ: <strong>{{ formatDept(s.department) }}</strong></span>
          </div>
          <div class="meta-row" v-if="s.phone">
            <icon name="chat" class="meta-icon" />
            <span>{{ s.phone }}</span>
          </div>
          <div class="meta-row" v-if="s.email">
            <icon name="external" class="meta-icon" />
            <span>{{ s.email }}</span>
          </div>
          <div class="meta-row" v-if="s.salary">
            <icon name="money" class="meta-icon" />
            <span>বেতন: <strong>{{ formatCurrency(s.salary) }} ৳</strong></span>
          </div>
        </div>

        <div class="staff-actions">
          <NuxtLink :to="`/hr/staff/${s.id}`" class="view-link">
            বিস্তারিত তথ্য <icon name="arrow-right" />
          </NuxtLink>
          <div class="action-buttons">
            <button class="action-btn" @click="openEdit(s)" title="সম্পাদনা">
              <icon name="pencil" />
            </button>
            <button class="action-btn text-danger" @click="deleteStaff(s.id)" title="মুছুন">
              <icon name="delete" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const staff = ref<any[]>([])
const filteredStaff = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')

const search = ref('')
const departmentFilter = ref('')
const statusFilter = ref('')

const form = reactive({
  name_bn: '',
  name_en: '',
  designation: '',
  department: 'Academic',
  phone: '',
  email: '',
  join_date: '',
  salary: 0,
  fathers_name_bn: '',
  nid_number: '',
  address_bn: '',
  is_active: true,
})

const activeCount = computed(() => staff.value.filter(s => s.is_active).length)
const departmentCount = computed(() => {
  const depts = new Set(staff.value.map(s => s.department).filter(Boolean))
  return depts.size
})
const totalSalary = computed(() => staff.value.reduce((sum, s) => sum + (Number(s.salary) || 0), 0))

async function loadStaff() {
  loading.value = true
  try {
    const res = await api.get('/hr/staff?per_page=100')
    staff.value = res.data?.data?.data || res.data?.data || []
    filterStaff()
  } catch (err) {
    console.error('Failed to load staff:', err)
  } finally {
    loading.value = false
  }
}

function filterStaff() {
  let list = [...staff.value]
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(s =>
      (s.name_bn?.toLowerCase().includes(q) ?? false) ||
      (s.name_en?.toLowerCase().includes(q) ?? false) ||
      (s.designation?.toLowerCase().includes(q) ?? false) ||
      (s.phone?.toLowerCase().includes(q) ?? false) ||
      (s.email?.toLowerCase().includes(q) ?? false) ||
      (s.department?.toLowerCase().includes(q) ?? false)
    )
  }
  if (departmentFilter.value) {
    list = list.filter(s => (s.department || '').toLowerCase() === departmentFilter.value.toLowerCase())
  }
  if (statusFilter.value) {
    list = list.filter(s => (statusFilter.value === 'active' ? s.is_active : !s.is_active))
  }
  filteredStaff.value = list
}

watch([search, departmentFilter, statusFilter], filterStaff)

function openCreate() {
  editingId.value = null
  error.value = ''
  form.name_bn = ''
  form.name_en = ''
  form.designation = ''
  form.department = 'Academic'
  form.phone = ''
  form.email = ''
  form.join_date = new Date().toISOString().split('T')[0]
  form.salary = 0
  form.fathers_name_bn = ''
  form.nid_number = ''
  form.address_bn = ''
  form.is_active = true
  showForm.value = true
}

function openEdit(s: any) {
  editingId.value = s.id
  error.value = ''
  form.name_bn = s.name_bn || ''
  form.name_en = s.name_en || ''
  form.designation = s.designation || ''
  form.department = s.department || 'Academic'
  form.phone = s.phone || ''
  form.email = s.email || ''
  form.join_date = s.join_date || new Date().toISOString().split('T')[0]
  form.salary = Number(s.salary) || 0
  form.fathers_name_bn = s.fathers_name_bn || ''
  form.nid_number = s.nid_number || ''
  form.address_bn = s.address_bn || ''
  form.is_active = !!s.is_active
  showForm.value = true
}

async function saveStaff() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/hr/staff/${editingId.value}`, form)
    } else {
      await api.post('/hr/staff', form)
    }
    showForm.value = false
    await loadStaff()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'কর্মী সংরক্ষণে ত্রুটি দেখা দিয়েছে।'
  } finally {
    saving.value = false
  }
}

async function deleteStaff(id: number) {
  if (!confirm('আপনি কি নিশ্চিত যে এই কর্মী মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/hr/staff/${id}`)
    await loadStaff()
  } catch (e) {
    console.error(e)
  }
}

function formatDept(d: string) {
  const map: Record<string, string> = {
    Academic: 'একাডেমিক',
    Administration: 'প্রশাসন',
    Finance: 'হিসাব ও অর্থ',
    IT: 'আইটি ও প্রযুক্তি',
    Support: 'সহায়ক কর্মী',
  }
  return map[d] || d
}

function formatCurrency(val: number) {
  if (!val) return '০'
  return val.toLocaleString('bn-BD')
}

function initials(name: string) {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
}

function userColor(name: string) {
  const colors = ['#145032', '#0d7a5f', '#1b6b93', '#b45309', '#0284c7', '#7c3aed', '#db2777']
  let hash = 0
  for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colors[Math.abs(hash) % colors.length]
}

onMounted(loadStaff)
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

/* Staff Cards Grid */
.staff-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

.staff-card {
  padding: 1.35rem;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 14px;
}

.staff-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.staff-card-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 1rem;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid var(--color-border-light);
}

.staff-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 0.95rem;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.staff-title-group {
  flex: 1;
  min-width: 0;
}

.staff-title-group h3 {
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.designation {
  font-size: 0.8rem;
  color: var(--color-primary);
  font-weight: 600;
  display: block;
}

.staff-meta {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-bottom: 1.25rem;
  font-size: 0.83rem;
  color: var(--color-text-light);
}

.meta-row {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.meta-icon {
  font-size: 0.95rem;
  color: var(--color-text-muted);
}

.staff-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid var(--color-border-light);
}

.view-link {
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--color-primary);
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  text-decoration: none;
}

.view-link:hover {
  text-decoration: underline;
}

.action-buttons {
  display: flex;
  gap: 0.35rem;
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