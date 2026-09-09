<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/certificates" class="back-link"><icon name="arrow-left" /> সনদ তালিকায় ফিরে যান</NuxtLink>
        <h1>সনদপত্র প্রস্তুত ও মুদ্রণ (Sanad / Degree Certificate)</h1>
        <p class="page-subtitle">দাওরায়ে হাদীস, হিফজুল কুরআন ও কিতাব বিভাগের উত্তীর্ণ শিক্ষার্থীদের আনুষ্ঠানিক সনদপত্র</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="printSanad">
          <icon name="printer" /> সনদ প্রিন্ট করুন
        </button>
      </div>
    </div>

    <!-- Configuration Toolbar -->
    <div class="card toolbar no-print">
      <div class="filter-row">
        <div class="filter-item" v-if="studentsList.length">
          <label class="filter-label">শিক্ষার্থী সিলেক্ট করুন</label>
          <select v-model="selectedStudentId" class="form-select" @change="onStudentSelect">
            <option value="">ম্যানুয়াল এন্ট্রি</option>
            <option v-for="st in studentsList" :key="st.id" :value="st.id">
              {{ st.name_bn || st.name_en }} ({{ st.academic_class?.name || 'হিফজ' }})
            </option>
          </select>
        </div>
        <div class="filter-item">
          <label class="filter-label">শিক্ষার্থীর নাম *</label>
          <input v-model="form.student_name" class="form-input" placeholder="মুহাম্মদ সালমান ফারসি" />
        </div>
        <div class="filter-item">
          <label class="filter-label">পিতার নাম *</label>
          <input v-model="form.father_name" class="form-input" placeholder="মুহাম্মদ রফিকুল ইসলাম" />
        </div>
        <div class="filter-item">
          <label class="filter-label">গ্রাম / জেলা</label>
          <input v-model="form.address" class="form-input" placeholder="টুঙ্গিপাড়া, গোপালগঞ্জ" />
        </div>
        <div class="filter-item">
          <label class="filter-label">বিভাগ / ডিগ্রি *</label>
          <select v-model="form.degree_title" class="form-select">
            <option value="হিফজুল কুরআন সমাপনী">হিফজুল কুরআন সমাপনী সনদ</option>
            <option value="দাওরায়ে হাদীস (মাস্টার্স সমমান)">দাওরায়ে হাদীস (মাস্টার্স সমমান)</option>
            <option value="ফযীলত (স্নাতক সমমান)">ফযীলত (স্নাতক সমমান)</option>
            <option value="সানাবিয়া আম্মাহ (দাখিল সমমান)">সানাবিয়া আম্মাহ (দাখিল সমমান)</option>
          </select>
        </div>
        <div class="filter-item small">
          <label class="filter-label">প্রাপ্ত বিভাগ</label>
          <select v-model="form.division" class="form-select">
            <option value="মুমতাজ (প্রথম বিভাগ)">মুমতাজ (A+)</option>
            <option value="জায়্যিদ জিদ্দান (প্রথম শ্রেণি)">জায়্যিদ জিদ্দান (A)</option>
            <option value="জায়্যিদ (দ্বিতীয় শ্রেণি)">জায়্যিদ (A-)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Printable Islamic Sanad Preview Card -->
    <div class="sanad-container-outer">
      <div class="sanad-paper">
        <div class="sanad-border">
          <div class="sanad-inner-border">
            <!-- Header Bismillah -->
            <div class="sanad-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>

            <div class="sanad-madrasha-header">
              <h2 class="ar-madrasha-title">الجامعة الإسلامية مركز العلوم</h2>
              <h1 class="bn-madrasha-title">মারকাযুল উলুম মাদ্রাসা গোপালগঞ্জ</h1>
              <p class="madrasha-est">স্থাপিত: ১৪১৬ হিজরী / ১৯৯৫ খ্রিষ্টাব্দ · বাংলাদেশ</p>
            </div>

            <div class="sanad-badge-title">
              <span class="ar-text">شهادة التخرج</span>
              <span class="bn-text">{{ form.degree_title }}</span>
            </div>

            <!-- Sanad Text Body -->
            <div class="sanad-body-text">
              <p class="body-para">
                এই মর্মে সনদপত্র প্রদান করা যাইতেছে যে,
                <strong class="highlight-text">{{ form.student_name }}</strong>,
                পিতা: <strong class="highlight-text">{{ form.father_name }}</strong>,
                গ্রাম/থানা: <span class="highlight-text">{{ form.address }}</span>,
                তিনি অত্র প্রতিষ্ঠান হইতে <strong>{{ form.degree_title }}</strong> পরীক্ষায় অংশগ্রহণ করিয়া
                <strong class="highlight-text">{{ form.division }}</strong> স্থান সহ সাফল্যের সহিত উত্তীর্ণ হইয়াছেন।
              </p>
              <p class="body-para-dua">
                আমরা তাহার উজ্জ্বল ভবিষ্যৎ, ইলমে দ্বীনের প্রচার-প্রসার এবং জীবনে পূর্ণ সফলতা ও বরকত কামনা করি।
              </p>
            </div>

            <!-- Sanad Meta details -->
            <div class="sanad-meta-row">
              <div><strong>সনদ নং:</strong> <span class="mono">{{ form.sanad_no }}</span></div>
              <div><strong>হিঃ শিক্ষাবর্ষ:</strong> ১৪৪৭-১৪৪৮ হিঃ</div>
              <div><strong>ইস্যুর তারিখ:</strong> ২৬ আগস্ট, ২০২৬ খ্রিঃ</div>
            </div>

            <!-- Signatures Section -->
            <div class="sanad-signatures">
              <div class="sig-block">
                <div class="sig-line" />
                <span class="sig-role">পরীক্ষা নিয়ন্ত্রক</span>
              </div>
              <div class="seal-block">
                <div class="official-seal">
                  <span>মারকাযুল উলুম</span>
                  <small>অফিসিয়াল সিল</small>
                </div>
              </div>
              <div class="sig-block">
                <div class="sig-line" />
                <span class="sig-role">মুহতামিম / প্রিন্সিপাল</span>
              </div>
            </div>
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
  address: 'টুঙ্গিপাড়া, গোপালগঞ্জ',
  degree_title: 'হিফজুল কুরআন সমাপনী',
  division: 'মুমতাজ (প্রথম বিভাগ)',
  sanad_no: 'SND-2026-0842'
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
      form.address = first.present_address || form.address
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
    form.address = found.present_address || 'টুঙ্গিপাড়া, গোপালগঞ্জ'
    form.sanad_no = `SND-2026-${String(found.id).padStart(4, '0')}`
  }
}

onMounted(loadStudents)

function printSanad() {
  window.print()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Anek+Bangla:wght@400;600;700;800&display=swap');

.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.filter-row { display: flex; gap: 1rem; flex-wrap: wrap; }
.filter-item { flex: 1; min-width: 180px; display: flex; flex-direction: column; gap: 0.3rem; }
.filter-item.small { flex: 0.6; min-width: 140px; }
.filter-label { font-size: 0.8rem; font-weight: 700; color: var(--color-text); }

/* Sanad Certificate Styling */
.sanad-container-outer { margin: 2rem auto; max-width: 900px; }
.sanad-paper { background: #fffdf7; padding: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12); border-radius: 4px; color: #1e293b; }
.sanad-border { border: 4px double #d4af37; padding: 12px; }
.sanad-inner-border { border: 1.5px solid #145032; padding: 2.5rem 2rem; text-align: center; background: radial-gradient(circle, rgba(254, 252, 240, 0.6) 0%, rgba(255, 255, 255, 0.9) 100%); }

.sanad-bismillah { font-family: 'Amiri', serif; font-size: 1.6rem; color: #145032; margin-bottom: 1rem; font-weight: 700; }
.ar-madrasha-title { font-family: 'Amiri', serif; font-size: 1.4rem; color: #854d0e; margin: 0 0 0.2rem; }
.bn-madrasha-title { font-size: 1.8rem; font-weight: 800; color: #145032; margin: 0 0 0.2rem; }
.madrasha-est { font-size: 0.85rem; color: #64748b; margin: 0 0 1.5rem; }

.sanad-badge-title { display: inline-flex; flex-direction: column; align-items: center; background: linear-gradient(135deg, #145032, #0d3b24); color: #fff; padding: 0.6rem 2.5rem; border-radius: 40px; border: 2px solid #d4af37; margin-bottom: 2rem; }
.sanad-badge-title .ar-text { font-family: 'Amiri', serif; font-size: 1.1rem; }
.sanad-badge-title .bn-text { font-size: 1.2rem; font-weight: 800; }

.sanad-body-text { max-width: 680px; margin: 0 auto 2.5rem; font-size: 1.1rem; line-height: 2.1; text-align: justify; text-align-last: center; }
.highlight-text { color: #145032; font-weight: 800; text-decoration: underline dotted #d4af37; }
.body-para-dua { font-size: 0.95rem; color: #64748b; margin-top: 1rem; font-style: italic; }

.sanad-meta-row { display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1rem; margin-bottom: 3rem; font-size: 0.9rem; }
.mono { font-family: monospace; font-weight: 700; color: #145032; }

.sanad-signatures { display: flex; justify-content: space-between; align-items: flex-end; padding: 0 1.5rem; }
.sig-block { display: flex; flex-direction: column; align-items: center; }
.sig-line { width: 160px; border-bottom: 1.5px dashed #145032; margin-bottom: 0.5rem; }
.sig-role { font-size: 0.88rem; font-weight: 700; color: #145032; }

.official-seal { width: 80px; height: 80px; border-radius: 50%; border: 2px dashed #d4af37; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 0.72rem; color: #854d0e; font-weight: 700; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }

@media print {
  .no-print { display: none !important; }
  .page-wrapper { max-width: 100%; padding: 0; }
  .sanad-container-outer { margin: 0; max-width: 100%; }
  .sanad-paper { box-shadow: none; padding: 0; }
}
</style>
