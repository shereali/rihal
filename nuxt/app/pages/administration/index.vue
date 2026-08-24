<template>
  <div class="admin-page">
    <div class="page-header">
      <div class="header-left">
        <h1>প্রশাসনিক ব্যবস্থাপনা</h1>
        <p class="subtitle">কর্মকর্তা, কর্মী, আয়োজন, ছুটি ও নিয়োগ ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/hr" class="btn btn-primary btn-sm"><icon name="users" /> কর্মকর্তা তালিকা</NuxtLink>
        <NuxtLink to="/hr/events" class="btn btn-outline btn-sm"><icon name="calendar" /> আয়োজন</NuxtLink>
        <NuxtLink to="/hr/holidays" class="btn btn-outline btn-sm"><icon name="calendar" /> ছুটির দিন</NuxtLink>
        <NuxtLink to="/hr/recruitments" class="btn btn-outline btn-sm"><icon name="users" /> নিয়োগ</NuxtLink>
      </div>
    </div>

    <div class="stats-row">
      <div class="stat-card stat-staff">
        <div class="stat-icon"><icon name="users" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_staff || 0 }}</p>
          <p class="stat-label">মোট কর্মকর্তা ও কর্মী</p>
        </div>
      </div>
      <div class="stat-card stat-events">
        <div class="stat-icon"><icon name="calendar" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_events || 0 }}</p>
          <p class="stat-label">আয়োজন</p>
        </div>
      </div>
      <div class="stat-card stat-holidays">
        <div class="stat-icon"><icon name="calendar" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_holidays || 0 }}</p>
          <p class="stat-label">ছুটির দিন</p>
        </div>
      </div>
      <div class="stat-card stat-recruitments">
        <div class="stat-icon"><icon name="users" /></div>
        <div class="stat-info">
          <p class="stat-value">{{ summary?.total_recruitments || 0 }}</p>
          <p class="stat-label">চলমান নিয়োগ</p>
        </div>
      </div>
    </div>

    <!-- Upcoming Events -->
    <div class="card mt-4">
      <div class="card-header">
        <h3>আসন্ন আয়োজন</h3>
        <NuxtLink to="/hr/events" class="view-all">সব দেখুন</NuxtLink>
      </div>
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(events?.data || []).length === 0" class="empty-state"><p>আসন্ন কোনো আয়োজন নেই</p></div>
        <table v-else class="table table-hover">
          <thead><tr><th>নাম</th><th>তারিখ</th><th>রেজিস্ট্রার</th><th>স্থিতি</th></tr></thead>
          <tbody>
            <tr v-for="event in (events?.data || [])" :key="event.id">
              <td>{{ event.title_bn || event.title || '-' }}</td>
              <td>{{ event.event_date || event.date || '-' }}</td>
              <td>{{ event.registrations_count || event.registrations?.length || 0 }}</td>
              <td><span class="badge" :class="event.is_active ? 'badge-success' : 'badge-secondary'">{{ event.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upcoming Holidays -->
    <div class="card mt-4">
      <div class="card-header">
        <h3>আসন্ন ছুটির দিন</h3>
        <NuxtLink to="/hr/holidays" class="view-all">সব দেখুন</NuxtLink>
      </div>
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(holidays?.data || []).length === 0" class="empty-state"><p>আসন্ন কোনো ছুটির দিন নেই</p></div>
        <table v-else class="table table-hover">
          <thead><tr><th>শিরোনাম</th><th>তারিখ</th><th>ধরণ</th></tr></thead>
          <tbody>
            <tr v-for="h in (holidays?.data || [])" :key="h.id">
              <td>{{ h.title_bn || h.title || '-' }}</td>
              <td>{{ h.holiday_date || h.date || '-' }}</td>
              <td>{{ h.type || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recruitment Applications -->
    <div class="card mt-4">
      <div class="card-header">
        <h3>চলমান নিয়োগ আবেদন</h3>
        <NuxtLink to="/hr/recruitments" class="view-all">সব দেখুন</NuxtLink>
      </div>
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(recruitments?.data || []).length === 0" class="empty-state"><p>কোনো নিয়োগ আবেদন নেই</p></div>
        <table v-else class="table table-hover">
          <thead><tr><th>বিজ্ঞাপন</th><th>আবেদন</th><th>স্থিতি</th></tr></thead>
          <tbody>
            <tr v-for="r in (recruitments?.data || [])" :key="r.id">
              <td>{{ r.title_bn || r.title || '-' }}</td>
              <td>{{ r.applications_count || r.applications?.length || 0 }}</td>
              <td><span class="badge badge-outline">{{ r.status || 'সক্রিয়' }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const summary = ref<any>(null)
const events = ref<any>(null)
const holidays = ref<any>(null)
const recruitments = ref<any>(null)

async function loadData() {
  loading.value = true
  try {
    // Fetch staff count
    const staffRes = await api.get('/hr/staff?per_page=1')
    const totalStaff = staffRes.data?.data?.total || 0
    // Fetch upcoming events
    const eventsRes = await api.get('/hr/events?per_page=5')
    const totalEvents = eventsRes.data?.data?.total || 0
    // Fetch holidays
    const holidaysRes = await api.get('/hr/holidays?per_page=5')
    const totalHolidays = holidaysRes.data?.data?.total || 0
    // Fetch recruitments
    const recruitmentsRes = await api.get('/hr/recruitments?per_page=5')
    const totalRecruitments = recruitmentsRes.data?.data?.total || 0

    summary.value = {
      total_staff: totalStaff,
      total_events: totalEvents,
      total_holidays: totalHolidays,
      total_recruitments: totalRecruitments,
    }
    events.value = eventsRes.data
    holidays.value = holidaysRes.data
    recruitments.value = recruitmentsRes.data
  } catch (e: any) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.admin-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.subtitle { color: var(--color-text-light); font-size: 0.9rem; font-family: 'Noto Sans Bengali', sans-serif; }
.header-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.btn { padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; }
.btn-sm { padding: 0.35rem 0.8rem; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.2rem; display: flex; align-items: center; gap: 0.8rem; }
.stat-icon { width: 40px; height: 40px; flex-shrink: 0; color: var(--color-primary); display: flex; align-items: center; justify-content: center; }
.stat-icon icon { width: 24px; height: 24px; }
.stat-info p { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: var(--color-text); }
.stat-label { font-size: 0.8rem; color: var(--color-text-light); }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; margin-bottom: 1rem; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { margin: 0; font-size: 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.view-all { font-size: 0.8rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.card-body { padding: 1rem; }
.mt-4 { margin-top: 1.5rem; }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.spinner { width: 24px; height: 24px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { padding: 1.5rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.table { width: 100%; border-collapse: collapse; font-family: 'Noto Sans Bengali', sans-serif; }
.table th, .table td { padding: 0.6rem 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border-light); }
.table th { font-weight: 600; font-size: 0.85rem; color: var(--color-text-light); }
.badge { padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; }
.badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.badge-secondary { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.badge-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text-light); }
</style>
