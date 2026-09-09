<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/digital-attendance/devices" class="back-link"><icon name="arrow-left" /> ডিভাইস তালিকায় ফিরে যান</NuxtLink>
        <h1>RFID স্মার্ট কার্ড ব্যবস্থাপনা</h1>
        <p class="page-subtitle">শিক্ষার্থী ও শিক্ষক-কর্মীদের রেডিও ফ্রিকোয়েন্সি (RFID / NFC) পরিচয়পত্র ও হাজিরা কার্ড</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAssignModal">
          <icon name="plus" /> নতুন RFID কার্ড বরাদ্দ
        </button>
        <button class="btn btn-outline" @click="loadCards">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="কার্ড নম্বর (UID), নাম বা রোল খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="roleFilter" class="form-select">
        <option value="">সকল ধরন (All Roles)</option>
        <option value="student">শিক্ষার্থী</option>
        <option value="teacher">শিক্ষক</option>
        <option value="staff">স্টাফ / কর্মকর্তা</option>
      </select>
      <select v-model="statusFilter" class="form-select">
        <option value="">সকল স্ট্যাটাস</option>
        <option value="active">সক্রিয়</option>
        <option value="blocked">স্থগিত / ব্লকড</option>
        <option value="lost">হারিয়ে গেছে</option>
      </select>
      <div class="pagination-info" v-if="filteredCards.length">
        মোট <span class="highlight">{{ filteredCards.length.toLocaleString('bn-BD') }}</span> টি কার্ড
      </div>
    </div>

    <!-- RFID Cards Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>RFID কার্ড UID / নম্বর</th>
              <th>কার্ডধারী ব্যক্তি</th>
              <th>ধরন</th>
              <th>শ্রেণি / পদবী</th>
              <th>ইস্যুর তারিখ</th>
              <th class="text-center">স্ট্যাটাস</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="card in filteredCards" :key="card.id">
              <td>
                <strong class="mono-badge">{{ card.card_uid }}</strong>
              </td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(card.holder_name) }">
                    {{ (card.holder_name || 'ক').charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ card.holder_name }}</strong>
                    <div class="sub-text">আইডি: #{{ card.user_id }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="type-tag" :class="card.role">{{ roleLabel(card.role) }}</span>
              </td>
              <td>{{ card.designation || card.class_name }}</td>
              <td>{{ card.issue_date }}</td>
              <td class="text-center">
                <span class="status-pill" :class="statusBadgeClass(card.status)">
                  <span class="status-dot" />
                  {{ statusLabel(card.status) }}
                </span>
              </td>
              <td class="text-right">
                <div class="action-buttons">
                  <button class="action-btn" @click="toggleStatus(card)" title="স্ট্যাটাস পরিবর্তন">
                    <icon :name="card.status === 'active' ? 'lock' : 'check'" />
                  </button>
                  <button class="action-btn delete" @click="deleteCard(card.id)" title="কার্ড বাতিল">
                    <icon name="trash" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Assign Card Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন RFID কার্ড বরাদ্দকরণ</h3>
            <p>কার্ড রিডারে কার্ড ছুঁইয়ে UID কোড প্রবেশ করান এবং ইউজার নির্বাচন করুন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveCard" class="modal-form">
          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">RFID কার্ড UID / হেক্স কোড *</label>
              <input v-model="form.card_uid" class="form-input mono" placeholder="e.g. 04A3B2C19E" required autofocus />
            </div>
            <div class="form-group">
              <label class="form-label">ইউজার ধরন *</label>
              <select v-model="form.role" class="form-select" required>
                <option value="student">শিক্ষার্থী</option>
                <option value="teacher">শিক্ষক</option>
                <option value="staff">স্টাফ</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">ব্যক্তির নাম *</label>
              <input v-model="form.holder_name" class="form-input" placeholder="পূর্ণ নাম লিখুন" required />
            </div>
            <div class="form-group">
              <label class="form-label">শ্রেণি / পদবী</label>
              <input v-model="form.designation" class="form-input" placeholder="যেমন: মিজান জামাত / মুহাদ্দিস" />
            </div>
            <div class="form-group">
              <label class="form-label">ইস্যুর তারিখ</label>
              <input v-model="form.issue_date" type="date" class="form-input" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">কার্ড বরাদ্দ সম্পন্ন করুন</button>
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
const search = ref('')
const roleFilter = ref('')
const statusFilter = ref('')
const showModal = ref(false)

const cards = ref<any[]>([
  {
    id: 1,
    card_uid: '04A3B2C19E',
    user_id: 'STU-101',
    holder_name: 'মুহাম্মদ সালমান ফারসি',
    role: 'student',
    class_name: 'মিজান জামাত',
    issue_date: '০১ জানু, ২০২৬',
    status: 'active'
  },
  {
    id: 2,
    card_uid: '18E4F9012A',
    user_id: 'TEA-01',
    holder_name: 'মাওলানা মাহমুদ হাসান',
    role: 'teacher',
    designation: 'সিনিয়র উস্তাদ ও মুহাদ্দিস',
    issue_date: '০১ জানু, ২০২৬',
    status: 'active'
  }
])

const form = reactive({
  card_uid: '',
  holder_name: '',
  role: 'student',
  designation: '',
  issue_date: new Date().toISOString().slice(0, 10)
})

async function loadCards() {
  try {
    const res = await api.get('/digital-attendance/rfid-cards').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      cards.value = fetched.map((c: any) => ({
        id: c.id,
        card_uid: c.card_uid,
        user_id: c.user_id || 'ID-' + c.id,
        holder_name: c.holder_name,
        role: c.role,
        designation: c.designation || c.class_name,
        issue_date: c.issue_date || '০১ জানু, ২০২৬',
        status: c.status || 'active'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredCards = computed(() => {
  return cards.value.filter(c => {
    const term = (c.card_uid + ' ' + c.holder_name + ' ' + c.user_id).toLowerCase()
    const matchesSearch = !search.value || term.includes(search.value.toLowerCase())
    const matchesRole = !roleFilter.value || c.role === roleFilter.value
    const matchesStatus = !statusFilter.value || c.status === statusFilter.value
    return matchesSearch && matchesRole && matchesStatus
  })
})

function openAssignModal() {
  form.card_uid = ''
  form.holder_name = ''
  form.designation = ''
  form.role = 'student'
  showModal.value = true
}

async function saveCard() {
  try {
    const res = await api.post('/digital-attendance/rfid-cards', {
      card_uid: form.card_uid.toUpperCase(),
      holder_name: form.holder_name,
      role: form.role,
      designation: form.designation,
      issue_date: form.issue_date,
    }).catch(() => null)

    const saved = res?.data?.data
    cards.value.unshift({
      id: saved?.id || Date.now(),
      card_uid: form.card_uid.toUpperCase(),
      user_id: saved?.user_id || 'ID-' + Math.floor(100 + Math.random() * 900),
      holder_name: form.holder_name,
      role: form.role,
      designation: form.designation,
      issue_date: 'আজ',
      status: 'active'
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

async function toggleStatus(card: any) {
  const newStatus = card.status === 'active' ? 'blocked' : 'active'
  card.status = newStatus
  await api.put(`/digital-attendance/rfid-cards/${card.id}`, { status: newStatus }).catch(() => {})
}

async function deleteCard(id: number) {
  if (confirm('আপনি কি এই কার্ডটি বাতিল করতে চান?')) {
    await api.delete(`/digital-attendance/rfid-cards/${id}`).catch(() => {})
    cards.value = cards.value.filter(c => c.id !== id)
  }
}

onMounted(loadCards)

function roleLabel(r: string) {
  if (r === 'student') return 'শিক্ষার্থী'
  if (r === 'teacher') return 'শিক্ষক'
  return 'স্টাফ'
}

function statusBadgeClass(s: string) {
  if (s === 'active') return 'badge-approved'
  if (s === 'blocked') return 'badge-rejected'
  return 'badge-pending'
}

function statusLabel(s: string) {
  if (s === 'active') return 'সক্রিয়'
  if (s === 'blocked') return 'ব্লকড'
  return 'হারিয়ে গেছে'
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
.sub-text { font-size: 0.76rem; color: var(--color-text-light); }
.mono-badge { font-family: monospace; font-size: 0.84rem; background: #f1f5f9; padding: 0.25rem 0.6rem; border-radius: 6px; color: #0f172a; border: 1px solid #e2e8f0; }

.type-tag { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.type-tag.student { background: #dbeafe; color: #1e40af; }
.type-tag.teacher { background: #dcfce7; color: #15803d; }
.type-tag.staff { background: #f3e8ff; color: #6b21a8; }

.action-buttons { display: flex; gap: 0.35rem; justify-content: flex-end; }
.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); }
.action-btn.delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

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
</style>
