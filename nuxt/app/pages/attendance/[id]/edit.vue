<template>
  <div class="attendance-edit">
    <div class="page-header">
      <NuxtLink to="/attendance" class="btn btn-outline btn-sm">
        <icon :name="mdiArrowLeft" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink to="/attendance" class="btn btn-secondary btn-sm">
          <icon :name="mdiClose" /> বাতিল
        </NuxtLink>
        <button class="btn btn-primary btn-sm" :disabled="saving || !isFormValid" @click="saveRecord">
          <icon v-if="saving" :name="mdiLoad" /><icon v-else :name="mdiContentSave" />
          {{ editing ? 'আপডেট' : 'সংরক্ষণ' }}
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="saveRecord">
          <div class="form-section">
            <h4 class="section-title">হাজিরা তথ্য</h4>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">ছাত্র</label>
                <select v-model="form.student_id" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                </select>
                <p v-if="errors.student_id" class="error-message">{{ errors.student_id[0] }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">অবস্থা *</label>
                <select v-model="form.status" class="form-control" required>
                  <option value="present">উপস্থিত</option>
                  <option value="absent">অনুপস্থিত</option>
                  <option value="late">দেরি</option>
                  <option value="excused">অনুচিত অনুপস্থিত</option>
                  <option value="permission">অনুমতি প্রাপ্ত অনুপস্থিত</option>
                </select>
                <p v-if="errors.status" class="error-message">{{ errors.status[0] }}</p>
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">তারিখ *</label>
                <input v-model="form.date" type="date" class="form-control" required />
                <p v-if="errors.date" class="error-message">{{ errors.date[0] }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">হাজিরার পদ্ধতি</label>
                <select v-model="form.method" class="form-control">
                  <option value="manual">ম্যানুয়াল</option>
                  <option value="fingerprint">ফিঙ্গারপ্রিন্ট</option>
                  <option value="biometric">বায়োমেট্রিক</option>
                  <option value="qr">QR কোড</option>
                  <option value="online">অনলাইন</option>
                </select>
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">চেক-ইন সময়</label>
                <input v-model="form.check_in_time" type="datetime-local" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">চেক-আউট সময়</label>
                <input v-model="form.check_out_time" type="datetime-local" class="form-control" />
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">অতিরিক্ত তথ্য</h4>
            <div class="form-row form-row-2">
              <div class="form-group form-check">
                <label class="form-check-label">
                  <input type="checkbox" v-model="form.parent_notified" class="form-check-input" /> অভিভাবককে জানানো হয়েছে
                </label>
              </div>
              <div class="form-group">
                <label class="form-label">টিচার নোট</label>
                <textarea v-model="form.teacher_notes" class="form-control" rows="2" placeholder="অতিরিক্ত নোট..." />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">অনুপস্থিতির কারণ</label>
              <textarea v-model="form.absence_reason" class="form-control" rows="2" placeholder="অনুপস্থিতির কারণ (যদি প্রযোজ্য হয়)" />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving || !isFormValid">
              <icon v-if="saving" :name="mdiLoad" /><icon v-else :name="mdiContentSave" />
              {{ editing ? 'আপডেট' : 'সংরক্ষণ' }}
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
const studentOptions = ref<any[]>([])

const form = reactive({
  student_id: '',
  status: 'present',
  date: new Date().toISOString().split('T')[0],
  method: 'manual',
  check_in_time: '',
  check_out_time: '',
  parent_notified: false,
  teacher_notes: '',
  absence_reason: '',
})

const isFormValid = computed(() => form.student_id && form.status && form.date)

async function loadStudentOptions() {
  try {
    const res = await api.get('/students?per_page=1000')
    studentOptions.value = (res.data.data || []).map((s: any) => ({
      id: s.id,
      label: `${s.name_bn} (${s.class?.name_bn || s.class_name || ''})`,
    }))
  } catch (error) { console.error('Failed to load student options:', error) }
}

async function loadRecord() {
  if (!editing.value) return
  try {
    const res = await api.get(`/attendance/${route.params.id}`)
    const r = res.data.data
    form.student_id = String(r.student_id || r.student?.id || '')
    form.status = r.status || 'present'
    form.date = r.date ? r.date.split('T')[0] : new Date().toISOString().split('T')[0]
    form.method = r.method || 'manual'
    form.check_in_time = r.check_in_time ? r.check_in_time.substring(0, 16) : ''
    form.check_out_time = r.check_out_time ? r.check_out_time.substring(0, 16) : ''
    form.parent_notified = r.parent_notified ?? false
    form.teacher_notes = r.teacher_notes || ''
    form.absence_reason = r.absence_reason || ''
  } catch (error) { console.error('Failed to load record:', error); navigateTo('/attendance') }
}

async function saveRecord() {
  saving.value = true; errors.value = {}
  try {
    if (editing.value) await api.put(`/attendance/${route.params.id}`, form)
    else await api.post('/attendance', form)
    navigateTo('/attendance')
  } catch (error: any) {
    if (error.response?.data?.errors) errors.value = error.response.data.errors
    else alert('সংরক্ষণে ত্রুটি: ' + (error.message || 'অজানা ত্রুটি'))
  } finally { saving.value = false }
}

onMounted(async () => { await loadStudentOptions(); if (editing.value) await loadRecord() })
</script>

<style scoped>
.attendance-edit { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.form-section { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border-light); }
.form-section:last-of-type { border-bottom: none; }
.section-title { font-size: 1rem; font-weight: 600; color: var(--color-text); margin: 0 0 1rem 0; }
.form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.form-row-2 { grid-template-columns: repeat(2, 1fr); }
.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-label { font-size: 0.875rem; font-weight: 500; color: var(--color-text); }
.form-control { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9375rem; color: var(--color-text); background: var(--color-bg); }
.form-control:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary-light); }
textarea.form-control { resize: vertical; }
form-check { flex-direction: row; align-items: center; gap: 0.5rem; }
form-check-label { font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; }
form-check-input { width: 18px; height: 18px; }
.form-actions { padding-top: 1rem; }
.error-message { font-size: 0.8125rem; color: var(--color-error); margin: 0; }
</style>
