<template>
  <div class="exam-show">
    <div class="page-header">
      <NuxtLink to="/exams" class="btn btn-outline btn-sm">
        <icon :name="mdiArrowLeft" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink :to="`/exams/${exam?.id}/edit`" class="btn btn-primary btn-sm">
          <icon :name="mdiPencil" /> সম্পাদনা
        </NuxtLink>
        <button class="btn btn-outline-danger btn-sm" @click="confirmDelete">
          <icon :name="mdiDelete" /> মুছুন
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
              <h3 class="detail-value detail-title">{{ exam.title_bn }}</h3>
            </div>
            <div class="detail-item">
              <label class="detail-label">নাম (ইংরেজি)</label>
              <p class="detail-value">{{ exam.title_en || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">পরীক্ষার ধরন</label>
              <span class="badge" :class="getTypeBadge(exam.type)">{{ exam.type || 'নিয়মিত' }}</span>
            </div>
            <div class="detail-item">
              <label class="detail-label">শ্রেণি</label>
              <p class="detail-value">{{ exam.class?.name_bn || exam.class_name || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">অংশ</label>
              <p class="detail-value">{{ exam.section?.name_bn || exam.section_name || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">বিষয়</label>
              <p class="detail-value">{{ exam.subject?.name_bn || exam.subject_name || '-' }}</p>
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
              <label class="detail-label">শুরুর সময়</label>
              <p class="detail-value">{{ formatDateTime(exam.start_time) }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">শেষের সময়</label>
              <p class="detail-value">{{ formatDateTime(exam.end_time) }}</p>
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

async function loadExam() {
  loading.value = true
  try {
    const res = await api.get(`/exams/${route.params.id}`)
    exam.value = res.data.data
  } catch (error) {
    console.error('Failed to load exam:', error)
  } finally {
    loading.value = false
  }
}

const confirmDelete = () => {
  if (confirm(`"${exam.value?.title_bn}" পরীক্ষাটি মুছে ফেলতে চান?`)) {
    api.delete(`/exams/${exam.value?.id}`).then(() => navigateTo('/exams'))
  }
}

const formatDate = (date: string | null | undefined): string => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
}

const formatDateTime = (datetime: string | null | undefined): string => {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
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
</style>
