<template>
  <div class="student-edit">
    <div class="page-header">
      <NuxtLink to="/students" class="btn btn-outline btn-sm">
        <icon name="arrow-left" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink to="/students" class="btn btn-secondary btn-sm">
          <icon name="close" /> বাতিল
        </NuxtLink>
        <button class="btn btn-primary btn-sm" :disabled="saving || !isFormValid" @click="saveStudent">
          <icon v-if="saving" name="loader" /><icon v-else name="save" />
          {{ editing ? 'আপডেট' : 'সংরক্ষণ' }}
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="saveStudent">
          <div class="form-section">
            <h4 class="section-title">ছাত্রের তথ্য</h4>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">নাম (বাংলা) *</label>
                <input v-model="form.name_bn" type="text" class="form-control" placeholder="ছাত্রের নাম বাংলায়" required />
                <p v-if="errors.name_bn" class="error-message">{{ errors.name_bn[0] }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">নাম (ইংরেজি)</label>
                <input v-model="form.name_en" type="text" class="form-control" placeholder="Student name in English" />
              </div>
            </div>
            <div class="form-row form-row-3">
              <div class="form-group">
                <label class="form-label">ভর্তি নং</label>
                <input v-model="form.admission_number" type="text" class="form-control" placeholder="যেমন: 2024-001" />
              </div>
              <div class="form-group">
                <label class="form-label">রোল নং</label>
                <input v-model="form.roll_number" type="text" class="form-control" placeholder="যেমন: 5" />
              </div>
              <div class="form-group">
                <label class="form-label">ফোন</label>
                <input v-model="form.phone" type="tel" class="form-control" placeholder="যোগাযোগের ফোন নম্বর" />
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">জন্ম তারিখ</label>
                <input v-model="form.date_of_birth" type="date" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">লিঙ্গ</label>
                <select v-model="form.gender" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                  <option value="ছেলে">ছেলে</option>
                  <option value="মেয়ে">মেয়ে</option>
                  <option value="অন্যান্য">অন্যান্য</option>
                </select>
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">রক্তের গ্রুপ</label>
                <select v-model="form.blood_group" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                  <option value="A+">A+</option><option value="A-">A-</option>
                  <option value="B+">B+</option><option value="B-">B-</option>
                  <option value="AB+">AB+</option><option value="AB-">AB-</option>
                  <option value="O+">O+</option><option value="O-">O-</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">শ্রেণি</label>
                <select v-model="form.class_id" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="cls in classOptions" :key="cls.id" :value="cls.id">{{ cls.name_bn }}</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">ঠিকানা</h4>
            <div class="form-group">
              <label class="form-label">বাড়ির ঠিকানা (বাংলা)</label>
              <textarea v-model="form.address_bn" class="form-control" rows="3" placeholder="বাড়ির ঠিকানা বাংলায় লিখুন" />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving || !isFormValid">
              <icon v-if="saving" name="loader" /><icon v-else name="save" />
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
const classOptions = ref<any[]>([])

const form = reactive({
  name_bn: '', name_en: '', admission_number: '', roll_number: '', phone: '',
  date_of_birth: '', gender: '', blood_group: '', class_id: '',
  address_bn: '',
})

const isFormValid = computed(() => form.name_bn.trim().length >= 2)

async function loadClassOptions() {
  try {
    const res = await api.get('/students?per_page=1000')
    const classes = new Map()
    (res.data.data || []).forEach((s: any) => {
      const key = s.class?.id || s.class_id
      const name = s.class?.name_bn || s.class_name || 'Unknown'
      if (key && !classes.has(key)) classes.set(key, { id: key, name_bn: name })
    })
    classOptions.value = Array.from(classes.values()).sort((a, b) => a.name_bn.localeCompare(b.name_bn))
  } catch (error) { console.error('Failed to load class options:', error) }
}

async function loadStudent() {
  if (!editing.value) return
  try {
    const res = await api.get(`/students/${route.params.id}`)
    const s = res.data.data
    form.name_bn = s.name_bn || ''; form.name_en = s.name_en || ''
    form.admission_number = s.admission_number || ''; form.roll_number = s.roll_number || ''
    form.phone = s.user?.phone || s.phone || ''
    form.date_of_birth = s.date_of_birth || ''; form.gender = s.gender || ''
    form.blood_group = s.blood_group || ''
    form.class_id = String(s.class_id || s.class?.id || '')
    form.address_bn = s.address_bn || ''
  } catch (error) { console.error('Failed to load student:', error); navigateTo('/students') }
}

async function saveStudent() {
  saving.value = true; errors.value = {}
  try {
    if (editing.value) {
      await api.put(`/students/${route.params.id}`, form)
      navigateTo('/students')
    } else {
      alert('প্রথমে একটি ব্যবহারকারী তৈরি করুন অথবা নির্বাচন করুন।')
      return
    }
  } catch (error: any) {
    if (error.response?.data?.errors) errors.value = error.response.data.errors
    else alert('সংরক্ষণে ত্রুটি: ' + (error.message || 'অজানা ত্রুটি'))
  } finally { saving.value = false }
}

onMounted(async () => { await loadClassOptions(); if (editing.value) await loadStudent() })
</script>

<style scoped>
.student-edit { padding: 1.5rem; }
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
textarea.form-control { resize: vertical; }
.error-message { font-size: 0.8125rem; color: var(--color-error); margin: 0; }
.form-actions { padding-top: 1rem; }
</style>
