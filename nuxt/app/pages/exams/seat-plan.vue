<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/exams" class="back-link"><icon name="arrow-left" /> পরীক্ষা তালিকায় ফিরে যান</NuxtLink>
        <h1>পরীক্ষার সিট প্ল্যান ও রুম বিন্যাস</h1>
        <p class="page-subtitle">হল রুম ভিত্তিক আসন বিন্যাস, রোল রেঞ্জ এবং প্রিন্টযোগ্য ডেস্ক স্লিপ প্রস্তুতকরণ</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printDeskSlips">
          <icon name="printer" /> ডেস্ক স্লিপ প্রিন্ট
        </button>
        <button class="btn btn-primary" @click="printMasterPlan">
          <icon name="printer" /> হল মাস্টার চার্ট প্রিন্ট
        </button>
      </div>
    </div>

    <!-- Filters & Settings Toolbar -->
    <div class="card toolbar no-print">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">পরীক্ষা *</label>
          <select v-model="selectedExam" class="form-select">
            <option v-for="ex in (examsList.length ? examsList : [{ name: 'বার্ষিক পরীক্ষা ২০২৬' }, { name: 'প্রথম সাময়িক পরীক্ষা ২০২৬' }])" :key="ex.id || ex.name" :value="ex.name_bn || ex.name">
              {{ ex.name_bn || ex.name }}
            </option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">হল রুম / কক্ষ *</label>
          <select v-model="selectedRoom" class="form-select" @change="generateSeats">
            <option value="রুম ১০১ (দ্বিতীয় তলা পূর্ব)">রুম ১০১ (দ্বিতীয় তলা পূর্ব - ধারণক্ষমতা: ৪০)</option>
            <option value="রুম ১০২ (দ্বিতীয় তলা পশ্চিম)">রুম ১০২ (দ্বিতীয় তলা পশ্চিম - ধারণক্ষমতা: ৪০)</option>
            <option value="মূল অডিটোরিয়াম হল">মূল অডিটোরিয়াম হল (ধারণক্ষমতা: ১২০)</option>
          </select>
        </div>
        <div class="filter-item small">
          <label class="filter-label">প্রতি বেঞ্চে আসন</label>
          <select v-model.number="studentsPerBench" class="form-select" @change="generateSeats">
            <option :value="2">২ জন (ZIGZAG মোড)</option>
            <option :value="3">৩ জন</option>
            <option :value="1">১ জন (সিঙ্গেল)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Room Summary KPI -->
    <div class="stats-grid no-print">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ allocatedSeats.length.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট বরাদ্দকৃত আসন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ toBn(Math.ceil(allocatedSeats.length / studentsPerBench)) }}</span>
          <span class="stat-label">ব্যবহৃত বেঞ্চ সংখ্যা</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">মিজান + নাহবেমীর</span>
          <span class="stat-label">অন্তর্ভুক্ত জামাতসমূহ</span>
        </div>
      </div>
    </div>

    <!-- Visual Bench Matrix Display -->
    <div class="card matrix-card no-print">
      <div class="matrix-header">
        <div class="blackboard-label">বোর্ড / শিক্ষকের অবস্থান (সামনে)</div>
      </div>
      <div class="benches-grid">
        <div v-for="(bench, bIdx) in benchesList" :key="bIdx" class="bench-item">
          <div class="bench-top-bar">বেঞ্চ নং #{{ toBn(bIdx + 1) }}</div>
          <div class="bench-seats">
            <div v-for="seat in bench.seats" :key="seat.roll" class="seat-spot">
              <span class="seat-roll">{{ toBn(seat.roll) }}</span>
              <span class="seat-name">{{ seat.name }}</span>
              <span class="seat-class">{{ seat.class_name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Printable Desk Slips (Stickers for pasting on tables) -->
    <div class="print-desk-slips-container">
      <div v-for="seat in allocatedSeats" :key="seat.roll" class="desk-slip">
        <div class="slip-header">মারকাযুল উলুম মাদ্রাসা</div>
        <div class="slip-exam">{{ selectedExam }}</div>
        <div class="slip-roll-block">
          <span class="lbl">রোল:</span>
          <span class="big-roll">{{ toBn(seat.roll) }}</span>
        </div>
        <div class="slip-details">
          <div class="detail-row"><strong>নাম:</strong> {{ seat.name }}</div>
          <div class="detail-row"><strong>শ্রেণি:</strong> {{ seat.class_name }}</div>
          <div class="detail-row"><strong>কক্ষ:</strong> {{ selectedRoom.split(' ')[0] }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const selectedExam = ref('বার্ষিক পরীক্ষা ২০২৬')
const selectedRoom = ref('রুম ১০১ (দ্বিতীয় তলা পূর্ব)')
const studentsPerBench = ref(2)
const examsList = ref<any[]>([])

const allocatedSeats = ref<any[]>([])

async function loadExamsAndStudents() {
  try {
    const [examsRes, studentsRes] = await Promise.all([
      api.get('/exams').catch(() => null),
      api.get('/students?per_page=50').catch(() => null)
    ])

    if (examsRes?.data?.data) {
      const exs = Array.isArray(examsRes.data.data) ? examsRes.data.data : (examsRes.data.data?.data || [])
      if (exs.length > 0) {
        examsList.value = exs
        selectedExam.value = exs[0].name_bn || exs[0].name || selectedExam.value
      }
    }

    const studs = studentsRes?.data?.data?.data || studentsRes?.data?.data || []
    if (studs.length > 0) {
      allocatedSeats.value = studs.map((s: any, idx: number) => ({
        roll: s.roll_number || (100 + idx + 1),
        name: s.name_bn || s.name_en || `শিক্ষার্থী ${idx + 1}`,
        class_name: s.academic_class?.name || (idx % 2 === 0 ? 'মিজান জামাত' : 'নাহবেমীর')
      }))
    } else {
      generateSeats()
    }
  } catch (e) {
    generateSeats()
  }
}

function generateSeats() {
  const list = []
  for (let i = 1; i <= 24; i++) {
    const isMizan = i % 2 !== 0
    list.push({
      roll: 100 + i,
      name: isMizan ? `মুহাম্মদ সালমান (${i})` : `মুহাম্মদ আবদুল্লাহ (${i})`,
      class_name: isMizan ? 'মিজান জামাত' : 'নাহবেমীর'
    })
  }
  allocatedSeats.value = list
}

const benchesList = computed(() => {
  const benches = []
  const perBench = studentsPerBench.value
  for (let i = 0; i < allocatedSeats.value.length; i += perBench) {
    benches.push({
      seats: allocatedSeats.value.slice(i, i + perBench)
    })
  }
  return benches
})

function printDeskSlips() {
  window.print()
}

function printMasterPlan() {
  window.print()
}

function toBn(num: any) {
  if (num === null || num === undefined) return ''
  return String(num).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d])
}

onMounted(loadExamsAndStudents)
</script>

<style scoped>
.page-wrapper { max-width: 1320px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.filter-row { display: flex; gap: 1.25rem; flex-wrap: wrap; }
.filter-item { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 0.35rem; }
.filter-item.small { flex: 0.6; min-width: 140px; }
.filter-label { font-size: 0.82rem; font-weight: 700; color: var(--color-text); }

/* Matrix card */
.matrix-card { border-radius: 14px; padding: 1.5rem; }
.matrix-header { text-align: center; margin-bottom: 1.5rem; }
.blackboard-label { display: inline-block; background: #334155; color: #f8fafc; padding: 0.4rem 2rem; border-radius: 6px; font-weight: 700; font-size: 0.88rem; letter-spacing: 0.05em; }

.benches-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
.bench-item { border: 1.5px solid var(--color-border); border-radius: 10px; overflow: hidden; background: #fff; }
.bench-top-bar { background: #f8fafc; padding: 0.4rem 0.75rem; font-size: 0.76rem; font-weight: 700; color: var(--color-text-light); border-bottom: 1px solid var(--color-border-light); }
.bench-seats { display: flex; divide-x: 1px solid var(--color-border-light); }
.seat-spot { flex: 1; padding: 0.75rem 0.5rem; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.15rem; border-right: 1px solid var(--color-border-light); }
.seat-spot:last-child { border-right: none; }
.seat-roll { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); }
.seat-name { font-size: 0.78rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.seat-class { font-size: 0.7rem; color: var(--color-text-light); background: rgba(0, 0, 0, 0.05); padding: 0.1rem 0.4rem; border-radius: 4px; }

/* Desk Slips Print */
.print-desk-slips-container { display: none; }

@media print {
  .no-print { display: none !important; }
  .print-desk-slips-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
  .desk-slip { border: 2px solid #000; border-radius: 6px; padding: 10px; page-break-inside: avoid; text-align: center; }
  .slip-header { font-weight: 800; font-size: 0.95rem; }
  .slip-exam { font-size: 0.78rem; font-weight: 600; margin-bottom: 5px; }
  .slip-roll-block { border: 1.5px solid #000; padding: 4px 8px; border-radius: 4px; margin: 4px 0 8px; display: inline-block; }
  .slip-roll-block .lbl { font-size: 0.85rem; font-weight: 700; margin-right: 4px; }
  .slip-roll-block .big-roll { font-size: 1.4rem; font-weight: 900; }
  .slip-details { text-align: left; font-size: 0.82rem; }
  .detail-row { margin-bottom: 2px; }
}

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
</style>
