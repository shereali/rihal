<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <NuxtLink to="/students" class="back-link"><icon name="arrow-left" /> শিক্ষার্থী তালিকায় ফিরে যান</NuxtLink>
        <h1>খালি ভর্তি আবেদন ফরম (Printable Blank Admission Form)</h1>
        <p class="page-subtitle">নতুন শিক্ষাবর্ষে অফলাইনে শিক্ষার্থী ভর্তির জন্য প্রস্তুতকৃত আনুষ্ঠানিক ভর্তি ফরম</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/students/admission-register" class="btn btn-outline">
          <icon name="document-text" /> ভর্তি রেজিস্টার
        </NuxtLink>
        <button class="btn btn-primary" @click="printForm">
          <icon name="printer" /> ফরম প্রিন্ট করুন
        </button>
      </div>
    </div>

    <!-- Blank Form Paper -->
    <div class="form-paper-container">
      <div class="form-paper">
        <!-- Top Header -->
        <div class="form-top-header">
          <div class="bismillah-text">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>
          <div class="logo-and-title">
            <h2 class="inst-name">{{ institution.name }}</h2>
            <p class="address">{{ institution.address }}<span v-if="institution.established"> · স্থাপিত: {{ institution.established }}</span></p>
            <div class="form-main-heading">ভর্তি আবেদন ফরম (শিক্ষাবর্ষ: {{ institution.session }})</div>
          </div>
          <div class="photo-box">
            <span>পাসপোর্ট সাইজ ছবি</span>
          </div>
        </div>

        <!-- Office Use Box -->
        <div class="office-use-box">
          <div class="use-item"><strong>ফরম নং:</strong> ........................................</div>
          <div class="use-item"><strong>দাখেলা / রোল:</strong> ........................................</div>
          <div class="use-item"><strong>ভর্তিকৃত জামাত:</strong> ........................................</div>
          <div class="use-item"><strong>ভর্তি ফি:</strong> .................... ৳</div>
        </div>

        <!-- Section 1: Student Information -->
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

        <!-- Section 2: Parents Information -->
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
              <span class="label">অভিভাবকের মোবাইল:</span>
              <div class="dotted-line" />
            </div>
          </div>
          <div class="line-row">
            <span class="label">জাতীয় পরিচয়পত্র (NID):</span>
            <div class="dotted-line" />
          </div>
        </div>

        <!-- Section 3: Address & Contact -->
        <div class="form-section-title">৩. ঠিকানা ও যোগাযোগ:</div>
        <div class="form-lines-grid">
          <div class="line-row">
            <span class="label">বর্তমান ঠিকানা:</span>
            <div class="dotted-line" />
          </div>
          <div class="line-row-triple">
            <div class="line-row third">
              <span class="label">গ্রাম / মহল্লা:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row third">
              <span class="label">ডাকঘর:</span>
              <div class="dotted-line" />
            </div>
            <div class="line-row third">
              <span class="label">উপজেলা ও জেলা:</span>
              <div class="dotted-line" />
            </div>
          </div>
        </div>

        <!-- Section 4: Declaration -->
        <div class="form-declaration">
          <strong>অভিভাবকের অঙ্গীকারনামা:</strong>
          <span>আমি অঙ্গীকার করিতেছি যে, অত্র ফরমে প্রদত্ত যাবতীয় তথ্য সম্পূর্ণ সত্য ও সঠিক। আমার সন্তান অত্র মাদ্রাসার যাবতীয় নিয়ম-শৃঙ্খলা ও উস্তাদগণের অনুশাসন যথাযথ মানিয়া চলিবে। অন্যথায় যেকোনো সিদ্ধান্তের ক্ষেত্রে কর্তৃপক্ষ চূড়ান্ত সিদ্ধান্ত গ্রহণে বাধ্য থাকিবেন।</span>
        </div>

        <!-- Section 5: Signatures -->
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
            <span>মুহতামিম / অধ্যক্ষের স্বাক্ষর</span>
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
  name: 'দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট',
  address: 'পোস্ট ও জেলা: গোপালগঞ্জ',
  established: '১৯৯৫ খ্রিঃ',
  session: '২০২৬-২০২৭'
})

async function loadInstitution() {
  try {
    const res = await api.get('/settings/general').catch(() => null)
    if (res?.data?.name || res?.data?.institution_name) {
      institution.name = res.data.name || res.data.institution_name
    }
    if (res?.data?.address || res?.data?.address_bn) {
      institution.address = res.data.address || res.data.address_bn
    }
    if (res?.data?.established) {
      institution.established = res.data.established
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
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.form-paper-container { margin: 1rem auto; max-width: 820px; }
.form-paper {
  background: #ffffff;
  border: 2px solid #145032;
  border-radius: 6px;
  padding: 2.2rem 2rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  box-sizing: border-box;
}

.form-top-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #145032; padding-bottom: 0.6rem; margin-bottom: 0.85rem; }
.bismillah-text { font-family: 'Amiri', serif; font-size: 1.15rem; color: #145032; font-weight: 700; width: 90px; }
.logo-and-title { text-align: center; flex: 1; }
.logo-and-title .inst-name { font-size: 1.35rem; font-weight: 800; color: #145032; margin: 0 0 0.15rem; }
.logo-and-title .address { font-size: 0.78rem; color: #64748b; margin: 0 0 0.4rem; }
.form-main-heading { display: inline-block; background: #145032; color: #ffffff; padding: 0.25rem 1.1rem; border-radius: 16px; font-weight: 700; font-size: 0.85rem; }

.photo-box { width: 85px; height: 95px; border: 1px dashed #64748b; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; text-align: center; color: #94a3b8; border-radius: 4px; }

.office-use-box { display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #cbd5e1; padding: 0.45rem 0.85rem; font-size: 0.8rem; margin-bottom: 0.85rem; border-radius: 4px; }

.form-section-title { font-size: 0.88rem; font-weight: 800; color: #145032; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.2rem; margin: 0.75rem 0 0.45rem; }

.form-lines-grid { display: flex; flex-direction: column; gap: 0.55rem; }
.line-row { display: flex; align-items: flex-end; gap: 0.45rem; font-size: 0.82rem; }
.line-row .label { white-space: nowrap; font-weight: 600; color: #1e293b; }
.dotted-line { flex: 1; border-bottom: 1px dotted #64748b; min-height: 14px; }

.line-row-dual { display: flex; gap: 1.25rem; }
.line-row.half { flex: 1; }
.line-row-triple { display: flex; gap: 0.85rem; }
.line-row.third { flex: 1; }

.form-declaration { background: #fefce8; border: 1px solid #fef08a; padding: 0.55rem 0.75rem; font-size: 0.75rem; line-height: 1.45; margin: 0.85rem 0; border-radius: 4px; }
.form-declaration strong { display: block; margin-bottom: 0.15rem; color: #854d0e; }

.form-footer-signatures { display: flex; justify-content: space-between; margin-top: 2rem; }
.sig-item { display: flex; flex-direction: column; align-items: center; font-size: 0.78rem; font-weight: 700; color: #1e293b; }
.sig-item .line { width: 140px; border-bottom: 1px dashed #000; margin-bottom: 0.35rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }

@media print {
  @page {
    size: A4 portrait;
    margin: 6mm 8mm;
  }

  .no-print { display: none !important; }

  .page-wrapper {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    background: #ffffff !important;
  }

  .form-paper-container {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .form-paper {
    box-shadow: none !important;
    border: 2px solid #145032 !important;
    border-radius: 4px !important;
    padding: 1.1rem 1.35rem !important;
    margin: 0 auto !important;
    width: 100% !important;
    box-sizing: border-box !important;
    page-break-inside: avoid !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .form-top-header {
    border-bottom: 2px solid #145032 !important;
    padding-bottom: 0.4rem !important;
    margin-bottom: 0.55rem !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .bismillah-text {
    font-size: 1.1rem !important;
    color: #145032 !important;
  }

  .logo-and-title .inst-name {
    font-size: 1.3rem !important;
    color: #145032 !important;
  }

  .logo-and-title .address {
    font-size: 0.72rem !important;
    color: #475569 !important;
    margin-bottom: 0.3rem !important;
  }

  .form-main-heading {
    background: #145032 !important;
    color: #ffffff !important;
    font-size: 0.8rem !important;
    padding: 0.2rem 0.9rem !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .photo-box {
    width: 80px !important;
    height: 90px !important;
    border: 1px dashed #475569 !important;
    font-size: 0.7rem !important;
  }

  .office-use-box {
    background: #f1f5f9 !important;
    border: 1px solid #94a3b8 !important;
    padding: 0.35rem 0.75rem !important;
    font-size: 0.76rem !important;
    margin-bottom: 0.55rem !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .form-section-title {
    font-size: 0.82rem !important;
    color: #145032 !important;
    border-bottom: 1px solid #cbd5e1 !important;
    padding-bottom: 0.15rem !important;
    margin: 0.55rem 0 0.35rem !important;
  }

  .form-lines-grid {
    gap: 0.42rem !important;
  }

  .line-row {
    font-size: 0.78rem !important;
  }

  .dotted-line {
    border-bottom: 1px dotted #475569 !important;
    min-height: 12px !important;
  }

  .form-declaration {
    background: #fefce8 !important;
    border: 1px solid #fef08a !important;
    padding: 0.45rem 0.65rem !important;
    font-size: 0.72rem !important;
    line-height: 1.38 !important;
    margin: 0.65rem 0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .form-footer-signatures {
    margin-top: 1.75rem !important;
    page-break-inside: avoid !important;
  }

  .sig-item {
    font-size: 0.74rem !important;
    color: #000000 !important;
  }

  .sig-item .line {
    width: 135px !important;
    border-bottom: 1px dashed #000000 !important;
  }
}
</style>
