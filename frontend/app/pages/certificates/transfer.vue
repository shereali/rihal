<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/certificates" class="back-link"><icon name="arrow-left" /> সনদ তালিকায় ফিরে যান</NuxtLink>
        <h1>ছাড়পত্র প্রস্তুত ও ছাড়পত্র রেজিস্টার (Transfer Certificate)</h1>
        <p class="page-subtitle">শিক্ষার্থীর প্রতিষ্ঠান পরিবর্তনের আনুষ্ঠানিক ছাড়পত্র ও অনাপত্তিপত্র প্রস্তুতকরণ</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="printTC">
          <icon name="printer" /> ছাড়পত্র প্রিন্ট করুন
        </button>
      </div>
    </div>

    <!-- Configuration Form -->
    <div class="card toolbar no-print">
      <div class="form-grid">
        <div class="form-group" v-if="studentsList.length">
          <label class="form-label">শিক্ষার্থী সিলেক্ট করুন</label>
          <select v-model="selectedStudentId" class="form-select" @change="onStudentSelect">
            <option value="">ম্যানুয়াল এন্ট্রি</option>
            <option v-for="st in studentsList" :key="st.id" :value="st.id">
              {{ st.name_bn || st.name_en }} ({{ st.academic_class?.name || 'হিফজ' }})
            </option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">শিক্ষার্থীর নাম *</label>
          <input v-model="form.student_name" class="form-input" placeholder="মুহাম্মদ সালমান ফারসি" />
        </div>
        <div class="form-group">
          <label class="form-label">পিতার নাম *</label>
          <input v-model="form.father_name" class="form-input" placeholder="মুহাম্মদ রফিকুল ইসলাম" />
        </div>
        <div class="form-group">
          <label class="form-label">অধ্যয়নরত শ্রেণি *</label>
          <input v-model="form.class_name" class="form-input" placeholder="মিজান জামাত" />
        </div>
        <div class="form-group">
          <label class="form-label">ভর্তি নম্বর / দাখেলা নং</label>
          <input v-model="form.admission_no" class="form-input" placeholder="ADM-2024-102" />
        </div>
        <div class="form-group">
          <label class="form-label">ছাড়ের কারণ *</label>
          <select v-model="form.reason" class="form-select">
            <option value="অভিভাবকের বাসস্থান পরিবর্তন">অভিভাবকের বাসস্থান পরিবর্তন</option>
            <option value="উচ্চ শিক্ষার উদ্দেশ্যে অন্য প্রতিষ্ঠানে ভর্তি">উচ্চ শিক্ষার উদ্দেশ্যে অন্য প্রতিষ্ঠানে ভর্তি</option>
            <option value="পারিবারিক অসুবিধা">পারিবারিক অসুবিধা</option>
            <option value="কোর্স সফলভাবে সমাপ্ত">কোর্স সফলভাবে সমাপ্ত</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">আচরণ ও স্বভাব</label>
          <select v-model="form.conduct" class="form-select">
            <option value="উত্তম ও প্রশংসনীয়">উত্তম ও প্রশংসনীয়</option>
            <option value="সন্তোষজনক">সন্তোষজনক</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Printable Transfer Certificate Paper -->
    <div class="tc-container-outer">
      <div class="tc-paper">
        <div class="tc-header">
          <h2>মারকাযুল উলুম মাদ্রাসা গোপালগঞ্জ</h2>
          <p class="tc-address">পোস্ট ও জেলা: গোপালগঞ্জ · স্থাপিত: ১৯৯৫</p>
          <div class="tc-title-badge">ছাড়পত্র / অনাপত্তিপত্র (TRANSFER CERTIFICATE)</div>
        </div>

        <div class="tc-meta-row">
          <div><strong>স্মারক নং:</strong> TC-2026-{{ form.admission_no.slice(-3) }}</div>
          <div><strong>তারিখ:</strong> ২৬ আগস্ট, ২০২৬</div>
        </div>

        <div class="tc-body-content">
          <p>
            এই মর্মে প্রত্যয়ন করা যাইতেছে যে, <strong>{{ form.student_name }}</strong>,
            পিতা: <strong>{{ form.father_name }}</strong>,
            তিনি অত্র প্রতিষ্ঠানের <strong>{{ form.class_name }}</strong> শ্রেণির একজন নিয়মিত ছাত্র ছিলেন। তাহার ভর্তি নম্বর <strong>{{ form.admission_no }}</strong>।
          </p>
          <p>
            অত্র প্রতিষ্ঠানে অধ্যয়নকালে তাহার স্বভাব ও চরিত্র <strong>{{ form.conduct }}</strong> ছিল।
            তিনি প্রতিষ্ঠানের যাবতীয় আর্থিক পাওনাদি পরিশোধ করিয়াছেন।
          </p>
          <p>
            <strong>"{{ form.reason }}"</strong> কারণ দর্শাইয়া তাহার অভিভাবকের লিখিত আবেদনের প্রেক্ষিতে অত্র ছাড়পত্র প্রদান করা হইল।
          </p>
          <p class="dua-text">আমরা তাহার ভবিষ্যৎ জীবনের উত্তরোত্তর মঙ্গল ও সাফল্য কামনা করি।</p>
        </div>

        <div class="tc-footer-signatures">
          <div class="sig-block">
            <div class="sig-line" />
            <span>হিসাবরক্ষক</span>
          </div>
          <div class="sig-block">
            <div class="sig-line" />
            <span>শ্রেণি শিক্ষক</span>
          </div>
          <div class="sig-block">
            <div class="sig-line" />
            <span>মুহতামিম / প্রধান শিক্ষক</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const studentsList = ref<any[]>([])
const selectedStudentId = ref<any>('')

const form = reactive({
  student_name: 'মুহাম্মদ সালমান ফারসি',
  father_name: 'মুহাম্মদ রফিকুল ইসলাম',
  class_name: 'মিজান জামাত',
  admission_no: 'ADM-2024-102',
  reason: 'অভিভাবকের বাসস্থান পরিবর্তন',
  conduct: 'উত্তম ও প্রশংসনীয়'
})

async function loadStudents() {
  try {
    const res = await api.get('/students?per_page=50').catch(() => null)
    const studs = res?.data?.data?.data || res?.data?.data || []
    if (studs.length > 0) {
      studentsList.value = studs
      const first = studs[0]
      form.student_name = first.name_bn || first.name_en || form.student_name
      form.father_name = first.father_name_bn || first.father_name || form.father_name
      form.class_name = first.academic_class?.name || form.class_name
      form.admission_no = first.admission_number || `ADM-2024-${first.id}`
      selectedStudentId.value = first.id
    }
  } catch (e) {
    console.error(e)
  }
}

function onStudentSelect() {
  const found = studentsList.value.find(s => s.id === Number(selectedStudentId.value))
  if (found) {
    form.student_name = found.name_bn || found.name_en || ''
    form.father_name = found.father_name_bn || found.father_name || 'মুহাম্মদ রফিকুল ইসলাম'
    form.class_name = found.academic_class?.name || 'হিফজ'
    form.admission_no = found.admission_number || `ADM-2024-${found.id}`
  }
}

onMounted(loadStudents)

function printTC() {
  window.print()
}
</script>

<style scoped>
.page-wrapper { max-width: 1100px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; }

.tc-container-outer { margin: 2rem auto; max-width: 800px; }
.tc-paper { background: #fff; border: 2px solid #145032; border-radius: 8px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.tc-header { text-align: center; border-bottom: 2px solid #145032; padding-bottom: 1rem; margin-bottom: 1.25rem; }
.tc-header h2 { font-size: 1.5rem; font-weight: 800; color: #145032; margin: 0 0 0.2rem; }
.tc-address { font-size: 0.84rem; color: var(--color-text-light); margin: 0 0 0.6rem; }
.tc-title-badge { display: inline-block; background: #145032; color: #fff; padding: 0.3rem 1.2rem; border-radius: 20px; font-weight: 700; font-size: 0.9rem; }

.tc-meta-row { display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 2rem; }
.tc-body-content { font-size: 1.05rem; line-height: 2.2; text-align: justify; margin-bottom: 3.5rem; }
.dua-text { font-style: italic; color: #64748b; margin-top: 1.5rem; text-align: center; }

.tc-footer-signatures { display: flex; justify-content: space-between; padding-top: 2rem; }
.sig-block { display: flex; flex-direction: column; align-items: center; }
.sig-line { width: 140px; border-bottom: 1.5px dashed #000; margin-bottom: 0.4rem; }
.sig-block span { font-size: 0.84rem; font-weight: 700; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }

@media print {
  .no-print { display: none !important; }
  .page-wrapper { max-width: 100%; padding: 0; }
  .tc-container-outer { margin: 0; max-width: 100%; }
  .tc-paper { box-shadow: none; border: none; }
}
</style>
