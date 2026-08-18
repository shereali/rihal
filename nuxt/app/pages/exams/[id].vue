<template>
  <div class="exam-show">
    <div class="page-header">
      <NuxtLink to="/exams" class="btn btn-outline btn-sm">
        <icon name="arrow-left" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink :to="`/exams/${exam?.id}/edit`" class="btn btn-primary btn-sm">
          <icon name="pencil" /> সম্পাদনা
        </NuxtLink>
        <button class="btn btn-outline-danger btn-sm" @click="confirmDelete">
          <icon name="delete" /> মুছুন
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner" />
      <p>পরীক্ষার তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!exam" class="empty-state">
      <p>পরীক্ষা পাওয়া যায়নি</p>
      <NuxtLink to="/exams" class="btn btn-primary">পরীক্ষার তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <div class="card">
        <div class="card-header">
          <h3>পরীক্ষার তথ্য</h3>
        </div>
        <div class="card-body">
          <div class="detail-grid">
            <div class="detail-item">
              <label class="detail-label">নাম (বাংলা)</label>
              <h3 class="detail-value detail-title">{{ exam.name_bn }}</h3>
            </div>
            <div class="detail-item">
              <label class="detail-label">নাম (ইংরেজি)</label>
              <p class="detail-value">{{ exam.name_en || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">পরীক্ষার ধরন</label>
              <span class="badge" :class="getTypeBadge(exam.exam_type)">{{ exam.exam_type || 'নিয়মিত' }}</span>
            </div>
            <div class="detail-item">
              <label class="detail-label">শ্রেণি আইডি</label>
              <p class="detail-value">{{ exam.class_id || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">সেকশন আইডি</label>
              <p class="detail-value">{{ exam.section_id || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">বিষয় আইডি</label>
              <p class="detail-value">{{ exam.subject_id || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">শুরুর তারিখ</label>
              <p class="detail-value">{{ formatDate(exam.start_date) }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">শেষ তারিখ</label>
              <p class="detail-value">{{ formatDate(exam.end_date) }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">মোট নম্বর</label>
              <p class="detail-value">{{ exam.total_marks ?? '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">পাস নম্বর</label>
              <p class="detail-value">{{ exam.passing_marks ?? '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">সময় (মিনিট)</label>
              <p class="detail-value">{{ exam.duration_minutes ?? '-' }} মিনিট</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Results section -->
      <div class="card">
        <div class="card-header results-header">
          <h3>ফলাফল তালিকা</h3>
          <NuxtLink :to="`/marks/create`" class="btn btn-outline btn-sm">নতুন মার্ক এন্ট্রি</NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="resultsLoading" class="loading-state"><div class="spinner" /></div>
          <div v-else-if="(results?.data?.data || []).length === 0" class="empty-state">
            <p>এই পরীক্ষার কোনো ফলাফল নেই</p>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ছাত্র</th>
                  <th>প্রাপ্ত</th>
                  <th>মোট</th>
                  <th>শতকরা</th>
                  <th>অবস্থা</th>
                  <th>কার্যক্রম</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in (results?.data?.data || [])" :key="r.id">
                  <td>{{ r.student?.name_bn || r.student?.name_en || '-' }}</td>
                  <td>{{ r.marks_obtained ?? '-' }}</td>
                  <td>{{ r.total_marks ?? '-' }}</td>
                  <td>{{ r.percentage ?? '-' }}%</td>
                  <td>
                    <span class="badge" :class="r.is_published ? 'badge-success' : 'badge-warning'">
                      {{ r.is_published ? 'প্রকাশিত' : 'অপ্রকাশিত' }}
                    </span>
                  </td>
                  <td>
                    <button
                      v-if="!r.is_published"
                      class="btn btn-sm btn-primary"
                      :disabled="publishingId === r.id"
                      @click="togglePublish(r)"
                    >প্রকাশ করুন</button>
                    <button
                      v-else
                      class="btn btn-sm btn-outline"
                      :disabled="publishingId === r.id"
                      @click="togglePublish(r)"
                    >সরান</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(true)
const exam = ref<any>(null)
const results = ref<any>(null)
const resultsLoading = ref(false)
const publishingId = ref<number | null>(null)

async function loadExam() {
  loading.value = true
  try {
    const res = await api.get(`/exams/${route.params.id}`)
    exam.value = res.data.data
    await loadResults()
  } catch (error) {
    console.error('Failed to load exam:', error)
  } finally {
    loading.value = false
  }
}

async function loadResults() {
  resultsLoading.value = true
  try {
    const res = await api.get(`/exam-results?exam_id=${route.params.id}`)
    results.value = res.data
  } catch (error) {
    console.error('Failed to load results:', error)
  } finally {
    resultsLoading.value = false
  }
}

async function togglePublish(r: any) {
  publishingId.value = r.id
  try {
    const path = r.is_published ? 'unpublish' : 'publish'
    await api.patch(`/exam-results/${r.id}/${path}`)
    r.is_published = !r.is_published
    r.published_at = r.is_published ? new Date().toISOString() : null
  } catch (error: any) {
    alert(error?.response?.data?.message ?? 'ফলাফল আপডেট করা যায়নি')
  } finally {
    publishingId.value = null
  }
}

const confirmDelete = () => {
  if (confirm(`"${exam.value?.name_bn}" পরীক্ষাটি মুছে ফেলতে চান?`)) {
    api.delete(`/exams/${exam.value?.id}`).then(() => navigateTo('/exams'))
  }
}

const formatDate = (date: string | null | undefined): string => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
}

const getTypeBadge = (type: string): string => {
  return { final: 'badge-dark', mid: 'badge-warning', unit: 'badge-info', monthly: 'badge-success', model: 'badge-primary' }[type] || 'badge-secondary'
}

onMounted(() => { loadExam() })
</script>

<style scoped>
.exam-show { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.detail-layout { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 1rem; }
.detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem 1rem; }
.detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
.detail-label { font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; }
.detail-value { font-size: 0.9375rem; color: var(--color-text); margin: 0; }
.detail-title { font-size: 1.25rem; }
.results-header { display: flex; justify-content: space-between; align-items: center; }
</style>
