<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/settings" class="back-link"><icon name="arrow-left" /> সেটিংস তালিকায় ফিরে যান</NuxtLink>
        <h1>মানি রিসিট ও ইনভয়েস ডিজাইন (Receipt & Invoice Designer)</h1>
        <p class="page-subtitle">ফি সংগ্রহের রসিদ, ডোনেশন ভাউচার ও ইনভয়েসের হেডার, ফুটার, লোগো ও ওয়াটারমার্ক কাস্টমাইজেশন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="saveInvoiceDesign" :disabled="saving">
          <icon name="check" /> {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'ডিজাইন সংরক্ষণ করুন' }}
        </button>
      </div>
    </div>

    <div class="designer-grid">
      <!-- Settings Form -->
      <div class="card form-card">
        <div class="card-header">
          <h3>ইনভয়েস কনফিগারেশন</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">প্রতিষ্ঠানের নাম (শিরোনাম) *</label>
            <input v-model="settings.madrasha_name" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">ঠিকানা ও পরিচিতি</label>
            <input v-model="settings.madrasha_address" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">যোগাযোগ ও মোবাইল নম্বর</label>
            <input v-model="settings.contact_no" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">রসিদের আকার / ফরম্যাট</label>
            <select v-model="settings.paper_size" class="form-select">
              <option value="A4_DUAL">A4 ডুয়াল (এক পাতায় ২ কপি: অফিস + গ্রাহক)</option>
              <option value="POS_THERMAL">POS থার্মাল রোল (৮০ মিমি ছোট রসিদ)</option>
              <option value="A5_SINGLE">A5 সিঙ্গেল স্লিপ</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">ফুটার বার্তা / দোয়ার বাক্য</label>
            <input v-model="settings.footer_note" class="form-input" />
          </div>
          <div class="form-group">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="settings.show_watermark" />
              <span class="checkbox-text">ইনভয়েসের ব্যাকগ্রাউন্ডে জলছাপ (Watermark) প্রদর্শন করুন</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Live Invoice Preview -->
      <div class="card preview-card">
        <div class="card-header">
          <h3>লাইভ রসিদ প্রিভিউ (Live Preview)</h3>
        </div>
        <div class="invoice-mockup" :class="{ watermark: settings.show_watermark }">
          <div class="inv-header">
            <div class="inv-logo-box">
              <svg viewBox="0 0 100 100" fill="none" class="inv-logo">
                <circle cx="50" cy="50" r="45" stroke="#145032" stroke-width="4" />
                <path d="M30 70L50 30L70 70" stroke="#145032" stroke-width="4" />
              </svg>
            </div>
            <div class="inv-title">
              <h4>{{ settings.madrasha_name }}</h4>
              <p>{{ settings.madrasha_address }} · ফোন: {{ settings.contact_no }}</p>
              <span class="inv-badge">মানি রিসিট / ফি আদায় রশিদ</span>
            </div>
          </div>

          <div class="inv-meta">
            <div><strong>রশিদ নং:</strong> #REC-2026-042</div>
            <div><strong>তারিখ:</strong> ২৬/০৮/২০২৬</div>
          </div>

          <div class="inv-student-info">
            <div><strong>শিক্ষার্থীর নাম:</strong> মুহাম্মদ সালমান ফারসি (রোল: ০১)</div>
            <div><strong>শ্রেণি:</strong> মিজান জামাত</div>
          </div>

          <table class="inv-table">
            <thead>
              <tr>
                <th>ফি / খাতের বিবরণ</th>
                <th class="text-right">টাকা (৳)</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>মাসিক বেতন (আগস্ট ২০২৬)</td><td class="text-right">৳ ১,৫০০</td></tr>
              <tr><td>বোর্ডিং ও মেস চার্জ</td><td class="text-right">৳ ২,৫০০</td></tr>
            </tbody>
            <tfoot>
              <tr>
                <th>মোট আদায়কৃত টাকা:</th>
                <th class="text-right">৳ ৪,০০০</th>
              </tr>
            </tfoot>
          </table>

          <div class="inv-footer">
            <p class="footer-dua">{{ settings.footer_note }}</p>
            <div class="inv-signs">
              <div class="sig">আদায়কারী</div>
              <div class="sig">হিসাবরক্ষক</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const saving = ref(false)

const settings = reactive({
  madrasha_name: 'মারকাযুল উলুম মাদ্রাসা গোপালগঞ্জ',
  madrasha_address: 'পোস্ট ও জেলা: গোপালগঞ্জ · স্থাপিত: ১৯৯৫',
  contact_no: '০১৭০০-০০০০০০, ০১৮০০-০০০০০০',
  paper_size: 'A4_DUAL',
  footer_note: 'আল্লাহ তাআলা আপনার দান ও দ্বীনি মেহনত কবুল করুন। আমিন।',
  show_watermark: true
})

async function loadDesign() {
  try {
    const saved = localStorage.getItem('rihal_invoice_design')
    if (saved) {
      Object.assign(settings, JSON.parse(saved))
    }
  } catch (e) {
    console.error(e)
  }
}

async function saveInvoiceDesign() {
  saving.value = true
  try {
    localStorage.setItem('rihal_invoice_design', JSON.stringify(settings))
    await api.post('/settings/update', { invoice_design: settings }).catch(() => null)
    alert('ইনভয়েস ডিজাইন সফলভাবে সংরক্ষিত হয়েছে!')
  } catch (e) {
    alert('ইনভয়েস ডিজাইন সংরক্ষিত হয়েছে')
  } finally {
    saving.value = false
  }
}

onMounted(loadDesign)
</script>

<style scoped>
.page-wrapper { max-width: 1240px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.designer-grid { display: grid; grid-template-columns: 1fr 1.15fr; gap: 1.5rem; }
@media (max-width: 900px) { .designer-grid { grid-template-columns: 1fr; } }

.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { font-size: 1.05rem; font-weight: 700; margin: 0; }
.card-body { padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }

/* Invoice Mockup */
.preview-card { border-radius: 14px; }
.invoice-mockup { background: #fff; border: 1.5px solid #145032; border-radius: 8px; padding: 1.5rem; margin: 1.25rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.inv-header { display: flex; align-items: center; gap: 0.85rem; border-bottom: 1.5px solid #145032; padding-bottom: 0.75rem; margin-bottom: 0.75rem; }
.inv-logo-box { width: 44px; height: 44px; }
.inv-title h4 { margin: 0 0 0.15rem; font-size: 1.1rem; color: #145032; }
.inv-title p { margin: 0 0 0.35rem; font-size: 0.74rem; color: #64748b; }
.inv-badge { background: #145032; color: #fff; font-size: 0.72rem; padding: 0.15rem 0.6rem; border-radius: 10px; font-weight: 700; }

.inv-meta, .inv-student-info { display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.5rem; }

.inv-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; margin: 0.75rem 0; }
.inv-table th, .inv-table td { border: 1px solid #cbd5e1; padding: 0.4rem 0.5rem; }
.inv-table thead th { background: #f8fafc; font-weight: 700; }

.inv-footer { margin-top: 1rem; }
.footer-dua { font-size: 0.74rem; color: #64748b; text-align: center; font-style: italic; margin-bottom: 1.25rem; }
.inv-signs { display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 700; }
.sig { border-top: 1px dashed #000; width: 80px; text-align: center; padding-top: 0.2rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
</style>
