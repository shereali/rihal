<template>
  <div class="dashboard">
    <div class="page-header">
      <h1>ড্যাশবোর্ড</h1>
      <p class="text-muted">মাদ্রাসা পরিচালনার সামারি</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card" v-for="stat in stats" :key="stat.label">
        <div class="stat-icon" :style="{ background: stat.bg }">
          <icon :name="stat.icon" />
        </div>
        <div class="stat-info">
          <p class="stat-value">{{ stat.value }}</p>
          <p class="stat-label">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="card">
        <div class="card-header">
          <h3>সাম্প্রতিক বিজ্ঞপ্তি</h3>
          <NuxtLink to="/notice" class="btn btn-sm btn-outline">সব দেখুন</NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="recentNotices.length === 0" class="empty-state">
            <p>কোনো বিজ্ঞপ্তি নেই</p>
          </div>
          <ul v-else class="notice-list">
            <li v-for="notice in recentNotices" :key="notice.id" class="notice-item">
              <div class="notice-content">
                <p class="notice-title">{{ notice.title_bn }}</p>
                <p class="notice-meta">{{ formatDate(notice.published_at) }}</p>
              </div>
              <NuxtLink :to="`/notice`" class="btn btn-xs btn-primary">দেখুন</NuxtLink>
            </li>
          </ul>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>হাজিরার সারসংক্ষেপ (আজ)</h3>
          <NuxtLink to="/attendance" class="btn btn-sm btn-outline">বিস্তারিত</NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="attendanceSummary" class="attendance-summary">
            <div class="attendance-bar">
              <div class="bar present" :style="{ width: `${attendanceSummary.attendance_rate}%` }"></div>
            </div>
            <div class="attendance-stats">
              <div class="att-stat">
                <span class="att-value present">{{ attendanceSummary.present }}</span>
                <span class="att-label">উপস্থিত</span>
              </div>
              <div class="att-stat">
                <span class="att-value absent">{{ attendanceSummary.absent }}</span>
                <span class="att-label">অনুপস্থিত</span>
              </div>
              <div class="att-stat">
                <span class="att-value late">{{ attendanceSummary.late }}</span>
                <span class="att-label">দেরি</span>
              </div>
            </div>
            <p class="attendance-rate">হাজিরার হার: {{ attendanceSummary.attendance_rate }}%</p>
          </div>
          <div v-else class="empty-state">
            <p>হাজিরা তথ্য পাওয়া যায়নি</p>
          </div>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="card">
        <div class="card-header">
          <h3>সাম্প্রতিক ছাত্র</h3>
          <NuxtLink to="/students" class="btn btn-sm btn-outline">সব দেখুন</NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="recentStudents.length === 0" class="empty-state">
            <p>কোনো ছাত্র নেই</p>
          </div>
          <table v-else class="table table-sm">
            <thead>
              <tr>
                <th>নাম</th>
                <th>শ্রেণি</th>
                <th>ভর্তি নং</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in recentStudents" :key="student.id">
                <td>{{ student.name_bn }}</td>
                <td>{{ student.class_name }}</td>
                <td>{{ student.admission_number }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>সাম্প্রতিক পরীক্ষা</h3>
          <NuxtLink to="/exams" class="btn btn-sm btn-outline">সব দেখুন</NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="recentExams.length === 0" class="empty-state">
            <p>কোনো পরীক্ষা নেই</p>
          </div>
          <ul v-else class="exam-list">
            <li v-for="exam in recentExams" :key="exam.id" class="exam-item">
              <div class="exam-content">
                <p class="exam-title">{{ exam.title_bn }}</p>
                <p class="exam-meta">{{ exam.class_name }} | {{ formatDate(exam.start_date) }}</p>
              </div>
              <NuxtLink :to="`/exams/${exam.id}`" class="btn btn-xs btn-primary">বিস্তারিত</NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const stats = ref([])
const recentNotices = ref([])
const attendanceSummary = ref<any>(null)
const recentStudents = ref([])
const recentExams = ref([])

onMounted(async () => {
  try {
    const [statsRes, notices, students, exams] = await Promise.all([
      api.get('/dashboard/stats'),
      api.get('/notices?per_page=5'),
      api.get('/students?per_page=5'),
      api.get('/exams?per_page=5'),
    ])

    const s = statsRes.data.data

    stats.value = [
      { label: 'মোট ছাত্র', value: s.total_students, icon: 'users', bg: 'var(--color-primary-light)' },
      { label: 'মোট শিক্ষক', value: s.total_teachers, icon: 'users', bg: 'var(--color-accent)' },
      { label: 'মোট পরীক্ষা', value: s.total_exams, icon: 'book', bg: 'var(--color-success)' },
      { label: 'অপ্রকাশিত ফলাফল', value: s.unpublished_results, icon: 'alert', bg: 'var(--color-warning)' },
      { label: 'আজকের উপস্থিতি', value: `${s.attendance.attendance_rate}%`, icon: 'calendar', bg: 'var(--color-info)' },
      { label: 'নিট ব্যালেন্স', value: `৳${Number(s.finance.net_balance).toLocaleString('bn-BD')}`, icon: 'money', bg: 'var(--color-primary)' },
    ]

    attendanceSummary.value = {
      present: s.attendance.present,
      absent: s.attendance.absent,
      late: s.attendance.late,
      attendance_rate: s.attendance.attendance_rate,
    }

    recentNotices.value = notices.data?.data ?? []
    recentStudents.value = (students.data?.data ?? []).map((st: any) => ({
      ...st,
      name_bn: st.user?.name_bn ?? st.name_bn ?? '-',
      class_name: st.class_name ?? '-',
      admission_number: st.admission_number ?? '-',
    }))
    recentExams.value = (exams.data?.data ?? []).map((e: any) => ({
      ...e,
      title_bn: e.title_bn ?? e.title_en ?? '-',
      class_name: e.class?.name_bn ?? e.class?.name_en ?? '-',
      start_date: e.start_date,
    }))
  } catch (err) {
    console.error('Dashboard load error:', err)
  }
})

function formatDate(date: string | null | undefined): string {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}
</script>

<style scoped>
.dashboard {
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--color-text);
  margin-bottom: 0.25rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text);
  margin: 0;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--color-text-muted);
  margin: 0;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.empty-state {
  padding: 2rem;
  text-align: center;
  color: var(--color-text-muted);
}

.attendance-bar {
  height: 8px;
  background: var(--color-border);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.attendance-bar .present {
  height: 100%;
  background: var(--color-success);
  transition: width 0.3s ease;
}

.attendance-stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.att-stat {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.att-stat .att-value {
  font-size: 1.5rem;
  font-weight: 700;
}

.att-stat .att-value.present { color: var(--color-success); }
.att-stat .att-value.absent { color: var(--color-error); }
.att-stat .att-value.late { color: var(--color-warning); }

.att-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
}

.attendance-rate {
  font-size: 0.875rem;
  color: var(--color-text-muted);
  text-align: center;
  margin: 0;
}
</style>
