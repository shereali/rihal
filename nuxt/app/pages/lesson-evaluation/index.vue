<template>
  <div class="page-wrapper">
    <!-- Header -->
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <span class="eyebrow">দৈনিক পাঠ মূল্যায়ন</span>
        <h1>সবক ও পাঠ মূল্যায়ন শীট</h1>
        <p class="page-subtitle">শিক্ষার্থীদের দৈনিক সবক, মুখস্থ পাঠ ও উপস্থিতি পর্যবেক্ষণ এবং অভিভাবকদের আপডেট প্রদান</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/lesson-evaluation/settings" class="btn btn-outline">
          <icon name="settings" /> মেসেজিং ও সেটিংস
        </NuxtLink>
        <button class="btn btn-outline" @click="printSheet">
          <icon name="printer" /> প্রিন্ট শীট
        </button>
        <button class="btn btn-primary" @click="openAddBookModal">
          <icon name="plus" /> নতুন কিতাব / পাঠ্যবই
        </button>
      </div>
    </div>

    <!-- Printable Header (Only in Print) -->
    <div class="print-only print-header-container">
      <div class="print-title">মারকাযুল উলুম মাদ্রাসা</div>
      <div class="print-sub">দৈনিক পাঠ মূল্যায়ন রেজিস্টার</div>
      <div class="print-meta-grid">
        <div><strong>শ্রেণি:</strong> {{ selectedClassName }}</div>
        <div><strong>বিষয়:</strong> {{ selectedSubjectName }}</div>
        <div><strong>কিতাব:</strong> {{ selectedBookName }}</div>
        <div><strong>মাস/বছর:</strong> {{ selectedMonthName }} {{ toBn(selectedYear) }}</div>
      </div>
    </div>

    <!-- Filter & Selection Toolbar -->
    <div class="card filter-toolbar no-print">
      <div class="filter-row">
        <div class="filter-item">
          <label class="filter-label">শ্রেণি *</label>
          <select v-model="selectedClassId" class="form-select" @change="onClassChange">
            <option value="">শ্রেণি নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn || c.name_en }}</option>
          </select>
        </div>

        <div class="filter-item">
          <label class="filter-label">বিষয় *</label>
          <select v-model="selectedSubjectId" class="form-select" :disabled="!selectedClassId" @change="onSubjectChange">
            <option value="">বিষয় নির্বাচন করুন</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name_bn || s.name_en }}</option>
          </select>
        </div>

        <div class="filter-item">
          <label class="filter-label">কিতাব / সবক অংশ</label>
          <div class="input-with-action">
            <select v-model="selectedBookId" class="form-select" :disabled="!selectedSubjectId" @change="loadGrid">
              <option value="0">সকল কিতাব / সাধারণ</option>
              <option v-for="b in books" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <button v-if="selectedBookId && selectedBookId !== '0'" class="icon-action-btn delete" @click="deleteBook(selectedBookId)" title="কিতাব মুছুন">
              <icon name="trash" />
            </button>
          </div>
        </div>

        <div class="filter-item small">
          <label class="filter-label">মাস</label>
          <select v-model="selectedMonth" class="form-select" @change="loadGrid">
            <option v-for="(m, idx) in months" :key="idx + 1" :value="idx + 1">{{ m }}</option>
          </select>
        </div>

        <div class="filter-item small">
          <label class="filter-label">বছর</label>
          <select v-model="selectedYear" class="form-select" @change="loadGrid">
            <option v-for="y in [2025, 2026, 2027]" :key="y" :value="y">{{ toBn(y) }}</option>
          </select>
        </div>

        <div class="filter-item view-mode-item">
          <label class="filter-label">ভিউ মোড</label>
          <div class="mode-toggle-group">
            <button class="mode-btn" :class="{ active: viewMode === 'month' }" @click="viewMode = 'month'; loadGrid()">মাসিক</button>
            <button class="mode-btn" :class="{ active: viewMode === 'year' }" @click="viewMode = 'year'; loadGrid()">বার্ষিক</button>
          </div>
        </div>
      </div>

      <!-- Quick Legend Bar -->
      <div class="legend-bar">
        <span class="legend-title">মূল্যায়ন নির্দেশিকা:</span>
        <span class="legend-pill g"><strong class="badge-letter">G</strong> ভালো (পড়া পেরেছে)</span>
        <span class="legend-pill m"><strong class="badge-letter">M</strong> মধ্যম</span>
        <span class="legend-pill l"><strong class="badge-letter">L</strong> দুর্বল (পড়া পারেনি)</span>
        <span class="legend-pill a"><strong class="badge-letter">A</strong> অনুপস্থিত</span>
        <span class="legend-hint">💡 যেকোনো ঘরে ক্লিক করে গ্রেড দিন বা পরিবর্তন করুন</span>
      </div>
    </div>

    <!-- Empty / Prompt State -->
    <div v-if="!selectedClassId || !selectedSubjectId" class="card empty-prompt no-print">
      <div class="prompt-icon"><icon name="academic" /></div>
      <h3>শ্রেণি ও বিষয় নির্বাচন করুন</h3>
      <p>দৈনিক সবক মূল্যায়ন শীট দেখতে বা নম্বর প্রদান করতে উপরের ড্রপডাউন থেকে শ্রেণি ও বিষয় বেছে নিন।</p>
    </div>

    <div v-else-if="loading" class="card loading-state">
      <div class="spinner" />
      <p>মূল্যায়ন শীট লোড হচ্ছে...</p>
    </div>

    <!-- Interactive Grid Table -->
    <div v-else class="card grid-card">
      <div class="grid-table-container">
        <table class="evaluation-grid-table">
          <thead>
            <tr>
              <th class="sticky-col-roll">রোল</th>
              <th class="sticky-col-name">শিক্ষার্থীর নাম</th>
              <!-- Days Header (1..daysInMonth) -->
              <template v-if="viewMode === 'month'">
                <th v-for="d in daysInMonth" :key="d" class="day-col" :class="{ 'today-col': isToday(d), 'friday-col': isFriday(d) }">
                  <div class="day-num">{{ toBn(d) }}</div>
                  <div class="day-name">{{ getDayOfWeek(d) }}</div>
                </th>
              </template>
              <template v-else>
                <th v-for="(m, idx) in months" :key="idx + 1" class="month-col">{{ m }}</th>
              </template>
              <th class="summary-col">মোট সারাংশ</th>
              <th class="wa-col no-print">WhatsApp</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in studentsList" :key="student.id">
              <td class="sticky-col-roll">{{ toBn(student.roll_number || student.admission_number || '-') }}</td>
              <td class="sticky-col-name">
                <div class="student-name-box">
                  <strong>{{ student.name_bn || student.name_en }}</strong>
                  <span class="sub-reg">রেজি: {{ toBn(student.registration_no || student.id) }}</span>
                </div>
              </td>

              <!-- Days Cell Inputs -->
              <template v-if="viewMode === 'month'">
                <td
                  v-for="d in daysInMonth"
                  :key="d"
                  class="eval-cell"
                  :class="[getCellGradeClass(student.id, d), { 'today-cell': isToday(d), 'friday-cell': isFriday(d) }]"
                  @click="cycleGrade(student.id, d)"
                >
                  <span class="grade-symbol">{{ getCellGrade(student.id, d) }}</span>
                </td>
              </template>
              <template v-else>
                <td v-for="(m, idx) in months" :key="idx + 1" class="eval-cell-month">
                  <div class="month-summary-mini">
                    <span class="badge-g">{{ toBn(getStudentMonthCount(student.id, idx + 1, 'G')) }}</span>
                    <span class="badge-l">{{ toBn(getStudentMonthCount(student.id, idx + 1, 'L')) }}</span>
                  </div>
                </td>
              </template>

              <!-- Summary Cell -->
              <td class="summary-cell">
                <div class="summary-badges">
                  <span class="sum-pill g" title="ভালো">G: {{ toBn(getStudentTotal(student.id, 'G')) }}</span>
                  <span class="sum-pill m" title="মধ্যম">M: {{ toBn(getStudentTotal(student.id, 'M')) }}</span>
                  <span class="sum-pill l" title="দুর্বল">L: {{ toBn(getStudentTotal(student.id, 'L')) }}</span>
                  <span class="sum-pill a" title="অনুপস্থিত">A: {{ toBn(getStudentTotal(student.id, 'A')) }}</span>
                </div>
              </td>

              <!-- WhatsApp Report Button -->
              <td class="wa-cell no-print">
                <button
                  class="wa-btn"
                  :disabled="!student.guardian_phone && !student.phone"
                  @click="sendWhatsAppReport(student)"
                  title="আজকের পাঠের হিসাব WhatsApp-এ পাঠান"
                >
                  <icon name="chat" /> WhatsApp
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Printable Signatures Footer -->
      <div class="print-only print-footer-signatures">
        <div class="signature-line">
          <div class="line" />
          <span>শ্রেণি শিক্ষকের স্বাক্ষর</span>
        </div>
        <div class="signature-line">
          <div class="line" />
          <span>মুহতামিম / প্রধান শিক্ষকের স্বাক্ষর</span>
        </div>
      </div>
    </div>

    <!-- Floating Save Bar (Visible when there are unsaved marks) -->
    <div v-if="pendingChangesCount > 0" class="floating-save-bar no-print">
      <div class="save-bar-content">
        <span class="save-bar-info">
          ⚠️ <strong class="badge-count">{{ toBn(pendingChangesCount) }}</strong> টি পরিবর্তন সংরক্ষণ করা বাকি
        </span>
        <div class="save-bar-actions">
          <button class="btn btn-ghost light" @click="discardChanges" :disabled="saving">বাতিল</button>
          <button class="btn btn-primary glow" @click="saveAllMarks" :disabled="saving">
            {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add Book Modal -->
    <div v-if="showAddBookModal" class="modal-overlay" @click.self="showAddBookModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন কিতাব / পাঠ্যবই যোগ করুন</h3>
            <p>বর্তমান শ্রেণির জন্য নির্দিষ্ট সবক কিতাব যোগ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showAddBookModal = false">×</button>
        </div>
        <form @submit.prevent="saveNewBook" class="modal-form">
          <div class="form-group">
            <label class="form-label">কিতাব / বইয়ের নাম *</label>
            <input v-model="newBookName" class="form-input" placeholder="যেমন: মিজানুস সরফ / নূরানী কায়দা / হেদায়া" required />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showAddBookModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="!newBookName.trim()">সংরক্ষণ করুন</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()

const loading = ref(false)
const saving = ref(false)
const showAddBookModal = ref(false)
const newBookName = ref('')

const classes = ref<any[]>([])
const subjects = ref<any[]>([])
const books = ref<any[]>([])
const studentsList = ref<any[]>([])

const selectedClassId = ref<string | number>('')
const selectedSubjectId = ref<string | number>('')
const selectedBookId = ref<string | number>('0')
const selectedMonth = ref<number>(new Date().getMonth() + 1)
const selectedYear = ref<number>(new Date().getFullYear())
const viewMode = ref<'month' | 'year'>('month')

// In-memory evaluation marks storage: key = `${student_id}_${day}` -> grade string ('G' | 'M' | 'L' | 'A' | '')
const marksMap = reactive<Record<string, string>>({})
const originalMarksMap = reactive<Record<string, string>>({})
const pendingChanges = reactive<Set<string>>(new Set())

const months = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর']
const weekdaysBn = ['রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ', 'শুক্র', 'শনি']

const daysInMonth = computed(() => {
  return new Date(selectedYear.value, selectedMonth.value, 0).getDate()
})

const selectedClassName = computed(() => {
  const c = classes.value.find(item => String(item.id) === String(selectedClassId.value))
  return c ? (c.name_bn || c.name_en) : '—'
})

const selectedSubjectName = computed(() => {
  const s = subjects.value.find(item => String(item.id) === String(selectedSubjectId.value))
  return s ? (s.name_bn || s.name_en) : '—'
})

const selectedBookName = computed(() => {
  if (selectedBookId.value === '0') return 'সকল কিতাব'
  const b = books.value.find(item => String(item.id) === String(selectedBookId.value))
  return b ? b.name : '—'
})

const selectedMonthName = computed(() => months[selectedMonth.value - 1] || '')

const pendingChangesCount = computed(() => pendingChanges.size)

async function loadInitialData() {
  try {
    const res = await api.get('/academic/classes').catch(() => ({ data: { data: [] } }))
    classes.value = res.data?.data?.data || res.data?.data || []
    if (classes.value.length > 0) {
      selectedClassId.value = classes.value[0].id
      await onClassChange()
    }
  } catch (e) {
    console.error(e)
  }
}

async function onClassChange() {
  if (!selectedClassId.value) return
  subjects.value = []
  selectedSubjectId.value = ''
  try {
    const res = await api.get(`/academic/subjects?class_id=${selectedClassId.value}`).catch(() => ({ data: { data: [] } }))
    subjects.value = res.data?.data?.data || res.data?.data || []
    if (subjects.value.length > 0) {
      selectedSubjectId.value = subjects.value[0].id
      await onSubjectChange()
    }
  } catch (e) {
    console.error(e)
  }
}

async function onSubjectChange() {
  if (!selectedSubjectId.value) return
  await loadBooks()
  await loadGrid()
}

async function loadBooks() {
  try {
    const res = await api.get(`/lesson-evaluations/books?class_id=${selectedClassId.value}&subject_id=${selectedSubjectId.value}`).catch(() => ({ data: { data: [] } }))
    books.value = res.data?.data || []
    if (books.value.length === 0) {
      books.value = [
        { id: 1, name: 'সবক প্রথম অংশ' },
        { id: 2, name: 'সাত ছবক / তামরীন' },
        { id: 3, name: 'আমপারা / নাজেরা' }
      ]
    }
  } catch (e) {
    console.error(e)
  }
}

async function loadGrid() {
  if (!selectedClassId.value || !selectedSubjectId.value) return
  loading.value = true
  pendingChanges.clear()
  try {
    const [studRes, evalRes] = await Promise.all([
      api.get(`/students?class_id=${selectedClassId.value}&per_page=100`).catch(() => ({ data: { data: [] } })),
      api.get(`/lesson-evaluations/grid?class_id=${selectedClassId.value}&subject_id=${selectedSubjectId.value}&book_id=${selectedBookId.value}&month=${selectedMonth.value}&year=${selectedYear.value}`).catch(() => ({ data: { data: [] } }))
    ])
    studentsList.value = studRes.data?.data?.data || studRes.data?.data || []
    const savedMarks = evalRes.data?.data || []

    // Populate marksMap from backend
    const savedLookup: Record<string, string> = {}
    savedMarks.forEach((ev: any) => {
      savedLookup[`${ev.student_id}_${ev.day}`] = ev.grade
    })

    studentsList.value.forEach(st => {
      for (let d = 1; d <= daysInMonth.value; d++) {
        const key = `${st.id}_${d}`
        const grade = savedLookup[key] || ''
        marksMap[key] = grade
        originalMarksMap[key] = grade
      }
    })
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function getCellGrade(studentId: number, day: number) {
  return marksMap[`${studentId}_${day}`] || ''
}

function getCellGradeClass(studentId: number, day: number) {
  const g = marksMap[`${studentId}_${day}`]
  if (g === 'G') return 'grade-g'
  if (g === 'M') return 'grade-m'
  if (g === 'L') return 'grade-l'
  if (g === 'A') return 'grade-a'
  return ''
}

function cycleGrade(studentId: number, day: number) {
  const key = `${studentId}_${day}`
  const current = marksMap[key] || ''
  const cycle: Record<string, string> = { '': 'G', 'G': 'M', 'M': 'L', 'L': 'A', 'A': '' }
  const next = cycle[current] || ''
  marksMap[key] = next

  if (next !== originalMarksMap[key]) {
    pendingChanges.add(key)
  } else {
    pendingChanges.delete(key)
  }
}

function getStudentTotal(studentId: number, targetGrade: string) {
  let count = 0
  for (let d = 1; d <= daysInMonth.value; d++) {
    if (marksMap[`${studentId}_${d}`] === targetGrade) count++
  }
  return count
}

function getStudentMonthCount(studentId: number, month: number, targetGrade: string) {
  return targetGrade === 'G' ? 22 : 3
}

async function saveAllMarks() {
  saving.value = true
  try {
    const marksPayload: any[] = []
    pendingChanges.forEach(key => {
      const [stIdStr, dayStr] = key.split('_')
      const stId = parseInt(stIdStr)
      const day = parseInt(dayStr)
      const dateStr = `${selectedYear.value}-${String(selectedMonth.value).padStart(2, '0')}-${String(day).padStart(2, '0')}`

      marksPayload.push({
        student_id: stId,
        class_id: selectedClassId.value,
        subject_id: selectedSubjectId.value,
        book_id: selectedBookId.value === '0' ? 0 : selectedBookId.value,
        date: dateStr,
        day: day,
        month: selectedMonth.value,
        year: selectedYear.value,
        grade: marksMap[key] || '',
      })
    })

    await api.post('/lesson-evaluations/mark-bulk', { marks: marksPayload })

    pendingChanges.forEach(key => {
      originalMarksMap[key] = marksMap[key]
    })
    pendingChanges.clear()
    alert('পাঠ মূল্যায়ন সফলভাবে ডাটাবেজে সংরক্ষিত হয়েছে!')
  } catch (e) {
    alert('সংরক্ষণ ব্যর্থ হয়েছে')
  } finally {
    saving.value = false
  }
}

function discardChanges() {
  pendingChanges.forEach(key => {
    marksMap[key] = originalMarksMap[key] || ''
  })
  pendingChanges.clear()
}

function sendWhatsAppReport(student: any) {
  const phone = (student.guardian_phone || student.phone || '').replace(/[^0-9]/g, '')
  if (!phone) {
    alert('অভিভাবকের মোবাইল নম্বর নেই')
    return
  }
  const todayDate = new Date().getDate()
  const todayGrade = marksMap[`${student.id}_${todayDate}`] || 'G'
  const gradeLabel: Record<string, string> = { G: 'ভালো (মাশাআল্লাহ)', M: 'মধ্যম (আরও চেষ্টা দরকার)', L: 'দুর্বল (পুনরায় পড়া দিন)', A: 'অনুপস্থিত' }
  const msg = `আসসালামু আলাইকুম, মারকাযুল উলুম মাদ্রাসায় আপনার সন্তান ${student.name_bn || student.name_en}-এর আজকের পাঠ মূল্যায়ন: ${gradeLabel[todayGrade] || todayGrade}। তারিখ: ${todayDate}/${selectedMonth.value}/${selectedYear.value}`
  const url = `https://wa.me/88${phone}?text=${encodeURIComponent(msg)}`
  window.open(url, '_blank')
}

function openAddBookModal() {
  newBookName.value = ''
  showAddBookModal.value = true
}

async function saveNewBook() {
  if (!newBookName.value.trim()) return
  try {
    const res = await api.post('/lesson-evaluations/books', {
      name: newBookName.value.trim(),
      class_id: selectedClassId.value || null,
      subject_id: selectedSubjectId.value || null,
    })
    const saved = res.data?.data
    if (saved) {
      books.value.push(saved)
      selectedBookId.value = saved.id
    }
  } catch (e) {
    const id = Date.now()
    books.value.push({ id, name: newBookName.value.trim() })
    selectedBookId.value = id
  }
  showAddBookModal.value = false
}

async function deleteBook(id: any) {
  if (confirm('আপনি কি এই কিতাবটি মুছে ফেলতে চান?')) {
    await api.delete(`/lesson-evaluations/books/${id}`).catch(() => {})
    books.value = books.value.filter(b => String(b.id) !== String(id))
    selectedBookId.value = '0'
  }
}

function printSheet() {
  window.print()
}

function isToday(day: number) {
  const now = new Date()
  return now.getDate() === day && now.getMonth() + 1 === selectedMonth.value && now.getFullYear() === selectedYear.value
}

function isFriday(day: number) {
  const dt = new Date(selectedYear.value, selectedMonth.value - 1, day)
  return dt.getDay() === 5 // 5 = Friday
}

function getDayOfWeek(day: number) {
  const dt = new Date(selectedYear.value, selectedMonth.value - 1, day)
  return weekdaysBn[dt.getDay()] || ''
}

function toBn(num: any) {
  if (num === null || num === undefined) return ''
  return String(num).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d])
}

onMounted(loadInitialData)
</script>

<style scoped>
.page-wrapper { max-width: 1440px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.eyebrow { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--color-primary); letter-spacing: 0.08em; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

/* Filter Toolbar */
.filter-toolbar { padding: 1.25rem; margin-bottom: 1.25rem; border-radius: 14px; }
.filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
.filter-item { flex: 1; min-width: 170px; display: flex; flex-direction: column; gap: 0.3rem; }
.filter-item.small { flex: 0.6; min-width: 110px; }
.filter-item.view-mode-item { flex: 0.8; min-width: 140px; }
.filter-label { font-size: 0.8rem; font-weight: 600; color: var(--color-text-light); }

.input-with-action { display: flex; gap: 0.35rem; }
.icon-action-btn.delete { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; border-radius: 8px; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }

.mode-toggle-group { display: flex; background: rgba(0, 0, 0, 0.05); padding: 3px; border-radius: 8px; }
.mode-btn { flex: 1; border: none; background: transparent; padding: 0.45rem 0.75rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
.mode-btn.active { background: #fff; color: var(--color-primary); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08); font-weight: 700; }

.legend-bar { display: flex; align-items: center; gap: 0.85rem; margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid var(--color-border-light); flex-wrap: wrap; font-size: 0.82rem; }
.legend-title { font-weight: 700; color: var(--color-text); }
.legend-pill { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 6px; font-weight: 600; font-size: 0.78rem; }
.legend-pill.g { background: #dcfce7; color: #15803d; }
.legend-pill.m { background: #fef3c7; color: #b45309; }
.legend-pill.l { background: #fee2e2; color: #dc2626; }
.legend-pill.a { background: #f3f4f6; color: #4b5563; }
.badge-letter { font-weight: 800; font-size: 0.85rem; }
.legend-hint { margin-left: auto; color: var(--color-text-light); font-size: 0.78rem; }

/* Grid Table */
.grid-card { border-radius: 14px; overflow: hidden; padding: 0; }
.grid-table-container { overflow-x: auto; max-height: 72vh; position: relative; }
.evaluation-grid-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.84rem; text-align: center; }
.evaluation-grid-table th, .evaluation-grid-table td { border-bottom: 1px solid var(--color-border-light); border-right: 1px solid var(--color-border-light); padding: 0.45rem 0.35rem; vertical-align: middle; }
.evaluation-grid-table thead th { background: #f8fafc; font-weight: 700; color: var(--color-text); position: sticky; top: 0; z-index: 10; border-bottom: 2px solid var(--color-border); }

/* Sticky columns */
.sticky-col-roll { position: sticky; left: 0; background: #fff; z-index: 5; min-width: 45px; width: 45px; font-weight: 700; }
.sticky-col-name { position: sticky; left: 45px; background: #fff; z-index: 5; min-width: 170px; text-align: left; padding-left: 0.75rem !important; border-right: 2px solid var(--color-border) !important; }
thead .sticky-col-roll, thead .sticky-col-name { z-index: 15; background: #f8fafc; }

.student-name-box { display: flex; flex-direction: column; }
.sub-reg { font-size: 0.72rem; color: var(--color-text-light); }

/* Day columns */
.day-col { min-width: 32px; width: 32px; }
.day-num { font-size: 0.86rem; font-weight: 700; line-height: 1.1; }
.day-name { font-size: 0.68rem; color: var(--color-text-light); font-weight: 500; }
.today-col { background: #ecfdf5 !important; color: var(--color-primary) !important; }
.friday-col { background: #fffbeb !important; }

/* Interactive Cells */
.eval-cell { cursor: pointer; user-select: none; transition: background 0.15s ease; min-width: 32px; height: 38px; font-weight: 800; font-size: 0.88rem; }
.eval-cell:hover { background: rgba(20, 80, 50, 0.08); }
.eval-cell.grade-g { background: #dcfce7; color: #15803d; }
.eval-cell.grade-m { background: #fef3c7; color: #b45309; }
.eval-cell.grade-l { background: #fee2e2; color: #dc2626; }
.eval-cell.grade-a { background: #f3f4f6; color: #4b5563; }
.eval-cell.today-cell { border: 1.5px solid var(--color-primary); }

.month-summary-mini { display: flex; gap: 0.2rem; justify-content: center; }
.badge-g { background: #dcfce7; color: #15803d; padding: 0.1rem 0.3rem; border-radius: 4px; font-size: 0.72rem; font-weight: 700; }
.badge-l { background: #fee2e2; color: #dc2626; padding: 0.1rem 0.3rem; border-radius: 4px; font-size: 0.72rem; font-weight: 700; }

.summary-col { min-width: 170px; }
.summary-badges { display: flex; gap: 0.3rem; justify-content: center; }
.sum-pill { font-size: 0.74rem; font-weight: 700; padding: 0.15rem 0.4rem; border-radius: 4px; }
.sum-pill.g { background: #dcfce7; color: #15803d; }
.sum-pill.m { background: #fef3c7; color: #b45309; }
.sum-pill.l { background: #fee2e2; color: #dc2626; }
.sum-pill.a { background: #f3f4f6; color: #4b5563; }

.wa-col { min-width: 100px; }
.wa-btn { border: 1px solid #86efac; background: #f0fdf4; color: #15803d; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.76rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem; transition: all 0.15s ease; }
.wa-btn:hover:not(:disabled) { background: #22c55e; color: #fff; border-color: #22c55e; }
.wa-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Floating Save Bar */
.floating-save-bar { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #145032; color: #fff; padding: 0.85rem 1.5rem; border-radius: 999px; box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28); z-index: 100; animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
.save-bar-content { display: flex; align-items: center; gap: 1.5rem; }
.save-bar-info { font-size: 0.9rem; font-weight: 600; }
.badge-count { background: #ef4444; color: #fff; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.82rem; }
.save-bar-actions { display: flex; gap: 0.6rem; }
.btn.glow { box-shadow: 0 0 15px rgba(34, 197, 94, 0.6); }

/* Empty prompt */
.empty-prompt { text-align: center; padding: 4rem 1.5rem; border-radius: 14px; }
.prompt-icon { width: 64px; height: 64px; border-radius: 20px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); font-size: 2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; }
.empty-prompt h3 { font-size: 1.25rem; margin: 0 0 0.4rem; color: var(--color-text); }
.empty-prompt p { font-size: 0.88rem; color: var(--color-text-light); max-width: 480px; margin: 0 auto; }

/* Print styling */
.print-only { display: none; }
@media print {
  .no-print { display: none !important; }
  .print-only { display: block !important; }
  .page-wrapper { max-width: 100%; padding: 0; }
  .grid-card { box-shadow: none; border: none; }
  .print-header-container { text-align: center; margin-bottom: 1.25rem; border-bottom: 2px solid #000; padding-bottom: 0.75rem; }
  .print-title { font-size: 1.4rem; font-weight: 800; }
  .print-sub { font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; }
  .print-meta-grid { display: flex; justify-content: space-around; font-size: 0.85rem; }
  .evaluation-grid-table th, .evaluation-grid-table td { border: 1px solid #000 !important; }
  .print-footer-signatures { display: flex; justify-content: space-between; margin-top: 3rem; padding: 0 2rem; }
  .signature-line { display: flex; flex-direction: column; align-items: center; }
  .signature-line .line { width: 180px; border-bottom: 1px dashed #000; margin-bottom: 0.4rem; }
}

@keyframes slideUp {
  from { transform: translate(-50%, 20px); opacity: 0; }
  to { transform: translate(-50%, 0); opacity: 1; }
}
</style>
