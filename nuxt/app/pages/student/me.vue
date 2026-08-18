<template>
  <div class="me-page">
    <div class="page-header">
      <h1>আমার তথ্য</h1>
      <p class="text-muted">{{ data?.student?.name_bn || 'ছাত্র' }}</p>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>
    <div v-else-if="data">
      <div class="cards">
        <div class="card stat">
          <h4>হাজিরা (৩০ দিন)</h4>
          <div class="big">{{ data.attendance.rate }}%</div>
          <p class="text-muted">উপস্থিত {{ data.attendance.present }}/{{ data.attendance.total }}</p>
        </div>
        <div class="card stat">
          <h4>ভর্তি নং</h4>
          <div class="big sm">{{ data.student.admission_number || '-' }}</div>
        </div>
      </div>

      <div class="card section">
        <h3>সাম্প্রতিক ফলাফল</h3>
        <div v-if="!data.results.length" class="text-muted">কোনো ফলাফল নেই</div>
        <ul v-else class="list">
          <li v-for="(r, i) in data.results" :key="i">
            <span>{{ r.exam }}</span>
            <span class="badge" :class="r.published ? 'badge-success' : 'badge-secondary'">
              {{ r.marks ?? '-' }} {{ r.grade ? '(' + r.grade + ')' : '' }} {{ r.published ? '' : '· অপ্রকাশিত' }}
            </span>
          </li>
        </ul>
      </div>

      <div class="card section">
        <h3>ফি অবস্থা</h3>
        <div v-if="!data.fees.length" class="text-muted">কোনো ফি রেকর্ড নেই</div>
        <ul v-else class="list">
          <li v-for="(f, i) in data.fees" :key="i">
            <span>৳{{ Number(f.total).toLocaleString('bn-BD') }}</span>
            <span class="badge" :class="f.is_fully_paid ? 'badge-success' : 'badge-warning'">
              {{ f.is_fully_paid ? 'পরিশোধিত' : 'বকেয়া ৳' + Number(f.balance).toLocaleString('bn-BD') }}
            </span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const data = ref<any>(null)

onMounted(async () => {
  try {
    const res = await api.get('/student/me')
    data.value = res.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>

<style scoped>
.me-page { padding: 1.5rem; }
.page-header h1 { font-family: 'Noto Sans Bengali', sans-serif; margin: 0; }
.cards { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.stat { padding: 1.25rem; min-width: 180px; }
.stat h4 { font-family: 'Noto Sans Bengali', sans-serif; margin: 0 0 0.5rem; font-size: 0.9rem; }
.big { font-size: 2rem; font-weight: 700; color: var(--color-primary); }
.big.sm { font-size: 1.3rem; }
.section { padding: 1.25rem; margin-bottom: 1rem; }
.section h3 { font-family: 'Noto Sans Bengali', sans-serif; margin: 0 0 0.75rem; }
.list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; }
.list li { display: flex; justify-content: space-between; align-items: center; font-family: 'Noto Sans Bengali', sans-serif; }
.badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.78rem; }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.badge-warning { background: #fff4e0; color: var(--color-warning); }
.badge-secondary { background: var(--color-bg-muted); color: var(--color-text-muted); }
.text-muted { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.loading-state { padding: 3rem; text-align: center; font-family: 'Noto Sans Bengali', sans-serif; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
