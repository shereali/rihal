<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/students" class="back-link"><icon name="arrow-left" /> শিক্ষার্থী তালিকায় ফিরে যান</NuxtLink>
        <h1>খালি ভর্তি আবেদন ফরম (Printable Blank Admission Form)</h1>
        <p class="page-subtitle">নতুন শিক্ষাবর্ষে অফলাইনে শিক্ষার্থী ভর্তির জন্য প্রস্তুতকৃত আনুষ্ঠানিক ভর্তি ফরম</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="printForm">
          <icon name="printer" /> ফরম প্রিন্ট করুন
        </button>
      </div>
    </div>

    <!-- Blank Form Paper -->
    <div class="form-paper-container">
      <div class="form-paper">
        <div class="form-top-header">
          <div class="bismillah-text">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>
          <div class="logo-and-title">
            <h2>{{ institution.name }}</h2>
            <p class="address">{{ institution.address }} · স্থাপিত: {{ institution.established }}</p>
            <div class="form-main-heading">ভর্তি আবেদন ফরম (সেশন: {{ institution.session }})</div>
          </div>
          <div class="photo-box">
            <span>পাসপোর্ট সাইজ ছবি</span>
          </div>
        </div>

        <div class="office-use-box">
          <div class="use-item"><strong>ফরম নম্বর:</strong> ........................................</div>
          <div class="use-item"><strong>দাখেলা / রোল:</strong> ........................................</div>
          <div class="use-item"><strong>ভর্তিকৃত জামাত:</strong> ........................................</div>
        </div>

        <div class="form-section-title">১. শিক্ষার্থীর ব্যক্তিগত তথ্য:</div>
        <div class="form-lines-grid">
          <div class="line-row">
            <span class="label">শিক্ষার্থীর নাম (বাংলা):</span>
            <div class="dotted-line" />
          </div>
          <div class="line-row">
            <span class="label">শিক্ষার্থীর নাম (ইংরেজি):</span>
            <div class="dotted-line" />
          </div>
          <div class="line-row-dual">
            <div class="line-row half">
              <span class="label">জন্ম তারিখ:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row half">
              <span class="label">জন্ম নিবন্ধন নম্বর:</span>
              <div class="dotted-line" />
            </div>
          </div>
          <div class="line-row-dual">
            <div class="line-row half">
              <span class="label">রক্তের গ্রুপ:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row half">
              <span class="label">ভর্তিচ্ছু শ্রেণি / বিভাগ:</span>
              <div class="dotted-line" />
            </div>
          </div>
        </div>

        <div class="form-section-title">২. পিতা ও মাতার তথ্য:</div>
        <div class="form-lines-grid">
          <div class="line-row-dual">
            <div class="line-row half">
              <span class="label">পিতার নাম:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row half">
              <span class="label">পিতার পেশা:</span>
              <div class="dotted-line" />
            </div>
          </div>
          <div class="line-row-dual">
            <div class="line-row half">
              <span class="label">মাতার নাম:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row half">
              <span class="label">মোবাইল নম্বর:</span>
              <div class="dotted-line" />
            </div>
          </div>
          <div class="line-row">
            <span class="label">জাতীয় পরিচয়পত্র (NID):</span>
            <div class="dotted-line" />
          </div>
        </div>

        <div class="form-section-title">৩. ঠিকানা ও যোগাযোগ:</div>
        <div class="form-lines-grid">
          <div class="line-row">
            <span class="label">বর্তমান ঠিকানা:</span>
            <div class="dotted-line" />
          </div>
          <div class="line-row-triple">
            <div class="line-row third">
              <span class="label">গ্রাম:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row third">
              <span class="label">ডাকঘর:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row third">
              <span class="label">থানা ও জেলা:</span>
              <div class="dotted-line" />
            </div>
          </div>
        </div>

        <div class="form-declaration">
          <strong>অভিভাবকের অঙ্গীকারনামা:</strong>
          <p>আমি অঙ্গীকার করিতেছি যে, অত্র ফরমে প্রদত্ত যাবতীয় তথ্য সম্পূর্ণ সত্য। আমার সন্তান মাদ্রাসার সকল নিয়ম-শৃঙ্খলা ও উস্তাদগণের অনুশাসন মানিয়া চলিবে। অন্যথায় কর্তৃপক্ষ যেকোনো সিদ্ধান্ত গ্রহণে বাধ্য থাকিবেন।</p>
        </div>

        <div class="form-footer-signatures">
          <div class="sig-item">
            <div class="line" />
            <span>অভিভাবকের স্বাক্ষর ও তারিখ</span>
          </div>
          <div class="sig-item">
            <div class="line" />
            <span>ভর্তি কমিটির স্বাক্ষর</span>
          </div>
          <div class="sig-item">
            <div class="line" />
            <span>মুহতামিম / প্রধানের স্বাক্ষর</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()

const institution = reactive({
  name: 'মারকাযুল উলুম মাদ্রাসা গোপালগঞ্জ',
  address: 'পোস্ট ও জেলা: গোপালগঞ্জ',
  established: '১৯৯৫ খ্রিঃ',
  session: '২০২৬-২০২৭'
})

async function loadInstitution() {
  try {
    const res = await api.get('/settings/general').catch(() => null)
    if (res?.data?.name) {
      institution.name = res.data.name
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadInstitution)

function printForm() {
  window.print()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap');

.page-wrapper { max-width: 1000px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.form-paper-container { margin: 1rem auto; max-width: 820px; }
.form-paper { background: #fff; border: 2px solid #145032; border-radius: 4px; padding: 2.5rem 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }

.form-top-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #145032; padding-bottom: 0.75rem; margin-bottom: 1rem; }
.bismillah-text { font-family: 'Amiri', serif; font-size: 1.2rem; color: #145032; font-weight: 700; width: 80px; }
.logo-and-title { text-align: center; flex: 1; }
.logo-and-title h2 { font-size: 1.4rem; font-weight: 800; color: #145032; margin: 0 0 0.15rem; }
.logo-and-title .address { font-size: 0.78rem; color: #64748b; margin: 0 0 0.4rem; }
.form-main-heading { display: inline-block; background: #145032; color: #fff; padding: 0.25rem 1rem; border-radius: 16px; font-weight: 700; font-size: 0.88rem; }

.photo-box { width: 90px; height: 100px; border: 1px dashed #64748b; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; text-align: center; color: #94a3b8; }

.office-use-box { display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; font-size: 0.82rem; margin-bottom: 1.25rem; border-radius: 4px; }

.form-section-title { font-size: 0.95rem; font-weight: 800; color: #145032; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.25rem; margin: 1rem 0 0.65rem; }

.form-lines-grid { display: flex; flex-direction: column; gap: 0.75rem; }
.line-row { display: flex; align-items: flex-end; gap: 0.5rem; font-size: 0.86rem; }
.line-row .label { white-space: nowrap; font-weight: 600; }
.dotted-line { flex: 1; border-bottom: 1px dotted #64748b; min-height: 16px; }

.line-row-dual { display: flex; gap: 1.5rem; }
.line-row.half { flex: 1; }
.line-row-triple { display: flex; gap: 1rem; }
.line-row.third { flex: 1; }

.form-declaration { background: #fefce8; border: 1px solid #fef08a; padding: 0.75rem; font-size: 0.78rem; line-height: 1.5; margin: 1.5rem 0; border-radius: 4px; }

.form-footer-signatures { display: flex; justify-content: space-between; margin-top: 3rem; }
.sig-item { display: flex; flex-direction: column; align-items: center; font-size: 0.78rem; font-weight: 700; }
.sig-item .line { width: 150px; border-bottom: 1px dashed #000; margin-bottom: 0.35rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }

@media print {
  .no-print { display: none !important; }
  .page-wrapper { max-width: 100%; padding: 0; }
  .form-paper { box-shadow: none; border: none; padding: 0; }
}
</style>
