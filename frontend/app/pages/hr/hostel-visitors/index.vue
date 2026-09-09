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
        <button class="btn btn-primary" @click="showForm = !showForm">
          <icon name="plus" /> নতুন দর্শনার্থী
        </button>
      </div>
      <h1>হোস্টেল দর্শনার্থী তালিকা</h1>
    </div>

    <!-- Create Panel -->
    <form v-if="showForm" class="create-panel card" @submit.prevent="createVisitor">
      <div class="form-heading">
        <div>
          <h2>নতুন হোস্টেল দর্শনার্থী যোগ করুন</h2>
          <p>দর্শনার্থীর তথ্য ও যোগাযোগ উল্লেখ করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-row">
        <div class="form-group wide">
          <label>দর্শনার্থীর নাম (বাংলা) *</label>
          <input v-model="formData.name_bn" class="form-control" required placeholder="দর্শনার্থীর পূর্ণ নাম (বাংলা)" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>নাম (ইংরেজি)</label>
          <input v-model="formData.name_en" class="form-control" placeholder="Visitor Full Name" />
        </div>
        <div class="form-group">
          <label>ফোন নম্বর *</label>
          <input v-model="formData.phone" class="form-control" required placeholder="০১৭১২৩৪৫৬৭৮" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>ইমেইল</label>
          <input v-model="formData.email" type="email" class="form-control" placeholder="visitor@example.com" />
        </div>
        <div class="form-group">
          <label>আসার তারিখ</label>
          <input v-model="formData.expected_date" type="date" class="form-control" />
        </div>
      </div>
      <div class="form-group wide">
        <label>ঠিকানা</label>
        <textarea v-model="formData.address_bn" class="form-control" rows="2" placeholder="পূর্ণ ঠিকানা..."></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>দর্শনার্থীদের সংখ্যা</label>
          <input v-model.number="formData.guest_count" type="number" class="form-control" min="1" placeholder="১" />
        </div>
        <div class="form-group">
          <label>বিশেষ নোট / উদ্দেশ্য</label>
          <input v-model="formData.notes_bn" class="form-control" placeholder="পরিদর্শনের কারণ বা শিক্ষার্থী সম্পর্ক..." />
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'দর্শনার্থী যোগ করুন' }}
        </button>
        <button class="btn btn-ghost" type="button" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-overlay"><div class="spinner" /><p>দর্শনার্থী তালিকা লোড হচ্ছে...</p></div>

    <div v-else-if="!visitors.length" class="empty-card">
      <div class="empty-icon"><icon name="users" /></div>
      <h3>কোনো হোস্টেল দর্শনার্থী নেই</h3>
      <p>নতুন দর্শনার্থী যোগ করে রেকর্ড শুরু করুন</p>
      <button class="btn btn-primary" @click="showForm = true">প্রথম দর্শনার্থী যোগ করুন</button>
    </div>

    <div v-else class="visitors-table card">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>দর্শনার্থীর নাম</th>
              <th>ফোন</th>
              <th>ইমেইল</th>
              <th>আসার তারিখ</th>
              <th>সংখ্যা</th>
              <th>উদ্দেশ্য / নোট</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(vis, idx) in visitors" :key="vis.id">
              <td>{{ idx + 1 }}</td>
              <td class="visitor-name"><strong>{{ vis.name_bn || vis.name_en }}</strong></td>
              <td>{{ vis.phone || '—' }}</td>
              <td>{{ vis.email || '—' }}</td>
              <td>{{ vis.expected_date ? formatDate(vis.expected_date) : '—' }}</td>
              <td>{{ vis.guest_count || 1 }} জন</td>
              <td class="notes-cell">{{ vis.notes_bn || '—' }}</td>
              <td class="text-right">
                <button class="btn btn-outline btn-sm" @click="selectedVisitor = vis">
                  <icon name="eye" /> বিস্তারিত
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Visitor Detail Modal -->
    <div v-if="selectedVisitor" class="modal-overlay" @click.self="selectedVisitor = null">
      <div class="modal card">
        <div class="modal-header">
          <h3>দর্শনার্থীর তথ্য</h3>
          <button class="close-btn" @click="selectedVisitor = null">×</button>
        </div>
        <div class="modal-body">
          <div class="visitor-detail-grid">
            <div class="detail-item">
              <label>নাম (বাংলা)</label>
              <p><strong>{{ selectedVisitor.name_bn || '—' }}</strong></p>
            </div>
            <div class="detail-item">
              <label>নাম (ইংরেজি)</label>
              <p>{{ selectedVisitor.name_en || '—' }}</p>
            </div>
            <div class="detail-item">
              <label>ফোন নম্বর</label>
              <p>{{ selectedVisitor.phone || '—' }}</p>
            </div>
            <div class="detail-item">
              <label>ইমেইল</label>
              <p>{{ selectedVisitor.email || '—' }}</p>
            </div>
            <div class="detail-item">
              <label>আসার তারিখ</label>
              <p>{{ selectedVisitor.expected_date ? formatDate(selectedVisitor.expected_date) : '—' }}</p>
            </div>
            <div class="detail-item">
              <label>দর্শনার্থী সংখ্যা</label>
              <p>{{ selectedVisitor.guest_count || 1 }} জন</p>
            </div>
            <div class="detail-item full-width" v-if="selectedVisitor.address_bn">
              <label>ঠিকানা</label>
              <p>{{ selectedVisitor.address_bn }}</p>
            </div>
            <div class="detail-item full-width" v-if="selectedVisitor.notes_bn">
              <label>বিশেষ নোট / উদ্দেশ্য</label>
              <p class="notes-text">{{ selectedVisitor.notes_bn }}</p>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn btn-ghost" @click="selectedVisitor = null">বন্ধ করুন</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const visitors = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const selectedVisitor = ref<any>(null)
const error = ref('')

const formData = reactive({
  name_bn: '',
  name_en: '',
  phone: '',
  email: '',
  expected_date: '',
  address_bn: '',
  guest_count: 1,
  notes_bn: '',
})

async function loadVisitors() {
  loading.value = true
  try {
    const r = await api.get('/hr/hostel-visitors')
    visitors.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to load visitors:', e)
  } finally {
    loading.value = false
  }
}

async function createVisitor() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/hr/hostel-visitors', {
      ...formData,
      guest_count: formData.guest_count || undefined,
    })
    showForm.value = false
    formData.name_bn = ''
    formData.name_en = ''
    formData.phone = ''
    formData.email = ''
    formData.expected_date = ''
    formData.address_bn = ''
    formData.guest_count = 1
    formData.notes_bn = ''
    await loadVisitors()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'দর্শনার্থী যোগ করা যায়নি'
  } finally {
    saving.value = false
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

onMounted(loadVisitors)
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
  max-width: 500px;
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

.visitor-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem;
  margin-bottom: 1rem;
}

.visitor-detail-grid .full-width {
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

.notes-text {
  background: var(--color-bg);
  padding: 0.6rem 0.8rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border-light);
  font-size: 0.85rem !important;
}

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