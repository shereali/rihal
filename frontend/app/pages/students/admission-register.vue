<template>
  <div class="page-wrapper">
    <!-- Dedicated Printable Ledger Header (Visible only when printed) -->
    <div class="print-register-header print-only">
      <div class="bismillah-line">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>
      <h1 class="institution-title">{{ institutionName }}</h1>
      <p class="institution-address" v-if="institutionAddress">{{ institutionAddress }}</p>
      <div class="ledger-badge-title">স্থায়ী শিক্ষার্থী ভর্তি রেজিস্টার বহিখাতা (Admission Ledger)</div>
      <div class="print-meta-bar">
        <span><strong>শিক্ষাবর্ষ:</strong> {{ yearFilter ? yearFilter + ' শিক্ষাবর্ষ' : 'সকল শিক্ষাবর্ষ (সার্বিক)' }}</span>
        <span><strong>মোট শিক্ষার্থী:</strong> {{ filteredStudents.length.toLocaleString('bn-BD') }} জন</span>
        <span><strong>মুদ্রণের তারিখ:</strong> {{ printDateBn }}</span>
      </div>
    </div>

    <!-- On-screen Header -->
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
              <th style="width: 40px;">ক্র.</th>
              <th style="width: 110px;">ভর্তি / দাখেলা নং</th>
              <th style="width: 95px;">ভর্তির তারিখ</th>
              <th>শিক্ষার্থীর পূর্ণ নাম</th>
              <th>পিতার নাম ও পেশা</th>
              <th>মাতার নাম</th>
              <th>স্থায়ী ঠিকানা</th>
              <th style="width: 95px;">জন্ম তারিখ</th>
              <th style="width: 55px;">রক্তের গ্রুপ</th>
              <th style="width: 100px;">ভর্তিকৃত শ্রেণি</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(st, idx) in filteredStudents" :key="st.id">
              <td class="text-center font-bold">{{ (idx + 1).toLocaleString('bn-BD') }}</td>
              <td><strong class="mono-font">{{ st.admission_no }}</strong></td>
              <td>{{ st.admission_date }}</td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials no-print" :style="{ backgroundColor: getAvatarColor(st.name) }">
                    {{ st.name.charAt(0) }}
                  </div>
                  <strong class="student-name-text">{{ st.name }}</strong>
                </div>
              </td>
              <td>
                <div>{{ st.father_name }}</div>
                <div class="sub-text" v-if="st.father_occupation !== '—'">পেশা: {{ st.father_occupation }}</div>
              </td>
              <td>{{ st.mother_name }}</td>
              <td>{{ st.village }}<span v-if="st.post && st.post !== '—'">, {{ st.post }}</span><span v-if="st.district && st.district !== '—'">, {{ st.district }}</span></td>
              <td>{{ st.dob }}</td>
              <td class="text-center"><span class="type-tag" v-if="st.blood_group">{{ st.blood_group }}</span><span v-else>—</span></td>
              <td><span class="fund-tag">{{ st.enrolled_class }}</span></td>
            </tr>
            <tr v-if="filteredStudents.length === 0">
              <td colspan="10" class="empty-state-cell">
                কোনো শিক্ষার্থীর তথ্য পাওয়া যায়নি
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Dedicated Printable Ledger Footer Signatures (Visible only when printed) -->
    <div class="print-register-footer print-only">
      <div class="sig-block">
        <div class="sig-line" />
        <span>প্রস্তুতকারী / অফিস সহকারী</span>
      </div>
      <div class="sig-block">
        <div class="sig-line" />
        <span>নাযেম / ভর্তি রেজিস্টারার</span>
      </div>
      <div class="sig-block">
        <div class="sig-line" />
        <span>মুহতামিম / অধ্যক্ষ</span>
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

const institutionName = ref('দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট')
const institutionAddress = ref('পোস্ট ও জেলা: গোপালগঞ্জ')

const registerData = ref<any[]>([
  {
    id: 1,
    admission_no: 'ADM-2026-001',
    admission_date: '০১ জানু, ২০২৬',
    raw_admission_date: '2026-01-01',
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

function formatDateBn(dateStr: string | null | undefined): string {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return dateStr
  }
}

const printDateBn = computed(() => {
  return new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' })
})

async function loadInstitution() {
  try {
    const res = await api.get('/settings/general').catch(() => null)
    if (res?.data?.name || res?.data?.institution_name) {
      institutionName.value = res.data.name || res.data.institution_name
    }
    if (res?.data?.address || res?.data?.address_bn) {
      institutionAddress.value = res.data.address || res.data.address_bn
    }
  } catch (e) {
    console.error(e)
  }
}

async function loadStudents() {
  try {
    const res = await api.get('/students?per_page=100').catch(() => null)
    const studs = res?.data?.data?.data || res?.data?.data || []
    if (studs.length > 0) {
      registerData.value = studs.map((s: any) => ({
        id: s.id,
        admission_no: s.admission_number || `ADM-${s.id}`,
        admission_date: s.admission_date ? formatDateBn(s.admission_date) : '—',
        raw_admission_date: s.admission_date || '',
        name: s.name_bn || s.name_en || 'শিক্ষার্থী',
        father_name: s.father_name_bn || s.father_name || '—',
        father_occupation: s.father_occupation || s.father_occupation_bn || '—',
        mother_name: s.mother_name || '—',
        village: s.address_bn || s.present_address || '—',
        post: s.post_office || '—',
        district: s.district || '—',
        dob: s.date_of_birth ? formatDateBn(s.date_of_birth) : '—',
        blood_group: s.blood_group || '',
        enrolled_class: s.enrollments?.[0]?.class?.name_bn || s.enrollments?.[0]?.class?.name_en || s.academic_class?.name_bn || s.academic_class?.name || s.class_name || '—'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredStudents = computed(() => {
  return registerData.value.filter(st => {
    const term = (st.admission_no + ' ' + st.name + ' ' + st.village + ' ' + st.district).toLowerCase()
    const matchesSearch = !search.value || term.includes(search.value.toLowerCase())
    const matchesYear = !yearFilter.value ||
      (st.raw_admission_date && String(st.raw_admission_date).includes(yearFilter.value)) ||
      (st.admission_date && String(st.admission_date).includes(yearFilter.value)) ||
      (st.admission_no && String(st.admission_no).includes(yearFilter.value))
    return matchesSearch && matchesYear
  })
})

onMounted(() => {
  loadInstitution()
  loadStudents()
})

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
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap');

.print-only { display: none !important; }

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
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.empty-state-cell { text-align: center; padding: 2rem; color: var(--color-text-light); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }

@media print {
  @page {
    size: A4 landscape;
    margin: 8mm 10mm;
  }

  .no-print { display: none !important; }
  .print-only { display: block !important; }

  .page-wrapper {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    background: #ffffff !important;
  }

  .table-card {
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    overflow: visible !important;
    background: transparent !important;
  }

  .table-responsive {
    overflow: visible !important;
    display: block !important;
    width: 100% !important;
  }

  .ledger-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 11px !important;
    border: 1.5px solid #222222 !important;
  }

  .ledger-table th, .ledger-table td {
    padding: 5px 6px !important;
    border: 1px solid #444444 !important;
    color: #000000 !important;
    line-height: 1.25 !important;
    background: #ffffff !important;
  }

  .ledger-table th {
    background: #e2e8f0 !important;
    font-weight: 700 !important;
    text-align: center !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    font-size: 11.5px !important;
  }

  .print-register-header {
    text-align: center;
    margin-bottom: 10px;
    border-bottom: 2px solid #145032;
    padding-bottom: 6px;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .bismillah-line {
    font-family: 'Amiri', serif;
    font-size: 14px;
    color: #145032;
    margin-bottom: 2px;
    font-weight: bold;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .institution-title {
    font-size: 18px;
    font-weight: 800;
    color: #145032;
    margin: 0 0 2px;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .institution-address {
    font-size: 11px;
    color: #475569;
    margin: 0 0 4px;
  }

  .ledger-badge-title {
    display: inline-block;
    background: #145032;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 14px;
    border-radius: 12px;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .print-meta-bar {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #1e293b;
    margin-top: 6px;
    padding: 3px 8px;
    background: #f1f5f9;
    border: 1px solid #94a3b8;
    border-radius: 4px;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .print-register-footer {
    display: flex !important;
    justify-content: space-between;
    margin-top: 25px;
    padding: 0 30px;
    page-break-inside: avoid;
  }

  .sig-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 11px;
    font-weight: 700;
    color: #000000;
  }

  .sig-line {
    width: 140px;
    border-bottom: 1.5px dashed #000000;
    margin-bottom: 4px;
  }

  .type-tag, .fund-tag {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    color: #000000 !important;
    font-weight: 600 !important;
  }

  .student-name-text {
    font-size: 11.5px !important;
  }
}
</style>
