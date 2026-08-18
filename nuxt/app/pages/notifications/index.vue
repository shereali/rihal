<template>
  <div class="notif-page">
    <div class="page-header">
      <h1>নোটিফিকেশন</h1>
      <p class="text-muted">অভিভাবকদের অনুপস্থিতি ও ফি-বকেয়া নোটিফিকেশন পাঠান</p>
    </div>

    <div class="tabs">
      <button class="tab" :class="{ active: tab === 'absence' }" @click="tab = 'absence'; clearSelection()">অনুপস্থিতি</button>
      <button class="tab" :class="{ active: tab === 'fee' }" @click="tab = 'fee'; clearSelection()">ফি-বকেয়া</button>
      <button class="tab" :class="{ active: tab === 'sent' }" @click="tab = 'sent'; loadSent()">পাঠানো তালিকা</button>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>
    <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>

    <!-- Absence: pick class -> absent students today -->
    <div v-if="tab === 'absence' && !loading" class="card">
      <div class="card-header"><h3>আজকের অনুপস্থিত ছাত্র (শ্রেণি নির্বাচন)</h3></div>
      <div class="card-body">
        <div class="form-row">
          <select v-model="classId" @change="loadAbsent" class="form-select">
            <option value="">শ্রেণি নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
          </select>
        </div>
        <div v-if="absentList.length" class="select-all">
          <label><input type="checkbox" :checked="allSelected" @change="toggleAll" /> সব নির্বাচন</label>
        </div>
        <ul class="pick-list">
          <li v-for="a in absentList" :key="a.id">
            <label>
              <input type="checkbox" v-model="selectedAbsence" :value="a.id" />
              {{ a.student_name }} — {{ a.date }}
            </label>
          </li>
        </ul>
        <button class="btn btn-primary" :disabled="!selectedAbsence.length || sending" @click="sendAbsence">পাঠান ({{ selectedAbsence.length }})</button>
      </div>
    </div>

    <!-- Fee due: unpaid list -->
    <div v-if="tab === 'fee' && !loading" class="card">
      <div class="card-header"><h3>বকেয়া ফি (অর্থপ্রদান করা হয়নি)</h3></div>
      <div class="card-body">
        <div v-if="feeList.length" class="select-all">
          <label><input type="checkbox" :checked="allFeeSelected" @change="toggleAllFee" /> সব নির্বাচন</label>
        </div>
        <ul class="pick-list">
          <li v-for="f in feeList" :key="f.id">
            <label>
              <input type="checkbox" v-model="selectedFee" :value="f.id" />
              {{ f.student_name }} — বকেয়া ৳{{ Number(f.balance).toLocaleString('bn-BD') }}
            </label>
          </li>
        </ul>
        <div v-if="!feeList.length" class="text-muted">কোনো বকেয়া ফি নেই</div>
        <button class="btn btn-primary" :disabled="!selectedFee.length || sending" @click="sendFee">পাঠান ({{ selectedFee.length }})</button>
      </div>
    </div>

    <!-- Sent list -->
    <div v-if="tab === 'sent' && sentList.length" class="card">
      <div class="card-header"><h3>পাঠানো নোটিফিকেশন</h3></div>
      <div class="card-body">
        <ul class="sent-list">
          <li v-for="n in sentList" :key="n.id">
            <span class="badge" :class="n.type === 'absence' ? 'badge-warning' : 'badge-danger'">{{ n.type === 'absence' ? 'অনুপস্থিতি' : 'ফি-বকেয়া' }}</span>
            <span>{{ n.title_bn }} — {{ n.body_bn }}</span>
            <span class="text-muted">{{ n.created_at?.slice(0, 10) }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const tab = ref<'absence' | 'fee' | 'sent'>('absence')
const classes = ref<any[]>([])
const classId = ref('' as string | number)
const absentList = ref<any[]>([])
const feeList = ref<any[]>([])
const selectedAbsence = ref<number[]>([])
const selectedFee = ref<number[]>([])
const sentList = ref<any[]>([])
const loading = ref(false)
const sending = ref(false)
const error = ref('')
const success = ref('')

const allSelected = computed(() => absentList.value.length > 0 && selectedAbsence.value.length === absentList.value.length)
const allFeeSelected = computed(() => feeList.value.length > 0 && selectedFee.value.length === feeList.value.length)

async function loadMeta() {
  try { const c = await api.get('/academic/classes'); classes.value = c.data?.data || [] } catch {}
}
async function loadAbsent() {
  absentList.value = []; selectedAbsence.value = []; error.value = ''
  if (!classId.value) return
  loading.value = true
  try {
    const r = await api.get(`/attendance?class_id=${classId.value}&per_page=200`)
    const recs = r.data?.data?.data || []
    absentList.value = recs.filter((x: any) => x.status === 'absent').map((x: any) => ({
      id: x.id, student_name: x.student?.name_bn ?? x.student_name ?? 'ছাত্র', date: x.date,
    }))
  } catch (e: any) { error.value = e?.response?.data?.message ?? 'লোড করা যায়নি' }
  finally { loading.value = false }
}
async function loadFee() {
  feeList.value = []; selectedFee.value = []
  loading.value = true
  try {
    const r = await api.get('/finance/fee-payments?per_page=200')
    const ps = r.data?.data?.data || []
    feeList.value = ps.filter((p: any) => !p.is_fully_paid).map((p: any) => ({
      id: p.id, student_name: p.student?.name_bn ?? 'ছাত্র', balance: p.balance,
    }))
  } catch (e: any) { error.value = e?.response?.data?.message ?? 'লোড করা যায়নি' }
  finally { loading.value = false }
}
async function loadSent() {
  loading.value = true
  try { const r = await api.get('/notifications?per_page=50'); sentList.value = r.data?.data?.data || [] } catch {}
  finally { loading.value = false }
}
function toggleAll(e: Event) { selectedAbsence.value = (e.target as HTMLInputElement).checked ? absentList.value.map((a) => a.id) : [] }
function toggleAllFee(e: Event) { selectedFee.value = (e.target as HTMLInputElement).checked ? feeList.value.map((f) => f.id) : [] }
function clearSelection() { selectedAbsence.value = []; selectedFee.value = []; error.value = ''; success.value = '' }
async function sendAbsence() {
  error.value = ''; success.value = ''; sending.value = true
  try {
    const r = await api.post('/notifications/absence', { attendance_record_ids: selectedAbsence.value, channel: 'in_app' })
    success.value = `${r.data?.sent ?? selectedAbsence.value.length}টি অনুপস্থিতি নোটিফিকেশন পাঠানো হয়েছে`
    selectedAbsence.value = []; await loadAbsent()
  } catch (e: any) { error.value = e?.response?.data?.message ?? 'পাঠানো যায়নি' }
  finally { sending.value = false }
}
async function sendFee() {
  error.value = ''; success.value = ''; sending.value = true
  try {
    const r = await api.post('/notifications/fee-due', { fee_payment_ids: selectedFee.value, channel: 'in_app' })
    success.value = `${r.data?.sent ?? selectedFee.value.length}টি ফি-বকেয়া নোটিফিকেশন পাঠানো হয়েছে`
    selectedFee.value = []; await loadFee()
  } catch (e: any) { error.value = e?.response?.data?.message ?? 'পাঠানো যায়নি' }
  finally { sending.value = false }
}

onMounted(() => { loadMeta(); loadAbsent(); loadFee() })
</script>

<style scoped>
.notif-page { padding: 1.5rem; }
.page-header h1 { font-family: 'Noto Sans Bengali', sans-serif; margin: 0; }
.tabs { display: flex; gap: 0.5rem; margin: 1rem 0; }
.tab { padding: 0.55rem 1rem; border: 1px solid var(--color-border); background: var(--color-bg); border-radius: 8px; cursor: pointer; font-family: 'Noto Sans Bengali', sans-serif; }
.tab.active { background: var(--color-primary); color: var(--color-text-on-primary); border-color: var(--color-primary); }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; margin-bottom: 1rem; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; font-size: 1rem; }
.card-body { padding: 1.25rem; }
.form-select { padding: 0.6rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg); }
.form-row { margin-bottom: 1rem; }
.select-all { margin-bottom: 0.5rem; font-family: 'Noto Sans Bengali', sans-serif; }
.pick-list { list-style: none; padding: 0; margin: 0 0 1rem; display: flex; flex-direction: column; gap: 0.4rem; }
.pick-list li label { display: flex; gap: 0.5rem; align-items: center; font-family: 'Noto Sans Bengali', sans-serif; }
.sent-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem; }
.sent-list li { display: flex; gap: 0.6rem; align-items: center; font-family: 'Noto Sans Bengali', sans-serif; flex-wrap: wrap; }
.badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; }
.badge-warning { background: #fff4e0; color: var(--color-warning); }
.badge-danger { background: #fce4e4; color: var(--color-error); }
.btn { padding: 0.6rem 1.1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-primary); color: var(--color-text-on-primary); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.alert { padding: 0.7rem 1rem; border-radius: 8px; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: #fce4e4; color: var(--color-error); }
.alert-success { background: #e8f5e9; color: var(--color-success); }
.text-muted { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.loading-state { padding: 3rem; text-align: center; font-family: 'Noto Sans Bengali', sans-serif; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
