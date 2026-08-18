<template>
  <div class="exam-edit">
    <div class="page-header">
      <NuxtLink to="/exams" class="btn btn-outline btn-sm">
        <icon :name="mdiArrowLeft" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink to="/exams" class="btn btn-secondary btn-sm">
          <icon :name="mdiClose" /> বাতিল
        </NuxtLink>
        <button class="btn btn-primary btn-sm" :disabled="saving || !isFormValid" @click="saveExam">
          <icon v-if="saving" :name="mdiLoad" /><icon v-else :name="mdiContentSave" />
          {{ editing ? 'আপডেট' : 'তৈরি' }}
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="saveExam">
          <div class="form-section">
            <h4 class="section-title">মৌলিক তথ্য</h4>
            <div class="form-group">
              <label class="form-label">পরীক্ষার নাম (বাংলা) *</label>
              <input v-model="form.title_bn" type="text" class="form-control" placeholder="যেমন: সপ্তম শ্রেণির বার্ষিক পরীক্ষা ২০২৪" required />
              <p v-if="errors.title_bn" class="error-message">{{ errors.title_bn[0] }}</p>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">পরীক্ষার ধরন</label>
                <select v-model="form.type" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                  <option value="নিয়মিত">নিয়মিত</option>
                  <option value="মাসিক">মাসিক</option>
                  <option value="ত্রৈমাসিক">ত্রৈমাসিক</option>
                  <option value="বার্ষিক">বার্ষিক</option>
                  <option value="মডেল">মডেল</option>
                  <option value="অন্যান্য">অন্যান্য</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">বিষয়</label>
                <select v-model="form.subject_id" class="form-control">
                  <option value="">সব বিষয়</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">শ্রেণি ও সময় নির্ধারণ</h4>
            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">শ্রেণি</label>
                <select v-model="form.class_id" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">অংশ</label>
                <select v-model="form.section_id" class="form-control">
                  <option value="">সব অংশ</option>
                  <option value="A">ক শ্রেণি</option>
                  <option value="B">খ শ্রেণি</option>
                  <option value="C">গ শ্রেণি</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">শিক্ষার্থী</label>
                <select v-model="form.limit_students" class="form-control">
                  <option :value="0">সব শিক্ষার্থী</option>
                  <option :value="10">১০ জন</option>
                  <option :value="20">২০ জন</option>
                  <option :value="50">৫০ জন</option>
                </select>
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">শুরুর তারিখ</label>
                <input v-model="form.start_date" type="date" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">শেষ তারিখ</label>
                <input v-model="form.end_date" type="date" class="form-control" />
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">পরীক্ষার বিবরণ</h4>
            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">মোট নম্বর</label>
                <input v-model.number="form.total_marks" type="number" class="form-control" min="1" placeholder="১০০" />
              </div>
              <div class="form-group">
                <label class="form-label">পাস নম্বর</label>
                <input v-model.number="form.passing_marks" type="number" class="form-control" min="0" placeholder="৩৩" />
              </div>
              <div class="form-group">
                <label class="form-label">সময় (মিনিট)</label>
                <input v-model.number="form.duration_minutes" type="number" class="form-control" min="1" placeholder="৬০" />
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group form-check">
                <label class="form-check-label">
                  <input type="checkbox" v-model="form.attendance_required" class="form-check-input" /> হাজিরা প্রয়োজন
                </label>
              </div>
              <div class="form-group form-check">
                <label class="form-check-label">
                  <input type="checkbox" v-model="form.is_active" class="form-check-input" /> সক্রিয় (ডিফল্ট: হ্যাঁ)
                </label>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving || !isFormValid">
              <icon v-if="saving" :name="mdiLoad" /><icon v-else :name="mdiContentSave" />
              {{ editing ? 'আপডেট' : 'তৈরি' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const editing = computed(() => route.params.id !== 'create')
const saving = ref(false)
const errors = ref<any>({})
const classOptions = ref<any[]>([])
const subjectOptions = ref<any[]>([])

const form = reactive({
  title_bn: '',
  title_en: '',
  type: '',
  session_id: '',
  class_id: '',
  section_id: '',
  subject_id: '',
  start_date: '',
  end_date: '',
  total_marks: null as number | null,
  passing_marks: null as number | null,
  duration_minutes: null as number | null,
  attendance_required: false,
  is_active: true,
  limit_students: 0,
})

const isFormValid = computed(() => form.title_bn.trim().length >= 3)

async function loadOptions() {
  try {
    const [classesRes] = await Promise.all([api.get('/students?per_page=1000')])
    const classes = new Map()
    (classesRes.data.data || []).forEach((s: any) => {
      const key = s.class?.id || s.class_id
      const name = s.class?.name_bn || s.class_name || 'Unknown'
      if (key && !classes.has(key)) classes.set(key, { id: key, name_bn: name })
    })
    classOptions.value = Array.from(classes.values()).sort((a, b) => a.name_bn.localeCompare(b.name_bn))
    subjectOptions.value = [
      { id: 1, name_bn: 'ইসলামি ইতিহাস' }, { id: 2, name_bn: 'ইসলামি বিজ্ঞান ও প্রযুক্তি' },
      { id: 3, name_bn: 'মুফস্সির তফসীর' }, { id: 4, name_bn: 'হাদিস শিক্ষা' },
      { id: 5, name_bn: 'ফিকহ' }, { id: 6, name_bn: 'অরবি ব্যাকরণ' },
      { id: 7, name_bn: 'কিতাব আল-খোনেজ' }, { id: 8, name_bn: 'সারফ' },
    ]
  } catch (error) { console.error('Failed to load options:', error) }
}

async function loadExam() {
  if (!editing.value) return
  try {
    const res = await api.get(`/exams/${route.params.id}`)
    const e = res.data.data
    form.title_bn = e.title_bn || ''
    form.title_en = e.title_en || ''
    form.type = e.type || ''
    form.session_id = String(e.session_id || '')
    form.class_id = String(e.class_id || e.class?.id || '')
    form.section_id = e.section_id || e.section?.name_bn?.replace('শ্রেণি', '') || ''
    form.subject_id = String(e.subject_id || e.subject?.id || '')
    form.start_date = e.start_date ? e.start_date.split('T')[0] : ''
    form.end_date = e.end_date ? e.end_date.split('T')[0] : ''
    form.total_marks = e.total_marks ?? null
    form.passing_marks = e.passing_marks ?? null
    form.duration_minutes = e.duration_minutes ?? null
    form.attendance_required = e.attendance_required ?? false
    form.is_active = e.is_active ?? true
  } catch (error) { console.error('Failed to load exam:', error); navigateTo('/exams') }
}

async function saveExam() {
  saving.value = true; errors.value = {}
  try {
    if (editing.value) await api.put(`/exams/${route.params.id}`, form)
    else await api.post('/exams', form)
    navigateTo('/exams')
  } catch (error: any) {
    if (error.response?.data?.errors) errors.value = error.response.data.errors
    else alert('সংরক্ষণে ত্রুটি: ' + (error.message || 'অজানা ত্রুটি'))
  } finally { saving.value = false }
}

onMounted(async () => { await loadOptions(); if (editing.value) await loadExam() })
</script>

<style scoped>
.exam-edit { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.form-section { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border-light); }
.form-section:last-of-type { border-bottom: none; }
.section-title { font-size: 1rem; font-weight: 600; color: var(--color-text); margin: 0 0 1rem 0; }
.form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.form-row-2 { grid-template-columns: repeat(2, 1fr); }
.form-row-3 { grid-template-columns: repeat(3, 1fr); }
.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-label { font-size: 0.875rem; font-weight: 500; color: var(--color-text); }
.form-control { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9375rem; color: var(--color-text); background: var(--color-bg); }
.form-control:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary-light); }
form-check { flex-direction: row; align-items: center; gap: 0.5rem; }
form-check-label { font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; }
form-check-input { width: 18px; height: 18px; }
.form-actions { padding-top: 1rem; }
.error-message { font-size: 0.8125rem; color: var(--color-error); margin: 0; }
</style>
