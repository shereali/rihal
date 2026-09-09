<template>
  <div class="page-wrapper">
    <div class="page-header">
      <div class="page-header-row">
        <div class="page-title-block">
          <div class="breadcrumb">
            <NuxtLink to="/hr">স্টাফ ও কর্মী</NuxtLink>
            <span class="sep">/</span>
            <NuxtLink to="/hr/recruitments">নিয়োগ বিজ্ঞপ্তি</NuxtLink>
            <span class="sep">/</span>
            <span class="current">{{ recruitmentTitle }}</span>
          </div>
        </div>
        <div class="header-actions">
          <button class="btn btn-primary" @click="showForm = !showForm">
            <icon name="plus" /> নতুন আবেদন
          </button>
          <NuxtLink to="/hr/recruitments" class="btn btn-outline">
            <icon name="arrow-left" /> ফিরে যান
          </NuxtLink>
        </div>
      </div>
      <h1>নিয়োগ আবেদন তালিকা</h1>
    </div>

    <!-- Create Application Panel -->
    <form v-if="showForm" class="create-panel card" @submit.prevent="createApplication">
      <div class="form-heading">
        <div>
          <h2>নতুন আবেদন যোগ করুন</h2>
          <p>আবেদনকারীর তথ্য ও যোগাযোগ উল্লেখ করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>

      <div v-if="error" class="alert alert-error">{{ error }}</div>

      <div class="form-row">
        <div class="form-group wide">
          <label>আবেদনকারীর নাম (বাংলা) *</label>
          <input v-model="formData.guest_name_bn" class="form-control" required placeholder="আবেদনকারীর পূর্ণ নাম (বাংলা)" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>নাম (ইংরেজি)</label>
          <input v-model="formData.guest_name_en" class="form-control" placeholder="Full Name in English" />
        </div>
        <div class="form-group">
          <label>জাতীয় পরিচয়পত্র নম্বর</label>
          <input v-model="formData.nid_number" class="form-control" placeholder="NID নম্বর" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>ফোন নম্বর *</label>
          <input v-model="formData.guest_phone" class="form-control" required placeholder="০১৭১২৩৪৫৬৭৮" />
        </div>
        <div class="form-group">
          <label>ইমেইল</label>
          <input v-model="formData.guest_email" type="email" class="form-control" placeholder="applicant@email.com" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group wide">
          <label>ঠিকানা</label>
          <input v-model="formData.guest_address_bn" class="form-control" placeholder="বর্তমান ঠিকানা..." />
        </div>
      </div>
      <div class="form-group wide">
        <label>সংক্ষিপ্ত পরিচয় / আবেদনের কারণ</label>
        <textarea v-model="formData.guest_bio_bn" class="form-control" rows="2" placeholder="অভিজ্ঞতা ও সংক্ষিপ্ত পরিচয়..."></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>অবস্থা</label>
          <select v-model="formData.status" class="form-control">
            <option value="pending">মুলতুবি (Pending)</option>
            <option value="reviewed">পর্যালোচিত (Reviewed)</option>
            <option value="accepted">গৃহীত (Accepted)</option>
            <option value="rejected">প্রত্যাখ্যাত (Rejected)</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'আবেদন জমা করুন' }}
        </button>
        <button class="btn btn-ghost" type="button" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-overlay"><div class="spinner" /><p>আবেদন লোড হচ্ছে...</p></div>

    <div v-else-if="!applications.length" class="empty-card">
      <div class="empty-icon"><icon name="file-text" /></div>
      <h3>কোনো আবেদন নেই</h3>
      <p>এই বিজ্ঞপ্তির জন্য এখনও কোনো আবেদন জমা পড়েনি</p>
      <button class="btn btn-primary" @click="showForm = true">প্রথম আবেদন যোগ করুন</button>
    </div>

    <div v-else class="applications-table card">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>আবেদনকারীর নাম</th>
              <th>ফোন</th>
              <th>ইমেইল</th>
              <th>তারিখ</th>
              <th>অবস্থা</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(app, idx) in applications" :key="app.id">
              <td>{{ idx + 1 }}</td>
              <td class="applicant-name">
                <strong>{{ app.guest_name_bn || app.guest_name_en }}</strong>
                <small v-if="app.guest_name_en && app.guest_name_bn" class="text-muted d-block">{{ app.guest_name_en }}</small>
              </td>
              <td>{{ app.guest_phone || '—' }}</td>
              <td>{{ app.guest_email || '—' }}</td>
              <td>{{ formatDate(app.created_at || app.registered_at) }}</td>
              <td>
                <span class="status-badge" :class="statusClass(app.status)">
                  {{ formatStatus(app.status) }}
                </span>
              </td>
              <td class="text-right">
                <button class="btn btn-outline btn-sm" @click="openViewModal(app)">
                  <icon name="eye" /> বিস্তারিত
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Application Detail Modal -->
    <div v-if="selectedApp" class="modal-overlay" @click.self="selectedApp = null">
      <div class="modal card">
        <div class="modal-header">
          <h3>আবেদনকারীর বিস্তারিত তথ্য</h3>
          <button class="close-btn" @click="selectedApp = null">×</button>
        </div>
        <div class="modal-body">
          <div class="app-detail-grid">
            <div class="detail-item">
              <label>নাম (বাংলা)</label>
              <p><strong>{{ selectedApp.guest_name_bn || '—' }}</strong></p>
            </div>
            <div class="detail-item">
              <label>নাম (ইংরেজি)</label>
              <p>{{ selectedApp.guest_name_en || '—' }}</p>
            </div>
            <div class="detail-item">
              <label>ফোন নম্বর</label>
              <p>{{ selectedApp.guest_phone || '—' }}</p>
            </div>
            <div class="detail-item">
              <label>ইমেইল</label>
              <p>{{ selectedApp.guest_email || '—' }}</p>
            </div>
            <div class="detail-item">
              <label>জাতীয় পরিচয়পত্র নং</label>
              <p>{{ selectedApp.nid_number || '—' }}</p>
            </div>
            <div class="detail-item">
              <label>বর্তমান অবস্থা</label>
              <p>
                <span class="status-badge" :class="statusClass(selectedApp.status)">
                  {{ formatStatus(selectedApp.status) }}
                </span>
              </p>
            </div>
            <div class="detail-item full-width" v-if="selectedApp.guest_address_bn">
              <label>ঠিকানা</label>
              <p>{{ selectedApp.guest_address_bn }}</p>
            </div>
            <div class="detail-item full-width" v-if="selectedApp.guest_bio_bn">
              <label>পরিচিতি / কারণ</label>
              <p class="bio-text">{{ selectedApp.guest_bio_bn }}</p>
            </div>
          </div>

          <div class="status-change-box">
            <label>অবস্থা পরিবর্তন করুন:</label>
            <div class="status-buttons">
              <button class="btn btn-sm btn-outline" @click="updateStatus(selectedApp, 'pending')">মুলতুবি</button>
              <button class="btn btn-sm btn-outline" @click="updateStatus(selectedApp, 'reviewed')">পর্যালোচিত</button>
              <button class="btn btn-sm btn-success" @click="updateStatus(selectedApp, 'accepted')">গ্রহণযোগ্য</button>
              <button class="btn btn-sm btn-danger" @click="updateStatus(selectedApp, 'rejected')">প্রত্যাখ্যাত</button>
            </div>
          </div>
        </div>
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
const recruitmentTitle = ref<string>('নিয়োগ')
const applications = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const selectedApp = ref<any>(null)
const error = ref('')

const formData = reactive({
  guest_name_bn: '',
  guest_name_en: '',
  nid_number: '',
  guest_phone: '',
  guest_email: '',
  guest_address_bn: '',
  guest_bio_bn: '',
  photo_url: '',
  status: 'pending',
})

async function loadApplications() {
  loading.value = true
  try {
    const id = recruitmentId.value
    const [appsRes, recRes] = await Promise.all([
      api.get(`/hr/recruitments/${id}/applications`).catch(() => ({ data: { data: [] } })),
      api.get(`/hr/recruitments/${id}`).catch(() => ({ data: { data: {} } })),
    ])
    applications.value = appsRes.data?.data?.data || appsRes.data?.data || []
    recruitmentTitle.value = recRes.data?.data?.post_title_bn || recRes.data?.data?.post_title_en || 'নিয়োগ'
  } catch (e) {
    console.error('Failed to load:', e)
  } finally {
    loading.value = false
  }
}

async function createApplication() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/hr/applications', { ...formData, event_id: recruitmentId.value })
    showForm.value = false
    formData.guest_name_bn = ''
    formData.guest_name_en = ''
    formData.nid_number = ''
    formData.guest_phone = ''
    formData.guest_email = ''
    formData.guest_address_bn = ''
    formData.guest_bio_bn = ''
    formData.status = 'pending'
    await loadApplications()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'আবেদন জমা করা যায়নি'
  } finally {
    saving.value = false
  }
}

function openViewModal(app: any) {
  selectedApp.value = app
}

async function updateStatus(app: any, newStatus: string) {
  try {
    await api.put(`/hr/applications/${app.id}`, { status: newStatus })
    app.status = newStatus
    if (selectedApp.value && selectedApp.value.id === app.id) {
      selectedApp.value.status = newStatus
    }
  } catch (e) {
    console.error(e)
  }
}

function formatDate(date: string) {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return date
  }
}

function formatStatus(status: string) {
  const map: Record<string, string> = {
    pending: 'মুলতুবি',
    reviewed: 'পর্যালোচিত',
    accepted: 'গ্রহণযোগ্য',
    rejected: 'প্রত্যাখ্যাত',
  }
  return map[status] || status || '-'
}

function statusClass(status: string) {
  if (status === 'accepted') return 'status-accepted'
  if (status === 'reviewed') return 'status-reviewed'
  if (status === 'rejected') return 'status-rejected'
  return 'status-pending'
}

onMounted(loadApplications)
</script>

<style scoped>
.page-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.create-panel {
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border: 1px solid var(--color-primary-light);
}

.form-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--color-border-light);
}

.form-heading h2 { font-size: 1.15rem; margin: 0; }
.form-heading p { font-size: 0.8rem; color: var(--color-text-light); margin: 0.2rem 0 0; }

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-light);
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.form-group.wide {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 0.82rem;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.9rem;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
  justify-content: flex-end;
}

.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.table th, .table td { padding: 0.85rem 1rem; text-align: left; border-bottom: 1px solid var(--color-border-light); }
.table th { background: rgba(0, 0, 0, 0.02); font-weight: 600; color: var(--color-text-light); font-size: 0.8rem; }
.table-hover tr:hover { background: rgba(0, 0, 0, 0.015); }

.status-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
}

.status-pending { background: rgba(245, 158, 11, 0.15); color: #b45309; }
.status-reviewed { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.status-accepted { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  padding: 1rem;
}

.modal {
  width: 100%;
  max-width: 540px;
  background: var(--color-bg-card);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
}

.modal-header h3 { margin: 0; font-size: 1.1rem; }
.modal-body { padding: 1.25rem; }

.app-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem;
  margin-bottom: 1.25rem;
}

.app-detail-grid .full-width {
  grid-column: 1 / -1;
}

.detail-item label {
  font-size: 0.75rem;
  color: var(--color-text-light);
  display: block;
  margin-bottom: 0.15rem;
}

.detail-item p {
  margin: 0;
  font-size: 0.9rem;
}

.bio-text {
  background: var(--color-bg);
  padding: 0.6rem 0.8rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border-light);
  font-size: 0.85rem !important;
}

.status-change-box {
  border-top: 1px solid var(--color-border-light);
  padding-top: 1rem;
}

.status-change-box label {
  font-size: 0.82rem;
  font-weight: 600;
  display: block;
  margin-bottom: 0.5rem;
}

.status-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-success { background: #10b981; color: #fff; }
.btn-danger { background: #ef4444; color: #fff; }

.btn {
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
}

.btn-primary { background: var(--color-primary); color: #fff; }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-ghost { background: transparent; color: var(--color-text); }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }

.card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
}

.empty-card, .loading-overlay {
  padding: 3rem;
  text-align: center;
  color: var(--color-text-light);
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
}

.empty-icon { font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0.5rem; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 0.75rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 0.65rem 0.9rem; border-radius: var(--radius-sm); margin-bottom: 1rem; font-size: 0.85rem; }
.alert-error { background: #fce4e4; color: var(--color-error); }
</style>