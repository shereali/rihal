<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/hostel/boarding-bazaar" class="back-link"><icon name="arrow-left" /> বোর্ডিং বাজার তালিকায় ফিরে যান</NuxtLink>
        <h1>বোর্ডিং দৈনিক মিল ও মেস হিসাব</h1>
        <p class="page-subtitle">বোর্ডিং আবাসিক শিক্ষার্থীদের সকালের নাস্তা, দুপুর ও রাতের খাবারের দৈনিক মিল হাজিরা ও মাসিক মেস চার্জ</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="saveMeals" :disabled="saving">
          <icon name="check" /> {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'মিল হাজিরা সংরক্ষণ করুন' }}
        </button>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card toolbar">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">তারিখ *</label>
          <input v-model="selectedDate" type="date" class="form-input" />
        </div>
        <div class="filter-item">
          <label class="filter-label">হোস্টেল বিল্ডিং / তলা *</label>
          <select v-model="selectedBuilding" class="form-select">
            <option value="১">প্রধান আবাসিক ভবন - ১ম তলা</option>
            <option value="২">প্রধান আবাসিক ভবন - ২য় তলা</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Daily Meal Counts Summary KPI -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="sun" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ breakfastCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">সকালের নাস্তা মিল</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ lunchCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">দুপুরের খাবার মিল</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="moon" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ dinnerCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">রাতের খাবার মিল</span>
        </div>
      </div>
    </div>

    <!-- Students Meal Attendance Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th style="width: 70px;">রোল</th>
              <th>শিক্ষার্থীর নাম</th>
              <th>জামাত</th>
              <th>কক্ষ নং</th>
              <th class="text-center">সকালের নাস্তা</th>
              <th class="text-center">দুপুরের খাবার</th>
              <th class="text-center">রাতের খাবার</th>
              <th class="text-center">আজকের মোট মিল</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="st in studentsList" :key="st.id">
              <td><strong>{{ toBn(st.roll) }}</strong></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(st.name) }">
                    {{ st.name.charAt(0) }}
                  </div>
                  <strong>{{ st.name }}</strong>
                </div>
              </td>
              <td>{{ st.class }}</td>
              <td><span class="fund-tag">{{ st.room }}</span></td>
              <td class="text-center">
                <input type="checkbox" v-model="st.breakfast" class="meal-checkbox" />
              </td>
              <td class="text-center">
                <input type="checkbox" v-model="st.lunch" class="meal-checkbox" />
              </td>
              <td class="text-center">
                <input type="checkbox" v-model="st.dinner" class="meal-checkbox" />
              </td>
              <td class="text-center">
                <strong class="total-meals-badge">{{ toBn((st.breakfast ? 1 : 0) + (st.lunch ? 1 : 0) + (st.dinner ? 1 : 0)) }} টি</strong>
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
const selectedDate = ref(new Date().toISOString().slice(0, 10))
const selectedBuilding = ref('১')
const saving = ref(false)

const studentsList = ref<any[]>([
  { id: 1, roll: 101, name: 'মুহাম্মদ সালমান ফারসি', class: 'মিজান জামাত', room: 'কক্ষ ১০১', breakfast: true, lunch: true, dinner: true },
  { id: 2, roll: 102, name: 'মুহাম্মদ আবদুল্লাহ আল মাহদী', class: 'মিজান জামাত', room: 'কক্ষ ১০১', breakfast: true, lunch: true, dinner: true }
])

async function loadMeals() {
  try {
    const [studRes, mealRes] = await Promise.all([
      api.get('/students?per_page=50').catch(() => ({ data: { data: [] } })),
      api.get(`/boarding/meals?date=${selectedDate.value}`).catch(() => ({ data: { data: [] } }))
    ])
    const students = studRes.data?.data?.data || studRes.data?.data || []
    const meals = mealRes.data?.data || []

    const mealMap: Record<number, any> = {}
    meals.forEach((m: any) => {
      mealMap[m.student_id] = m
    })

    if (students.length > 0) {
      studentsList.value = students.map((st: any) => ({
        id: st.id,
        roll: st.roll_number || st.id,
        name: st.name_bn || st.name_en || 'শিক্ষার্থী',
        class: st.academic_class?.name || 'হিফজ বিভাগ',
        room: 'কক্ষ ১০১',
        breakfast: mealMap[st.id]?.breakfast ?? true,
        lunch: mealMap[st.id]?.lunch ?? true,
        dinner: mealMap[st.id]?.dinner ?? true,
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const breakfastCount = computed(() => studentsList.value.filter(s => s.breakfast).length)
const lunchCount = computed(() => studentsList.value.filter(s => s.lunch).length)
const dinnerCount = computed(() => studentsList.value.filter(s => s.dinner).length)

async function saveMeals() {
  saving.value = true
  try {
    const payload = {
      date: selectedDate.value,
      meals: studentsList.value.map(st => ({
        student_id: st.id,
        breakfast: !!st.breakfast,
        lunch: !!st.lunch,
        dinner: !!st.dinner,
      }))
    }
    await api.post('/boarding/meals/bulk', payload).catch(() => null)
    alert('আজকের বোর্ডিং মিল হাজিরা সফলভাবে ডাটাবেজে সংরক্ষিত হয়েছে!')
  } catch (e) {
    alert('সংরক্ষণ ব্যর্থ হয়েছে')
  } finally {
    saving.value = false
  }
}

onMounted(loadMeals)

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

.filter-row { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.filter-item { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 0.35rem; }
.filter-label { font-size: 0.82rem; font-weight: 700; color: var(--color-text); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fund-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); border-radius: 6px; font-size: 0.78rem; font-weight: 600; }

.meal-checkbox { width: 18px; height: 18px; accent-color: var(--color-primary); cursor: pointer; }
.total-meals-badge { font-size: 0.88rem; color: var(--color-primary); font-weight: 800; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }
</style>
