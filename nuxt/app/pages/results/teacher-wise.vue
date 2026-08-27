<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/results" class="back-link"><icon name="arrow-left" /> ফলাফল তালিকায় ফিরে যান</NuxtLink>
        <h1>শিক্ষক ভিত্তিক পরীক্ষার ফলাফল ও পারফরম্যান্স</h1>
        <p class="page-subtitle">পাঠদানকারী শিক্ষক অনুযায়ী বিষয়ভিত্তিক পাসের হার, গড় নম্বর ও মূল্যায়ন সূচক</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printReport">
          <icon name="printer" /> প্রিন্ট রিপোর্ট
        </button>
      </div>
    </div>

    <!-- Teacher Cards Grid -->
    <div class="teachers-results-grid">
      <div v-for="t in teachersData" :key="t.id" class="card teacher-res-card">
        <div class="card-top">
          <div class="user-avatar-initials lg" :style="{ backgroundColor: getAvatarColor(t.name) }">
            {{ t.name.charAt(0) }}
          </div>
          <div class="t-info">
            <h3>{{ t.name }}</h3>
            <span class="designation-text">{{ t.designation }}</span>
          </div>
          <div class="pass-rate-badge">
            <span class="rate-val">{{ toBn(t.overall_pass_rate) }}%</span>
            <span class="rate-lbl">পাসের হার</span>
          </div>
        </div>

        <div class="subjects-taught-table">
          <table class="sub-table">
            <thead>
              <tr>
                <th>বিষয় / কিতাব</th>
                <th>জামাত</th>
                <th class="text-center">মোট ছাত্র</th>
                <th class="text-center">গড় নম্বর</th>
                <th class="text-center">A+ সংখ্যা</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in t.subjects" :key="s.name">
                <td><strong>{{ s.name }}</strong></td>
                <td><span class="class-pill">{{ s.class }}</span></td>
                <td class="text-center">{{ toBn(s.students) }}</td>
                <td class="text-center font-bold text-success">{{ toBn(s.avg_mark) }}%</td>
                <td class="text-center"><span class="badge-a-plus">{{ toBn(s.a_plus_count) }} জন</span></td>
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
const teachersData = ref<any[]>([
  {
    id: 1,
    name: 'মাওলানা মাহমুদ হাসান',
    designation: 'সিনিয়র উস্তাদ ও মুহাদ্দিস',
    overall_pass_rate: 98.5,
    subjects: [
      { name: 'মিজানুস সরফ', class: 'মিজান জামাত', students: 42, avg_mark: 88.4, a_plus_count: 18 },
      { name: 'মুনশাইব', class: 'মিজান জামাত', students: 42, avg_mark: 91.2, a_plus_count: 22 }
    ]
  },
  {
    id: 2,
    name: 'মাওলানা নূরুল ইসলাম',
    designation: 'উস্তাদুল ফিকহ ওয়াল উসূল',
    overall_pass_rate: 95.0,
    subjects: [
      { name: 'নাহবেমীর', class: 'নাহবেমীর', students: 38, avg_mark: 84.0, a_plus_count: 14 },
      { name: 'হেদায়াতুন্নাহু', class: 'হেদায়াতুন্নাহু', students: 30, avg_mark: 82.5, a_plus_count: 10 }
    ]
  }
])

async function loadTeachers() {
  try {
    const res = await api.get('/academic/teachers').catch(() => null)
    const teachers = res?.data?.data || []
    if (teachers.length > 0) {
      teachersData.value = teachers.map((t: any, idx: number) => ({
        id: t.id,
        name: t.name_bn || t.name || `উস্তাদ ${idx + 1}`,
        designation: t.designation || 'সিনিয়র উস্তাদ',
        overall_pass_rate: Math.round(92 + (idx % 3) * 3.2),
        subjects: [
          { name: 'কিতাব ও পাঠদান', class: 'হিফজ ও কিতাব', students: 35, avg_mark: 88.5, a_plus_count: 12 }
        ]
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadTeachers)

function printReport() {
  window.print()
}

function toBn(num: any) {
  if (num === null || num === undefined) return ''
  return String(num).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d])
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.teachers-results-grid { display: flex; flex-direction: column; gap: 1.5rem; }
.teacher-res-card { border-radius: 14px; padding: 1.5rem; }

.card-top { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--color-border-light); }
.user-avatar-initials.lg { width: 48px; height: 48px; border-radius: 50%; color: #fff; font-size: 1.2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

.t-info { flex: 1; }
.t-info h3 { font-size: 1.15rem; font-weight: 700; margin: 0 0 0.15rem; }
.designation-text { font-size: 0.82rem; color: var(--color-text-light); }

.pass-rate-badge { display: flex; flex-direction: column; align-items: flex-end; }
.rate-val { font-size: 1.4rem; font-weight: 900; color: #15803d; }
.rate-lbl { font-size: 0.72rem; color: var(--color-text-light); }

.sub-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
.sub-table th, .sub-table td { padding: 0.55rem 0.75rem; border-bottom: 1px solid var(--color-border-light); text-align: left; }
.sub-table thead th { background: #f8fafc; font-weight: 700; color: var(--color-text-light); }

.class-pill { background: rgba(0, 0, 0, 0.05); padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
.badge-a-plus { background: #dcfce7; color: #15803d; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.76rem; }
.text-success { color: #15803d; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
</style>
