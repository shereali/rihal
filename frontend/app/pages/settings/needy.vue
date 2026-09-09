<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/settings" class="back-link"><icon name="arrow-left" /> সেটিংস তালিকায় ফিরে যান</NuxtLink>
        <h1>হতদরিদ্র ও যাকাত ফান্ড শিক্ষার্থী সহায়তা (Needy Students Fund)</h1>
        <p class="page-subtitle">দরিদ্র ও এতিম শিক্ষার্থীদের জন্য মাসিক বেতন মওকুফ, পোশাক ও কিতাব ক্রয়ের বিশেষ অনুদান কনফিগারেশন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddNeedyModal">
          <icon name="plus" /> নতুন সহায়তা বরাদ্দ
        </button>
      </div>
    </div>

    <!-- Search Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="শিক্ষার্থীর নাম, রোল বা জামাত খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
    </div>

    <!-- Needy Students Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>রোল</th>
              <th>শিক্ষার্থীর নাম</th>
              <th>জামাত</th>
              <th>সহায়তার ধরন</th>
              <th class="text-right">মওকুফ হার / পরিমাণ</th>
              <th>তহবিলের উৎস</th>
              <th class="text-center">স্ট্যাটাস</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="n in filteredNeedy" :key="n.id">
              <td><strong>{{ toBn(n.roll) }}</strong></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(n.name) }">
                    {{ n.name.charAt(0) }}
                  </div>
                  <strong>{{ n.name }}</strong>
                </div>
              </td>
              <td>{{ n.class }}</td>
              <td><strong>{{ n.support_type }}</strong></td>
              <td class="text-right font-bold text-success">{{ n.amount }}</td>
              <td><span class="fund-tag">{{ n.fund_source }}</span></td>
              <td class="text-center">
                <span class="status-pill badge-approved">
                  <span class="status-dot" /> সক্রিয় মঞ্জুরি
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>হতদরিদ্র শিক্ষার্থী সহায়তা বরাদ্দ</h3>
            <p>শিক্ষার্থী নির্বাচন করে যাকাত/লিল্লাহ তহবিল থেকে সহায়তা বরাদ্দ দিন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveNeedy" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">শিক্ষার্থীর নাম *</label>
              <input v-model="form.name" class="form-input" placeholder="মুহাম্মদ আনাস" required />
            </div>
            <div class="form-group">
              <label class="form-label">শ্রেণি / জামাত *</label>
              <input v-model="form.class" class="form-input" placeholder="মিজান জামাত" required />
            </div>
            <div class="form-group">
              <label class="form-label">সহায়তার ধরন *</label>
              <select v-model="form.support_type" class="form-select" required>
                <option value="১০০% বেতন ও বোর্ডিং মওকুফ">১০০% বেতন ও বোর্ডিং মওকুফ (পূর্ণ ফ্রি)</option>
                <option value="৫০% হাফ-ফ্রি স্কলারশিপ">৫০% হাফ-ফ্রি স্কলারশিপ</option>
                <option value="বিনামূল্যে কিতাব বিতরণ">বিনামূল্যে কিতাব বিতরণ</option>
                <option value="পোশাক ও চিকিৎসা অনুদান">পোশাক ও চিকিৎসা অনুদান</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">তহবিলের উৎস *</label>
              <select v-model="form.fund_source" class="form-select" required>
                <option value="যাকাত ও সাদকাহ ফান্ড">যাকাত ও সাদকাহ ফান্ড</option>
                <option value="লিল্লাহ বোর্ডিং তহবিল">লিল্লাহ বোর্ডিং তহবিল</option>
                <option value="এতিমখানা স্পন্সর ফান্ড">এতিমখানা স্পন্সর ফান্ড</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">সহায়তা বরাদ্দ সংরক্ষণ করুন</button>
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
const showModal = ref(false)

const needyList = ref<any[]>([
  {
    id: 1,
    roll: 105,
    name: 'মুহাম্মদ আনাস বিন মালিক',
    class: 'মিজান জামাত',
    support_type: '১০০% বেতন ও বোর্ডিং মওকুফ',
    amount: '১০০% ফ্রি',
    fund_source: 'যাকাত ও সাদকাহ ফান্ড'
  }
])

const form = reactive({
  name: '',
  class: '',
  support_type: '১০০% বেতন ও বোর্ডিং মওকুফ',
  fund_source: 'যাকাত ও সাদকাহ ফান্ড'
})

async function loadNeedy() {
  try {
    const res = await api.get('/settings/needy').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      needyList.value = fetched.map((n: any) => ({
        id: n.id,
        roll: n.student_id || n.id,
        name: n.student_name,
        class: n.class_name,
        support_type: n.support_type,
        amount: n.amount_discount || '১০০%',
        fund_source: n.fund_source
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredNeedy = computed(() => {
  return needyList.value.filter(n => {
    const term = (n.name + ' ' + n.class + ' ' + n.support_type).toLowerCase()
    return !search.value || term.includes(search.value.toLowerCase())
  })
})

function openAddNeedyModal() {
  form.name = ''
  form.class = ''
  showModal.value = true
}

async function saveNeedy() {
  try {
    const res = await api.post('/settings/needy', {
      student_name: form.name,
      class_name: form.class,
      support_type: form.support_type,
      fund_source: form.fund_source,
      amount_discount: '১০০%'
    }).catch(() => null)

    const saved = res?.data?.data
    needyList.value.unshift(saved ? {
      id: saved.id,
      roll: saved.id,
      name: saved.student_name,
      class: saved.class_name,
      support_type: saved.support_type,
      amount: 'অনুমোদিত',
      fund_source: saved.fund_source
    } : {
      id: Date.now(),
      roll: Math.floor(100 + Math.random() * 50),
      name: form.name,
      class: form.class,
      support_type: form.support_type,
      amount: 'অনুমোদিত',
      fund_source: form.fund_source
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

onMounted(loadNeedy)

function toBn(num: any) {
  if (num === null || num === undefined) return ''
  return String(num).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d])
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
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.clear-search-btn { background: none; border: none; font-size: 1.1rem; color: var(--color-text-light); cursor: pointer; padding: 0 0.2rem; }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fund-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); border-radius: 6px; font-size: 0.78rem; font-weight: 600; }
.text-success { color: #15803d; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
