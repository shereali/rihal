<template>
  <div class="exam-page slide-up-fade">
    <!-- Printable Header -->
    <div class="print-header-block print-only">
      <div class="print-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>
      <h1 class="institution-title">দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h1>
      <h2 class="ledger-badge-title">পরীক্ষার সময়সূচী ও বিবরণী</h2>
      <div class="print-meta-bar">
        <span><strong>মোট পরীক্ষা:</strong> {{ filteredExams.length.toLocaleString('bn-BD') }} টি</span>
        <span><strong>মুদ্রণের তারিখ:</strong> {{ printDateBn }}</span>
      </div>
    </div>

    <div class="page-header-row no-print">
      <div class="header-title-block">
        <span class="eyebrow">পরীক্ষা ও মূল্যায়ন</span>
        <h1>পরীক্ষা তালিকা ও সূচী</h1>
        <p class="page-subtitle">টার্ম পরীক্ষা, সাময়িকী ও বার্ষিক পরীক্ষার সময়সূচী এবং ফলাফল ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printPage">
          <Icon name="mdi:printer" /> প্রিন্ট শিডিউল
        </button>
        <NuxtLink to="/marks" class="btn btn-outline">
          <Icon name="mdi:playlist-edit" /> মার্কস এন্ট্রি
        </NuxtLink>
        <NuxtLink to="/exams/create" class="btn btn-primary">
          <Icon name="mdi:plus" /> নতুন পরীক্ষা তৈরি
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
          placeholder="পরীক্ষার নাম, শ্রেণি বা বিষয় দিয়ে খুঁজুন..."
        />
        <button v-if="searchQuery" class="clear-search-btn" @click="searchQuery = ''">×</button>
      </div>

      <div class="select-wrapper">
        <select v-model="statusFilter" class="form-select">
          <option value="">সকল অবস্থা (All Status)</option>
          <option value="draft">খসড়া (Draft)</option>
          <option value="scheduled">নির্ধারিত (Scheduled)</option>
          <option value="ongoing">চলমান (Ongoing)</option>
          <option value="completed">সম্পন্ন (Completed)</option>
          <option value="published">ফলাফল প্রকাশিত (Published)</option>
        </select>
      </div>

      <div class="pagination-info" v-if="filteredExams.length">
        মোট <span class="highlight">{{ filteredExams.length.toLocaleString('bn-BD') }}</span> টি পরীক্ষা
      </div>
    </div>

    <div class="card table-card">
      <div class="card-body">
        <div v-if="loading" class="loading-state">
          <div class="spinner" />
          <p>পরীক্ষার তথ্য লোড হচ্ছে...</p>
        </div>

        <div v-else-if="filteredExams.length === 0" class="empty-state">
          <Icon name="mdi:file-document-edit-outline" size="48" style="color: var(--color-border);" />
          <p class="mt-2 font-semibold">কোনো পরীক্ষা পাওয়া যায়নি</p>
          <p class="text-muted text-sm mb-3">নতুন পরীক্ষা তৈরি করতে নিচের বাটনে ক্লিক করুন</p>
          <NuxtLink to="/exams/create" class="btn btn-primary">প্রথম পরীক্ষা তৈরি করুন</NuxtLink>
        </div>

        <div v-else class="table-responsive">
          <table class="premium-table">
            <thead>
              <tr>
                <th>পরীক্ষার নাম</th>
                <th>ধরন</th>
                <th>শ্রেণি ও বিভাগ</th>
                <th>শুরুর তারিখ</th>
                <th>শেষ তারিখ</th>
                <th>অবস্থা</th>
                <th>ফলাফল প্রকাশ</th>
                <th class="text-right no-print">ক্রিয়া</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="exam in filteredExams" :key="exam.id">
                <td>
                  <p class="font-weight-medium mb-0">
                    <NuxtLink :to="`/exams/${exam.id}`" class="exam-name-link">
                      {{ exam.title_bn || exam.name_bn || exam.title_en || exam.name || 'পরীক্ষা #' + exam.id }}
                    </NuxtLink>
                  </p>
                  <p class="text-muted text-xs mb-0" v-if="exam.title_en && exam.title_en !== exam.title_bn">
                    {{ exam.title_en }}
                  </p>
                </td>
                <td>
                  <span class="type-tag">
                    {{ exam.type || exam.exam_type || 'টার্ম পরীক্ষা' }}
                  </span>
                </td>
                <td>
                  <span>{{ exam.class?.name_bn || exam.class?.class_name || exam.class_name || 'সকল শ্রেণি' }}</span>
                  <span class="text-muted text-xs d-block" v-if="exam.section">{{ exam.section?.name_bn || exam.section_name }}</span>
                </td>
                <td>{{ formatDate(exam.start_date) }}</td>
                <td>{{ formatDate(exam.end_date) }}</td>
                <td>
                  <span class="status-pill" :class="getExamStatusClass(exam.status)">
                    <span class="status-dot" />
                    {{ formatStatus(exam.status) }}
                  </span>
                </td>
                <td>
                  <span v-if="exam.has_results || exam.is_published" class="status-pill badge-approved">
                    <span class="status-dot" /> প্রকাশিত
                  </span>
                  <span v-else class="status-pill badge-pending">
                    <span class="status-dot" /> প্রস্তুত হচ্ছে
                  </span>
                </td>
                <td class="text-right no-print" @click.stop>
                  <div class="btn-group btn-group-sm">
                    <NuxtLink :to="`/exams/${exam.id}`" class="btn btn-outline btn-sm" title="বিস্তারিত">
                      <Icon name="mdi:eye" :size="15" />
                    </NuxtLink>
                    <NuxtLink :to="`/exams/${exam.id}/edit`" class="btn btn-outline btn-sm" title="সম্পাদনা">
                      <Icon name="mdi:pencil" :size="15" />
                    </NuxtLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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
const exams = ref<any>(null)
const searchQuery = ref('')
const statusFilter = ref('')

const printDateBn = computed(() => {
  return new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' })
})

const examList = computed<any[]>(() => {
  const data = exams.value?.data?.data || exams.value?.data || []
  return Array.isArray(data) ? data : []
})

const filteredExams = computed(() => {
  return examList.value.filter(e => {
    const title = (e.title_bn || e.name_bn || e.title_en || e.name || '').toLowerCase()
    const cls = (e.class?.name_bn || e.class_name || '').toLowerCase()
    const query = searchQuery.value.trim().toLowerCase()
    const matchQuery = !query || title.includes(query) || cls.includes(query)
    const matchStatus = !statusFilter.value || e.status === statusFilter.value
    return matchQuery && matchStatus
  })
})

async function loadExams() {
  loading.value = true
  try {
    const res = await api.get('/exams?per_page=50')
    exams.value = res.data
  } catch (error) {
    console.error('Failed to load exams:', error)
  } finally {
    loading.value = false
  }
}

function printPage() {
  window.print()
}

function formatDate(date: string | null | undefined): string {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return date
  }
}

function formatStatus(status: string | null | undefined): string {
  switch (status) {
    case 'published': return 'প্রকাশিত'
    case 'completed': return 'সম্পন্ন'
    case 'ongoing': return 'চলমান'
    case 'scheduled': return 'নির্ধারিত'
    case 'draft': return 'খসড়া'
    default: return status || 'খসড়া'
  }
}

function getExamStatusClass(status: string | null | undefined): string {
  switch (status) {
    case 'published':
    case 'completed': return 'badge-approved'
    case 'ongoing': return 'badge-approved'
    case 'scheduled': return 'badge-pending'
    default: return 'badge-pending'
  }
}

onMounted(loadExams)
</script>

<style scoped>
.exam-page { max-width: 1320px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-actions { display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap; }
.exam-name-link { color: var(--color-text); font-weight: 700; text-decoration: none; }
.exam-name-link:hover { color: var(--color-primary); text-decoration: underline; }
.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.empty-state { text-align: center; padding: 3rem 1rem; color: var(--color-text-muted); }

/* Printable Header Styles */
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
  .exam-page { max-width: 100% !important; padding: 0 !important; }
  .card, .table-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; border-radius: 0 !important; }
  .premium-table { width: 100% !important; border-collapse: collapse !important; }
  .premium-table th, .premium-table td { border: 1px solid #cbd5e1 !important; padding: 6px 8px !important; font-size: 11px !important; }
}
</style>
