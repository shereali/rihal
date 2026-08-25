<template>
  <div class="orphan-detail-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/orphan-sponsorship" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1 v-if="orphan">অর্ফান: {{ orphan.name_bn }}</h1>
        <p v-else class="text-muted">অর্ফান লোড হচ্ছে...</p>
      </div>
      <span v-if="orphan" class="badge" :class="statusClass(orphan.sponsorship_status)">
        {{ statusLabel(orphan.sponsorship_status) }}
      </span>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <div v-if="orphan" class="detail-grid">
      <!-- Orphan Info -->
      <div class="card">
        <h3>অর্ফানের তথ্য</h3>
        <dl class="info-list">
          <div><dt>নাম (বাংলা)</dt><dd>{{ orphan.name_bn }}</dd></div>
          <div v-if="orphan.name_en"><dt>নাম (ইংরেজি)</dt><dd>{{ orphan.name_en }}</dd></div>
          <div><dt>জন্মতারিখ</dt><dd>{{ orphan.birth_date || '-' }}</dd></div>
          <div><dt>বয়স</dt><dd>{{ orphan.birth_date ? calculateAge(orphan.birth_date) : '-' }}</dd></div>
          <div><dt>লিঙ্গ</dt><dd>{{ genderLabel(orphan.gender) }}</dd></div>
          <div><dt>শ্রেণি</dt><dd>{{ orphan.class_id || '-' }}</dd></div>
          <div><dt>বড়/অভিভাবক</dt><dd>{{ orphan.guardian_name_bn || orphan.guardian_name_en || '-' }}</dd></div>
          <div><dt>ফোন</dt><dd>{{ orphan.guardian_phone || '-' }}</dd></div>
          <div><dt>ঠিকানা</dt><dd>{{ orphan.address_bn || orphan.address_en || '-' }}</dd></div>
          <div><dt>মাসিক স্পন্সরশিপ (৳)</dt><dd>{{ orphan.monthly_amount ? Number(orphan.monthly_amount).toLocaleString('bn-BD') : 0 }}</dd></div>
          <div><dt>মোট স্পন্সরড (৳)</dt><dd>{{ orphan.total_sponsored ? Number(orphan.total_sponsored).toLocaleString('bn-BD') : 0 }}</dd></div>
          <div><dt>স্পন্সর</dt><dd>{{ (orphan.sponsors || []).map(s => s.name_bn || s.name_en).join(', ') || 'অভাজন' }}</dd></div>
          <div v-if="orphan.story"><dt>গল্প</dt><dd>{{ orphan.story }}</dd></div>
          <div><dt>সৃষ্টির তারিখ</dt><dd>{{ formatDate(orphan.created_at) }}</dd></div>
        </dl>
      </div>

      <div class="card sponsor-card">
        <h3>একাধিক স্পন্সর ব্যবস্থাপনা</h3>
        <div class="sponsor-list">
          <div v-for="item in sponsorships" :key="item.id" class="sponsor-row">
            <div><strong>{{ item.donor?.name_bn || item.donor?.name_en }}</strong><small>মাসিক ৳{{ money(item.monthly_commitment) }} · {{ item.status === 'active' ? 'সক্রিয়' : 'সমাপ্ত' }}</small></div>
            <button v-if="item.status === 'active'" type="button" class="btn btn-sm btn-outline" @click="endSponsorship(item.id)">সমাপ্ত করুন</button>
          </div>
          <p v-if="!sponsorships.length" class="text-muted">কোনো স্পন্সর যুক্ত নেই।</p>
        </div>
        <form class="payment-form sponsor-form" @submit.prevent="addSponsor">
          <div class="form-row">
            <div class="form-group"><label>দাতা</label><select v-model="sponsorForm.donor_id" required><option value="">দাতা নির্বাচন</option><option v-for="donor in donors" :key="donor.id" :value="donor.id">{{ donor.name_bn || donor.name_en }}</option></select></div>
            <div class="form-group"><label>মাসিক অঙ্গীকার (৳)</label><input v-model.number="sponsorForm.monthly_commitment" type="number" min="0" required /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>শুরুর তারিখ</label><input v-model="sponsorForm.starts_at" type="date" required /></div>
            <div class="form-group"><label>অংশ (%)</label><input v-model.number="sponsorForm.share_percent" type="number" min="0" max="100" /></div>
          </div>
          <button type="submit" class="btn btn-primary" :disabled="loading">স্পন্সর যুক্ত করুন</button>
        </form>
      </div>

      <!-- Record Payment -->
      <div class="card">
        <h3>স্পন্সরশিপ প্রদান রেকর্ড</h3>
        <form @submit.prevent="recordPayment" class="payment-form">
          <div class="form-group">
            <label>স্পন্সরশিপ *</label>
            <select v-model="payment.orphan_sponsorship_id" required :disabled="loading">
              <option value="">স্পন্সর নির্বাচন করুন</option>
              <option v-for="item in activeSponsorships" :key="item.id" :value="String(item.id)">{{ item.donor?.name_bn || item.donor?.name_en }} — ৳{{ money(item.monthly_commitment) }}/মাস</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>পরিমাণ (৳) *</label>
              <input v-model.number="payment.amount" type="number" min="1" step="0.01" placeholder="0" :disabled="loading" />
            </div>
            <div class="form-group">
              <label>তারিখ</label>
              <input v-model="payment.payment_date" type="date" :disabled="loading" />
            </div>
          </div>
          <div class="form-group">
            <label> উদ্দেশ্য</label>
            <input v-model="payment.purpose_bn" type="text" placeholder="যেমন: মাসিক খরচ, বইয়ের দরকারি আইটেম" :disabled="loading" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>পদ্ধতি</label>
              <select v-model="payment.payment_method" :disabled="loading">
                <option value="নগদ">নগদ</option>
                <option value="ব্যাংক">ব্যাংক</option>
                <option value="মুদ্রা">মুদ্রা</option>
                <option value="চেক">চেক</option>
              </select>
            </div>
            <div class="form-group">
              <label>রেফারেন্স</label>
              <input v-model="payment.reference" type="text" :disabled="loading" />
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="loading || !payment.amount">
              <span v-if="loading" class="spinner"></span>
              <span v-else>প্রদান রেকর্ড</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Payment History -->
      <div class="card">
        <h3>প্রদান ইতিবোধ্য</h3>
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(payments || []).length === 0" class="empty-state"><p>কোনো প্রদান নেই</p></div>
        <table v-else class="table table-hover">
          <thead><tr><th>তারিখ</th><th>পরিমাণ (৳)</th><th> উদ্দেশ্য</th><th>পদ্ধতি</th><th>রেফারেন্স</th></tr></thead>
          <tbody>
            <tr v-for="p in payments" :key="p.id">
              <td>{{ p.payment_date }}</td>
              <td>{{ p.amount ? Number(p.amount).toLocaleString('bn-BD') : 0 }}</td>
              <td>{{ p.purpose_bn || '-' }}</td>
              <td>{{ p.payment_method || '-' }}</td>
              <td>{{ p.reference || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(false)
const orphan = ref<any>(null)
const payments = ref<any[]>([])
const sponsorships = ref<any[]>([])
const donors = ref<any[]>([])
const activeSponsorships = computed(() => sponsorships.value.filter(item => item.status === 'active'))
const error = ref('')
const success = ref('')

const payment = ref({
  orphan_sponsorship_id: '',
  amount: null,
  payment_date: new Date().toISOString().split('T')[0],
  purpose_bn: '',
  payment_method: 'নগদ',
  reference: '',
})
const sponsorForm = ref({
  donor_id: '',
  monthly_commitment: 0,
  share_percent: null as number | null,
  starts_at: new Date().toISOString().split('T')[0],
})

async function loadOrphan() {
  loading.value = true
  try {
    const id = route.params.id
    const [orphanResponse, sponsorResponse, donorResponse] = await Promise.all([
      api.get(`/orphans/${id}`),
      api.get(`/orphans/${id}/sponsors`),
      api.get('/orphans/sponsors'),
    ])
    orphan.value = orphanResponse.data?.data
    payments.value = orphan.value?.payments || []
    sponsorships.value = sponsorResponse.data?.data || []
    donors.value = donorResponse.data?.data || []
    if (!payment.value.orphan_sponsorship_id && activeSponsorships.value.length === 1) payment.value.orphan_sponsorship_id = String(activeSponsorships.value[0].id)
  } catch (e: any) {
    error.value = 'অর্ফান লোড করা যায়নি'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function addSponsor() {
  loading.value = true
  error.value = ''
  try {
    await api.post(`/orphans/${orphan.value.id}/sponsors`, sponsorForm.value)
    sponsorForm.value = { donor_id: '', monthly_commitment: 0, share_percent: null, starts_at: new Date().toISOString().split('T')[0] }
    success.value = 'স্পন্সর যুক্ত হয়েছে।'
    await loadOrphan()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'স্পন্সর যুক্ত করা যায়নি।'
  } finally { loading.value = false }
}
async function endSponsorship(id: number) {
  if (!confirm('এই স্পন্সরশিপ সমাপ্ত করবেন?')) return
  await api.delete(`/orphans/${orphan.value.id}/sponsors/${id}`)
  await loadOrphan()
}
function money(value: unknown) {
  return Number(value || 0).toLocaleString('bn-BD', { maximumFractionDigits: 2 })
}

async function recordPayment() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const r = await api.post(`/orphans/${orphan.value.id}/payments`, { ...payment.value })
    orphan.value = r.data?.data
    payments.value = orphan.value?.payments || []
    success.value = 'স্পন্সরশিপ প্রদান সফল!'
    payment.value = { orphan_sponsorship_id: payment.value.orphan_sponsorship_id, amount: null, payment_date: new Date().toISOString().split('T')[0], purpose_bn: '', payment_method: 'নগদ', reference: '' }
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'প্রদান রেকর্ড করা যায়নি'
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('bn-BD', { year: 'numeric', month: 'short', day: 'numeric' })
}

function calculateAge(birthDate: string): string {
  if (!birthDate) return '-'
  const age = Math.floor((Date.now() - new Date(birthDate).getTime()) / (365.25 * 24 * 60 * 60 * 1000))
  return `${age} বছর`
}

function genderLabel(g: string): string {
  switch (g) {
    case 'male': return 'পুরুষ'
    case 'female': return 'মহিলা'
    default: return 'অন্যান্য'
  }
}

function statusClass(status: string): string {
  switch (status) {
    case 'completed': return 'badge-success'
    case 'sponsored': return 'badge-outline'
    case 'closed': return 'badge-secondary'
    default: return 'badge-warning'
  }
}

function statusLabel(status: string): string {
  switch (status) {
    case 'completed': return 'সম্পূর্ণ'
    case 'sponsored': return 'স্পন্সরড'
    case 'closed': return 'বন্ধ'
    default: return 'অপেক্ষমান'
  }
}

onMounted(loadOrphan)
</script>

<style scoped>
.orphan-detail-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { margin: 0; font-size: 1.25rem; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.text-muted { color: var(--color-text-light); }
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1.25rem; }
.sponsor-card { grid-column: 1 / -1; }
.sponsor-list { display:grid;gap:.65rem;padding:0 1.25rem 1rem; }
.sponsor-row { display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:.8rem;border:1px solid var(--color-border-light);border-radius:10px; }
.sponsor-row div { display:grid;gap:.2rem; }.sponsor-row small { color:var(--color-text-light); }
.sponsor-form { border-top:1px solid var(--color-border-light); }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; margin-bottom: 1.25rem; }
.card h3 { margin: 0 0 1rem; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--color-border-light); font-size: 1.05rem; font-family: 'Noto Sans Bengali', sans-serif; }
.card-body { padding: 1.25rem; }
.info-list { display: grid; grid-template-columns: 140px 1fr; gap: 0.5rem 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.info-list dt { color: var(--color-text-light); font-size: 0.85rem; }
.info-list dd { margin: 0; font-size: 0.95rem; }
.badge { padding: 0.2rem 0.6rem; border-radius: 10px; font-size: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; white-space: nowrap; }
.badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.badge-warning { background: rgba(234, 179, 8, 0.15); color: #d97706; }
.badge-secondary { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.badge-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text-light); }
.payment-form .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 0.75rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group input, .form-group select {
  padding: 0.55rem 0.8rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 0.9rem;
  font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg);
}
.form-actions { margin-top: 0.5rem; }
.btn { padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; display: inline-flex; align-items: center; gap: 0.35rem; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.spinner { width: 14px; height: 14px; border: 2px solid var(--color-text-on-primary); border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.spinner { width: 20px; height: 20px; }
.empty-state { padding: 1.5rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.table { width: 100%; border-collapse: collapse; font-family: 'Noto Sans Bengali', sans-serif; }
.table th, .table td { padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border-light); }
.table th { font-weight: 600; font-size: 0.8rem; color: var(--color-text-light); }
.alert { padding: 0.6rem 0.9rem; border-radius: 8px; margin-bottom: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: #fde2e2; color: var(--color-error); }
.alert-success { background: #dcfce8; color: #16a34a; }
</style>
