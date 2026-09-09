<template>
  <div class="seats-page">
    <div class="page-header">
      <div>
        <span class="eyebrow">পরীক্ষার আসন বরাদ্দ</span>
        <h1 v-if="exam">{{ exam.name_bn || exam.title_bn }} — আসন বরাদ্দ</h1>
        <h1 v-else>আসন বরাদ্দ</h1>
      </div>
      <div class="header-actions">
        <NuxtLink :to="`/exams/${examId}/admit-cards`" class="btn btn-outline">
          <Icon name="document" /> ভরণা কার্ড
        </NuxtLink>
        <button class="btn btn-primary" @click="saveSeatPlan" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'সিট প্ল্যান সংরক্ষণ' }}
        </button>
      </div>
    </div>

    <div v-if="error" class="alert alert-error mb-2">{{ error }}</div>

    <div v-if="loading" class="loading-state"><div class="spinner" /></div>

    <div v-else class="seats-layout">
      <div class="card hall-card mb-2">
        <div class="hall-header">
          <div>
            <h2 class="m-0">হলের আসন বিন্যাস</h2>
            <small>{{ hall_rows }} সারি × {{ hall_cols }} কলাম</small>
          </div>
          <div class="hall-stats">
            <div class="stat"><span>মোট আসন</span><b>{{ totalSeats }}</b></div>
            <div class="stat"><span>বরাদ্দকৃত</span><b>{{ seatsAssigned }}</b></div>
          </div>
        </div>
        <div class="hall-grid">
          <div v-for="r in hall_rows" :key="'r'+r" class="hall-row">
            <div class="row-label">{{ r }}</div>
            <div class="seats" :style="{gridTemplateColumns:`repeat(${hall_cols}, 1fr)`}">
              <div v-for="c in hall_cols" :key="'s'+r+'-'+c"
                class="seat"
                :class="{
                  occupied: seatByPos(r,c)?.student_id !== undefined,
                  selected: selectedSeat && selectedSeat.row===r && selectedSeat.col===c
                }"
                @mouseenter="hoveredSeat={row:r,col:c}"
                @mouseleave="hoveredSeat=null"
                @click="handleSeatClick(r,c)">
                <div class="seat-inner">{{ seatLabel(r,c) }}</div>
                <div v-if="occupant(r,c)" class="seat-student">
                  <img v-if="occupant(r,c)?.photo_url" :src="occupant(r,c)?.photo_url" class="seat-avatar" />
                  <span>{{ occupantName(r,c) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="hall-legend">
          <span class="legend-item"><span class="dot empty"></span> খালি আসন</span>
          <span class="legend-item"><span class="dot occupied"></span> বরাদ্দকৃত</span>
          <span class="legend-item"><span class="dot selected"></span> নির্বাচিত</span>
        </div>
      </div>

      <div class="card student-panel mb-2">
        <div class="panel-header">
          <h2>অংশগ্রহণকারী শিক্ষার্থী ({{ enrolledStudents?.length ?? 0 }})</h2>
          <div class="search-box">
            <Icon name="search" />
            <input v-model="studentSearch" placeholder="শিক্ষার্থী খুঁজুন..." />
          </div>
        </div>
        <div v-if="selectedStudentId" class="selected-info">
          <Icon name="check" />
          <span>{{ selectedStudent?.name_bn }} বরাদ্দ করতে আসনে ক্লিক করুন</span>
          <button class="btn-link" @click="clearSelection">বাতিল</button>
        </div>
        <div class="student-list">
          <div v-for="s in filteredStudents" :key="s.id" class="student-row"
            :class="{ selected: s.id===selectedStudentId }"
            @click="selectStudent(s)">
            <img v-if="s.photo_url" :src="s.photo_url" class="student-avatar" />
            <div class="student-info">
              <b>{{ s.name_bn }}</b>
              <small>{{ s.name_en || '' }} · {{ s.roll_or_reg }}</small>
            </div>
            <Icon name="check" v-if="s.id===selectedStudentId" class="check-icon" />
          </div>
          <div v-if="!filteredStudents.length" class="empty-inline">
            {{ studentSearch ? 'কোনো শিক্ষার্থী পাওয়া যায়নি' : 'অংশগ্রহণকারী শিক্ষার্থী নেই' }}
          </div>
        </div>
      </div>
    </div>

    <div class="card stats-card mb-2">
      <div class="stats-row">
        <div class="stat-card green"><span>শিক্ষার্থী</span><b>{{ enrolledStudents?.length ?? 0 }}</b></div>
        <div class="stat-card blue"><span>বরাদ্দকৃত আসন</span><b>{{ seatsAssigned }}</b></div>
        <div class="stat-card purple"><span>অবশিষ্ট আসন</span><b>{{ availableSeats }}</b></div>
        <div class="stat-card gold"><span>হল</span><b>{{ hall_rows }} সারি</b></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Icon from '~/components/Icon.vue'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const examId = route.params.exam?.toString() ?? route.params.id?.toString() ?? ''
const api = useApiClient()

const exam = ref<any>(null)
const enrolledStudents = ref<any[]>([])
const seatPlan = ref<Record<string, any>>({})
const hall_rows = ref(6)
const hall_cols = ref(9)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const selectedStudentId = ref<number | null>(null)
const hoveredSeat = ref<{row:number,col:number}|null>(null)
const studentSearch = ref('')

const selectedStudent = computed(() =>
  enrolledStudents.value.find(s => s.id === selectedStudentId.value) ?? null
)

const seatsAssigned = computed(() => Object.keys(seatPlan.value).length)
const totalSeats = computed(() => hall_rows.value * hall_cols.value)
const availableSeats = computed(() => totalSeats.value - seatsAssigned.value)

const filteredStudents = computed(() => {
  if (!studentSearch.value) return enrolledStudents.value
  const q = studentSearch.value.toLowerCase()
  return enrolledStudents.value.filter(s =>
    (s.name_bn ?? '').toLowerCase().includes(q) ||
    (s.name_en ?? '').toLowerCase().includes(q) ||
    (s.roll_or_reg ?? '').toString().includes(q)
  )
})

function seatByPos(r:number, c:number) {
  return Object.values(seatPlan.value).find(info => info?.row === r && info?.col === c) ?? null
}

function occupant(r:number, c:number) {
  const info = seatByPos(r,c)
  if (!info) return null
  return enrolledStudents.value.find(s => s.id === info.student_id) ?? null
}

function occupantName(r:number, c:number) {
  const o = occupant(r,c)
  return o ? (o.name_bn ?? '') : ''
}

function seatLabel(r:number, c:number) {
  const info = seatByPos(r,c)
  return info?.label ?? `${r}-${c}`
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/exams/${examId}/seats`)
    exam.value = res.data?.data?.exam ?? null
    enrolledStudents.value = res.data?.data?.enrolled_students ?? []
    seatPlan.value = res.data?.data?.seat_plan ?? {}
    hall_rows.value = res.data?.data?.hall_rows ?? 6
    hall_cols.value = res.data?.data?.hall_cols ?? 9
  } catch(e:any) {
    error.value = e?.response?.data?.message ?? 'ডেটা লোড করা যায়নি'
  } finally {
    loading.value = false
  }
}

function selectStudent(s:any) {
  selectedStudentId.value = s.id
}

function clearSelection() {
  selectedStudentId.value = null
}

function handleSeatClick(r:number, c:number) {
  if (selectedStudentId.value === null) return
  if (seatByPos(r,c)?.student_id !== undefined) {
    error.value = 'এই আসনটি ইতিমধ্যে বরাদ্দ করা হয়েছে'
    return
  }
  assignSeat(r, c, selectedStudentId.value)
}

async function assignSeat(row:number, col:number, studentId:number) {
  saving.value = true
  try {
    await api.post(`/exams/${examId}/seats/allocate`, {
      student_id: studentId,
      row, col,
      seat_label: `${row}-${col}`,
    })
    // reload seat plan
    const planRes = await api.get(`/exams/${examId}/seats/plan`)
    seatPlan.value = planRes.data?.data ?? {}
    clearSelection()
  } catch(e:any) {
    error.value = e?.response?.data?.message ?? 'আসন বরাদ্দ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function saveSeatPlan() {
  saving.value = true
  try {
    await api.post(`/exams/${examId}/seats/plan`, {
      seat_plan: seatPlan.value,
      hall_rows: hall_rows.value,
      hall_cols: hall_cols.value,
    })
  } catch(e:any) {
    error.value = e?.response?.data?.message ?? 'সিট প্ল্যান সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.seats-page { max-width: 1280px; margin: 0 auto; padding: 1.5rem; }
.page-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.3rem; }
.eyebrow { color: var(--color-primary); font: 600 .78rem var(--font-bn); }
.seats-page h1 { margin:.25rem 0; color: var(--color-primary); font: 700 1.4rem var(--font-bn); }
.header-actions { display:flex; gap:.5rem; }
.seats-layout { display:grid; grid-template-columns: 1fr 360px; gap:1rem; }
.card { background:#fff; border:1px solid var(--color-border-light); border-radius:16px; padding:1.1rem; }
.hall-header { display:flex; justify-content:space-between; margin-bottom:.8rem; }
.hall-stats { display:flex; gap:1rem; }
.stat { display:flex; flex-direction:column; align-items:flex-end; }
.stat span { font:.68rem var(--font-bn); color: var(--color-text-muted); }
.stat b { font:800 1.2rem var(--font-sans); color: var(--color-primary); }
.hall-grid { display:flex; flex-direction:column; gap:.5rem; }
.hall-row { display:grid; grid-template-columns: 30px 1fr; gap:.4rem; align-items:center; }
.row-label { font:700 .8rem var(--font-bn); color: var(--color-text-muted); text-align:center; }
.seats { display:grid; gap:.35rem; }
.seat { aspect-ratio:1; border-radius:10px; display:grid; place-items:center; font:.6rem var(--font-bn); cursor:pointer; position:relative;
  background: var(--color-bg-muted); border:1px dashed var(--color-border); transition:.15s; }
.seat:hover { background: var(--color-primary-10); border-color: var(--color-primary); }
.seat.occupied { background: var(--color-primary-50); border-color: var(--color-primary); color: var(--color-primary); }
.seat.selected { background: var(--color-accent-10); border-color: var(--color-accent); box-shadow: 0 0 0 2px var(--color-accent); }
.seat-student { position:absolute; bottom:2px; font:.55rem var(--font-bn); color: var(--color-text); max-width:90%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.seat-avatar { width:14px; height:14px; border-radius:50%; object-fit:cover; margin-right:.25rem; }
.hall-legend { display:flex; gap:1rem; margin-top:.8rem; font:.72rem var(--font-bn); color: var(--color-text-muted); }
.legend-item { display:flex; align-items:center; gap:.35rem; }
.dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
.dot.empty { background: var(--color-bg-muted); border:1px dashed var(--color-border); }
.dot.occupied { background: var(--color-primary-50); border:1px solid var(--color-primary); }
.dot.selected { background: var(--color-accent-10); border:1px solid var(--color-accent); }
.student-panel { max-height: 480px; display:flex; flex-direction:column; }
.panel-header { display:flex; flex-direction:column; gap:.5rem; margin-bottom:.8rem; }
.panel-header h2 { font:700 1rem var(--font-bn); margin:0; }
.search-box { display:flex; align-items:center; gap:.4rem; padding:.5rem .6rem; background: var(--color-bg-muted); border-radius:9px; }
.search-box input { flex:1; border:0; background:transparent; outline:0; font:.8rem var(--font-bn); }
.student-list { flex:1; overflow-y:auto; }
.student-row { display:flex; align-items:center; gap:.5rem; padding:.5rem .5rem; border-bottom:1px solid var(--color-border-light); cursor:pointer; }
.student-row:hover { background: var(--color-bg-muted); }
.student-row.selected { background: var(--color-accent-10); }
.student-avatar { width:32px; height:32px; border-radius:50%; object-fit:cover; }
.student-info { flex:1; }
.student-info b { font:.82rem var(--font-bn); display:block; }
.student-info small { font:.68rem var(--font-bn); color: var(--color-text-muted); }
.check-icon { color: var(--color-accent); }
.selected-info { display:flex; align-items:center; gap:.5rem; padding:.6rem .8rem; background: var(--color-accent-10); border-radius:9px; margin-bottom:.5rem; font:.72rem var(--font-bn); }
.btn-link { background:0; border:0; color: var(--color-text-muted); cursor:pointer; font:.72rem var(--font-bn); }
.empty-inline { padding:1rem; text-align:center; color: var(--color-text-muted); font:.78rem var(--font-bn); }
.stats-card { }
.stats-row { display:grid; grid-template-columns: repeat(4,1fr); gap:.7rem; }
.stat-card { display:flex; flex-direction:column; align-items:center; padding:1rem; border-radius:12px; color:#fff; }
.stat-card span { font:.68rem var(--font-bn); opacity:.85; }
.stat-card b { font:800 1.3rem var(--font-sans); }
.stat-card.green { background: var(--color-primary); }
.stat-card.blue { background: #17658f; }
.stat-card.purple { background: #6e4da5; }
.stat-card.gold { background: #bd8e20; }
.mb-2 { margin-bottom: .5rem; }
</style>
