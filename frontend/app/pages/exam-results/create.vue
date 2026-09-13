<template>
  <div class="create-result-page">
    <div class="page-header-row">
      <div class="header-content">
        <NuxtLink to="/results" class="back-link">
          <icon name="arrowLeft" :size="16" /> ফলাফলে ফিরে যান
        </NuxtLink>
        <h1>নতুন পরীক্ষার ফলাফল তৈরি</h1>
        <p>শিক্ষার্থীর সামগ্রিক পরীক্ষার ফলাফল ও জিপিএ নির্ধারণ করুন</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/marks/create" class="btn btn-outline btn-sm">
          <icon name="plus" :size="15" /> বিষয়ভিত্তিক মার্ক এন্ট্রি
        </NuxtLink>
      </div>
    </div>

    <div v-if="successMsg" class="alert alert-success">
      <icon name="check" :size="18" />
      <span>{{ successMsg }}</span>
    </div>

    <div v-if="errorMsg" class="alert alert-error">
      <icon name="alertTriangle" :size="18" />
      <span>{{ errorMsg }}</span>
    </div>

    <div class="form-layout">
      <form class="main-form-card card" @submit.prevent="handleSubmit">
        <div class="card-header">
          <h3>ফলাফল তথ্য</h3>
          <span class="badge badge-emerald">নতুন এন্ট্রি</span>
        </div>

        <div class="form-grid">
          <!-- Exam Selection -->
          <div class="form-group required">
            <label>পরীক্ষা নির্বাচন করুন</label>
            <select v-model="form.exam_id" class="form-control" required @change="onExamChange">
              <option value="" disabled>পরীক্ষা বেছে নিন...</option>
              <option v-for="e in exams" :key="e.id" :value="e.id">
                {{ e.name_bn || e.name_en }} ({{ e.exam_type || 'সাধারণ' }})
              </option>
            </select>
            <small v-if="loadingExams" class="hint">পরীক্ষার তালিকা লোড হচ্ছে...</small>
          </div>

          <!-- Student Selection -->
          <div class="form-group required">
            <label>শিক্ষার্থী নির্বাচন করুন</label>
            <select v-model="form.student_id" class="form-control" required>
              <option value="" disabled>শিক্ষার্থী বেছে নিন...</option>
              <option v-for="s in students" :key="s.id" :value="s.id">
                {{ s.name_bn || s.name_en }} {{ s.student_id ? `(${s.student_id})` : `(রোল: ${s.roll_no || s.id})` }}
              </option>
            </select>
            <small v-if="loadingStudents" class="hint">শিক্ষার্থীর তালিকা লোড হচ্ছে...</small>
          </div>

          <!-- Total Marks -->
          <div class="form-group">
            <label>মোট পূর্ণমান (Total Marks)</label>
            <input
              v-model.number="form.total_marks"
              type="number"
              min="1"
              step="any"
              class="form-control"
              placeholder="উদা: ৫০০ বা ১০০"
              @input="autoCalculate"
            />
          </div>

          <!-- Marks Obtained -->
          <div class="form-group">
            <label>প্রাপ্ত নম্বর (Marks Obtained)</label>
            <input
              v-model.number="form.marks_obtained"
              type="number"
              min="0"
              :max="form.total_marks || undefined"
              step="any"
              class="form-control"
              placeholder="উদা: ৪২৫"
              @input="autoCalculate"
            />
          </div>

          <!-- Percentage -->
          <div class="form-group">
            <label>শতকরা হার (%)</label>
            <div class="input-with-action">
              <input
                v-model.number="form.percentage"
                type="number"
                step="0.01"
                min="0"
                max="100"
                class="form-control"
                placeholder="৮৫.০০"
                @input="deriveGradeFromPercentage"
              />
              <button type="button" class="btn btn-ghost btn-xs" @click="autoCalculate">হিসাব</button>
            </div>
          </div>

          <!-- GPA -->
          <div class="form-group">
            <label>জিপিএ (GPA 0.00 - 5.00)</label>
            <input
              v-model.number="form.gpa"
              type="number"
              step="0.01"
              min="0"
              max="5"
              class="form-control"
              placeholder="৫.০০"
            />
          </div>

          <!-- Grade -->
          <div class="form-group">
            <label>গ্রেড / বিভাগ</label>
            <select v-model="form.grade" class="form-control">
              <option value="">নির্বাচন করুন</option>
              <option value="A+">A+ (মুমতাজ - ৮০-১০০%)</option>
              <option value="A">A (জায়্যিদ জিদ্দান - ৭০-৭৯%)</option>
              <option value="A-">A- (জায়্যিদ - ৬০-৬৯%)</option>
              <option value="B">B (মাকবুল - ৫০-৫৯%)</option>
              <option value="C">C (উত্তীর্ণ - ৪০-৪৯%)</option>
              <option value="F">F (অনুত্তীর্ণ - ০-৩৯%)</option>
            </select>
          </div>

          <!-- Class Position -->
          <div class="form-group">
            <label>মেধাক্রম / স্থান (Position)</label>
            <input
              v-model.number="form.class_position"
              type="number"
              min="1"
              class="form-control"
              placeholder="উদা: ১, ২, ৩..."
            />
          </div>
        </div>

        <!-- Comments -->
        <div class="form-group mt-4">
          <label>শিক্ষক / মুহাদ্দিসের মন্তব্য</label>
          <textarea
            v-model="form.comments"
            rows="3"
            class="form-control"
            placeholder="শিক্ষার্থীর মেধা, চরিত্র ও ফলাফল সংক্রান্ত মন্তব্য লিখুন..."
          ></textarea>
        </div>

        <!-- Publication Status -->
        <div class="publication-card mt-4">
          <div class="checkbox-label">
            <input v-model="form.is_published" type="checkbox" id="publish-check" />
            <label for="publish-check">
              <strong>অবিলম্বে ফলাফল প্রকাশ করুন</strong>
              <small class="text-muted block">চিহ্নিত করলে শিক্ষার্থী ও অভিভাবকগণ সরাসরি ফলাফল দেখতে পাবেন।</small>
            </label>
          </div>
        </div>

        <div class="form-actions">
          <NuxtLink to="/results" class="btn btn-outline">বাতিল</NuxtLink>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            <span v-if="saving" class="spinner-sm"></span>
            <icon v-else name="save" :size="16" />
            ফলাফল সংরক্ষণ করুন
          </button>
        </div>
      </form>

      <!-- Sidebar Instructions / Helper -->
      <div class="side-instructions">
        <div class="helper-card card">
          <h4><icon name="document" :size="18" /> ফলাফল নির্দেশিকা</h4>
          <ul class="helper-list">
            <li>
              <strong>বিষয়ভিত্তিক মার্কস:</strong> আপনি যদি প্রতি কিতাব বা বিষয়ের নম্বর আলাদাভাবে ইনপুট দিতে চান, তবে 
              <NuxtLink to="/marks/create" class="text-primary font-medium underline">বিষয়ভিত্তিক মার্ক এন্ট্রি ফর্ম</NuxtLink> ব্যবহার করুন।
            </li>
            <li>
              <strong>স্বয়ংক্রিয় গ্রেডিং:</strong> মোট নম্বর ও প্রাপ্ত নম্বর লিখলে শতকরা হার, গ্রেড ও জিপিএ নিজে থেকেই হিসাব হবে।
            </li>
            <li>
              <strong>মার্কশিট প্রিন্ট:</strong> ফলাফল সংরক্ষণ করার পর বিস্তারিত পাতা থেকে মাদরাসার প্রাতিষ্ঠানিক সনদ ও নম্বরপত্র প্রিন্ট করা যাবে।
            </li>
          </ul>
        </div>

        <div class="scale-card card">
          <h4>গ্রেডিং স্কেল মানদণ্ড</h4>
          <table class="scale-table">
            <thead>
              <tr>
                <th>শতকরা</th>
                <th>গ্রেড</th>
                <th>মান (GPA)</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>৮০% - ১০০%</td><td><span class="badge badge-success">A+</span></td><td>৫.০০</td></tr>
              <tr><td>৭০% - ৭৯%</td><td><span class="badge badge-info">A</span></td><td>৪.০০</td></tr>
              <tr><td>৬০% - ৬৯%</td><td><span class="badge badge-primary">A-</span></td><td>৩.৫০</td></tr>
              <tr><td>৫০% - ৫৯%</td><td><span class="badge badge-warning">B</span></td><td>৩.০০</td></tr>
              <tr><td>৪০% - ৪৯%</td><td><span class="badge badge-secondary">C</span></td><td>২.০০</td></tr>
              <tr><td>০% - ৩৯%</td><td><span class="badge badge-danger">F</span></td><td>০.০০</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const route = useRoute()
const router = useRouter()
const api = useApi()

const exams = ref([])
const students = ref([])
const loadingExams = ref(false)
const loadingStudents = ref(false)
const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const form = reactive({
  exam_id: route.query.exam ? Number(route.query.exam) : '',
  student_id: route.query.student ? Number(route.query.student) : '',
  total_marks: 100,
  marks_obtained: null,
  percentage: null,
  gpa: null,
  grade: '',
  class_position: null,
  comments: '',
  is_published: true
})

async function fetchExams() {
  loadingExams.value = true
  try {
    const res = await api.get('/api/v1/exams', { params: { per_page: 100 } })
    const data = res?.data || res
    exams.value = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Failed to load exams', err)
  } finally {
    loadingExams.value = false
  }
}

async function fetchStudents() {
  loadingStudents.value = true
  try {
    const res = await api.get('/api/v1/students', { params: { per_page: 150 } })
    const data = res?.data || res
    students.value = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Failed to load students', err)
  } finally {
    loadingStudents.value = false
  }
}

function onExamChange() {
  // If exam has total marks or specific class, can set default
}

function autoCalculate() {
  if (form.total_marks && form.marks_obtained !== null && form.marks_obtained !== undefined) {
    const pct = (Number(form.marks_obtained) / Number(form.total_marks)) * 100
    form.percentage = Number(pct.toFixed(2))
    deriveGradeFromPercentage()
  }
}

function deriveGradeFromPercentage() {
  const p = Number(form.percentage)
  if (isNaN(p)) return

  if (p >= 80) {
    form.grade = 'A+'
    form.gpa = 5.00
  } else if (p >= 70) {
    form.grade = 'A'
    form.gpa = 4.00
  } else if (p >= 60) {
    form.grade = 'A-'
    form.gpa = 3.50
  } else if (p >= 50) {
    form.grade = 'B'
    form.gpa = 3.00
  } else if (p >= 40) {
    form.grade = 'C'
    form.gpa = 2.00
  } else {
    form.grade = 'F'
    form.gpa = 0.00
  }
}

async function handleSubmit() {
  if (!form.exam_id || !form.student_id) {
    errorMsg.value = 'অনুগ্রহ করে পরীক্ষা এবং শিক্ষার্থী নির্বাচন করুন।'
    return
  }

  saving.value = true
  errorMsg.value = ''
  successMsg.value = ''

  try {
    const payload = {
      exam_id: Number(form.exam_id),
      student_id: Number(form.student_id),
      total_marks: form.total_marks ? Number(form.total_marks) : null,
      marks_obtained: form.marks_obtained !== null && form.marks_obtained !== '' ? Number(form.marks_obtained) : null,
      percentage: form.percentage !== null && form.percentage !== '' ? Number(form.percentage) : null,
      gpa: form.gpa !== null && form.gpa !== '' ? Number(form.gpa) : null,
      grade: form.grade || null,
      class_position: form.class_position ? Number(form.class_position) : null,
      comments: form.comments || null,
      is_published: !!form.is_published
    }

    const res = await api.post('/api/v1/exam-results', payload)
    const newId = res?.data?.id || res?.id

    successMsg.value = 'ফলাফল সফলভাবে তৈরি হয়েছে!'
    setTimeout(() => {
      if (newId) {
        router.push(`/exam-results/${newId}`)
      } else {
        router.push('/results')
      }
    }, 1200)
  } catch (err) {
    console.error('Error creating exam result', err)
    errorMsg.value = err?.response?.data?.message || 'ফলাফল সংরক্ষণে ত্রুটি হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchExams()
  fetchStudents()
})
</script>

<style scoped>
.create-result-page {
  padding: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: var(--color-ink-muted, #64748b);
  margin-bottom: 0.4rem;
  text-decoration: none;
  font-weight: 500;
}
.back-link:hover {
  color: var(--color-primary-600, #0d9488);
}

.page-header-row h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-ink-headings, #0f172a);
  margin: 0 0 0.25rem 0;
}

.page-header-row p {
  font-size: 0.9rem;
  color: var(--color-ink-muted, #64748b);
  margin: 0;
}

.form-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1.5rem;
}

@media (max-width: 900px) {
  .form-layout {
    grid-template-columns: 1fr;
  }
}

.card {
  background: var(--color-surface, #ffffff);
  border: 1px solid var(--color-border-subtle, #e2e8f0);
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--color-border-subtle, #e2e8f0);
  margin-bottom: 1.25rem;
}

.card-header h3 {
  font-size: 1.15rem;
  font-weight: 600;
  margin: 0;
  color: var(--color-ink-headings, #0f172a);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem 1.25rem;
}

@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-group.required label::after {
  content: ' *';
  color: #ef4444;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-ink-body, #334155);
}

.form-control {
  padding: 0.55rem 0.85rem;
  border: 1px solid var(--color-border-subtle, #cbd5e1);
  border-radius: 0.5rem;
  font-size: 0.9rem;
  outline: none;
  background: var(--color-surface, #ffffff);
  color: var(--color-ink-body, #1e293b);
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-control:focus {
  border-color: var(--color-primary-500, #14b8a6);
  box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15);
}

.input-with-action {
  display: flex;
  gap: 0.5rem;
}

.input-with-action input {
  flex: 1;
}

.hint {
  font-size: 0.75rem;
  color: var(--color-ink-muted, #94a3b8);
}

.publication-card {
  padding: 0.85rem 1rem;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 0.5rem;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  cursor: pointer;
}

.checkbox-label input {
  margin-top: 0.2rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--color-border-subtle, #e2e8f0);
}

.side-instructions {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.helper-card h4, .scale-card h4 {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0 0 0.85rem 0;
  color: var(--color-ink-headings, #0f172a);
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.helper-list {
  padding-left: 1.2rem;
  margin: 0;
  font-size: 0.82rem;
  color: var(--color-ink-muted, #475569);
  line-height: 1.6;
}

.helper-list li {
  margin-bottom: 0.6rem;
}

.scale-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
}

.scale-table th {
  text-align: left;
  padding: 0.45rem 0.5rem;
  background: #f1f5f9;
  color: #475569;
  font-weight: 600;
  border-bottom: 1px solid #e2e8f0;
}

.scale-table td {
  padding: 0.45rem 0.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-emerald {
  background: #d1fae5;
  color: #065f46;
}

.badge-success { background: #dcfce7; color: #166534; }
.badge-info { background: #e0f2fe; color: #075985; }
.badge-primary { background: #e0e7ff; color: #3730a3; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-secondary { background: #f1f5f9; color: #475569; }
.badge-danger { background: #fee2e2; color: #991b1b; }

.alert {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1.25rem;
  font-size: 0.9rem;
}

.alert-success {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.alert-error {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.spinner-sm {
  width: 14px;
  height: 14px;
  border: 2px solid #ffffff;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
