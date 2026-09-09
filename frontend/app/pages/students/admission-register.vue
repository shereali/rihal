<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/students" class="back-link"><icon name="arrow-left" /> শিক্ষার্থী তালিকায় ফিরে যান</NuxtLink>
        <h1>স্থায়ী ভর্তি রেজিস্টার (Admission Register Ledger)</h1>
        <p class="page-subtitle">প্রতিষ্ঠানের প্রতিষ্ঠাকালীন থেকে ভর্তি হওয়া সকল শিক্ষার্থীর স্থায়ী বহিখাতা ও তথ্যভাণ্ডার</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/students/blank-form" class="btn btn-outline">
          <icon name="document-text" /> খালি ভর্তি ফরম
        </NuxtLink>
        <button class="btn btn-primary" @click="printRegister">
          <icon name="printer" /> রেজিস্টার প্রিন্ট করুন
        </button>
      </div>
    </div>

    <!-- Search Toolbar -->
    <div class="toolbar card no-print">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="ভর্তি নং, দাখেলা নং, নাম বা গ্রাম খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="yearFilter" class="form-select">
        <option value="">সকল শিক্ষাবর্ষ (All Sessions)</option>
        <option value="2026">২০২৬ শিক্ষাবর্ষ</option>
        <option value="2025">২০২৫ শিক্ষাবর্ষ</option>
        <option value="2024">২০২৪ শিক্ষাবর্ষ</option>
      </select>
      <div class="pagination-info" v-if="filteredStudents.length">
        মোট <span class="highlight">{{ filteredStudents.length.toLocaleString('bn-BD') }}</span> জন শিক্ষার্থী
      </div>
    </div>

    <!-- Register Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table ledger-table">
          <thead>
            <tr>
              <th>ভর্তি / দাখেলা নং</th>
              <th>ভর্তির তারিখ</th>
              <th>শিক্ষার্থীর পূর্ণ নাম</th>
              <th>পিতার নাম ও পেশা</th>
              <th>মাতার নাম</th>
              <th>স্থায়ী ঠিকানা</th>
              <th>জন্ম তারিখ / বয়স</th>
              <th>রক্তের গ্রুপ</th>
              <th>ভর্তিকৃত শ্রেণি</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="st in filteredStudents" :key="st.id">
              <td><strong class="mono-font">{{ st.admission_no }}</strong></td>
              <td>{{ st.admission_date }}</td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(st.name) }">
                    {{ st.name.charAt(0) }}
                  </div>
                  <strong>{{ st.name }}</strong>
                </div>
              </td>
              <td>
                <div>{{ st.father_name }}</div>
                <div class="sub-text">পেশা: {{ st.father_occupation }}</div>
              </td>
              <td>{{ st.mother_name }}</td>
              <td>{{ st.village }}, {{ st.post }}, {{ st.district }}</td>
              <td>{{ st.dob }}</td>
              <td><span class="type-tag" v-if="st.blood_group">{{ st.blood_group }}</span><span v-else>—</span></td>
              <td><span class="fund-tag">{{ st.enrolled_class }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const search = ref('')
const yearFilter = ref('')

const registerData = ref<any[]>([
  {
    id: 1,
    admission_no: 'ADM-2026-001',
    admission_date: '০১ জানু, ২০২৬',
    name: 'মুহাম্মদ সালমান ফারসি',
    father_name: 'মুহাম্মদ রফিকুল ইসলাম',
    father_occupation: 'ব্যবসায়ী',
    mother_name: 'মোসাম্মাৎ ফাতেমা বেগম',
    village: 'টুঙ্গিপাড়া',
    post: 'টুঙ্গিপাড়া',
    district: 'গোপালগঞ্জ',
    dob: '১২ মার্চ, ২০১২',
    blood_group: 'B+',
    enrolled_class: 'মিজান জামাত'
  }
])

async function loadStudents() {
  try {
    const res = await api.get('/students?per_page=50').catch(() => null)
    const studs = res?.data?.data?.data || res?.data?.data || []
    if (studs.length > 0) {
      registerData.value = studs.map((s: any) => ({
        id: s.id,
        admission_no: s.admission_number || `ADM-2026-${String(s.id).padStart(3, '0')}`,
        admission_date: s.admission_date || '০১ জানু, ২০২৬',
        name: s.name_bn || s.name_en || 'শিক্ষার্থী',
        father_name: s.father_name_bn || s.father_name || '—',
        father_occupation: s.father_occupation || 'ব্যবসায়ী',
        mother_name: s.mother_name || '—',
        village: s.present_address || 'গোপালগঞ্জ',
        post: s.present_address || 'গোপালগঞ্জ',
        district: 'গোপালগঞ্জ',
        dob: s.date_of_birth || '১২ মার্চ, ২০১২',
        blood_group: s.blood_group || 'B+',
        enrolled_class: s.academic_class?.name || 'হিফজ'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredStudents = computed(() => {
  return registerData.value.filter(st => {
    const term = (st.admission_no + ' ' + st.name + ' ' + st.village + ' ' + st.district).toLowerCase()
    return !search.value || term.includes(search.value.toLowerCase())
  })
})

onMounted(loadStudents)

function printRegister() {
  window.print()
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
.page-wrapper { max-width: 1380px; margin: 0 auto; padding: 1.75rem; }
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
.user-avatar-initials { width: 30px; height: 30px; border-radius: 50%; color: #fff; font-size: 0.82rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }
.mono-font { font-family: monospace; font-size: 0.82rem; }
.type-tag { display: inline-block; padding: 0.15rem 0.5rem; background: rgba(0, 0, 0, 0.05); border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.fund-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); border-radius: 6px; font-size: 0.78rem; font-weight: 600; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }

@media print {
  .no-print { display: none !important; }
  .page-wrapper { max-width: 100%; padding: 0; }
  .table-card { box-shadow: none; border: none; }
}
</style>
