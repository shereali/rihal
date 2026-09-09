<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">মূল ড্যাশবোর্ড</span>
        <h1>প্রশাসনিক ও পরিচালনা কেন্দ্র</h1>
        <p class="page-subtitle">কর্মকর্তা, কর্মী, আয়োজন, ছুটির দিন, নিয়োগ ও সাংগঠনিক কার্যক্রম সারসংক্ষেপ</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/hr" class="btn btn-primary">
          <icon name="users" /> কর্মী তালিকা
        </NuxtLink>
        <NuxtLink to="/hr/events" class="btn btn-outline">
          <icon name="calendar" /> আয়োজন
        </NuxtLink>
        <NuxtLink to="/hr/holidays" class="btn btn-outline">
          <icon name="calendar" /> ছুটির দিন
        </NuxtLink>
        <NuxtLink to="/hr/recruitments" class="btn btn-outline">
          <icon name="users" /> নিয়োগ
        </NuxtLink>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_staff || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট কর্মকর্তা ও কর্মী</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="calendar" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_events || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">সর্বমোট আয়োজন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_holidays || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">বার্ষিক ছুটির দিন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (summary?.total_recruitments || 0).toLocaleString('bn-BD') }}</span>
          <span class="stat-label">চলমান নিয়োগ বিজ্ঞপ্তি</span>
        </div>
      </div>
    </div>

    <div class="admin-sections-grid">
      <!-- Upcoming Events -->
      <div class="card section-card">
        <div class="card-header">
          <div class="header-title-flex">
            <icon name="calendar" class="header-icon" />
            <h3>আসন্ন আয়োজন ও অনুষ্ঠান</h3>
          </div>
          <NuxtLink to="/hr/events" class="view-all-link">সব দেখুন <icon name="arrow-right" /></NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="loading" class="loading-state"><div class="spinner" /></div>
          <div v-else-if="!eventsList.length" class="empty-state">
            <p>আসন্ন কোনো আয়োজন নেই</p>
          </div>
          <div v-else class="table-responsive">
            <table class="premium-table">
              <thead>
                <tr>
                  <th>আয়োজনের নাম</th>
                  <th>তারিখ</th>
                  <th>অবস্থা</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="event in eventsList" :key="event.id">
                  <td><strong>{{ event.name_bn || event.name_en || event.title_bn || event.title }}</strong></td>
                  <td>{{ formatDate(event.start_date || event.date) }}</td>
                  <td>
                    <span class="status-pill" :class="event.status === 'completed' ? 'badge-rejected' : 'badge-approved'">
                      <span class="status-dot" />
                      {{ event.status === 'completed' ? 'সম্পন্ন' : 'আসন্ন' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Upcoming Holidays -->
      <div class="card section-card">
        <div class="card-header">
          <div class="header-title-flex">
            <icon name="calendar" class="header-icon" />
            <h3>আসন্ন ছুটির দিন</h3>
          </div>
          <NuxtLink to="/hr/holidays" class="view-all-link">সব দেখুন <icon name="arrow-right" /></NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="loading" class="loading-state"><div class="spinner" /></div>
          <div v-else-if="!holidaysList.length" class="empty-state">
            <p>আসন্ন কোনো ছুটির দিন নেই</p>
          </div>
          <div v-else class="table-responsive">
            <table class="premium-table">
              <thead>
                <tr>
                  <th>ছুটির নাম</th>
                  <th>তারিখ</th>
                  <th>ধরন</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="h in holidaysList" :key="h.id">
                  <td><strong>{{ h.name_bn || h.name_en || h.title_bn || h.title }}</strong></td>
                  <td>{{ formatDate(h.start_date || h.date) }}</td>
                  <td><span class="type-tag">{{ h.type || 'ধর্মীয়' }}</span></td>
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
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const summary = ref<any>({})
const events = ref<any>({})
const holidays = ref<any>({})
const recruitments = ref<any>({})

const eventsList = computed(() => events.value?.data?.data || events.value?.data || [])
const holidaysList = computed(() => holidays.value?.data?.data || holidays.value?.data || [])

async function load() {
  loading.value = true
  try {
    const [eventsRes, holidaysRes, recruitmentsRes, staffRes] = await Promise.all([
      api.get('/hr/events').catch(() => ({ data: { data: [] } })),
      api.get('/hr/holidays').catch(() => ({ data: { data: [] } })),
      api.get('/hr/recruitments').catch(() => ({ data: { data: [] } })),
      api.get('/hr/staff').catch(() => ({ data: { data: [] } })),
    ])

    events.value = eventsRes.data
    holidays.value = holidaysRes.data
    recruitments.value = recruitmentsRes.data

    const totalStaff = (staffRes.data?.data?.data || staffRes.data?.data || []).length
    const totalEvents = (eventsRes.data?.data?.data || eventsRes.data?.data || []).length
    const totalHolidays = (holidaysRes.data?.data?.data || holidaysRes.data?.data || []).length
    const totalRecruitments = (recruitmentsRes.data?.data?.data || recruitmentsRes.data?.data || []).length

    summary.value = {
      total_staff: totalStaff,
      total_events: totalEvents,
      total_holidays: totalHolidays,
      total_recruitments: totalRecruitments,
    }
  } catch (err: any) {
    console.error('Failed to load administration dashboard:', err)
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr: string) {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return dateStr
  }
}

onMounted(load)
</script>

<style scoped>
.page-wrapper {
  max-width: 1320px;
  margin: 0 auto;
  padding: 1.75rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-primary);
  letter-spacing: 0.08em;
}

.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0.2rem 0 0.35rem;
  color: var(--color-text);
}

.page-subtitle {
  color: var(--color-text-light);
  font-size: 0.88rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.55rem;
  align-items: center;
  flex-wrap: wrap;
}

.admin-sections-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
  gap: 1.5rem;
}

.section-card {
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.1rem 1.4rem;
  border-bottom: 1px solid var(--color-border-light);
  background: rgba(0, 0, 0, 0.015);
}

.header-title-flex {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.header-icon {
  color: var(--color-primary);
  font-size: 1.1rem;
}

.card-header h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0;
}

.view-all-link {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--color-primary);
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  text-decoration: none;
}

.view-all-link:hover {
  text-decoration: underline;
}

.table-responsive {
  overflow-x: auto;
}

.premium-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.88rem;
}

.premium-table th {
  padding: 0.85rem 1.1rem;
  background: rgba(0, 0, 0, 0.02);
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--color-text-light);
  border-bottom: 1px solid var(--color-border-light);
  white-space: nowrap;
}

.premium-table td {
  padding: 0.85rem 1.1rem;
  border-bottom: 1px solid var(--color-border-light);
  vertical-align: middle;
}

.type-tag {
  display: inline-block;
  padding: 0.15rem 0.55rem;
  background: rgba(20, 80, 50, 0.07);
  color: var(--color-primary);
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.btn {
  padding: 0.55rem 1.1rem;
  border-radius: 8px;
  font-size: 0.86rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  transition: all 0.2s ease;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #145032 0%, #1a6b43 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35);
}

.btn-outline {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.empty-state {
  padding: 2.5rem 1rem;
  text-align: center;
  color: var(--color-text-light);
  font-size: 0.88rem;
}
</style>
