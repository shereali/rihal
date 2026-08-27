<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/results" class="back-link"><icon name="arrow-left" /> ফলাফল তালিকায় ফিরে যান</NuxtLink>
        <h1>গড় নম্বর ভিত্তিক ফলাফল বিবরণী</h1>
        <p class="page-subtitle">শিক্ষার্থীদের মোট প্রাপ্ত নম্বর, বিষয়ভিত্তিক গড়, মেধা স্থান ও বিভাগ মূল্যায়ন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printSheet">
          <icon name="printer" /> প্রিন্ট শিট
        </button>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card toolbar no-print">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">পরীক্ষা *</label>
          <select v-model="selectedExam" class="form-select">
            <option value="১">বার্ষিক পরীক্ষা ২০২৬</option>
            <option value="২">প্রথম সাময়িক পরীক্ষা ২০২৬</option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">শ্রেণি / জামাত *</label>
          <select v-model="selectedClass" class="form-select">
            <option value="১">মিজান জামাত</option>
            <option value="২">নাহবেমীর জামাত</option>
            <option value="৩">হেদায়াতুন্নাহু</option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">অনুসন্ধান</label>
          <input v-model="search" class="form-input" placeholder="শিক্ষার্থীর নাম বা রোল..." />
        </div>
      </div>
    </div>

    <!-- Results Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th class="text-center">মেধা স্থান</th>
              <th>রোল</th>
              <th>শিক্ষার্থীর নাম</th>
              <th class="text-center">মোট নম্বর (৬০০)</th>
              <th class="text-center">গড় নম্বর (%)</th>
              <th class="text-center">বিভাগ / মান</th>
              <th class="text-center">ফলাফল</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in filteredResults" :key="r.id">
              <td class="text-center">
                <span class="rank-badge" :class="rankBadgeClass(r.rank)">{{ toBn(r.rank) }}</span>
              </td>
              <td><strong>{{ toBn(r.roll) }}</strong></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(r.name) }">
                    {{ r.name.charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ r.name }}</strong>
                    <div class="sub-text">রেজি: {{ toBn(r.reg) }}</div>
                  </div>
                </div>
              </td>
              <td class="text-center font-bold">{{ toBn(r.total) }}</td>
              <td class="text-center">
                <div class="avg-bar-wrap">
                  <strong>{{ toBn(r.avg) }}%</strong>
                  <div class="mini-progress-bar">
                    <div class="mini-progress-fill" :style="{ width: r.avg + '%' }" />
                  </div>
                </div>
              </td>
              <td class="text-center">
                <span class="grade-pill" :class="divisionClass(r.division)">{{ r.division }}</span>
              </td>
              <td class="text-center">
                <span class="status-pill badge-approved">
                  <span class="status-dot" /> উত্তীর্ণ
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const selectedExam = ref('১')
const selectedClass = ref('১')
const search = ref('')

const resultsList = ref<any[]>([
  { id: 1, rank: 1, roll: 101, reg: 'REG-2024-001', name: 'মুহাম্মদ সালমান ফারসি', total: 582, avg: 97.0, division: 'মুমতাজ (A+)' },
  { id: 2, rank: 2, roll: 102, reg: 'REG-2024-002', name: 'মুহাম্মদ আবদুল্লাহ আল মাহদী', total: 564, avg: 94.0, division: 'মুমতাজ (A+)' },
  { id: 3, rank: 3, roll: 103, reg: 'REG-2024-003', name: 'মুহাম্মদ তাওহীদুল ইসলাম', total: 528, avg: 88.0, division: 'জায়্যিদ জিদ্দান (A)' },
  { id: 4, rank: 4, roll: 104, reg: 'REG-2024-004', name: 'মুহাম্মদ উসামা বিন যুবায়ের', total: 495, avg: 82.5, division: 'জায়্যিদ (A-)' },
  { id: 5, rank: 5, roll: 105, reg: 'REG-2024-005', name: 'মুহাম্মদ আনাস বিন মালিক', total: 462, avg: 77.0, division: 'জায়্যিদ (A-)' }
])

async function loadResults() {
  try {
    const res = await api.get('/reports/results?exam_id=' + selectedExam.value).catch(() => null)
    const studs = res?.data?.rows || []
    if (studs.length > 0) {
      // Sort by total_marks descending
      studs.sort((a: any, b: any) => (b.total_marks || 0) - (a.total_marks || 0))
      
      resultsList.value = studs.map((s: any, idx: number) => {
        return {
          id: s.student_id,
          rank: idx + 1, // Determined natively from sort for now
          roll: s.admission_number || (100 + idx + 1),
          reg: `REG-2024-${String(s.student_id).padStart(3, '0')}`,
          name: s.name_bn || s.name_en || `শিক্ষার্থী ${idx + 1}`,
          total: s.total_marks || 0,
          avg: s.percentage || 0,
          division: s.grade || 'অজানা'
        }
      })
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredResults = computed(() => {
  if (!search.value) return resultsList.value
  const term = search.value.toLowerCase()
  return resultsList.value.filter(r => r.name.toLowerCase().includes(term) || String(r.roll).includes(term))
})

onMounted(loadResults)

function printSheet() {
  window.print()
}

function rankBadgeClass(rank: number) {
  if (rank === 1) return 'rank-1'
  if (rank === 2) return 'rank-2'
  if (rank === 3) return 'rank-3'
  return ''
}

function divisionClass(div: string) {
  if (div.includes('মুমতাজ')) return 'div-mumtaz'
  if (div.includes('জায়্যিদ জিদ্দান')) return 'div-jayyid-jiddan'
  if (div.includes('জায়্যিদ')) return 'div-jayyid'
  return 'div-maqbool'
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
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.filter-row { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.filter-item { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 0.35rem; }
.filter-label { font-size: 0.82rem; font-weight: 700; color: var(--color-text); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.76rem; color: var(--color-text-light); }

.rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-weight: 800; font-size: 0.88rem; background: rgba(0, 0, 0, 0.06); }
.rank-badge.rank-1 { background: #fef08a; color: #854d0e; border: 1.5px solid #eab308; }
.rank-badge.rank-2 { background: #e2e8f0; color: #334155; border: 1.5px solid #94a3b8; }
.rank-badge.rank-3 { background: #ffedd5; color: #9a3412; border: 1.5px solid #f97316; }

.avg-bar-wrap { display: flex; flex-direction: column; align-items: center; gap: 0.2rem; }
.mini-progress-bar { width: 70px; height: 5px; background: rgba(0, 0, 0, 0.08); border-radius: 3px; overflow: hidden; }
.mini-progress-fill { height: 100%; background: #15803d; border-radius: 3px; }

.grade-pill { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700; }
.div-mumtaz { background: #dcfce7; color: #15803d; }
.div-jayyid-jiddan { background: #dbeafe; color: #1e40af; }
.div-jayyid { background: #fef3c7; color: #b45309; }
.div-maqbool { background: #f3f4f6; color: #4b5563; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
</style>
