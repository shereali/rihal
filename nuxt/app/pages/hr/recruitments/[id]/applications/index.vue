<template>
  <div class="page-wrapper">
    <div class="page-header">
      <div class="page-header-row">
        <div class="page-title-block">
          <div class="breadcrumb">
            <NuxtLink to="/hr">স্টাফ ও কর্মী</NuxtLink>
            <span class="sep">/</span>
            <NuxtLink :to="`/hr/recruitments/${recruitmentId}`">{{ recruitmentTitle }}</NuxtLink>
            <span class="sep">/</span>
            <span class="current">আবেদন</span>
          </div>
        </div>
        <button class="btn btn-primary" @click="showForm = !showForm"><icon name="plus" /> নতুন আবেদন</button>
      </div>
      <h1>আবেদন তালিকা</h1>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createApplication">
      <div class="form-heading"><div><h2>নতুন আবেদন</h2><p>আবেদনকারীর তথ্য ও যোগাযোগ উল্লেখ করুন</p></div><button type="button" class="close-btn" @click="showForm = false">×</button></div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-row"><div class="form-group wide"><label>আবেদনকারীর নাম *</label><input v-model="formData.guest_name_bn" class="form-control" required placeholder="আবেদনকারীর পূর্ণ নাম (বাংলা)" /></div></div>
      <div class="form-row"><div class="form-group"><label>নাম (ইংরেজি)</label><input v-model="formData.guest_name_en" class="form-control" placeholder="আবেদনকারীর পূর্ণ নাম (ইংরেজি)" /></div><div class="form-group"><label>ভোটার/জাতীয় পরিচয়পত্র নম্বর</label><input v-model="formData.nid_number" class="form-control" placeholder="১২ ডিজিটের নাম্বার" /></div></div>
      <div class="form-row"><div class="form-group"><label>ফোন</label><input v-model="formData.guest_phone" class="form-control" placeholder="০১৭১২৩৪৫৬৭৮" /></div><div class="form-group"><label>ইমেইল</label><input v-model="formData.guest_email" type="email" class="form-control" placeholder="চিঠি@গ্রহীতা.com" /></div></div>
      <div class="form-row"><div class="form-group wide"><label>ঠিকানা</label><input v-model="formData.guest_address_bn" class="form-control" placeholder="ঠিকানা..." /></div></div>
      <div class="form-group wide"><label>সংক্ষিপ্ত পরিচয় / আবেদনের কারণ</label><textarea v-model="formData.guest_bio_bn" class="form-control" rows="3" placeholder="আপনার পরিচয় ও এই পদের জন্য আবেদনের কারণ..."></textarea></div>
      <div class="form-row"><div class="form-group"><label>পাসপোর্ট/ছবির লিংক</label><input v-model="formData.photo_url" class="form-control" placeholder="ছবির URL লিংক..." /></div><div class="form-group"><label>অবস্থা</label><select v-model="formData.status" class="form-control"><option value="pending">মুলতুবি</option><option value="reviewed">পর্যালোচনা করা হয়েছে</option><option value="accepted">গ্রহণযোগ্য</option><option value="rejected">প্রত্যাখ্যাত</option></select></div></div>
      <div class="form-actions"><button class="btn btn-primary" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে...' : 'আবেদন জমা করুন' }}</button><button class="btn btn-ghost" type="button" @click="showForm = false">বাতিল</button></div>
    </form>

    <div v-if="loading" class="loading-overlay"><div class="spinner" /></div>
    <div v-else-if="!applications.length" class="empty-card"><div class="empty-icon"><icon name="file-text" /></div><h3>কোনো আবেদন নেই</h3><p>আবেদন আসা শুরু করবে</p></div>
    <div v-else class="applications-table">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>#</th><th>আবেদনকারীর নাম</th><th>ফোন</th><th>ইমেইল</th><th>আবেদনের তারিখ</th><th>অবস্থা</th><th>কর্ম</th></tr></thead>
          <tbody>
            <tr v-for="(app, idx) in applications" :key="app.id">
              <td class="text-center">{{ (currentPage - 1) * perPage + idx + 1 }}</td>
              <td class="applicant-name">{{ app.guest_name_bn || app.guest_name_en }}</td>
              <td>{{ app.guest_phone || '-' }}</td>
              <td>{{ app.guest_email || '-' }}</td>
              <td class="text-center">{{ formatDate(app.registered_at) }}</td>
              <td><span class="status-badge" :class="statusClass(app.status)">{{ formatStatus(app.status) }}</span></td>
              <td class="text-center"><button class="btn btn-ghost btn-sm" @click="viewApplication(app.id)"><icon name="eye" /> দেখুন</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const recruitmentId = computed(() => Number(route.params.id))
const recruitmentTitle = ref<string>('')
const applications = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const currentPage = ref(1)
const perPage = 15

interface ApplicationFormData {
  guest_name_bn: string; guest_name_en: string; nid_number: string
  guest_phone: string; guest_email: string; guest_address_bn: string
  guest_bio_bn: string; photo_url: string; status: string
}

const formData = reactive<ApplicationFormData>({
  guest_name_bn: '', guest_name_en: '', nid_number: '',
  guest_phone: '', guest_email: '', guest_address_bn: '',
  guest_bio_bn: '', photo_url: '', status: 'pending',
})

async function loadApplications() {
  loading.value = true
  try {
    const id = recruitmentId.value
    const r = await api.get(`/hr/recruitments/${id}/applications`)
    applications.value = r.data?.data?.data || r.data?.data || []
    recruitmentTitle.value = (await api.get(`/hr/recruitments/${id}`)).data?.data?.post_title_bn || 'নিয়োগ'
  } catch (e) { console.error('Failed to load:', e) }
  finally { loading.value = false }
}

async function createApplication() {
  saving.value = true; error.value = ''
  try {
    await api.post('/hr/applications', { ...formData, event_id: recruitmentId.value })
    showForm.value = false
    formData.guest_name_bn = ''; formData.guest_name_en = ''; formData.nid_number = ''
    formData.guest_phone = ''; formData.guest_email = ''; formData.guest_address_bn = ''
    formData.guest_bio_bn = ''; formData.photo_url = ''; formData.status = 'pending'
    await loadApplications()
  } catch (e: any) { error.value = e?.response?.data?.message || 'আবেদন জমা করা যায়নি' }
  finally { saving.value = false }
}

function viewApplication(id: number) { window.location.href = `/hr/recruitments/${recruitmentId.value}/applications/${id}` }
function formatDate(date: string) { return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) }
function formatStatus(status: string) { const map: Record<string,string> = { pending: 'মুলতুবি', reviewed: 'পর্যালোচনা করা হয়েছে', accepted: 'গ্রহণযোগ্য', rejected: 'প্রত্যাখ্যাত' }; return map[status] || status || '-' }
function statusClass(status: string) { const classes: Record<string,string> = { pending: 'status-pending', reviewed: 'status-reviewed', accepted: 'status-accepted', rejected: 'status-rejected' }; return classes[status] || 'status-pending' }

onMounted(loadApplications)
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
.applications-table { background: white; border: 1px solid var(--color-border-light); border-radius: 15px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.82rem; font-family: var(--font-bn); }
.table thead tr { background: rgba(0,0,0,0.03); }
.table th { padding: 0.6rem 0.8rem; text-align: left; font-weight: 600; color: var(--color-text-muted); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 1px solid var(--color-border-light); white-space: nowrap; }
.table td { padding: 0.55rem 0.8rem; border-bottom: 1px solid var(--color-border-light); color: var(--color-text); }
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr:hover { background: var(--color-bg-muted); }
.applicant-name { font-weight: 600; }
.text-center { text-align: center; }
.status-badge { padding: 0.2rem 0.55rem; border-radius: 99px; font-size: 0.65rem; font-weight: 600; white-space: nowrap; }
.status-pending { background: #fff0e4; color: #a05c35; }
.status-reviewed { background: #e3f2fa; color: #1a5276; }
.status-accepted { background: #e6f4ec; color: #19724a; }
.status-rejected { background: #fde8e8; color: #a03030; }
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