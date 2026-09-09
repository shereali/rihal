<template>
  <div class="exam-page">
    <div class="page-header">
      <div class="header-left">
        <h1>পরীক্ষা তালিকা</h1>
        <p class="text-muted">{{ exams?.data?.meta?.total || 0 }}টি পরীক্ষা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/marks/create" class="btn btn-outline">নতুন মার্ক এন্ট্রি</NuxtLink>
        <NuxtLink to="/exams/create" class="btn btn-primary">
          <icon name="plus" /> নতুন পরীক্ষা
        </NuxtLink>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state">
          <div class="spinner" />
          <p>পরীক্ষার তথ্য লোড হচ্ছে...</p>
        </div>

        <div v-else-if="(exams?.data?.data || []).length === 0" class="empty-state">
          <p>কোনো পরীক্ষা নেই</p>
          <NuxtLink to="/exams/create" class="btn btn-primary">প্রথম পরীক্ষা তৈরি করুন</NuxtLink>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>নাম</th>
                <th>শ্রেণি</th>
                <th>শিক্ষার্থী</th>
                <th>শুরুর তারিখ</th>
                <th>শেষ তারিখ</th>
                <th>অবস্থা</th>
                <th>ফলাফল</th>
                <th>ক্রিয়া</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="exam in exams?.data?.data || []" :key="exam.id">
                <td>
                  <p class="font-weight-medium">{{ exam.title_bn }}</p>
                  <p class="text-muted text-sm" v-if="exam.title_en !== exam.title_bn">{{ exam.title_en }}</p>
                </td>
                <td>
                  <span class="badge" :class="getExamTypeBadge(exam.type || exam.exam_type || 'নিয়মিত')">
                    {{ exam.type || exam.exam_type || 'নিয়মিত' }}
                  </span>
                </td>
                <td>
                  <span class="text-muted">{{ exam.class?.name_bn || exam.class_name || '-' }}</span>
                  <span class="text-muted text-sm d-block" v-if="exam.section">{{ exam.section?.name_bn || exam.section_name }}</span>
                </td>
                <td>{{ formatDate(exam.start_date) }}</td>
                <td>{{ formatDate(exam.end_date) }}</td>
                <td>
                  <span class="status-badge" :class="getExamStatusBadge(exam.status || 'draft')">
                    {{ formatStatus(exam.status) }}
                  </span>
                </td>
                <td>
                  <div v-if="exam.has_results" class="result-indicator">
                    <span class="badge badge-success"><icon name="check-circle" /> ফলাফল প্রকাশিত</span>
                  </div>
                  <div v-else-if="exam.is_published" class="result-indicator">
                    <span class="badge badge-primary">প্রকাশিত</span>
                  </div>
                  <div v-else class="result-indicator">
                    <span class="badge badge-warning">ফলাফল যোগ করুন</span>
                  </div>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <NuxtLink :to="`/exams/${exam.id}`" class="btn btn-outline">
                      <icon name="eye" />
                    </NuxtLink>
                    <NuxtLink :to="`/exams/${exam.id}/edit`" class="btn btn-outline">
                      <icon name="pencil" />
                    </NuxtLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="exams?.data?.meta && exams.data.meta.total > exams.data.per_page" class="pagination-wrapper">
          <div class="pagination">
            <button v-for="page in totalPages" :key="page" :class="['page-btn', { active: page === exams?.data?.current_page }]" @click="goToPage(page)">
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const exams = ref<any>(null)
const totalPages = ref(1)

async function loadExams(page = 1) {
  loading.value = true
  try {
    const res = await api.get(`/exams?page=${page}&per_page=20`)
    exams.value = res.data
    totalPages.value = res.data.meta?.last_page || 1
  } catch (error) { console.error('Failed to load exams:', error) }
  finally { loading.value = false }
}

const goToPage = (page: number) => loadExams(page)
const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day:'numeric', month:'short', year:'numeric' }) : '-'
const formatStatus = (s: string) => ({ draft:'নকশা', scheduled:'নির্ধারিত', ongoing:'চলমান', completed:'সম্পন্ন', cancelled:'বাতিল' }[s] || s)
const getExamStatusBadge = (s: string) => ({ draft:'status-draft', scheduled:'status-scheduled', ongoing:'status-ongoing', completed:'status-completed', cancelled:'status-cancelled' }[s] || 'status-draft')
const getExamTypeBadge = (t: string) => ({ final:'badge-dark', mid:'badge-warning', unit:'badge-info', monthly:'badge-success' }[t] || 'badge-secondary')

loadExams()
</script>

<style scoped>
.exam-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.header-actions { display: flex; gap: 0.75rem; align-items: center; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.table-responsive { overflow-x: auto; }
.pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1rem; }
.page-btn { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; }
.page-btn:hover:not(.active) { background: var(--color-bg-hover); border-color: var(--color-border-dark); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
</style>
