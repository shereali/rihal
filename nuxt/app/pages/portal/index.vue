<template>
  <div class="portal-page">
    <div class="page-header">
      <h1>অভিভাবক পোর্টাল</h1>
      <p class="text-muted">স্বাগতম, {{ data?.guardian?.name || 'অভিভাবক' }}</p>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>
    <div v-else-if="(data?.students || []).length === 0" class="empty-state">
      <p>কোনো ছাত্র এই অ্যাকাউন্টের সাথে যুক্ত নেই।</p>
      <p class="text-muted">মাদ্রাসা প্রশাসকের সাথে যোগাযোগ করুন।</p>
    </div>

    <div v-else class="student-cards">
      <div v-for="s in (data?.students || [])" :key="s.id" class="card student-card">
        <div class="card-header">
          <div>
            <h3>{{ s.name_bn || s.name_en }}</h3>
            <p class="text-muted">ভর্তি নং: {{ s.admission_number || '-' }} · {{ s.relationship }}</p>
          </div>
        </div>
        <div class="card-body">
          <!-- Attendance -->
          <div class="section">
            <h4>হাজিরা (গত ৩০ দিন)</h4>
            <div class="att-bar"><div class="bar present" :style="{ width: s.attendance.rate + '%' }"></div></div>
            <p class="section-meta">উপস্থিত {{ s.attendance.present }}/{{ s.attendance.total }} — হার {{ s.attendance.rate }}%</p>
          </div>

          <!-- Results -->
          <div class="section">
            <h4>সাম্প্রতিক ফলাফল</h4>
            <div v-if="(s.results || []).length === 0" class="text-muted">কোনো ফলাফল নেই</div>
            <ul v-else class="result-list">
              <li v-for="(r, i) in s.results" :key="i">
                <span class="exam-name">{{ r.exam }}</span>
                <span class="badge" :class="r.published ? 'badge-success' : 'badge-secondary'">
                  {{ r.marks ?? '-' }} {{ r.grade ? '(' + r.grade + ')' : '' }} {{ r.published ? '' : '· অপ্রকাশিত' }}
                </span>
              </li>
            </ul>
          </div>

          <!-- Fees -->
          <div class="section">
            <h4>ফি অবস্থা</h4>
            <div v-if="(s.fees || []).length === 0" class="text-muted">কোনো ফি রেকর্ড নেই</div>
            <ul v-else class="fee-list">
              <li v-for="(f, i) in s.fees" :key="i">
                <span class="fee-amount">৳{{ Number(f.total).toLocaleString('bn-BD') }}</span>
                <span class="badge" :class="f.is_fully_paid ? 'badge-success' : 'badge-warning'">
                  {{ f.is_fully_paid ? 'পরিশোধিত' : 'বকেয়া ৳' + Number(f.balance).toLocaleString('bn-BD') }}
                </span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div v-if="notifications.length" class="notif-card">
      <h4>নোটিফিকেশন</h4>
      <ul class="notif-list">
        <li v-for="n in notifications" :key="n.id" :class="{ unread: !n.is_read }">
          <span class="badge" :class="n.type === 'absence' ? 'badge-warning' : 'badge-danger'">{{ n.type === 'absence' ? 'অনুপস্থিতি' : 'ফি-বকেয়া' }}</span>
          <span>{{ n.body_bn }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const data = ref<any>(null)
const notifications = ref<any[]>([])

onMounted(async () => {
  try {
    const res = await api.get('/guardian/portal')
    data.value = res.data
    const n = await api.get('/notifications?per_page=20')
    notifications.value = n.data?.data?.data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>

<style scoped>
.portal-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.student-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { margin: 0; font-size: 1.15rem; font-family: 'Noto Sans Bengali', sans-serif; }
.card-body { padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem; }
.section h4 { font-size: 0.95rem; margin: 0 0 0.6rem; font-family: 'Noto Sans Bengali', sans-serif; }
.att-bar { height: 8px; background: var(--color-border); border-radius: 4px; overflow: hidden; }
.att-bar .present { height: 100%; background: var(--color-success); }
.section-meta { font-size: 0.85rem; color: var(--color-text-muted); margin: 0.4rem 0 0; }
.result-list, .fee-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; }
.result-list li, .fee-list li { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; }
.exam-name { font-family: 'Noto Sans Bengali', sans-serif; }
.fee-amount { font-weight: 600; }
.badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.78rem; }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.badge-warning { background: #fff4e0; color: var(--color-warning); }
.badge-secondary { background: var(--color-bg-muted); color: var(--color-text-muted); }
.text-muted { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.loading-state, .empty-state { padding: 3rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
.notif-card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.25rem; }
.notif-card h4 { font-family: 'Noto Sans Bengali', sans-serif; margin: 0 0 0.75rem; }
.notif-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem; }
.notif-list li { display: flex; gap: 0.6rem; align-items: center; font-family: 'Noto Sans Bengali', sans-serif; padding: 0.5rem 0.75rem; border-radius: 8px; background: var(--color-bg-muted); }
.notif-list li.unread { background: #fff4e0; }
.badge-danger { background: #fce4e4; color: var(--color-error); }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
