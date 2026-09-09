<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> অর্থায়ন ড্যাশবোর্ড</NuxtLink>
        <h1>দাতাবৃন্দ ও পৃষ্ঠপোষক</h1>
        <p class="page-subtitle">মাদ্রাসার সম্মানিত দাতা ও নিয়মিত পৃষ্ঠপোষকদের যোগাযোগের তালিকা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="showForm = true">
          <icon name="plus" /> নতুন দাতা যোগ করুন
        </button>
        <button class="btn btn-outline" @click="loadDonors">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="দাতার নাম, মোবাইল বা প্রতিষ্ঠান খুঁজুন..." @keyup.enter="loadDonors" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadDonors()">×</button>
      </div>
      <div class="pagination-info" v-if="donorsList.length">
        মোট <span class="highlight">{{ donorsList.length.toLocaleString('bn-BD') }}</span> জন দাতা
      </div>
    </div>

    <!-- Create Donor Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন দাতা নিবন্ধন</h3>
            <p>দাতার নাম, মোবাইল নম্বর, প্রতিষ্ঠান ও যোগাযোগের ঠিকানা যোগ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showForm = false">×</button>
        </div>
        <form @submit.prevent="handleSubmit" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">দাতার নাম (বাংলা) *</label>
              <input v-model="form.name_bn" class="form-input" required placeholder="দাতার পূর্ণ নাম" />
            </div>
            <div class="form-group">
              <label class="form-label">দাতার নাম (ইংরেজি)</label>
              <input v-model="form.name_en" class="form-input" placeholder="Donor's English Name" />
            </div>
            <div class="form-group">
              <label class="form-label">মোবাইল নম্বর *</label>
              <input v-model="form.phone" class="form-input" required placeholder="০১৭১১..." />
            </div>
            <div class="form-group">
              <label class="form-label">ইমেইল</label>
              <input v-model="form.email" type="email" class="form-input" placeholder="donor@example.com" />
            </div>
            <div class="form-group">
              <label class="form-label">প্রতিষ্ঠান / পদবী</label>
              <input v-model="form.organization" class="form-input" placeholder="কোম্পানি বা ব্যবসার নাম" />
            </div>
            <div class="form-group">
              <label class="form-label">রক্তের গ্রুপ</label>
              <select v-model="form.blood_group" class="form-select">
                <option value="">নির্বাচন করুন</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
            <div class="form-group wide">
              <label class="form-label">ঠিকানা</label>
              <input v-model="form.address_bn" class="form-input" placeholder="বাসা/অফিসের ঠিকানা..." />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              {{ loading ? 'সংরক্ষণ হচ্ছে...' : 'দাতা সংরক্ষণ করুন' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Donors Table -->
    <div v-if="loading" class="loading-state card"><div class="spinner" /><p>দাতা তালিকা লোড হচ্ছে...</p></div>
    <div v-else-if="!donorsList.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="users" /></div>
      <h3>কোনো দাতা নিবন্ধিত নেই</h3>
      <p>নতুন দাতা বা পৃষ্ঠপোষক যোগ করে ডেটাবেজ সমৃদ্ধ করুন</p>
      <button class="btn btn-primary" @click="showForm = true"><icon name="plus" /> প্রথম দাতা যোগ করুন</button>
    </div>
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>দাতার নাম</th>
              <th>মোবাইল নম্বর</th>
              <th>ইমেইল</th>
              <th>প্রতিষ্ঠান</th>
              <th>রক্তের গ্রুপ</th>
              <th>ঠিকানা</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in donorsList" :key="d.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(d.name_bn || d.name_en) }">
                    {{ (d.name_bn || d.name_en || 'দ').charAt(0) }}
                  </div>
                  <strong>{{ d.name_bn }}</strong>
                  <span v-if="d.name_en" class="sub-text">({{ d.name_en }})</span>
                </div>
              </td>
              <td class="mono-font">{{ d.phone || '—' }}</td>
              <td>{{ d.email || '—' }}</td>
              <td>{{ d.organization || '—' }}</td>
              <td><span class="type-tag" v-if="d.blood_group">{{ d.blood_group }}</span><span v-else>—</span></td>
              <td>{{ d.address_bn || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(false)
const showForm = ref(false)
const donors = ref<any>(null)
const error = ref('')
const search = ref('')

const form = reactive({
  name_bn: '',
  name_en: '',
  phone: '',
  email: '',
  blood_group: '',
  organization: '',
  address_bn: '',
})

const donorsList = computed(() => {
  const list = donors.value?.data?.data || donors.value?.data || []
  if (!search.value) return list
  return list.filter((d: any) => {
    const term = (d.name_bn || '') + ' ' + (d.name_en || '') + ' ' + (d.phone || '') + ' ' + (d.organization || '')
    return term.toLowerCase().includes(search.value.toLowerCase())
  })
})

async function loadDonors() {
  loading.value = true
  try {
    const r = await api.get('/finance/donors?per_page=100')
    donors.value = r.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await api.post('/finance/donors', { ...form })
    showForm.value = false
    form.name_bn = ''
    form.name_en = ''
    form.phone = ''
    form.email = ''
    form.blood_group = ''
    form.organization = ''
    form.address_bn = ''
    await loadDonors()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'দাতা সংরক্ষণ করা যায়নি'
  } finally {
    loading.value = false
  }
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

onMounted(loadDonors)
</script>

<style scoped>
.page-wrapper { max-width: 1320px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.clear-search-btn { background: none; border: none; font-size: 1.1rem; color: var(--color-text-light); cursor: pointer; padding: 0 0.2rem; }
.pagination-info { margin-left: auto; font-size: 0.85rem; color: var(--color-text-light); }
.pagination-info .highlight { font-weight: 700; color: var(--color-primary); }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.76rem; color: var(--color-text-light); margin-left: 0.3rem; }
.type-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(0, 0, 0, 0.05); border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.mono-font { font-family: monospace; font-size: 0.84rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }
.btn-ghost { background: transparent; color: var(--color-text); }
.btn-ghost:hover { background: rgba(0, 0, 0, 0.05); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.empty-icon-wrap { width: 60px; height: 60px; border-radius: 16px; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem; }
.empty-state { padding: 3rem 1.5rem; text-align: center; }
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>
