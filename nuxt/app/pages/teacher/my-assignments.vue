<template>
  <div class="ta-page">
    <div class="page-header">
      <h1>আমার বরাদ্দ</h1>
      <p class="text-muted">আমাকে দেওয়া শিক্ষক বরাদ্দের তালিকা</p>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>
    <div v-else-if="assignments.length === 0" class="empty-state"><p>কোনো বরাদ্দ নেই</p></div>
    <div v-else class="cards">
      <div v-for="a in assignments" :key="a.id" class="card assignment">
        <div class="card-header">
          <h3>{{ a.subject?.name_bn || a.subject_name || 'বিষয়' }}</h3>
          <span class="badge" :class="a.is_active ? 'badge-success' : 'badge-secondary'">{{ a.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
        </div>
        <div class="card-body">
          <p><strong>শ্রেণি:</strong> {{ a.class?.name_bn || '-' }}</p>
          <p><strong>সেকশন:</strong> {{ a.section?.name_bn || '-' }}</p>
          <p v-if="a.topic"><strong>বিষয়:</strong> {{ a.topic }}</p>
          <p v-if="a.academic_year"><strong>শিক্ষাবর্ষ:</strong> {{ a.academic_year }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

definePageMeta({ name: 'teacher-my-assignments' })

const api = useApiClient()
const loading = ref(true)
const assignments = ref<any[]>([])

onMounted(async () => {
  try {
    const res = await api.get('/teacher/assignments')
    assignments.value = res.data?.data ?? res.data ?? []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>

<style scoped>
.ta-page { padding: 1.5rem; }
.page-header h1 { font-family: 'Noto Sans Bengali', sans-serif; margin: 0; }
.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.card-body { padding: 1.25rem; display: flex; flex-direction: column; gap: 0.4rem; font-family: 'Noto Sans Bengali', sans-serif; }
.badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.78rem; }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.badge-secondary { background: var(--color-bg-muted); color: var(--color-text-muted); }
.empty-state, .loading-state { padding: 3rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
