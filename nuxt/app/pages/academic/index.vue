<template>
  <div class="academic-page">
    <div class="page-header">
      <h1>একাডেমিক তথ্য</h1>
      <p class="text-muted">শ্রেণি, সেকশন ও বিষয়ের তালিকা</p>
    </div>

    <div class="academic-grid">
      <div class="card">
        <div class="card-header"><h3>শ্রেণিসমূহ ({{ classes.length }})</h3></div>
        <div class="card-body">
          <div v-if="loading" class="loading-state"><div class="spinner" /></div>
          <table v-else class="table table-sm">
            <thead><tr><th>নাম (বাংলা)</th><th>নাম (ইংরেজি)</th><th>ধরন</th></tr></thead>
            <tbody>
              <tr v-for="c in classes" :key="c.id">
                <td>{{ c.name_bn }}</td><td>{{ c.name_en || '-' }}</td><td>{{ c.class_type || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>সেকশনসমূহ ({{ sections.length }})</h3></div>
        <div class="card-body">
          <div v-if="loading" class="loading-state"><div class="spinner" /></div>
          <table v-else class="table table-sm">
            <thead><tr><th>নাম</th><th>শ্রেণি</th></tr></thead>
            <tbody>
              <tr v-for="s in sections" :key="s.id">
                <td>{{ s.name_bn }}</td>
                <td>{{ className(s.class_id) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>বিষয়সমূহ ({{ subjects.length }})</h3></div>
        <div class="card-body">
          <div v-if="loading" class="loading-state"><div class="spinner" /></div>
          <table v-else class="table table-sm">
            <thead><tr><th>নাম (বাংলা)</th><th>নাম (ইংরেজি)</th><th>ধরন</th></tr></thead>
            <tbody>
              <tr v-for="s in subjects" :key="s.id">
                <td>{{ s.name_bn }}</td><td>{{ s.name_en || '-' }}</td><td>{{ s.subject_type || '-' }}</td>
              </tr>
            </tbody>
          </table>
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
const classes = ref<any[]>([])
const sections = ref<any[]>([])
const subjects = ref<any[]>([])

function className(id: number): string {
  return classes.value.find((c) => c.id === id)?.name_bn || '-'
}

onMounted(async () => {
  try {
    const [c, s, sub] = await Promise.all([
      api.get('/academic/classes'),
      api.get('/academic/sections'),
      api.get('/academic/subjects'),
    ])
    classes.value = c.data?.data || []
    sections.value = s.data?.data || []
    subjects.value = sub.data?.data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>

<style scoped>
.academic-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.academic-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { margin: 0; font-size: 1.05rem; font-family: 'Noto Sans Bengali', sans-serif; }
.card-body { padding: 1rem 1.25rem; }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.spinner { width: 24px; height: 24px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
