<template>
  <div class="student-edit-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink :to="`/students/${studentId}`" class="btn btn-outline btn-sm">
          <icon name="arrow-left" /> প্রোফাইলে ফিরে যান
        </NuxtLink>
        <div class="title-group">
          <h1>শিক্ষার্থীর তথ্য সম্পাদনা</h1>
          <p class="text-muted" v-if="studentName">
            <strong>{{ studentName }}</strong> — আইডি: {{ toBn(studentId) }}
          </p>
        </div>
      </div>
      <div class="header-actions">
        <NuxtLink :to="`/students/${studentId}`" class="btn btn-ghost btn-sm">
          <icon name="close" /> বাতিল
        </NuxtLink>
        <button class="btn btn-primary btn-sm" :disabled="saving || !isFormValid" @click="saveStudent">
          <icon v-if="saving" name="loader" class="animate-spin" />
          <icon v-else name="save" />
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
        </button>
      </div>
    </div>

    <!-- Alert Notifications -->
    <div v-if="error" class="alert alert-error animate-fade-in">
      <icon name="alert-circle" />
      <span>{{ error }}</span>
    </div>
    <div v-if="success" class="alert alert-success animate-fade-in">
      <icon name="check-circle" />
      <span>{{ success }}</span>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="card loading-card">
      <div class="spinner" />
      <p>শিক্ষার্থীর তথ্য লোড হচ্ছে...</p>
    </div>

    <!-- Form Container -->
    <div v-else class="card form-card">
      <div class="card-body">
        <form @submit.prevent="saveStudent">
          <!-- Personal Information -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon"><icon name="account" /></div>
              <div>
                <h4 class="section-title">ব্যক্তিগত ও পরিচিতি তথ্য</h4>
                <p class="section-desc">শিক্ষার্থীর মৌলিক তথ্যাবলী প্রদান করুন</p>
              </div>
            </div>

            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">নাম (বাংলায়) <span class="required-star">*</span></label>
                <input
                  v-model="form.name_bn"
                  type="text"
                  class="form-control"
                  placeholder="যেমন: মুহাম্মদ আবদুল্লাহ আল নোমান"
                  required
                />
                <p v-if="errors.name_bn" class="error-message">{{ errors.name_bn[0] }}</p>
              </div>

              <div class="form-group">
                <label class="form-label">নাম (ইংরেজিতে)</label>
                <input
                  v-model="form.name_en"
                  type="text"
                  class="form-control"
                  placeholder="e.g. Muhammad Abdullah Al Noman"
                />
              </div>
            </div>

            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">ভর্তি নম্বর (Admission No)</label>
                <input
                  v-model="form.admission_number"
                  type="text"
                  class="form-control"
                  placeholder="যেমন: ADM-2026-001"
                />
              </div>

              <div class="form-group">
                <label class="form-label">রোল নম্বর</label>
                <input
                  v-model="form.roll_number"
                  type="text"
                  class="form-control"
                  placeholder="যেমন: ০৫"
                />
              </div>

              <div class="form-group">
                <label class="form-label">মোবাইল ফোন নম্বর</label>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="form-control"
                  placeholder="017XXXXXXXX"
                />
              </div>
            </div>

            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">জন্ম তারিখ</label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="form-control"
                />
              </div>

              <div class="form-group">
                <label class="form-label">লিঙ্গ</label>
                <select v-model="form.gender" class="form-control form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option value="ছেলে">ছেলে</option>
                  <option value="মেয়ে">মেয়ে</option>
                  <option value="অন্যান্য">অন্যান্য</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">রক্তের গ্রুপ</label>
                <select v-model="form.blood_group" class="form-control form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                </select>
              </div>
            </div>

            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">ইমেইল ঠিকানা (ঐচ্ছিক)</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="form-control"
                  placeholder="student@example.com"
                />
              </div>

              <div class="form-group">
                <label class="form-label">জাতীয়তা</label>
                <input
                  v-model="form.nationality"
                  type="text"
                  class="form-control"
                  placeholder="বাংলাদেশী"
                />
              </div>
            </div>
          </div>

          <!-- Academic & Class Assignment -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon"><icon name="academic" /></div>
              <div>
                <h4 class="section-title">জামাত / শ্রেণি ও ভর্তি তথ্য</h4>
                <p class="section-desc">বর্তমান শিক্ষাবর্ষ ও জামাতের বিবরণ</p>
              </div>
            </div>

            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">শ্রেণি / জামাত <span class="required-star">*</span></label>
                <select v-model="form.class_id" class="form-control form-select" @change="onClassChange">
                  <option value="">শ্রেণি নির্বাচন করুন</option>
                  <option v-for="cls in classOptions" :key="cls.id" :value="cls.id">
                    {{ cls.name_bn || cls.name }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">শাখা / সেকশন</label>
                <select v-model="form.section_id" class="form-control form-select">
                  <option value="">শাখা নেই / সাধারণ</option>
                  <option v-for="sec in sectionOptions" :key="sec.id" :value="sec.id">
                    {{ sec.name_bn || sec.name }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">ছাত্রাবস্থা (Status)</label>
                <select v-model="form.is_active" class="form-control form-select">
                  <option :value="true">নিয়মিত ও সক্রিয় (Active)</option>
                  <option :value="false">নিষ্ক্রিয় / স্থগিত (Inactive)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Parents & Guardians Information -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon"><icon name="users" /></div>
              <div>
                <h4 class="section-title">পিতা-মাতা ও অভিভাবকের তথ্য</h4>
                <p class="section-desc">অভিভাবকের নাম ও জরুরি যোগাযোগের তথ্য</p>
              </div>
            </div>

            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">পিতার নাম</label>
                <input
                  v-model="form.father_name"
                  type="text"
                  class="form-control"
                  placeholder="পিতার পূর্ণ নাম"
                />
              </div>

              <div class="form-group">
                <label class="form-label">পিতার ফোন নম্বর</label>
                <input
                  v-model="form.father_phone"
                  type="tel"
                  class="form-control"
                  placeholder="017XXXXXXXX"
                />
              </div>
            </div>

            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">মাতার নাম</label>
                <input
                  v-model="form.mother_name"
                  type="text"
                  class="form-control"
                  placeholder="মাতার পূর্ণ নাম"
                />
              </div>

              <div class="form-group">
                <label class="form-label">মাতার ফোন নম্বর</label>
                <input
                  v-model="form.mother_phone"
                  type="tel"
                  class="form-control"
                  placeholder="017XXXXXXXX"
                />
              </div>
            </div>

            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">স্থানীয় অভিভাবকের নাম</label>
                <input
                  v-model="form.guardian_name"
                  type="text"
                  class="form-control"
                  placeholder="অভিভাবকের নাম"
                />
              </div>

              <div class="form-group">
                <label class="form-label">অভিভাবকের ফোন</label>
                <input
                  v-model="form.guardian_phone"
                  type="tel"
                  class="form-control"
                  placeholder="018XXXXXXXX"
                />
              </div>

              <div class="form-group">
                <label class="form-label">সম্পর্ক</label>
                <input
                  v-model="form.guardian_relation"
                  type="text"
                  class="form-control"
                  placeholder="যেমন: চাচা / মামা / ভাই"
                />
              </div>
            </div>
          </div>

          <!-- Address & Additional Info -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon"><icon name="pin" /></div>
              <div>
                <h4 class="section-title">ঠিকানা ও স্বাস্থ্য সম্পর্কিত তথ্য</h4>
                <p class="section-desc">স্থায়ী/বর্তমান ঠিকানা ও স্বাস্থ্য বিবরণ</p>
              </div>
            </div>

            <div class="form-group mb-4">
              <label class="form-label">পূর্ণ ঠিকানা (বাংলায়)</label>
              <textarea
                v-model="form.address_bn"
                class="form-control"
                rows="3"
                placeholder="গ্রাম/মহল্লা, ডাকঘর, থানা/উপজেলা, জেলা..."
              ></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">স্বাস্থ্য / জরুরি বিবরণ (ঐচ্ছিক)</label>
              <input
                v-model="form.health_summary"
                type="text"
                class="form-control"
                placeholder="কোনো বিশেষ শারীরিক অসুস্থতা বা এলার্জি থাকলে উল্লেখ করুন"
              />
            </div>
          </div>

          <!-- Form Actions Footer -->
          <div class="form-actions-footer">
            <NuxtLink :to="`/students/${studentId}`" class="btn btn-ghost">
              বাতিল
            </NuxtLink>
            <button type="submit" class="btn btn-primary btn-lg" :disabled="saving || !isFormValid">
              <icon v-if="saving" name="loader" class="animate-spin" />
              <icon v-else name="save" />
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'আপডেট সম্পন্ন করুন' }}
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

const studentId = computed(() => route.params.id)
const studentName = ref('')
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const success = ref('')
const errors = ref<any>({})

const classOptions = ref<any[]>([])
const sectionOptions = ref<any[]>([])

const form = reactive({
  name_bn: '',
  name_en: '',
  admission_number: '',
  roll_number: '',
  phone: '',
  email: '',
  date_of_birth: '',
  gender: '',
  blood_group: '',
  nationality: 'বাংলাদেশী',
  class_id: '' as string | number,
  section_id: '' as string | number,
  is_active: true,
  father_name: '',
  father_phone: '',
  mother_name: '',
  mother_phone: '',
  guardian_name: '',
  guardian_phone: '',
  guardian_relation: '',
  address_bn: '',
  health_summary: '',
})

const isFormValid = computed(() => form.name_bn && form.name_bn.trim().length >= 2)

function toBn(n: any) {
  if (n === null || n === undefined) return ''
  return Number(n).toLocaleString('bn-BD')
}

async function loadClassOptions() {
  try {
    const res = await api.get('/academic/classes').catch(() => null)
    if (res?.data?.data && Array.isArray(res.data.data)) {
      classOptions.value = res.data.data
    } else if (res?.data && Array.isArray(res.data)) {
      classOptions.value = res.data
    } else {
      const alt = await api.get('/settings/classes').catch(() => ({ data: { data: [] } }))
      classOptions.value = alt.data?.data || alt.data || []
    }
  } catch (err) {
    console.error('Failed to load classes:', err)
  }
}

async function loadSections(classId: string | number) {
  if (!classId) {
    sectionOptions.value = []
    return
  }
  try {
    const res = await api.get(`/academic/sections?class_id=${classId}`).catch(() => null)
    if (res?.data?.data && Array.isArray(res.data.data)) {
      sectionOptions.value = res.data.data
    } else if (res?.data && Array.isArray(res.data)) {
      sectionOptions.value = res.data
    } else {
      const alt = await api.get(`/settings/sections?class_id=${classId}`).catch(() => null)
      sectionOptions.value = alt?.data?.data || alt?.data || []
    }
  } catch (err) {
    console.error('Failed to load sections:', err)
  }
}

function onClassChange() {
  form.section_id = ''
  if (form.class_id) {
    loadSections(form.class_id)
  }
}

async function loadStudent() {
  loading.value = true
  error.value = ''
  try {
    const id = route.params.id as string
    const res = await api.get(`/students/${id}`)
    const s = res.data?.data || res.data
    if (!s || typeof s !== 'object') {
      error.value = 'শিক্ষার্থীর কোনো তথ্য পাওয়া যায়নি।'
      return
    }

    studentName.value = s.name_bn || s.name_en || s.user?.name_bn || s.user?.name || 'শিক্ষার্থী'
    form.name_bn = s.name_bn || s.user?.name_bn || s.user?.name || ''
    form.name_en = s.name_en || s.user?.name_en || ''
    form.admission_number = s.admission_number || ''
    form.roll_number = s.roll_number || s.enrollments?.[0]?.roll_number || ''
    form.phone = s.phone || s.user?.phone || s.guardian?.phone || s.father_phone || ''
    form.email = s.email || s.user?.email || ''
    form.date_of_birth = s.date_of_birth ? String(s.date_of_birth).slice(0, 10) : ''
    form.gender = s.gender || ''
    form.blood_group = s.blood_group || ''
    form.nationality = s.nationality || 'বাংলাদেশী'
    form.is_active = s.status ? s.status === 'active' : true

    // Academic Class and Section
    const activeEnrollment = s.enrollments?.[0]
    const activeClassId = s.class_id || activeEnrollment?.class_id || s.class?.id || s.class_info?.id
    const activeSectionId = s.section_id || activeEnrollment?.section_id || s.section?.id

    if (activeClassId) {
      form.class_id = activeClassId
      await loadSections(activeClassId)
      if (activeSectionId) {
        form.section_id = activeSectionId
      }
    }

    // Parents & Guardian
    form.father_name = s.father_name || s.guardian?.father_name || ''
    form.father_phone = s.father_phone || s.guardian?.father_phone || ''
    form.mother_name = s.mother_name || s.guardian?.mother_name || ''
    form.mother_phone = s.mother_phone || s.guardian?.mother_phone || ''
    form.guardian_name = s.guardian_name || s.guardian?.guardian_name || ''
    form.guardian_phone = s.guardian_phone || s.guardian?.guardian_phone || ''
    form.guardian_relation = s.guardian_relation || s.guardian?.relation || s.guardian?.relationship || ''

    // Address & Health
    form.address_bn = s.address_bn || ''
    form.health_summary = typeof s.health_summary === 'string' ? s.health_summary : (s.health_summary?.notes || '')
  } catch (err: any) {
    console.error('Failed to load student:', err)
    error.value = err?.response?.data?.message || 'শিক্ষার্থীর তথ্য লোড করতে সমস্যা হয়েছে।'
  } finally {
    loading.value = false
  }
}

async function saveStudent() {
  if (!isFormValid.value) return
  saving.value = true
  error.value = ''
  success.value = ''
  errors.value = {}

  try {
    const id = route.params.id as string
    const payload: Record<string, any> = {
      name_bn: form.name_bn?.trim(),
      name_en: form.name_en?.trim() || null,
      admission_number: form.admission_number?.trim() || null,
      roll_number: form.roll_number?.trim() || null,
      phone: form.phone?.trim() || null,
      email: form.email?.trim() || null,
      date_of_birth: form.date_of_birth || null,
      gender: form.gender || null,
      blood_group: form.blood_group || null,
      nationality: form.nationality || 'বাংলাদেশী',
      class_id: form.class_id ? Number(form.class_id) : null,
      section_id: form.section_id ? Number(form.section_id) : null,
      is_active: !!form.is_active,
      father_name: form.father_name?.trim() || null,
      father_phone: form.father_phone?.trim() || null,
      mother_name: form.mother_name?.trim() || null,
      mother_phone: form.mother_phone?.trim() || null,
      guardian_name: form.guardian_name?.trim() || null,
      guardian_phone: form.guardian_phone?.trim() || null,
      guardian_relation: form.guardian_relation?.trim() || null,
      address_bn: form.address_bn?.trim() || null,
      health_summary: form.health_summary?.trim() || null,
    }

    const res = await api.put(`/students/${id}`, payload)
    success.value = res.data?.message || 'শিক্ষার্থীর তথ্য সফলভাবে আপডেট করা হয়েছে!'

    setTimeout(() => {
      navigateTo(`/students/${id}`)
    }, 600)
  } catch (err: any) {
    console.error('Failed to update student:', err)
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
    error.value = err?.response?.data?.message || 'সংরক্ষণে ত্রুটি দেখা দিয়েছে। তথ্যাবলী পুনরায় যাচাই করুন।'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await loadClassOptions()
  await loadStudent()
})
</script>

<style scoped>
.student-edit-page {
  max-width: 1000px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.title-group h1 {
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--color-primary, #1e3a8a);
  margin: 0 0 0.15rem 0;
}

.title-group p {
  font-size: 0.85rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  border-radius: 0.6rem;
  margin-bottom: 1.25rem;
  font-size: 0.92rem;
  font-weight: 500;
}

.alert-error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

.alert-success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #86efac;
}

.form-card {
  background: #ffffff;
  border: 1px solid var(--color-border-light, #e5e7eb);
  border-radius: 14px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.card-body {
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 1.75rem;
  border-bottom: 1px solid var(--color-border-light, #e5e7eb);
}

.form-section:last-of-type {
  border-bottom: none;
  margin-bottom: 1rem;
  padding-bottom: 0;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 1.25rem;
}

.section-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: var(--color-bg-muted, #f3f4f6);
  color: var(--color-primary, #1e3a8a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-text, #1f2937);
  margin: 0 0 0.15rem 0;
}

.section-desc {
  font-size: 0.8rem;
  color: var(--color-text-muted, #6b7280);
  margin: 0;
}

.form-row {
  display: grid;
  gap: 1.25rem;
  margin-bottom: 1.25rem;
}

.form-row-2 {
  grid-template-columns: repeat(2, 1fr);
}

.form-row-3 {
  grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 768px) {
  .form-row-2,
  .form-row-3 {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text, #374151);
}

.required-star {
  color: #ef4444;
  margin-left: 0.15rem;
}

.form-control {
  padding: 0.625rem 0.85rem;
  border: 1.5px solid var(--color-border-light, #d1d5db);
  border-radius: 8px;
  font-size: 0.92rem;
  color: var(--color-text, #111827);
  background: #ffffff;
  transition: all 0.2s ease;
  outline: none;
}

.form-control:focus {
  border-color: var(--color-primary, #2563eb);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.form-select {
  appearance: auto;
  cursor: pointer;
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
}

.error-message {
  font-size: 0.8rem;
  color: #dc2626;
  margin: 0.25rem 0 0 0;
}

.form-actions-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border-light, #e5e7eb);
  margin-top: 1rem;
}

.loading-card {
  padding: 3rem;
  text-align: center;
  color: var(--color-text-muted, #6b7280);
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(0, 0, 0, 0.1);
  border-top-color: var(--color-primary, #2563eb);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 0.75rem auto;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
