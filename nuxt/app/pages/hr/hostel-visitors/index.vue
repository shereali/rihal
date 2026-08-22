<template>
  <div class="page-wrapper">
    <div class="page-header">
      <div class="page-header-row">
        <div class="page-title-block">
          <div class="breadcrumb">
            <NuxtLink to="/hr">স্টাফ ও কর্মী</NuxtLink>
            <span class="sep">/</span>
            <span class="current">হোস্টেল দর্শনার্থী</span>
          </div>
        </div>
        <button class="btn btn-primary" @click="showForm = !showForm"><icon name="plus" /> নতুন দর্শনার্থী</button>
      </div>
      <h1>হোস্টেল দর্শনার্থী তালিকা</h1>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createVisitor">
      <div class="form-heading"><div><h2>নতুন হোস্টেল দর্শনার্থী যোগ করুন</h2><p>দর্শনার্থীর তথ্য ও যোগাযোগ উল্লেখ করুন</p></div><button type="button" class="close-btn" @click="showForm = false">×</button></div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-row"><div class="form-group wide"><label>দর্শনার্থীর নাম *</label><input v-model="formData.name_bn" class="form-control" required placeholder="দর্শনার্থীর পূর্ণ নাম (বাংলা)" /></div></div>
      <div class="form-row"><div class="form-group"><label>নাম (ইংরেজি)</label><input v-model="formData.name_en" class="form-control" placeholder="দর্শনার্থীর পূর্ণ নাম (ইংরেজি)" /></div><div class="form-group"><label>ফোন</label><input v-model="formData.phone" class="form-control" placeholder="০১৭১২৩৪৫৬৭৮" /></div></div>
      <div class="form-row"><div class="form-group"><label>ইমেইল</label><input v-model="formData.email" type="email" class="form-control" placeholder="চিঠি@গ্রহীতা.com" /></div><div class="form-group"><label>আসার তারিখ</label><input v-model="formData.expected_date" type="date" class="form-control" /></div></div>
      <div class="form-group wide"><label>ঠিকানা</label><textarea v-model="formData.address_bn" class="form-control" rows="2" placeholder="ঠিকানা..."></textarea></div>
      <div class="form-row"><div class="form-group"><label>দর্শনার্থীদের সংখ্যা</label><input v-model.number="formData.guest_count" type="number" class="form-control" min="1" placeholder="১" /></div><div class="form-group"><label>বিশেষ নোট</label><input v-model="formData.notes_bn" class="form-control" placeholder="বিশেষ দর্শনার্থী বা উদ্দেশ্য..." /></div></div>
      <div class="form-actions"><button class="btn btn-primary" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে...' : 'দর্শনার্থী যোগ করুন' }}</button><button class="btn btn-ghost" type="button" @click="showForm = false">বাতিল</button></div>
    </form>

    <div v-if="loading" class="loading-overlay"><div class="spinner" /></div>
    <div v-else-if="!visitors.length" class="empty-card"><div class="empty-icon"><icon name="users" /></div><h3>কোনো হোস্টেল দর্শনার্থী নেই</h3><p>দর্শনার্থী যোগ করে শুরু করুন</p></div>
    <div v-else class="visitors-table">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>#</th><th>দর্শনার্থীর নাম</th><th>ফোন</th><th>ইমেইল</th><th>আসার তারিখ (আশা)</th><th>দর্শনার্থী সংখ্যা</th><th>বিশেষ নোট</th><th>কর্ম</th></tr></thead>
          <tbody>
            <tr v-for="(vis, idx) in visitors" :key="vis.id">
              <td class="text-center">{{ (currentPage - 1) * perPage + idx + 1 }}</td>
              <td class="visitor-name">{{ vis.name_bn || vis.name_en }}</td>
              <td>{{ vis.phone || '-' }}</td>
              <td>{{ vis.email || '-' }}</td>
              <td class="text-center">{{ vis.expected_date ? formatDate(vis.expected_date) : '-' }}</td>
              <td class="text-center">{{ vis.guest_count || 1 }}</td>
              <td class="notes-cell">{{ vis.notes_bn || '-' }}</td>
              <td class="text-center"><button class="btn btn-ghost btn-sm" @click="viewVisitor(vis.id)"><icon name="eye" /> দেখুন</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const visitors = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const currentPage = ref(1)
const perPage = 15

interface VisitorFormData {
  name_bn: string; name_en: string; phone: string; email: string
  expected_date: string; address_bn: string; guest_count: number; notes_bn: string
}

const formData = reactive<VisitorFormData>({
  name_bn: '', name_en: '', phone: '', email: '',
  expected_date: '', address_bn: '', guest_count: 1, notes_bn: '',
})

async function loadVisitors() {
  loading.value = true
  try {
    const r = await api.get('/hr/hostel-visitors')
    visitors.value = r.data?.data?.data || r.data?.data || []
  } catch (e) { console.error('Failed to load visitors:', e) }
  finally { loading.value = false }
}

async function createVisitor() {
  saving.value = true; error.value = ''
  try {
    await api.post('/hr/hostel-visitors', { ...formData, guest_count: formData.guest_count || undefined })
    showForm.value = false
    formData.name_bn = ''; formData.name_en = ''; formData.phone = ''; formData.email = ''
    formData.expected_date = ''; formData.address_bn = ''; formData.guest_count = 1; formData.notes_bn = ''
    await loadVisitors()
  } catch (e: any) { error.value = e?.response?.data?.message || 'দর্শনার্থী যোগ করা যায়নি' }
  finally { saving.value = false }
}

function viewVisitor(id: number) { window.location.href = `/hr/hostel-visitors/${id}` }
function formatDate(date: string) { return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) }

onMounted(loadVisitors)
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding-bottom: 2rem; }
.page-header { margin-bottom: 1.2rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem; }
.breadcrumb { display: flex; align-items: center; gap: 0.3rem; font-size: 0.78rem; color: var(--color-text-muted); }
.breadcrumb .sep { color: var(--color-text-muted); }
.breadcrumb .current { color: var(--color-text); font-weight: 500; }
.breadcrumb a { color: var(--color-primary); text-decoration: none; }
.page-header h1 { font-size: 1.4rem; color: var(--color-primary); margin: 0; font-family: var(--font-bn); }
.create-panel { margin-bottom: 1.2rem; border: 1px solid var(--color-primary-100); }
.form-heading { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; }
.form-heading h2 { font-size: 1.05rem; font-family: var(--font-bn); margin: 0; }
.close-btn { background: none; border: none; font-size: 1.5rem; color: var(--color-text-muted); cursor: pointer; padding: 0; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 0.7rem; }
.form-group { display: flex; flex-direction: column; gap: 0.3rem; }
.form-group.wide { grid-column: span 2; }
.form-group label { font-size: 0.78rem; font-weight: 600; color: var(--color-text-muted); font-family: var(--font-bn); text-transform: uppercase; letter-spacing: 0.02em; }
.form-control { padding: 0.6rem 0.8rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 0.85rem; font-family: var(--font-bn); background: var(--color-bg); color: var(--color-text); outline: none; }
.form-control:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary-100); }
.form-actions { display: flex; gap: 0.6rem; margin-top: 0.6rem; justify-content: flex-end; }
.alert { padding: 0.6rem 0.9rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 0.8rem; }
.alert-error { background: #fde8e8; color: #a03030; border: 1px solid #f5c6c6; }
.visitors-table { background: white; border: 1px solid var(--color-border-light); border-radius: 15px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.82rem; font-family: var(--font-bn); }
.table thead tr { background: rgba(0,0,0,0.03); }
.table th { padding: 0.6rem 0.8rem; text-align: left; font-weight: 600; color: var(--color-text-muted); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 1px solid var(--color-border-light); white-space: nowrap; }
.table td { padding: 0.55rem 0.8rem; border-bottom: 1px solid var(--color-border-light); color: var(--color-text); }
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr:hover { background: var(--color-bg-muted); }
.visitor-name { font-weight: 600; }
.text-center { text-align: center; }
.notes-cell { max-width: 160px; font-size: 0.78rem; color: var(--color-text-muted); }
.btn { padding: 0.5rem 0.9rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: var(--font-bn); font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem; border: 1px solid transparent; transition: all 0.15s; }
.btn-primary { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.btn-primary:hover:not(:disabled) { opacity: 0.9; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-ghost { background: transparent; border: none; color: var(--color-text-muted); }
.btn-ghost:hover { color: var(--color-text); background: var(--color-bg-muted); }
.btn-sm { padding: 0.35rem 0.7rem; font-size: 0.72rem; }
.empty-card { text-align: center; padding: 3rem 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.6rem; }
.empty-icon { width: 48px; height: 48px; color: var(--color-text-muted); margin-bottom: 0.3rem; }
.empty-card h3 { font-size: 1rem; color: var(--color-text); margin: 0; font-family: var(--font-bn); }
.empty-card p { font-size: 0.82rem; color: var(--color-text-muted); margin: 0; font-family: var(--font-bn); }
.loading-overlay { display: flex; justify-content: center; padding: 3rem 0; }
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
</style>