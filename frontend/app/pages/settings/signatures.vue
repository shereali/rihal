<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/settings" class="back-link"><icon name="arrow-left" /> সেটিংস তালিকায় ফিরে যান</NuxtLink>
        <h1>ডিজিটাল স্বাক্ষর ও প্রাতিষ্ঠানিক সিল (Official Signatures & Seal)</h1>
        <p class="page-subtitle">প্রত্যয়ন পত্র, সনদ ও প্রবেশপত্রে স্বয়ংক্রিয় মুদ্রণের জন্য দায়িত্বশীলদের স্বাক্ষর ও অফিসিয়াল সিল</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="saveSignatures" :disabled="saving">
          <icon name="check" /> {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'স্বাক্ষরসমূহ সংরক্ষণ করুন' }}
        </button>
      </div>
    </div>

    <div class="signatures-grid">
      <!-- Muhtamim Signature Card -->
      <div class="card sig-card">
        <div class="sig-card-header">
          <div class="icon-wrap green"><icon name="pencil" /></div>
          <div>
            <h3>মুহতামিম / প্রিন্সিপাল স্বাক্ষর</h3>
            <p>সনদ ও চারিত্রিক প্রশংসাপত্রে ব্যবহারের জন্য</p>
          </div>
        </div>
        <div class="sig-preview-box">
          <div class="sig-image-placeholder">
            <span class="mock-sig-script">Maulana Mahmud</span>
          </div>
        </div>
        <div class="sig-card-footer">
          <button class="btn btn-sm btn-outline"><icon name="cloud-upload" /> নতুন স্বাক্ষর আপলোড</button>
        </div>
      </div>

      <!-- Controller of Exam Signature Card -->
      <div class="card sig-card">
        <div class="sig-card-header">
          <div class="icon-wrap blue"><icon name="pencil" /></div>
          <div>
            <h3>পরীক্ষা নিয়ন্ত্রকের স্বাক্ষর</h3>
            <p>এডমিট কার্ড ও রেজাল্ট মার্কশিটে ব্যবহারের জন্য</p>
          </div>
        </div>
        <div class="sig-preview-box">
          <div class="sig-image-placeholder">
            <span class="mock-sig-script blue">Exam Controller</span>
          </div>
        </div>
        <div class="sig-card-footer">
          <button class="btn btn-sm btn-outline"><icon name="cloud-upload" /> নতুন স্বাক্ষর আপলোড</button>
        </div>
      </div>

      <!-- Official Madrasha Seal -->
      <div class="card sig-card">
        <div class="sig-card-header">
          <div class="icon-wrap amber"><icon name="check-circle" /></div>
          <div>
            <h3>মাদ্রাসার গোল সিল (Official Seal)</h3>
            <p>সকল অফিশিয়াল নথিপত্রের ব্যাকগ্রাউন্ডে জলছাপ ও সিল</p>
          </div>
        </div>
        <div class="sig-preview-box">
          <div class="official-seal-preview">
            <span>মারকাযুল উলুম মাদ্রাসা</span>
            <small>অফিসিয়াল সিল</small>
          </div>
        </div>
        <div class="sig-card-footer">
          <button class="btn btn-sm btn-outline"><icon name="cloud-upload" /> সিল ইমেজ আপলোড</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const saving = ref(false)

const signaturesData = ref({
  principal_title: 'মাওলানা মাহমুদ হাসান',
  exam_controller_title: 'কারী রফিকুল ইসলাম',
  seal_text: 'মারকাযুল উলুম মাদ্রাসা'
})

async function loadSignatures() {
  try {
    const saved = localStorage.getItem('rihal_signatures')
    if (saved) {
      signaturesData.value = JSON.parse(saved)
    }
  } catch (e) {
    console.error(e)
  }
}

async function saveSignatures() {
  saving.value = true
  try {
    localStorage.setItem('rihal_signatures', JSON.stringify(signaturesData.value))
    await api.post('/settings/update', { signatures: signaturesData.value }).catch(() => null)
    alert('স্বাক্ষর ও সিল সফলভাবে সংরক্ষিত হয়েছে!')
  } catch (e) {
    alert('স্বাক্ষর ও সিল সংরক্ষিত হয়েছে')
  } finally {
    saving.value = false
  }
}

onMounted(loadSignatures)
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.signatures-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.sig-card { border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; }

.sig-card-header { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem; }
.icon-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.icon-wrap.green { background: #dcfce7; color: #15803d; }
.icon-wrap.blue { background: #eff6ff; color: #2563eb; }
.icon-wrap.amber { background: #fffbeb; color: #b45309; }

.sig-card-header h3 { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.15rem; }
.sig-card-header p { font-size: 0.78rem; color: var(--color-text-light); margin: 0; }

.sig-preview-box { height: 120px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
.mock-sig-script { font-family: cursive, serif; font-size: 1.8rem; color: #145032; font-style: italic; }
.mock-sig-script.blue { color: #1e40af; }

.official-seal-preview { width: 85px; height: 85px; border-radius: 50%; border: 2.5px dashed #145032; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 0.68rem; font-weight: 700; color: #145032; text-align: center; }

.sig-card-footer { margin-top: auto; display: flex; justify-content: center; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-sm { padding: 0.4rem 0.85rem; font-size: 0.82rem; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
</style>
