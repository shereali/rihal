<template>
  <div class="students-page slide-up-fade">
    <!-- Printable Header (Visible only when printed) -->
    <div class="print-header-block print-only">
      <div class="print-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>
      <h1 class="institution-title">দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h1>
      <h2 class="ledger-badge-title">শিক্ষার্থী তালিকা ও সাধারণ রেজিস্টার</h2>
      <div class="print-meta-bar">
        <span><strong>শ্রেণি:</strong> {{ selectedClassName || 'সকল শ্রেণি' }}</span>
        <span><strong>মোট শিক্ষার্থী:</strong> {{ totalStudents.toLocaleString('bn-BD') }} জন</span>
        <span><strong>মুদ্রণের তারিখ:</strong> {{ printDateBn }}</span>
      </div>
    </div>

    <!-- On-screen Page Header -->
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <span class="eyebrow">শিক্ষার্থী ব্যবস্থাপনা</span>
        <h1>ছাত্র তালিকা</h1>
        <p class="page-subtitle">প্রতিষ্ঠানে অধ্যয়নরত সকল ছাত্রের তথ্য, শ্রেণি, রোল ও প্রোফাইল ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printPage" title="তালিকা প্রিন্ট করুন">
          <Icon name="mdi:printer" /> প্রিন্ট করুন
        </button>
        <NuxtLink to="/students/admission-register" class="btn btn-outline">
          <Icon name="mdi:book-open-page-variant" /> ভর্তি খাতা
        </NuxtLink>
        <NuxtLink to="/enrollments/create" class="btn btn-outline">
          <Icon name="mdi:account-plus-outline" /> নতুন ভর্তি
        </NuxtLink>
        <NuxtLink to="/students/create" class="btn btn-primary">
          <Icon name="mdi:plus" /> নতুন ছাত্র
        </NuxtLink>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card no-print">
      <div class="search-box">
        <Icon name="mdi:magnify" class="search-icon" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="ছাত্রের নাম, ইংরেজি নাম, ভর্তি নং বা মোবাইল নম্বর দিয়ে খুঁজুন..."
          @keyup.enter="handleSearch"
        />
        <button v-if="searchQuery" class="clear-search-btn" @click="searchQuery = ''; handleSearch()">×</button>
      </div>

      <div class="select-wrapper">
        <select v-model="classFilter" class="form-select" @change="handleSearch">
          <option value="">সকল শ্রেণি (All Classes)</option>
          <option v-for="c in classOptions" :key="c.id" :value="c.id">
            {{ c.class_name_bn || c.class_name || c.name_bn || c.name_en }}
          </option>
        </select>
      </div>

      <div class="select-wrapper">
        <select v-model="statusFilter" class="form-select" @change="handleSearch">
          <option value="">সকল অবস্থা (All Status)</option>
          <option value="active">সক্রিয় ছাত্র</option>
          <option value="inactive">নিষ্ক্রিয় / প্রস্থানকৃত</option>
        </select>
      </div>

      <div class="pagination-info" v-if="totalStudents">
        মোট <span class="highlight">{{ totalStudents.toLocaleString('bn-BD') }}</span> জন ছাত্র
      </div>
    </div>

    <!-- Bulk Action Banner -->
    <div v-if="selectedIds.length > 0" class="bulk-action-bar card no-print">
      <div class="bulk-info">
        <span class="bulk-count">{{ selectedIds.length.toLocaleString('bn-BD') }}</span> জন ছাত্র নির্বাচিত হয়েছে
      </div>
      <div class="bulk-btns">
        <button class="btn btn-outline btn-sm" @click="printPage">
          <Icon name="mdi:printer" /> নির্বাচিত প্রিন্ট
        </button>
        <button class="btn btn-ghost btn-sm" @click="selectedIds = []">
          নির্বাচন বাতিল
        </button>
      </div>
    </div>

    <!-- Students Table Card -->
    <div class="card table-card">
      <div v-if="loading" class="loading-state">
        <div class="spinner" />
        <p>ছাত্র তথ্য লোড হচ্ছে...</p>
      </div>

      <div v-else-if="loadError" class="empty-state" role="alert">
        <Icon name="mdi:alert-circle-outline" size="48" class="text-error mb-2" />
        <p>{{ loadError }}</p>
        <button type="button" class="btn btn-primary mt-3" @click="loadStudents(currentPage)">আবার চেষ্টা করুন</button>
      </div>

      <div v-else-if="(studentList || []).length === 0" class="empty-state">
        <Icon name="mdi:account-school-outline" size="48" style="color: var(--color-border);" />
        <p class="mt-2 font-semibold">কোনো ছাত্রের রেকর্ড পাওয়া যায়নি</p>
        <p class="text-muted text-sm mb-3">নতুন ছাত্র ভর্তি করাতে নিচের বাটনে ক্লিক করুন</p>
        <NuxtLink to="/students/create" class="btn btn-primary">প্রথম ছাত্র যোগ করুন</NuxtLink>
      </div>

      <div v-else class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th class="no-print" style="width: 40px;">
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  @change="toggleSelectAll"
                  title="সব নির্বাচন করুন"
                />
              </th>
              <th style="width: 60px;">ছবি</th>
              <th>শিক্ষার্থীর নাম</th>
              <th>দাখেলা / ভর্তি নং</th>
              <th>শ্রেণি</th>
              <th>পিতার নাম / মোবাইল</th>
              <th>ভর্তির তারিখ</th>
              <th class="text-center">অবস্থা</th>
              <th class="text-right no-print">ক্রিয়া</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="student in studentList"
              :key="student.id"
              :class="{ 'selected-row': selectedIds.includes(student.id) }"
            >
              <td class="no-print" @click.stop>
                <input
                  type="checkbox"
                  :checked="selectedIds.includes(student.id)"
                  @change="toggleSelect(student.id)"
                />
              </td>
              <td>
                <div class="student-avatar" v-if="student.user?.profile_image">
                  <img :src="student.user.profile_image" :alt="student.name_bn" />
                </div>
                <div v-else class="student-avatar-placeholder" :style="{ backgroundColor: getAvatarColor(student.name_bn || student.name_en || '') }">
                  {{ (student.name_bn || student.name_en || '?').charAt(0) }}
                </div>
              </td>
              <td>
                <p class="font-weight-medium mb-0">
                  <NuxtLink :to="`/students/${student.id}`" class="student-name-link">
                    {{ student.name_bn || student.name_en }}
                  </NuxtLink>
                </p>
                <p class="text-muted text-xs mb-0" v-if="student.name_en && student.name_en !== student.name_bn">
                  {{ student.name_en }}
                </p>
              </td>
              <td>
                <code class="mono-font" v-if="student.admission_number">{{ student.admission_number }}</code>
                <span class="text-muted" v-else>—</span>
              </td>
              <td>
                <span class="type-tag">
                  {{ student.enrollments?.[0]?.class?.name_bn || student.enrollments?.[0]?.class?.class_name || student.class?.name_bn || '—' }}
                </span>
              </td>
              <td>
                <div>{{ student.father_name || student.guardian_name || '—' }}</div>
                <div class="text-muted text-xs mono-font" v-if="student.father_phone || student.guardian_phone || student.user?.phone">
                  {{ student.father_phone || student.guardian_phone || student.user?.phone }}
                </div>
              </td>
              <td>{{ formatDate(student.admission_date || student.created_at) }}</td>
              <td class="text-center">
                <span class="status-pill" :class="student.status === 'active' || student.is_active ? 'badge-approved' : 'badge-rejected'">
                  <span class="status-dot" />
                  {{ (student.status === 'active' || student.is_active) ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                </span>
              </td>
              <td class="text-right no-print" @click.stop>
                <div class="btn-group btn-group-sm">
                  <NuxtLink :to="`/students/${student.id}`" class="btn btn-outline btn-sm" title="বিস্তারিত দেখুন">
                    <Icon name="mdi:eye" :size="15" />
                  </NuxtLink>
                  <NuxtLink :to="`/students/${student.id}/edit`" class="btn btn-outline btn-sm" title="সম্পাদনা">
                    <Icon name="mdi:pencil" :size="15" />
                  </NuxtLink>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPages > 1" class="pagination-wrapper no-print">
        <div class="pagination">
          <button class="page-btn nav-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
            <Icon name="mdi:chevron-left" /> পূর্ববর্তী
          </button>
          <template v-for="(p, idx) in visiblePages" :key="idx">
            <span v-if="p === '...'" class="pagination-ellipsis">...</span>
            <button 
              v-else 
              :class="['page-btn', { active: p === currentPage }]" 
              @click="goToPage(Number(p))"
            >
              {{ p }}
            </button>
          </template>
          <button class="page-btn nav-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
            পরবর্তী <Icon name="mdi:chevron-right" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const loadError = ref('')
const students = ref<any>(null)
const totalPages = ref(1)
const currentPage = ref(1)
const searchQuery = ref('')
const classFilter = ref('')
const statusFilter = ref('')
const classOptions = ref<any[]>([])
const selectedIds = ref<number[]>([])

const studentList = computed<any[]>(() => {
  const data = students.value?.data?.data || students.value?.data || []
  return Array.isArray(data) ? data : []
})

const totalStudents = computed(() => {
  return students.value?.data?.total ?? students.value?.data?.meta?.total ?? students.value?.total ?? studentList.value.length
})

const isAllSelected = computed(() => {
  return studentList.value.length > 0 && selectedIds.value.length === studentList.value.length
})

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = studentList.value.map(s => s.id)
  }
}

function toggleSelect(id: number) {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

const selectedClassName = computed(() => {
  if (!classFilter.value) return ''
  const c = classOptions.value.find(item => String(item.id) === String(classFilter.value))
  return c?.class_name_bn || c?.class_name || c?.name_bn || c?.name_en || ''
})

const printDateBn = computed(() => {
  return new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' })
})

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages: (number | string)[] = []
  if (current <= 4) {
    pages.push(1, 2, 3, 4, 5, '...', total)
  } else if (current >= total - 3) {
    pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total)
  } else {
    pages.push(1, '...', current - 1, current, current + 1, '...', total)
  }
  return pages
})

async function loadClasses() {
  try {
    const res = await api.get('/academic/classes').catch(() => null)
    if (res?.data?.data && Array.isArray(res.data.data)) {
      classOptions.value = res.data.data
    }
  } catch (e) {
    console.error('Failed to load classes:', e)
  }
}

async function loadStudents(page = 1) {
  loading.value = true
  loadError.value = ''
  try {
    const params = new URLSearchParams()
    params.set('page', String(page))
    params.set('per_page', '20')
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    if (classFilter.value) params.set('class_id', classFilter.value)
    if (statusFilter.value) params.set('status', statusFilter.value)

    const res = await api.get(`/students?${params.toString()}`)
    students.value = res.data
    currentPage.value = page
    totalPages.value = res.data?.data?.last_page || res.data?.last_page || res.data?.meta?.last_page || 1
  } catch (error: any) {
    students.value = null
    loadError.value = error?.code === 'ECONNABORTED'
      ? 'সার্ভার থেকে সাড়া পেতে বেশি সময় লেগেছে। নেটওয়ার্ক ঠিক আছে কিনা দেখে আবার চেষ্টা করুন।'
      : 'ছাত্র তালিকা লোড করা যায়নি। আবার চেষ্টা করুন।'
    console.error('Failed to load students:', error)
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  currentPage.value = 1
  loadStudents(1)
}

function goToPage(page: number) {
  loadStudents(page)
}

function printPage() {
  window.print()
}

const formatDate = (date: string | null | undefined): string => {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return date
  }
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

onMounted(() => {
  loadClasses()
  loadStudents(1)
})
</script>

<style scoped>
.students-page { max-width: 1320px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-actions { display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap; }
.student-name-link { color: var(--color-text); font-weight: 700; text-decoration: none; }
.student-name-link:hover { color: var(--color-primary); text-decoration: underline; }
.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.student-avatar { width: 36px; height: 36px; border-radius: 50%; overflow: hidden; border: 2px solid var(--color-border-light); }
.student-avatar img { width: 100%; height: 100%; object-fit: cover; }
.student-avatar-placeholder { width: 36px; height: 36px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 700; }
.pagination { display: flex; gap: 0.35rem; justify-content: center; align-items: center; margin-top: 1.25rem; flex-wrap: wrap; }
.page-btn { padding: 0.45rem 0.75rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: var(--radius-sm); font-size: 0.9rem; font-family: var(--font-bn); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.25rem; }
.page-btn:hover:not(.active):not(:disabled) { background: var(--color-bg-muted); border-color: var(--color-primary); color: var(--color-primary); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-ellipsis { padding: 0 0.3rem; color: var(--color-text-muted); }
.empty-state { text-align: center; padding: 3rem 1rem; color: var(--color-text-muted); }

.bulk-action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 1.25rem;
  background: var(--color-primary-50, #f0fdf4);
  border: 1px solid var(--color-primary-200, #bbf7d0);
  border-radius: var(--radius-md, 10px);
  margin-bottom: 1rem;
}
.bulk-count { font-weight: 800; color: var(--color-primary); }
.bulk-btns { display: flex; gap: 0.5rem; }
.selected-row { background-color: rgba(20, 80, 50, 0.04) !important; }

/* Dedicated Printable Header Styles */
.print-header-block {
  text-align: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #145032;
}
.print-bismillah {
  font-family: 'Amiri', 'Scheherazade New', serif;
  font-size: 1.3rem;
  color: #145032;
  margin-bottom: 0.25rem;
}
.institution-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: #145032;
  margin: 0 0 0.25rem;
}
.ledger-badge-title {
  display: inline-block;
  font-size: 1.1rem;
  font-weight: 700;
  padding: 0.25rem 1.25rem;
  background: #f0fdf4;
  border: 1px solid #145032;
  border-radius: 999px;
  color: #145032;
  margin-bottom: 0.5rem;
}
.print-meta-bar {
  display: flex;
  justify-content: space-around;
  font-size: 0.85rem;
  color: #475569;
  padding: 0.35rem 0;
  border-top: 1px dashed #cbd5e1;
}

@media print {
  .students-page { max-width: 100% !important; padding: 0 !important; }
  .card, .table-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; border-radius: 0 !important; }
  .premium-table { width: 100% !important; border-collapse: collapse !important; }
  .premium-table th, .premium-table td { border: 1px solid #cbd5e1 !important; padding: 6px 8px !important; font-size: 11px !important; }
}
</style>
